<?php
// app/Http/Controllers/SaleController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Menu;
use App\Models\User;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request)
    {
        // Query builder untuk sales
        $query = Sale::with(['user', 'items.menu']);

        // Filter berdasarkan tanggal mulai
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter berdasarkan user/kasir
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Cari berdasarkan ID transaksi
                $q->where('id', 'like', "%{$search}%")
                  // Atau cari berdasarkan nama kasir
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  // Atau cari berdasarkan notes
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Dapatkan data sales dengan pagination
        $sales = $query->latest('date')->latest('id')->paginate(20);

        // Hitung statistik
        $totalSales = Sale::sum('total');
        $totalTransactions = Sale::count();

        // Dapatkan semua users untuk filter dropdown
        $users = User::orderBy('name')->get();

        // Return view dengan semua data yang dibutuhkan
        return view('sales.index', compact(
            'sales',
            'totalSales',
            'totalTransactions',
            'users'
        ));
    }

    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        // Ambil semua menu yang aktif beserta ingredientsnya
        $menus = Menu::where('is_active', true)
                     ->with(['ingredients' => function($query) {
                         $query->where('stock', '>', 0);
                     }])
                     ->orderBy('name')
                     ->get()
                     ->map(function ($menu) {
                         // Add formatted price
                         $menu->formatted_price = 'Rp ' . number_format($menu->price, 0, ',', '.');
                         // Add category if not exists
                         if (!isset($menu->category) || empty($menu->category)) {
                             $menu->category = 'lainnya';
                         }
                         // Check if all ingredients have stock
                         $menu->is_available = $menu->ingredients->every(function($ingredient) {
                             return $ingredient->stock > 0;
                         }) && $menu->ingredients->count() > 0;
                         return $menu;
                     })
                     ->filter(function($menu) {
                         return $menu->is_available;
                     });

        return view('sales.create', compact('menus'));
    }

    /**
     * Store a newly created sale in storage
     */
    public function store(Request $request)
    {
        Log::info('Sale store request received:', $request->all());

        // Decode items if it's a JSON string
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
                    'message' => 'Keranjang tidak boleh kosong. Silakan tambahkan menu terlebih dahulu.'
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Keranjang tidak boleh kosong. Silakan tambahkan menu terlebih dahulu.')
                ->withInput();
        }

        // Prepare validation rules
        $validationRules = [
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,transfer',
            'cash_received' => 'required_if:payment_method,cash|numeric|min:0',
            'change' => 'nullable|numeric|min:0',
        ];

        // Add validation for each item
        foreach ($items as $index => $item) {
            $validationRules["items.{$index}.menu_id"] = 'required|exists:menus,id';
            $validationRules["items.{$index}.quantity"] = 'required|integer|min:1|max:999';
        }

        // Prepare validation messages
        $validationMessages = [
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'payment_method.required' => 'Metode pembayaran harus dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'cash_received.required_if' => 'Uang diterima harus diisi untuk pembayaran tunai',
            'cash_received.numeric' => 'Uang diterima harus berupa angka',
            'cash_received.min' => 'Uang diterima tidak boleh kurang dari 0',
            'change.numeric' => 'Kembalian harus berupa angka',
            'change.min' => 'Kembalian tidak boleh kurang dari 0',
            'items.*.menu_id.required' => 'Menu harus dipilih',
            'items.*.menu_id.exists' => 'Menu tidak ditemukan',
            'items.*.quantity.required' => 'Jumlah harus diisi',
            'items.*.quantity.integer' => 'Jumlah harus berupa angka bulat',
            'items.*.quantity.min' => 'Jumlah minimal 1',
            'items.*.quantity.max' => 'Jumlah maksimal 999',
        ];

        // Create validator with items in request data
        $requestData = $request->all();
        $requestData['items'] = $items;

        $validator = Validator::make($requestData, $validationRules, $validationMessages);

        if ($validator->fails()) {
            Log::error('Sale validation failed:', $validator->errors()->toArray());

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
            // Create new sale first
            $sale = Sale::create([
                'date' => $request->date,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
                'total' => 0, // Will be updated after adding items
            ]);

            $total = 0;

            // Add each item to sale using the model's addItem method
            foreach ($items as $itemData) {
                try {
                    $item = $sale->addItem($itemData['menu_id'], $itemData['quantity']);
                    $total += $item->subtotal;
                } catch (\Exception $e) {
                    throw new \Exception("Gagal menambahkan item: " . $e->getMessage());
                }
            }

            DB::commit();

            Log::info('Sale created successfully:', [
                'sale_id' => $sale->id,
                'total' => $total,
                'items_count' => count($items),
                'user_id' => Auth::id()
            ]);

            // Store payment information in session for receipt
            session([
                'sale_payment_method' => $request->payment_method,
                'sale_cash_received' => $request->cash_received ?? 0,
                'sale_change' => $request->change ?? 0,
            ]);

            // If AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi penjualan berhasil disimpan!',
                    'sale_id' => $sale->id,
                    'redirect' => route('sales.show', $sale),
                    'total' => $total,
                    'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.')
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Transaksi penjualan berhasil disimpan!')
                ->with('print_receipt', true);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to save sale: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            // If AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()
                ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale)
    {
        // Load relasi yang dibutuhkan
        $sale->load(['user', 'items.menu']);

        // Get payment info from session if available
        $payment_method = session('sale_payment_method', 'cash');
        $cash_received = session('sale_cash_received', 0);
        $change = session('sale_change', 0);

        // Clear session data
        session()->forget(['sale_payment_method', 'sale_cash_received', 'sale_change']);

        return view('sales.show', compact('sale', 'payment_method', 'cash_received', 'change'));
    }

    /**
     * Remove the specified sale from storage
     */
    public function destroy(Sale $sale)
    {
        DB::beginTransaction();

        try {
            // Hapus sale (soft delete)
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Transaksi penjualan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete sale: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Add item to existing sale (AJAX) - Updated to use model's addItem method
     */
    public function addItem(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id',
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($request->sale_id);

            // Use model's addItem method
            $item = $sale->addItem($request->menu_id, $request->quantity);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan',
                'item' => [
                    'id' => $item->id,
                    'menu_name' => $item->menu->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ],
                'sale_total' => $sale->total,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to add item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove item from sale (AJAX)
     */
    public function removeItem($id)
    {
        DB::beginTransaction();

        try {
            $item = SaleItem::findOrFail($id);
            $sale = $item->sale;
            $menu = $item->menu;

            // Kembalikan stok bahan baku
            foreach ($menu->ingredients as $ingredient) {
                $returnedQuantity = $ingredient->pivot->quantity * $item->quantity;
                $ingredient->increaseStock($returnedQuantity);
            }

            // Hapus item
            $item->delete();

            // Update total sale
            $sale->updateTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus',
                'sale_total' => $sale->total,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to remove item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Print receipt for a sale
     */
    public function printReceipt(Sale $sale)
    {
        $sale->load(['user', 'items.menu']);

        // Get payment info from request or session
        $payment_method = request('payment_method', session('sale_payment_method', 'cash'));
        $cash_received = request('cash_received', session('sale_cash_received', 0));
        $change = request('change', session('sale_change', 0));

        return view('sales.receipt', compact('sale', 'payment_method', 'cash_received', 'change'));
    }


    /**
     * Get daily sales report (AJAX)
     */
    public function getDailyReport(Request $request)
    {
        // JANGAN PAKAI ob_clean() DAN header() LANGSUNG
        // Gunakan response()->json() saja yang sudah handle header

        try {
            // Ambil tanggal dari request atau gunakan hari ini
            $date = $request->get('date', now()->format('Y-m-d'));

            // Validasi format tanggal
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD',
                ]);
            }

            // Ambil semua penjualan pada tanggal tersebut
            $sales = Sale::with(['items.menu', 'user'])
                ->whereDate('date', $date)
                ->get();

            // Hitung total penjualan
            $totalSales = $sales->sum('total');

            // Hitung total item terjual
            $totalItems = $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            });

            // Breakdown per menu
            $itemsBreakdown = [];
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $menuName = $item->menu->name ?? 'Unknown';

                    if (!isset($itemsBreakdown[$menuName])) {
                        $itemsBreakdown[$menuName] = [
                            'quantity' => 0,
                            'total' => 0,
                        ];
                    }

                    $itemsBreakdown[$menuName]['quantity'] += $item->quantity;
                    $itemsBreakdown[$menuName]['total'] += $item->subtotal;
                }
            }

            // Sort breakdown by total (descending)
            uasort($itemsBreakdown, function ($a, $b) {
                return $b['total'] - $a['total'];
            });

            // Nama bulan dalam bahasa Indonesia
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $dateObj = \Carbon\Carbon::parse($date);
            $day = $dateObj->format('d');
            $month = (int)$dateObj->format('m');
            $year = $dateObj->format('Y');
            $monthName = $monthNames[$month] ?? $dateObj->format('F');
            $formattedDate = $day . ' ' . $monthName . ' ' . $year;

            // Format items_breakdown untuk response
            $formattedItemsBreakdown = [];
            foreach ($itemsBreakdown as $menuName => $details) {
                $formattedItemsBreakdown[$menuName] = [
                    'quantity' => (int) $details['quantity'],
                    'total' => (float) $details['total'],
                    'formatted_quantity' => number_format($details['quantity'], 0, ',', '.'),
                    'formatted_total' => 'Rp ' . number_format($details['total'], 0, ',', '.'),
                ];
            }

            // Format sales untuk response
            $formattedSales = [];
            foreach ($sales as $sale) {
                $formattedSales[] = [
                    'id' => $sale->id,
                    'formatted_id' => '#' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                    'time' => $sale->created_at->format('H:i'),
                    'user' => $sale->user->name ?? 'Unknown',
                    'total' => (float) $sale->total,
                    'formatted_total' => 'Rp ' . number_format($sale->total, 0, ',', '.'),
                    'items_count' => (int) $sale->items->count(),
                    'total_quantity' => (int) $sale->items->sum('quantity'),
                ];
            }

            // Buat response JSON dengan struktur yang benar
            $response = [
                'success' => true,
                'date' => $date,
                'formatted_date' => $formattedDate,
                'total_sales' => (float) $totalSales,
                'formatted_total_sales' => 'Rp ' . number_format($totalSales, 0, ',', '.'),
                'total_transactions' => (int) $sales->count(),
                'total_items' => (int) $totalItems,
                'items_breakdown' => $formattedItemsBreakdown,
                'sales' => $formattedSales,
            ];

            // Log untuk debugging
            Log::info('Daily report response for date: ' . $date, [
                'total_transactions' => $sales->count(),
                'total_sales' => $totalSales
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Gagal memuat laporan harian: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat laporan: ' . $e->getMessage(),
            ], 500);
        }
    }

        /**
     * Print daily sales report
     */
    public function printDailyReport(Request $request)
    {
        // Validasi input
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        // Ambil semua penjualan pada tanggal tersebut
        $sales = Sale::with(['items.menu', 'user'])
            ->whereDate('date', $date)
            ->get();

        // Hitung total penjualan
        $totalSales = $sales->sum('total');

        // Hitung total item terjual
        $totalItems = $sales->sum(function ($sale) {
            return $sale->items->sum('quantity');
        });

        // Hitung rata-rata per transaksi
        $averageSales = $sales->count() > 0 ? $totalSales / $sales->count() : 0;

        // Breakdown per menu
        $itemsBreakdown = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $menuName = $item->menu->name ?? 'Unknown';

                if (!isset($itemsBreakdown[$menuName])) {
                    $itemsBreakdown[$menuName] = [
                        'quantity' => 0,
                        'total' => 0,
                    ];
                }

                $itemsBreakdown[$menuName]['quantity'] += $item->quantity;
                $itemsBreakdown[$menuName]['total'] += $item->subtotal;
            }
        }

        // Sort breakdown by total (descending)
        uasort($itemsBreakdown, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        // Nama bulan dalam bahasa Indonesia
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $dateObj = \Carbon\Carbon::parse($date);
        $day = $dateObj->format('d');
        $month = (int)$dateObj->format('m');
        $year = $dateObj->format('Y');
        $monthName = $monthNames[$month] ?? $dateObj->format('F');
        $formattedDate = $day . ' ' . $monthName . ' ' . $year;

        // Return view untuk print
        return view('sales.print-daily-report', compact(
            'date',
            'formattedDate',
            'sales',
            'totalSales',
            'averageSales',
            'totalItems',
            'itemsBreakdown'
        ));
    }
}
