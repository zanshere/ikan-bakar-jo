<?php
// app/Http/Controllers/IngredientController.php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::with('menus');

        // Search filter
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        // Stock status filter
        if ($request->has('stock_status')) {
            switch ($request->get('stock_status')) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
                case 'sufficient':
                    $query->sufficientStock();
                    break;
            }
        }

        // Unit filter
        if ($request->has('unit')) {
            $query->where('unit', $request->get('unit'));
        }

        $ingredients = $query->latest()->paginate(20);

        // Get unique units for filter
        $units = Ingredient::distinct()->pluck('unit');

        return view('ingredients.index', compact('ingredients', 'units'));
    }

    public function create()
    {
        return view('ingredients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            // Generate code
            $ingredient = new Ingredient();
            $validated['code'] = $ingredient->generateCode();

            Ingredient::create($validated);

            return redirect()->route('ingredients.index')
                ->with('success', 'Bahan baku berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan bahan baku: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Ingredient $ingredient)
    {
        $ingredient->load('menus', 'restockItems.restock');

        // Calculate statistics
        $totalRestocked = $ingredient->restockItems()->sum('quantity');
        $totalRestockCost = $ingredient->restockItems()->sum('subtotal');

        // Get usage in menus
        $menuUsage = $ingredient->menus()->withPivot('quantity')->get();

        return view('ingredients.show', compact(
            'ingredient',
            'totalRestocked',
            'totalRestockCost',
            'menuUsage'
        ));
    }

    public function edit(Ingredient $ingredient)
    {
        return view('ingredients.edit', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $ingredient->update($validated);

            return redirect()->route('ingredients.index')
                ->with('success', 'Bahan baku berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui bahan baku: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Ingredient $ingredient)
    {
        // Debug log
        Log::info('Attempting to delete ingredient', [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'is_ajax' => request()->ajax(),
            'wants_json' => request()->wantsJson(),
            'headers' => request()->headers->all()
        ]);

        // Memulai transaksi database
        DB::beginTransaction();

        try {
            // Mengecek apakah bahan baku digunakan dalam menu
            if ($ingredient->menus()->exists()) {
                $menuCount = $ingredient->menus()->count();
                $message = "Tidak dapat menghapus bahan baku karena digunakan dalam {$menuCount} menu. Harap hapus terlebih dahulu dari menu yang menggunakan bahan ini.";

                Log::warning('Cannot delete ingredient - used in menus', [
                    'ingredient_id' => $ingredient->id,
                    'menu_count' => $menuCount
                ]);

                // Mengecek apakah request adalah AJAX
                if (request()->ajax() || request()->wantsJson()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                } else {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', $message);
                }
            }

            // Mengecek apakah bahan baku memiliki riwayat restock
            if ($ingredient->restockItems()->exists()) {
                $restockCount = $ingredient->restockItems()->count();
                $message = "Tidak dapat menghapus bahan baku yang memiliki {$restockCount} riwayat restock. Data historis diperlukan untuk laporan dan audit.";

                Log::warning('Cannot delete ingredient - has restock history', [
                    'ingredient_id' => $ingredient->id,
                    'restock_count' => $restockCount
                ]);

                // Mengecek apakah request adalah AJAX
                if (request()->ajax() || request()->wantsJson()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                } else {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', $message);
                }
            }

            // Menghapus bahan baku
            $ingredientName = $ingredient->name;
            $ingredientId = $ingredient->id;
            $ingredient->delete();

            // Commit transaksi
            DB::commit();

            Log::info('Ingredient deleted successfully', [
                'id' => $ingredientId,
                'name' => $ingredientName
            ]);

            // Mengecek apakah request adalah AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Bahan baku '{$ingredientName}' berhasil dihapus",
                    'ingredient_id' => $ingredientId
                ]);
            } else {
                return redirect()->route('ingredients.index')
                    ->with('success', "Bahan baku '{$ingredientName}' berhasil dihapus");
            }
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();

            Log::error('Failed to delete ingredient', [
                'id' => $ingredient->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mengecek apakah request adalah AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus bahan baku: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal menghapus bahan baku: ' . $e->getMessage());
            }
        }
    }

    public function adjustStock(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'type' => 'required|in:increase,decrease,set',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $oldStock = $ingredient->stock;

            switch ($validated['type']) {
                case 'increase':
                    $ingredient->increaseStock($validated['quantity']);
                    break;

                case 'decrease':
                    $ingredient->decreaseStock($validated['quantity']);
                    break;

                case 'set':
                    $ingredient->stock = $validated['quantity'];
                    $ingredient->save();
                    break;
            }

            // Log stock adjustment
            // You can create a StockAdjustment model here if needed

            return redirect()->back()
                ->with('success', 'Stock berhasil disesuaikan')
                ->with('stock_details', [
                    'old' => $oldStock,
                    'new' => $ingredient->stock,
                    'difference' => $ingredient->stock - $oldStock,
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyesuaikan stock: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function getIngredients(Request $request)
    {
        $ingredients = Ingredient::when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($ingredients);
    }

    public function getIngredientDetails(Request $request)
    {
        $ingredient = Ingredient::find($request->id);

        if (!$ingredient) {
            return response()->json(['error' => 'Bahan baku tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'code' => $ingredient->code,
            'unit' => $ingredient->unit,
            'stock' => $ingredient->stock,
            'price' => $ingredient->price,
            'formatted_stock' => $ingredient->formatted_stock,
            'formatted_price' => $ingredient->formatted_price,
            'stock_status' => $ingredient->stock_status,
        ]);
    }
}
