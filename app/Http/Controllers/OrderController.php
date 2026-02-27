<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Menu;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['items.menu', 'items.sauce']);

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal mulai
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest('order_date')->latest('id')->paginate(20);

        // Hitung statistik untuk user
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $acceptedOrders = Order::where('status', 'accepted')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $rejectedOrders = Order::where('status', 'rejected')->count();

        return view('orders.index', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'acceptedOrders',
            'completedOrders',
            'rejectedOrders'
        ));
    }

    /**
     * Show the form for creating a new order (for users).
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get all active main menus with their ingredients and available sauces
        $menus = Menu::where('type', Menu::TYPE_MAIN)
            ->where('is_active', true)
            ->with([
                'ingredients' => function ($query) {
                    $query->where('stock', '>', 0);
                },
                'availableSauces' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['ingredients' => function ($q) {
                            $q->where('stock', '>', 0);
                        }]);
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($menu) {
                // Format price
                $menu->formatted_price = 'Rp ' . number_format($menu->price, 0, ',', '.');

                // Add category if not exists
                if (!isset($menu->category) || empty($menu->category)) {
                    $menu->category = 'lainnya';
                }

                // Check if menu is available (all ingredients have stock)
                $menu->is_available = $menu->ingredients->every(function ($ingredient) {
                    return $ingredient->stock > 0;
                }) && $menu->ingredients->count() > 0;

                // Pastikan availableSauces tidak null dan merupakan collection
                if ($menu->availableSauces === null) {
                    $menu->availableSauces = collect([]);
                }

                // Check each sauce availability
                foreach ($menu->availableSauces as $sauce) {
                    $sauce->is_available = $sauce->ingredients->every(function ($ingredient) {
                        return $ingredient->stock > 0;
                    }) && $sauce->ingredients->count() > 0;

                    // Format sauce price
                    $sauce->formatted_price = 'Rp ' . number_format($sauce->price, 0, ',', '.');

                    // Format stock for display
                    $sauce->formatted_stock = $sauce->stock . ' ' . $sauce->unit;

                    // Pastikan pivot ada
                    if (!isset($sauce->pivot)) {
                        $sauce->pivot = (object)['is_default' => false];
                    }
                }

                return $menu;
            });

        return view('orders.create', compact('menus'));
    }

    /**
     * Store a newly created order in storage (by user).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Log::info('Order store request received:', $request->all());

        // Parse items from request
        $items = $request->items;
        if (is_string($items)) {
            $items = json_decode($items, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error:', [
                    'error' => json_last_error_msg(),
                    'items_string' => $request->items
                ]);
                return $this->jsonOrRedirect($request, false, 'Format data items tidak valid: ' . json_last_error_msg());
            }
        }

        // Validate items array
        if (!is_array($items) || empty($items)) {
            Log::error('Items is not array or empty:', ['items' => $items]);
            return $this->jsonOrRedirect($request, false, 'Keranjang tidak boleh kosong. Silakan pilih menu terlebih dahulu.');
        }

        // Build validation rules
        $validationRules = [
            'notes' => 'nullable|string|max:500',
        ];
        foreach ($items as $index => $item) {
            $validationRules["items.{$index}.menu_id"] = 'required|exists:menus,id';
            $validationRules["items.{$index}.sauce_id"] = 'required|exists:menus,id';
            $validationRules["items.{$index}.quantity"] = 'required|integer|min:1|max:999';
        }

        $validationMessages = [
            'notes.max' => 'Catatan maksimal 500 karakter',
            'items.*.menu_id.required' => 'Menu harus dipilih',
            'items.*.menu_id.exists' => 'Menu tidak ditemukan',
            'items.*.sauce_id.required' => 'Saus harus dipilih untuk setiap menu',
            'items.*.sauce_id.exists' => 'Saus tidak ditemukan',
            'items.*.quantity.required' => 'Jumlah harus diisi',
            'items.*.quantity.integer' => 'Jumlah harus berupa angka bulat',
            'items.*.quantity.min' => 'Jumlah minimal 1',
            'items.*.quantity.max' => 'Jumlah maksimal 999',
        ];

        $requestData = $request->all();
        $requestData['items'] = $items;

        $validator = Validator::make($requestData, $validationRules, $validationMessages);
        if ($validator->fails()) {
            Log::error('Order validation failed:', $validator->errors()->toArray());
            return $this->jsonOrRedirect($request, false, 'Validasi gagal', $validator->errors());
        }

        // --- VALIDASI RELASI DAN KETERSEDIAAN SAUS ---
        // Ambil semua ID menu dan sauce yang diperlukan
        $menuIds = array_unique(array_column($items, 'menu_id'));
        $sauceIds = array_unique(array_column($items, 'sauce_id'));
        $allMenuIds = array_merge($menuIds, $sauceIds);

        // Load semua menu + ingredients sekaligus
        $menus = Menu::with('ingredients')
            ->whereIn('id', $allMenuIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Pastikan semua menu dan sauce ditemukan dan aktif
        foreach ($items as $itemData) {
            if (!isset($menus[$itemData['menu_id']])) {
                return $this->jsonOrRedirect($request, false, "Menu dengan ID {$itemData['menu_id']} tidak ditemukan atau tidak aktif.");
            }
            if (!isset($menus[$itemData['sauce_id']])) {
                return $this->jsonOrRedirect($request, false, "Saus dengan ID {$itemData['sauce_id']} tidak ditemukan atau tidak aktif.");
            }

            $menu = $menus[$itemData['menu_id']];
            $sauce = $menus[$itemData['sauce_id']];

            // Validasi tipe: menu harus 'main', sauce harus 'sauce'
            if ($menu->type !== 'main') {
                return $this->jsonOrRedirect($request, false, "Menu {$menu->name} bukan menu utama.");
            }
            if ($sauce->type !== 'sauce') {
                return $this->jsonOrRedirect($request, false, "Saus {$sauce->name} bukan berjenis saus.");
            }

            // Validasi ketersediaan sauce untuk menu ini
            $isAvailable = $menu->availableSauces()
                ->where('sauce_id', $sauce->id)
                ->exists();
            if (!$isAvailable) {
                return $this->jsonOrRedirect($request, false, "Saus {$sauce->name} tidak tersedia untuk menu {$menu->name}.");
            }
        }

        // --- HITUNG KEBUTUHAN STOK PER INGREDIENT ---
        $requiredIngredients = [];

        // 1. Kebutuhan dari menu utama (langsung per porsi)
        foreach ($items as $itemData) {
            $menu = $menus[$itemData['menu_id']];
            foreach ($menu->ingredients as $ingredient) {
                $qty = $ingredient->pivot->quantity * $itemData['quantity'];
                $requiredIngredients[$ingredient->id] = ($requiredIngredients[$ingredient->id] ?? 0) + $qty;
            }
        }

        // 2. Kebutuhan dari saus (berdasarkan batch 5 order)
        $sauceQuantities = [];
        foreach ($items as $itemData) {
            $sauceId = $itemData['sauce_id'];
            $sauceQuantities[$sauceId] = ($sauceQuantities[$sauceId] ?? 0) + $itemData['quantity'];
        }

        foreach ($sauceQuantities as $sauceId => $totalQty) {
            $sauce = $menus[$sauceId];
            $batches = intdiv($totalQty, 5); // floor division
            if ($batches > 0) {
                foreach ($sauce->ingredients as $ingredient) {
                    $qty = $ingredient->pivot->quantity * $batches;
                    $requiredIngredients[$ingredient->id] = ($requiredIngredients[$ingredient->id] ?? 0) + $qty;
                }
            }
        }

        // Cek kecukupan stok
        foreach ($requiredIngredients as $ingredientId => $requiredQty) {
            $ingredient = Ingredient::find($ingredientId);
            if ($ingredient->stock < $requiredQty) {
                $msg = "Stok {$ingredient->name} tidak mencukupi. Dibutuhkan: {$requiredQty} {$ingredient->unit}, tersedia: {$ingredient->stock} {$ingredient->unit}.";
                Log::error('Stock check failed: ' . $msg);
                return $this->jsonOrRedirect($request, false, $msg);
            }
        }

        // --- SEMUA VALIDASI LULUS, MULAI TRANSAKSI ---
        DB::beginTransaction();
        try {
            // Create new order
            $order = Order::create([
                'user_id' => Auth::id(),
                'notes' => $request->notes,
                'status' => 'pending',
                'total' => 0,
            ]);

            $total = 0;

            // Add items to order with sauce
            foreach ($items as $itemData) {
                $menu = $menus[$itemData['menu_id']];
                $sauce = $menus[$itemData['sauce_id']];

                // Additional price is 0 (harga saus sudah include di menu utama)
                $additionalPrice = 0;
                $subtotal = $menu->price * $itemData['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'sauce_id' => $sauce->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $menu->price,
                    'additional_price' => $additionalPrice,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total' => $total]);

            // Create sale record
            $sale = Sale::create([
                'order_id' => $order->id,
                'date' => now(),
                'user_id' => Auth::id(),
                'total' => $total,
                'notes' => $request->notes,
                'payment_status' => 'pending',
            ]);

            // Copy items to sale
            foreach ($order->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'menu_id' => $item->menu_id,
                    'sauce_id' => $item->sauce_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'additional_price' => $item->additional_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            DB::commit();

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'sale_id' => $sale->id,
                'total' => $total,
                'items_count' => count($items),
                'user_id' => Auth::id()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibuat! Menunggu konfirmasi owner.',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'redirect' => route('orders.show', $order),
                    'total' => $total,
                    'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.')
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Pesanan berhasil dibuat! Menunggu konfirmasi owner.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save order: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return $this->jsonOrRedirect($request, false, 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk response JSON atau redirect.
     */
    private function jsonOrRedirect($request, $success, $message, $errors = null)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $response = ['success' => $success, 'message' => $message];
            if ($errors) {
                $response['errors'] = $errors;
            }
            return response()->json($response, $success ? 200 : 422);
        }

        if ($success) {
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', $message)->withInput();
        }
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        $user = Auth::user();

        // Ensure user can only view their own orders (unless owner)
        if ($user->role !== 'owner' && $order->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini');
        }

        $order->load(['user', 'items.menu', 'items.sauce', 'sale']);

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel order (by user before being processed).
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Order $order)
    {
        $user = Auth::user();

        // Ensure user can only cancel their own orders
        if ($user->role !== 'owner' && $order->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan pesanan ini');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Pesanan tidak dapat dibatalkan karena sudah diproses');
        }

        DB::beginTransaction();

        try {
            // Find and delete associated sale
            $sale = Sale::where('order_id', $order->id)->first();
            if ($sale) {
                $sale->items()->delete();
                $sale->delete();
            }

            // Soft delete the order
            $order->delete();

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Pesanan berhasil dibatalkan');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to cancel order: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return redirect()->back()
                ->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }
}
