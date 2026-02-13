<?php
// app/Http/Controllers/MenuController.php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        // dd(Menu::all());
        $query = Menu::with('ingredients');

        // Search filter
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->has('status') && in_array($request->get('status'), ['active', 'inactive'])) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        $menus = $query->latest()->paginate(20);

        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        return view('menus.create', compact('ingredients'));
    }

    public function store(Request $request)
    {
        // Validasi
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        // Handle image upload di luar transaksi dulu
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if ($image->isValid()) {
                $imageName = 'menu_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('menus', $imageName, 'public');

                if (!$imagePath) {
                    return redirect()->back()
                        ->with('error', 'Gagal mengupload gambar')
                        ->withInput();
                }

                Log::info('Image uploaded', ['path' => $imagePath]);
            }
        }

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Generate unique code DI DALAM transaksi dengan locking
            $code = Menu::generateUniqueCode();

            // Prepare data for menu creation
            $menuData = [
                'name' => $validated['name'],
                'code' => $code,
                'price' => $validated['price'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
                'image' => $imagePath,
            ];

            // Create menu
            $menu = Menu::create($menuData);

            // Attach ingredients
            foreach ($request->ingredients as $ingredientData) {
                $menu->ingredients()->attach($ingredientData['id'], [
                    'quantity' => $ingredientData['quantity']
                ]);
            }

            DB::commit();

            return redirect()->route('menus.index')
                ->with('success', 'Menu berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus image jika sudah terupload tapi terjadi error
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info('Rollback: Image deleted', ['path' => $imagePath]);
            }

            // Log error untuk debugging
            Log::error('Error creating menu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['_token', '_method'])
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menambahkan menu: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Menu $menu)
    {
        // Load ingredients dan saleItems DENGAN eager loading relasi sale dan user
        $menu->load([
            'ingredients',
            'saleItems' => function($query) {
                // Eager load relasi sale dan user dari sale
                $query->with(['sale' => function($q) {
                    $q->withTrashed(); // Include soft deleted sales
                }, 'sale.user']) // Eager load user melalui relasi sale
                ->whereHas('sale') // Hanya ambil saleItems yang memiliki sale
                ->orderBy('created_at', 'desc')
                ->limit(10); // Batasi 10 transaksi terakhir
            }
        ]);

        // Hitung statistik dengan query terpisah untuk akurasi
        $totalSold = $menu->saleItems()
            ->whereHas('sale') // Hanya hitung yang memiliki sale
            ->sum('quantity');

        $totalRevenue = $menu->saleItems()
            ->whereHas('sale') // Hanya hitung yang memiliki sale
            ->sum('subtotal');

        return view('menus.show', compact('menu', 'totalSold', 'totalRevenue'));
    }

    public function edit(Menu $menu)
    {
        $menu->load('ingredients');
        $ingredients = Ingredient::orderBy('name')->get();

        $currentIngredients = [];
        foreach ($menu->ingredients as $ingredient) {
            $currentIngredients[$ingredient->id] = $ingredient->pivot->quantity;
        }

        $ingredientCounter = count($currentIngredients);

        return view('menus.edit', compact('menu', 'ingredients', 'currentIngredients', 'ingredientCounter'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            $menuData = [
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
            ];

            // Handle image upload dengan lebih baik
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                    Storage::disk('public')->delete($menu->image);
                    Log::info('Old image deleted', ['path' => $menu->image]);
                }

                // Upload new image
                $image = $request->file('image');

                // Validasi file
                if ($image->isValid()) {
                    $imageName = 'menu_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('menus', $imageName, 'public');

                    if ($imagePath) {
                        $menuData['image'] = $imagePath;
                        Log::info('New image uploaded', ['path' => $imagePath]);
                    } else {
                        Log::error('Failed to upload image');
                    }
                }
            }
            // Handle remove image jika ada parameter remove_image
            elseif ($request->has('remove_image') && $request->remove_image == '1') {
                if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                    Storage::disk('public')->delete($menu->image);
                    Log::info('Image removed', ['path' => $menu->image]);
                }
                $menuData['image'] = null;
            }

            // Update menu (code tidak berubah saat update)
            $menu->update($menuData);

            // Sync ingredients
            $ingredientsData = [];
            foreach ($request->ingredients as $ingredientData) {
                $ingredientsData[$ingredientData['id']] = [
                    'quantity' => $ingredientData['quantity']
                ];
            }

            $menu->ingredients()->sync($ingredientsData);

            DB::commit();

            return redirect()->route('menus.index')
                ->with('success', 'Menu berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating menu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'menu_id' => $menu->id
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui menu: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Menu $menu)
    {
        // Log untuk debugging
        Log::info('Delete menu request received', [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'is_ajax' => request()->ajax(),
            'expects_json' => request()->expectsJson(),
            'has_x_requested_with' => request()->hasHeader('X-Requested-With'),
            'content_type' => request()->header('Content-Type'),
        ]);

        DB::beginTransaction();

        try {
            // Check if menu has sales
            $hasSales = $menu->saleItems()->exists();

            Log::info('Checking sales items', [
                'menu_id' => $menu->id,
                'has_sales' => $hasSales,
            ]);

            if ($hasSales) {
                DB::rollBack();

                $errorMessage = 'Tidak dapat menghapus menu yang sudah memiliki riwayat penjualan';

                Log::warning('Cannot delete menu with sales', [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                ]);

                if (request()->expectsJson() || request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMessage
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', $errorMessage);
            }

            Log::info('Proceeding with delete', ['menu_id' => $menu->id]);

            // Delete image if exists
            if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                Storage::disk('public')->delete($menu->image);
                Log::info('Image deleted', ['image_path' => $menu->image]);
            }

            // Detach ingredients
            $menu->ingredients()->detach();
            Log::info('Ingredients detached', ['menu_id' => $menu->id]);

            // Delete menu
            $menuName = $menu->name;
            $menu->delete();

            Log::info('Menu deleted successfully', [
                'menu_id' => $menu->id,
                'menu_name' => $menuName,
            ]);

            DB::commit();

            if (request()->expectsJson() || request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Menu "' . $menuName . '" berhasil dihapus'
                ]);
            }

            return redirect()->route('menus.index')
                ->with('success', 'Menu "' . $menuName . '" berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting menu', [
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (request()->expectsJson() || request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal menghapus menu: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menghapus menu: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Menu $menu)
    {
        $menu->is_active = !$menu->is_active;
        $menu->save();

        $status = $menu->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Menu berhasil {$status}");
    }

    public function getMenuDetails(Request $request)
    {
        $menu = Menu::with('ingredients')->find($request->id);

        if (!$menu) {
            return response()->json(['error' => 'Menu tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $menu->id,
            'name' => $menu->name,
            'price' => $menu->price,
            'formatted_price' => $menu->formatted_price,
            'ingredients' => $menu->ingredients->map(function ($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'unit' => $ingredient->unit,
                    'quantity' => $ingredient->pivot->quantity,
                    'stock' => $ingredient->stock,
                    'formatted_stock' => $ingredient->formatted_stock,
                ];
            }),
        ]);
    }
}
