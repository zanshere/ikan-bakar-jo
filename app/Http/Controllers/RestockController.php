<?php
// app/Http/Controllers/RestockController.php

namespace App\Http\Controllers;

use App\Models\Restock;
use App\Models\RestockItem;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RestockController extends Controller
{
    /**
     * Display a listing of restocks
     */
    public function index(Request $request)
    {
        // Query builder untuk restocks
        $query = Restock::with(['user', 'items.ingredient']);

        // Filter berdasarkan tanggal mulai
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter berdasarkan user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Cari berdasarkan ID transaksi
                $q->where('id', 'like', "%{$search}%")
                  // Atau cari berdasarkan nama user
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  // Atau cari berdasarkan nama ingredient
                  ->orWhereHas('items.ingredient', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  // Atau cari berdasarkan notes
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Dapatkan data restocks dengan pagination
        $restocks = $query->latest('date')->latest('id')->paginate(20);

        // Hitung statistik
        $totalRestocks = Restock::sum('total');
        $totalTransactions = Restock::count();

        // Dapatkan semua users untuk filter dropdown
        $users = User::orderBy('name')->get();

        // Return view dengan semua data yang dibutuhkan
        return view('restocks.index', compact(
            'restocks',
            'totalRestocks',
            'totalTransactions',
            'users'
        ));
    }

    /**
     * Show the form for creating a new restock
     */
    public function create()
    {
        // Ambil semua ingredients yang aktif
        $ingredients = Ingredient::orderBy('name')->get()->map(function($ingredient) {
            // Add formatted values for display
            $ingredient->formatted_price = 'Rp ' . number_format($ingredient->price, 0, ',', '.');
            $ingredient->formatted_stock = number_format($ingredient->stock, 2, ',', '.') . ' ' . $ingredient->unit;
            $ingredient->formatted_min_stock = number_format($ingredient->min_stock, 2, ',', '.') . ' ' . $ingredient->unit;

            // Determine stock status
            if ($ingredient->stock == 0) {
                $ingredient->stock_status = 'empty';
            } elseif ($ingredient->stock <= $ingredient->min_stock) {
                $ingredient->stock_status = 'low';
            } else {
                $ingredient->stock_status = 'normal';
            }

            return $ingredient;
        });

        return view('restocks.create', compact('ingredients'));
    }

    /**
     * Store a newly created restock in storage
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'items' => 'required|string', // Changed from array to string since we're sending JSON
        ], [
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'items.required' => 'Minimal harus ada 1 item',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Parse items from JSON string
        $items = json_decode($request->items, true);

        // Validate items array
        if (!is_array($items) || count($items) === 0) {
            return redirect()->back()
                ->withErrors(['items' => 'Minimal harus ada 1 item'])
                ->withInput();
        }

        // Validate each item
        foreach ($items as $item) {
            if (!isset($item['ingredient_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                return redirect()->back()
                    ->withErrors(['items' => 'Data item tidak lengkap'])
                    ->withInput();
            }

            if ($item['quantity'] <= 0) {
                return redirect()->back()
                    ->withErrors(['items' => 'Kuantitas harus lebih dari 0'])
                    ->withInput();
            }

            if ($item['price'] < 0) {
                return redirect()->back()
                    ->withErrors(['items' => 'Harga tidak valid'])
                    ->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Buat transaksi restock baru
            $restock = Restock::create([
                'date' => $request->date,
                'notes' => $request->notes,
                'supplier_name' => $request->supplier_name,
                'supplier_contact' => $request->supplier_contact,
                'user_id' => Auth::id(),
                'total' => 0, // Akan diupdate setelah items ditambahkan
            ]);

            $total = 0;

            // Tambahkan setiap item ke restock
            foreach ($items as $itemData) {
                $ingredient = Ingredient::findOrFail($itemData['ingredient_id']);

                // Buat restock item
                $restockItem = RestockItem::create([
                    'restock_id' => $restock->id,
                    'ingredient_id' => $itemData['ingredient_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'subtotal' => $itemData['quantity'] * $itemData['price'],
                ]);

                // Tambah stok bahan baku
                $ingredient->increaseStock($itemData['quantity']);

                $total += $restockItem->subtotal;
            }

            // Update total restock
            $restock->update(['total' => $total]);

            DB::commit();

            return redirect()->route('restocks.show', $restock)
                ->with('success', 'Transaksi restock berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified restock
     */
    public function show(Restock $restock)
    {
        // Load relasi yang dibutuhkan
        $restock->load(['user', 'items.ingredient']);

        return view('restocks.show', compact('restock'));
    }

    /**
     * Remove the specified restock from storage
     */
    public function destroy(Restock $restock)
    {
        // Check if restock is already deleted
        if ($restock->trashed()) {
            return redirect()->route('restocks.index')
                ->with('error', 'Transaksi sudah dihapus sebelumnya');
        }

        DB::beginTransaction();

        try {
            // Kurangi stok bahan baku
            foreach ($restock->items as $item) {
                $ingredient = $item->ingredient;
                $ingredient->decreaseStock($item->quantity);
            }

            // Hapus restock (soft delete)
            $restock->delete();

            DB::commit();

            return redirect()->route('restocks.index')
                ->with('success', 'Transaksi restock berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Add item to existing restock (AJAX)
     */
    public function addItem(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'restock_id' => 'required|exists:restocks,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $restock = Restock::findOrFail($request->restock_id);
            $ingredient = Ingredient::findOrFail($request->ingredient_id);

            // Cek apakah ingredient sudah ada di restock ini
            $existingItem = $restock->items()->where('ingredient_id', $ingredient->id)->first();

            if ($existingItem) {
                // Update quantity dan price jika sudah ada
                $existingItem->quantity += $request->quantity;
                $existingItem->price = $request->price;
                $existingItem->subtotal = $existingItem->quantity * $existingItem->price;
                $existingItem->save();
                $item = $existingItem;
            } else {
                // Buat item baru
                $item = RestockItem::create([
                    'restock_id' => $restock->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                    'subtotal' => $request->quantity * $request->price,
                ]);
            }

            // Tambah stok bahan baku
            $ingredient->increaseStock($request->quantity);

            // Update total restock
            $restock->total = $restock->items()->sum('subtotal');
            $restock->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan',
                'item' => [
                    'id' => $item->id,
                    'ingredient_name' => $item->ingredient->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                    'formatted_quantity' => number_format($item->quantity, 2, ',', '.') . ' ' . $item->ingredient->unit,
                    'formatted_price' => 'Rp ' . number_format($item->price, 0, ',', '.'),
                    'formatted_subtotal' => 'Rp ' . number_format($item->subtotal, 0, ',', '.'),
                ],
                'restock_total' => $restock->total,
                'formatted_restock_total' => 'Rp ' . number_format($restock->total, 0, ',', '.'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove item from restock (AJAX)
     */
    public function removeItem($id)
    {
        DB::beginTransaction();

        try {
            $item = RestockItem::findOrFail($id);
            $restock = $item->restock;
            $ingredient = $item->ingredient;

            // Kurangi stok bahan baku
            $ingredient->decreaseStock($item->quantity);

            // Hapus item
            $item->delete();

            // Update total restock
            $restock->total = $restock->items()->sum('subtotal');
            $restock->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus',
                'restock_total' => $restock->total,
                'formatted_restock_total' => 'Rp ' . number_format($restock->total, 0, ',', '.'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get monthly restock report (AJAX)
     */
    public function getMonthlyReport(Request $request)
    {
        // JANGAN PAKAI ob_clean() DAN header() LANGSUNG
        // Gunakan response()->json() saja yang sudah handle header

        try {
            // Ambil tahun dan bulan dari request atau gunakan bulan ini
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));

            // Validasi input
            if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun atau bulan tidak valid',
                ]);
            }

            // Format month dengan leading zero
            $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

            // Ambil semua restock pada bulan tersebut
            $restocks = Restock::with(['items.ingredient', 'user'])
                ->whereYear('date', $year)
                ->whereMonth('date', $monthFormatted)
                ->get();

            // Hitung total restock
            $totalRestocks = $restocks->sum('total');

            // Hitung total transaksi
            $totalTransactions = $restocks->count();

            // Hitung total item (jumlah baris item)
            $totalItems = $restocks->sum(function ($restock) {
                return $restock->items->count();
            });

            // Hitung total quantity
            $totalQuantity = $restocks->sum(function ($restock) {
                return $restock->items->sum('quantity');
            });

            // Breakdown per ingredient
            $ingredientsBreakdown = [];
            foreach ($restocks as $restock) {
                foreach ($restock->items as $item) {
                    $ingredientName = $item->ingredient->name ?? 'Unknown';
                    $ingredientUnit = $item->ingredient->unit ?? 'pcs';

                    if (!isset($ingredientsBreakdown[$ingredientName])) {
                        $ingredientsBreakdown[$ingredientName] = [
                            'quantity' => 0,
                            'total' => 0,
                            'average_price' => 0,
                            'unit' => $ingredientUnit,
                            'times_restocked' => 0,
                        ];
                    }

                    $ingredientsBreakdown[$ingredientName]['quantity'] += (float) $item->quantity;
                    $ingredientsBreakdown[$ingredientName]['total'] += (float) $item->subtotal;
                    $ingredientsBreakdown[$ingredientName]['times_restocked']++;
                }
            }

            // Calculate average price for each ingredient
            foreach ($ingredientsBreakdown as $name => &$breakdown) {
                if ($breakdown['quantity'] > 0) {
                    $breakdown['average_price'] = $breakdown['total'] / $breakdown['quantity'];
                }
                $breakdown['formatted_quantity'] = number_format($breakdown['quantity'], 2, ',', '.') . ' ' . $breakdown['unit'];
                $breakdown['formatted_total'] = 'Rp ' . number_format($breakdown['total'], 0, ',', '.');
                $breakdown['formatted_average_price'] = 'Rp ' . number_format($breakdown['average_price'], 0, ',', '.');
            }

            // Sort breakdown by total (descending)
            uasort($ingredientsBreakdown, function ($a, $b) {
                return $b['total'] - $a['total'];
            });

            // Nama bulan dalam bahasa Indonesia
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $monthInt = (int) $month;
            $monthName = $monthNames[$monthInt] ?? \Carbon\Carbon::create()->month($monthInt)->format('F');

            // Format restocks untuk response
            $formattedRestocks = [];
            foreach ($restocks as $restock) {
                $formattedRestocks[] = [
                    'id' => $restock->id,
                    'formatted_id' => '#' . str_pad($restock->id, 6, '0', STR_PAD_LEFT),
                    'date' => $restock->date->format('d/m/Y'),
                    'user' => $restock->user->name ?? 'Unknown',
                    'total' => (float) $restock->total,
                    'formatted_total' => 'Rp ' . number_format($restock->total, 0, ',', '.'),
                    'items_count' => (int) $restock->items->count(),
                    'total_quantity' => (float) $restock->items->sum('quantity'),
                ];
            }

            $response = [
                'success' => true,
                'year' => $year,
                'month' => $month,
                'month_name' => $monthName,
                'total_restocks' => (float) $totalRestocks,
                'formatted_total_restocks' => 'Rp ' . number_format($totalRestocks, 0, ',', '.'),
                'total_transactions' => (int) $totalTransactions,
                'total_items' => (int) $totalItems,
                'total_quantity' => (float) $totalQuantity,
                'formatted_total_quantity' => number_format($totalQuantity, 2, ',', '.'),
                'ingredients_breakdown' => $ingredientsBreakdown,
                'restocks' => $formattedRestocks,
            ];

            // Log untuk debugging
            Log::info('Monthly report response for period: ' . $monthName . ' ' . $year, [
                'total_transactions' => $totalTransactions,
                'total_restocks' => $totalRestocks
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil laporan bulanan restock: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan: ' . $e->getMessage(),
            ], 500);
        }
    }

        /**
     * Print monthly restock report
     */
    public function printMonthlyReport(Request $request)
    {
        // Validasi input
        $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->year;
        $month = $request->month;

        // Format month dengan leading zero
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        // Ambil semua restock pada bulan tersebut
        $restocks = Restock::with(['items.ingredient', 'user'])
            ->whereYear('date', $year)
            ->whereMonth('date', $monthFormatted)
            ->get();

        // Hitung total pengeluaran
        $totalRestocks = $restocks->sum('total');

        // Hitung total transaksi
        $totalTransactions = $restocks->count();

        // Hitung total item
        $totalItems = $restocks->sum(function ($restock) {
            return $restock->items->count();
        });

        // Hitung total quantity
        $totalQuantity = $restocks->sum(function ($restock) {
            return $restock->items->sum('quantity');
        });

        // Hitung rata-rata per transaksi
        $averagePerTransaction = $totalTransactions > 0 ? $totalRestocks / $totalTransactions : 0;

        // Breakdown per ingredient
        $ingredientsBreakdown = [];
        foreach ($restocks as $restock) {
            foreach ($restock->items as $item) {
                $ingredientName = $item->ingredient->name ?? 'Unknown';
                $ingredientUnit = $item->ingredient->unit ?? 'pcs';

                if (!isset($ingredientsBreakdown[$ingredientName])) {
                    $ingredientsBreakdown[$ingredientName] = [
                        'quantity' => 0,
                        'total' => 0,
                        'average_price' => 0,
                        'unit' => $ingredientUnit,
                        'times_restocked' => 0,
                    ];
                }

                $ingredientsBreakdown[$ingredientName]['quantity'] += $item->quantity;
                $ingredientsBreakdown[$ingredientName]['total'] += $item->subtotal;
                $ingredientsBreakdown[$ingredientName]['times_restocked']++;
            }
        }

        // Calculate average price for each ingredient
        foreach ($ingredientsBreakdown as $name => &$breakdown) {
            if ($breakdown['quantity'] > 0) {
                $breakdown['average_price'] = $breakdown['total'] / $breakdown['quantity'];
            }
            $breakdown['formatted_quantity'] = number_format($breakdown['quantity'], 2, ',', '.') . ' ' . $breakdown['unit'];
            $breakdown['formatted_total'] = 'Rp ' . number_format($breakdown['total'], 0, ',', '.');
            $breakdown['formatted_average_price'] = 'Rp ' . number_format($breakdown['average_price'], 0, ',', '.');
        }

        // Sort breakdown by total (descending)
        uasort($ingredientsBreakdown, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        // Nama bulan dalam bahasa Indonesia
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNames[(int)$month] ?? \Carbon\Carbon::create()->month((int)$month)->format('F');
        $period = $monthName . ' ' . $year;

        // Return view untuk print
        return view('restocks.print-monthly-report', compact(
            'year',
            'month',
            'monthName',
            'period',
            'restocks',
            'totalRestocks',
            'totalTransactions',
            'averagePerTransaction',
            'totalItems',
            'totalQuantity',
            'ingredientsBreakdown'
        ));
    }
}
