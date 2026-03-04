<?php
// app/Http/Controllers/SaleController.php - UPDATE controller ini

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Display a listing of sales for owner to process.
     */
    public function index(Request $request)
    {
        // Only owner can access sales management
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mengakses halaman ini');
        }

        $query = Sale::with(['user', 'order', 'items.menu', 'items.sauce']);

        // Filter berdasarkan status order
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'pending');
                });
            } elseif ($request->status === 'accepted') {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'accepted');
                });
            } elseif ($request->status === 'completed') {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'completed');
                });
            } elseif ($request->status === 'rejected') {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'rejected');
                });
            }
        }

        // Filter berdasarkan payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $sales = $query->latest('date')->latest('id')->paginate(20);

        // Hitung statistik
        $totalSales = Sale::sum('total');
        $totalTransactions = Sale::count();

        $pendingPaymentCount = Sale::where('payment_status', 'pending')->count();
        $paidCount = Sale::where('payment_status', 'paid')->count();

        $pendingOrders = Order::where('status', 'pending')->count();
        $acceptedOrders = Order::where('status', 'accepted')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $rejectedOrders = Order::where('status', 'rejected')->count();

        $users = User::all();

        return view('sales.index', compact(
            'sales',
            'totalSales',
            'totalTransactions',
            'pendingPaymentCount',
            'paidCount',
            'pendingOrders',
            'acceptedOrders',
            'completedOrders',
            'rejectedOrders',
            'users'
        ));
    }

    /**
     * Display the specified sale for processing.
     */
    public function show(Sale $sale)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mengakses halaman ini');
        }

        $sale->load(['user', 'order', 'items.menu', 'items.sauce']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show form to process a sale.
     */
    public function process(Sale $sale)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat memproses pesanan');
        }

        if (!$sale->order) {
            return redirect()->route('sales.index')
                ->with('error', 'Data order tidak ditemukan');
        }

        if ($sale->order->status !== 'pending') {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'Pesanan ini sudah diproses sebelumnya');
        }

        $sale->load(['user', 'order.items.menu', 'order.items.sauce', 'items.menu', 'items.sauce']);

        return view('sales.process', compact('sale'));
    }

    /**
     * Accept order.
     */
    public function accept(Request $request, Sale $sale)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat menerima pesanan');
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Process the sale (this will accept the order and reduce stock with batch system)
            $sale->accept(Auth::id());

            // Update notes if any
            if ($request->filled('notes')) {
                $sale->notes = $request->notes;
                $sale->save();
            }

            DB::commit();

            Log::info('Sale accepted successfully:', [
                'sale_id' => $sale->id,
                'order_number' => $sale->order->order_number,
                'processed_by' => Auth::id()
            ]);

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Pesanan berhasil diterima. Silakan proses pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to accept sale: ' . $e->getMessage(), [
                'sale_id' => $sale->id,
                'exception' => $e
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menerima pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Process payment and complete order.
     */
    public function complete(Request $request, Sale $sale)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat menyelesaikan pesanan');
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,transfer',
            'cash_received' => 'required_if:payment_method,cash|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Complete the sale
            $sale->complete(
                $request->payment_method,
                $request->cash_received
            );

            DB::commit();

            Log::info('Sale completed successfully:', [
                'sale_id' => $sale->id,
                'order_number' => $sale->order->order_number,
                'payment_method' => $request->payment_method,
                'completed_by' => Auth::id()
            ]);

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Pembayaran berhasil diproses. Pesanan selesai.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete sale: ' . $e->getMessage(), [
                'sale_id' => $sale->id,
                'exception' => $e
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Reject order.
     */
    public function reject(Request $request, Sale $sale)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat menolak pesanan');
        }

        $validator = Validator::make($request->all(), [
            'rejected_reason' => 'required|string|max:500',
        ], [
            'rejected_reason.required' => 'Alasan penolakan harus diisi',
            'rejected_reason.max' => 'Alasan penolakan maksimal 500 karakter',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $sale->reject($request->rejected_reason, Auth::id());

            DB::commit();

            Log::info('Sale rejected successfully:', [
                'sale_id' => $sale->id,
                'order_number' => $sale->order->order_number,
                'reason' => $request->rejected_reason,
                'rejected_by' => Auth::id()
            ]);

            return redirect()->route('sales.index')
                ->with('success', 'Pesanan berhasil ditolak');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to reject sale: ' . $e->getMessage(), [
                'sale_id' => $sale->id,
                'exception' => $e
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menolak pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Print receipt for a completed sale.
     */
    public function printReceipt(Sale $sale)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mencetak struk');
        }

        if ($sale->payment_status !== 'paid') {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'Pembayaran belum lunas');
        }

        $sale->load(['user', 'order', 'items.menu', 'items.sauce']);

        return view('sales.receipt', compact('sale'));
    }

    /**
     * Get daily sales report.
     */
    public function getDailyReport(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya owner yang dapat mengakses laporan'
            ], 403);
        }

        try {
            $date = $request->get('date', now()->format('Y-m-d'));

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD',
                ]);
            }

            $sales = Sale::with(['items.menu', 'items.sauce', 'user', 'order'])
                ->whereDate('date', $date)
                ->where('payment_status', 'paid')
                ->get();

            $totalSales = $sales->sum('total');
            $totalItems = $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            });

            // Items breakdown
            $itemsBreakdown = [];
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $key = $item->menu->name . ($item->sauce ? ' + ' . $item->sauce->name : '');

                    if (!isset($itemsBreakdown[$key])) {
                        $itemsBreakdown[$key] = [
                            'quantity' => 0,
                            'total' => 0,
                        ];
                    }

                    $itemsBreakdown[$key]['quantity'] += $item->quantity;
                    $itemsBreakdown[$key]['total'] += $item->subtotal;
                }
            }

            uasort($itemsBreakdown, function ($a, $b) {
                return $b['total'] - $a['total'];
            });

            $monthNames = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];

            $dateObj = Carbon::parse($date);
            $day = $dateObj->format('d');
            $month = (int)$dateObj->format('m');
            $year = $dateObj->format('Y');
            $monthName = $monthNames[$month] ?? $dateObj->format('F');
            $formattedDate = $day . ' ' . $monthName . ' ' . $year;

            $formattedItemsBreakdown = [];
            foreach ($itemsBreakdown as $menuName => $details) {
                $formattedItemsBreakdown[$menuName] = [
                    'quantity' => (int) $details['quantity'],
                    'total' => (float) $details['total'],
                    'formatted_quantity' => number_format($details['quantity'], 0, ',', '.'),
                    'formatted_total' => 'Rp ' . number_format($details['total'], 0, ',', '.'),
                ];
            }

            $formattedSales = [];
            foreach ($sales as $sale) {
                $formattedSales[] = [
                    'id' => $sale->id,
                    'order_number' => $sale->order ? $sale->order->order_number : 'N/A',
                    'time' => $sale->created_at->format('H:i'),
                    'user' => $sale->user->name ?? 'Unknown',
                    'total' => (float) $sale->total,
                    'formatted_total' => 'Rp ' . number_format($sale->total, 0, ',', '.'),
                    'payment_method' => $sale->payment_method === 'cash' ? 'Tunai' : 'Transfer',
                    'items_count' => (int) $sale->items->count(),
                    'total_quantity' => (int) $sale->items->sum('quantity'),
                ];
            }

            return response()->json([
                'success' => true,
                'date' => $date,
                'formatted_date' => $formattedDate,
                'total_sales' => (float) $totalSales,
                'formatted_total_sales' => 'Rp ' . number_format($totalSales, 0, ',', '.'),
                'total_transactions' => (int) $sales->count(),
                'total_items' => (int) $totalItems,
                'items_breakdown' => $formattedItemsBreakdown,
                'sales' => $formattedSales,
            ]);
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
     * Print daily sales report.
     */
    public function printDailyReport(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mencetak laporan');
        }

        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        $sales = Sale::with(['items.menu', 'items.sauce', 'user', 'order'])
            ->whereDate('date', $date)
            ->where('payment_status', 'paid')
            ->get();

        $totalSales = $sales->sum('total');
        $totalItems = $sales->sum(function ($sale) {
            return $sale->items->sum('quantity');
        });
        $averageSales = $sales->count() > 0 ? $totalSales / $sales->count() : 0;

        // Items breakdown
        $itemsBreakdown = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $key = $item->menu->name . ($item->sauce ? ' + ' . $item->sauce->name : '');

                if (!isset($itemsBreakdown[$key])) {
                    $itemsBreakdown[$key] = [
                        'quantity' => 0,
                        'total' => 0,
                    ];
                }

                $itemsBreakdown[$key]['quantity'] += $item->quantity;
                $itemsBreakdown[$key]['total'] += $item->subtotal;
            }
        }

        uasort($itemsBreakdown, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $dateObj = Carbon::parse($date);
        $day = $dateObj->format('d');
        $month = (int)$dateObj->format('m');
        $year = $dateObj->format('Y');
        $monthName = $monthNames[$month] ?? $dateObj->format('F');
        $formattedDate = $day . ' ' . $monthName . ' ' . $year;

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

    /**
     * Mark sale as paid.
     */
    public function markAsPaid(Request $request, Sale $sale)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mengakses halaman ini');
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,transfer',
            'cash_received' => 'required_if:payment_method,cash|numeric|min:0',
        ], [
            'payment_method.required' => 'Metode pembayaran harus dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'cash_received.required_if' => 'Jumlah uang diterima harus diisi untuk pembayaran tunai',
            'cash_received.numeric' => 'Jumlah uang diterima harus berupa angka',
            'cash_received.min' => 'Jumlah uang diterima tidak boleh kurang dari 0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Hitung kembalian jika pembayaran tunai
            $change = null;
            if ($request->payment_method === 'cash') {
                $change = $request->cash_received - $sale->total;
                if ($change < 0) {
                    return redirect()->back()
                        ->with('error', 'Uang diterima kurang dari total pembayaran')
                        ->withInput();
                }
            }

            // Update status pembayaran
            $sale->payment_status = 'paid';
            $sale->payment_method = $request->payment_method;
            $sale->cash_received = $request->cash_received;
            $sale->change = $change;
            $sale->completed_at = now();
            $sale->save();

            DB::commit();

            Log::info('Sale marked as paid successfully:', [
                'sale_id' => $sale->id,
                'order_number' => $sale->order ? $sale->order->order_number : 'N/A',
                'payment_method' => $request->payment_method,
                'processed_by' => Auth::id()
            ]);

            $successMessage = $request->payment_method === 'transfer'
                ? 'Pembayaran transfer berhasil diproses otomatis melalui device EDC. Status pesanan telah diperbarui menjadi Lunas.'
                : 'Pembayaran tunai berhasil diproses. Status pesanan telah diperbarui menjadi Lunas.';

            return redirect()->route('sales.show', $sale)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to mark sale as paid: ' . $e->getMessage(), [
                'sale_id' => $sale->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}
