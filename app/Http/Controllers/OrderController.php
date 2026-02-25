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

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format data items tidak valid: ' . json_last_error_msg()
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', 'Format data items tidak valid')
                    ->withInput();
            }
        }

        // Validate items array
        if (!is_array($items) || empty($items)) {
            Log::error('Items is not array or empty:', ['items' => $items]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang tidak boleh kosong. Silakan pilih menu terlebih dahulu.'
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Keranjang tidak boleh kosong. Silakan pilih menu terlebih dahulu.')
                ->withInput();
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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Check stock availability for all items before creating order
            foreach ($items as $itemData) {
                $menu = Menu::findOrFail($itemData['menu_id']);
                $sauce = Menu::findOrFail($itemData['sauce_id']);

                // Validate that the sauce is available for this menu
                $isAvailable = $menu->availableSauces()
                    ->where('sauce_id', $sauce->id)
                    ->exists();

                if (!$isAvailable) {
                    throw new \Exception("Saus {$sauce->name} tidak tersedia untuk menu {$menu->name}");
                }

                // Check if menu and sauce are active
                if (!$menu->is_active) {
                    throw new \Exception("Menu {$menu->name} tidak aktif");
                }

                if (!$sauce->is_active) {
                    throw new \Exception("Saus {$sauce->name} tidak aktif");
                }

                // Check stock for menu ingredients
                foreach ($menu->ingredients as $ingredient) {
                    $requiredQuantity = $ingredient->pivot->quantity * $itemData['quantity'];

                    if ($ingredient->stock < $requiredQuantity) {
                        throw new \Exception("Stok {$ingredient->name} tidak mencukupi untuk menu {$menu->name}. Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, Tersedia: {$ingredient->stock} {$ingredient->unit}");
                    }
                }

                // Check stock for sauce ingredients
                foreach ($sauce->ingredients as $ingredient) {
                    $requiredQuantity = $ingredient->pivot->quantity * $itemData['quantity'];

                    if ($ingredient->stock < $requiredQuantity) {
                        throw new \Exception("Stok {$ingredient->name} tidak mencukupi untuk saus {$sauce->name}. Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, Tersedia: {$ingredient->stock} {$ingredient->unit}");
                    }
                }
            }

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
                $menu = Menu::find($itemData['menu_id']);
                $sauce = Menu::find($itemData['sauce_id']);

                // Additional price is 0 because sauce price is already included in menu price
                $additionalPrice = 0;
                $subtotal = $menu->price * $itemData['quantity'];

                // Create order item
                $orderItem = OrderItem::create([
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

            // Update order total
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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()
                ->with('error', 'Gagal membuat pesanan: ' . $e->getMessage())
                ->withInput();
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
