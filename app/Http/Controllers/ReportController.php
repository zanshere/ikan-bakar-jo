<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Restock;
use App\Models\RestockItem;
use App\Models\Menu;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Menampilkan laporan pendapatan
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function income(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Validasi tanggal
        if ($startDate > $endDate) {
            return redirect()->back()
                ->with('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.')
                ->withInput();
        }

        try {
            $sales = Sale::with(['user', 'items.menu'])
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            // Daily summary - menggunakan DATE(date) sesuai database
            $dailySummary = Sale::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total) as total_amount')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date', 'desc')
                ->get();

            // Menu summary - Hitung average_price dari subtotal/quantity
            $menuSummary = DB::table('sale_items')
                ->join('menus', 'sale_items.menu_id', '=', 'menus.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->select(
                    'menus.id',
                    'menus.name',
                    DB::raw('SUM(sale_items.quantity) as total_quantity'),
                    DB::raw('SUM(sale_items.subtotal) as total_sales'),
                    DB::raw('AVG(CASE WHEN sale_items.quantity > 0 THEN sale_items.subtotal / sale_items.quantity ELSE 0 END) as average_price')
                )
                ->whereBetween('sales.date', [$startDate, $endDate])
                ->groupBy('menus.id', 'menus.name')
                ->orderBy('total_sales', 'desc')
                ->get();

            $totalIncome = $sales->sum('total');
            $totalTransactions = $sales->count();
            $averageTransaction = $totalTransactions > 0 ? $totalIncome / $totalTransactions : 0;
        } catch (\Exception $e) {
            Log::error('Error generating income report: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengambil data laporan pendapatan: ' . $e->getMessage())
                ->withInput();
        }

        return view('reports.income', compact(
            'sales',
            'dailySummary',
            'menuSummary',
            'totalIncome',
            'totalTransactions',
            'averageTransaction',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Mengekspor laporan pendapatan ke PDF
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Http\Response
     */
    public function exportIncomePdf($startDate, $endDate)
    {
        try {
            $sales = Sale::with(['user', 'items.menu'])
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $dailySummary = Sale::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total) as total_amount')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date', 'desc')
                ->get();

            $menuSummary = DB::table('sale_items')
                ->join('menus', 'sale_items.menu_id', '=', 'menus.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->select(
                    'menus.id',
                    'menus.name',
                    DB::raw('SUM(sale_items.quantity) as total_quantity'),
                    DB::raw('SUM(sale_items.subtotal) as total_sales'),
                    DB::raw('AVG(CASE WHEN sale_items.quantity > 0 THEN sale_items.subtotal / sale_items.quantity ELSE 0 END) as average_price')
                )
                ->whereBetween('sales.date', [$startDate, $endDate])
                ->groupBy('menus.id', 'menus.name')
                ->orderBy('total_sales', 'desc')
                ->get();

            $totalIncome = $sales->sum('total');
            $totalTransactions = $sales->count();
            $averageTransaction = $totalTransactions > 0 ? $totalIncome / $totalTransactions : 0;

            $data = [
                'title' => 'Laporan Pendapatan',
                'period' => Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y'),
                'sales' => $sales,
                'dailySummary' => $dailySummary,
                'menuSummary' => $menuSummary,
                'totalIncome' => $totalIncome,
                'totalTransactions' => $totalTransactions,
                'averageTransaction' => $averageTransaction,
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'generatedBy' => Auth::user()?->name ?? 'System',
            ];

            $pdf = Pdf::loadView('exports.income-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            $filename = 'laporan-pendapatan_' . $startDate . '_' . $endDate . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error exporting income PDF: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan laporan pengeluaran
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function expense(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Validasi tanggal
        if ($startDate > $endDate) {
            return redirect()->back()
                ->with('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.')
                ->withInput();
        }

        try {
            $restocks = Restock::with(['user', 'items.ingredient'])
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $dailySummary = Restock::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total) as total_amount')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date', 'desc')
                ->get();

            $ingredientSummary = DB::table('restock_items')
                ->join('ingredients', 'restock_items.ingredient_id', '=', 'ingredients.id')
                ->join('restocks', 'restock_items.restock_id', '=', 'restocks.id')
                ->select(
                    'ingredients.id',
                    'ingredients.name',
                    'ingredients.unit',
                    DB::raw('SUM(restock_items.quantity) as total_quantity'),
                    DB::raw('SUM(restock_items.subtotal) as total_cost'),
                    DB::raw('AVG(restock_items.price) as average_price'),
                    DB::raw('MAX(restock_items.price) as max_price'),
                    DB::raw('MIN(restock_items.price) as min_price')
                )
                ->whereBetween('restocks.date', [$startDate, $endDate])
                ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit')
                ->orderBy('total_cost', 'desc')
                ->get();

            $supplierSummary = [];
            $columns = DB::getSchemaBuilder()->getColumnListing('restocks');
            if (in_array('supplier', $columns)) {
                $supplierSummary = Restock::select(
                    'supplier',
                    DB::raw('COUNT(*) as transaction_count'),
                    DB::raw('SUM(total) as total_amount')
                )
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereNotNull('supplier')
                    ->groupBy('supplier')
                    ->orderBy('total_amount', 'desc')
                    ->get();
            }

            $totalExpense = $restocks->sum('total');
            $totalTransactions = $restocks->count();
            $averageTransaction = $totalTransactions > 0 ? $totalExpense / $totalTransactions : 0;
        } catch (\Exception $e) {
            Log::error('Error generating expense report: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengambil data laporan pengeluaran: ' . $e->getMessage())
                ->withInput();
        }

        return view('reports.expense', compact(
            'restocks',
            'dailySummary',
            'ingredientSummary',
            'supplierSummary',
            'totalExpense',
            'totalTransactions',
            'averageTransaction',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Mengekspor laporan pengeluaran ke PDF
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Http\Response
     */
    public function exportExpensePdf($startDate, $endDate)
    {
        try {
            $restocks = Restock::with(['user', 'items.ingredient'])
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $dailySummary = Restock::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total) as total_amount')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date', 'desc')
                ->get();

            $ingredientSummary = DB::table('restock_items')
                ->join('ingredients', 'restock_items.ingredient_id', '=', 'ingredients.id')
                ->join('restocks', 'restock_items.restock_id', '=', 'restocks.id')
                ->select(
                    'ingredients.id',
                    'ingredients.name',
                    'ingredients.unit',
                    DB::raw('SUM(restock_items.quantity) as total_quantity'),
                    DB::raw('SUM(restock_items.subtotal) as total_cost'),
                    DB::raw('AVG(restock_items.price) as average_price')
                )
                ->whereBetween('restocks.date', [$startDate, $endDate])
                ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit')
                ->orderBy('total_cost', 'desc')
                ->get();

            $totalExpense = $restocks->sum('total');
            $totalTransactions = $restocks->count();
            $averageTransaction = $totalTransactions > 0 ? $totalExpense / $totalTransactions : 0;

            $data = [
                'title' => 'Laporan Pengeluaran',
                'period' => Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y'),
                'restocks' => $restocks,
                'dailySummary' => $dailySummary,
                'ingredientSummary' => $ingredientSummary,
                'totalExpense' => $totalExpense,
                'totalTransactions' => $totalTransactions,
                'averageTransaction' => $averageTransaction,
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'generatedBy' => Auth::user()?->name ?? 'System',
            ];

            $pdf = Pdf::loadView('exports.expense-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            $filename = 'laporan-pengeluaran_' . $startDate . '_' . $endDate . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error exporting expense PDF: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan laporan laba rugi
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function profit(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($startDate > $endDate) {
            return redirect()->back()
                ->with('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.')
                ->withInput();
        }

        try {
            $incomeData = Sale::select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(total) as amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date')
                ->get();

            $expenseData = Restock::select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(total) as amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date')
                ->get();

            $dates = [];
            $incomes = [];
            $expenses = [];
            $profits = [];
            $incomeTransactions = [];
            $expenseTransactions = [];

            $allDates = collect($incomeData->pluck('date'))
                ->merge($expenseData->pluck('date'))
                ->unique()
                ->sort();

            foreach ($allDates as $date) {
                $dates[] = Carbon::parse($date)->format('d/m/Y');

                $income = $incomeData->where('date', $date)->first();
                $incomes[] = $income ? (float) $income->amount : 0;
                $incomeTransactions[] = $income ? $income->transaction_count : 0;

                $expense = $expenseData->where('date', $date)->first();
                $expenses[] = $expense ? (float) $expense->amount : 0;
                $expenseTransactions[] = $expense ? $expense->transaction_count : 0;

                $profits[] = ($income ? $income->amount : 0) - ($expense ? $expense->amount : 0);
            }

            $totalIncome = $incomeData->sum('amount');
            $totalExpense = $expenseData->sum('amount');
            $totalProfit = $totalIncome - $totalExpense;
            $totalIncomeTransactions = $incomeData->sum('transaction_count');
            $totalExpenseTransactions = $expenseData->sum('transaction_count');
            $profitMargin = $totalIncome > 0 ? ($totalProfit / $totalIncome) * 100 : 0;
            $averageIncomePerTransaction = $totalIncomeTransactions > 0 ? $totalIncome / $totalIncomeTransactions : 0;
            $averageExpensePerTransaction = $totalExpenseTransactions > 0 ? $totalExpense / $totalExpenseTransactions : 0;

            $bestDay = $incomeData->sortByDesc('amount')->first();
            $worstDay = $incomeData->sortBy('amount')->first();
        } catch (\Exception $e) {
            Log::error('Error generating profit report: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengambil data laporan laba rugi: ' . $e->getMessage())
                ->withInput();
        }

        return view('reports.profit', compact(
            'dates',
            'incomes',
            'expenses',
            'profits',
            'incomeTransactions',
            'expenseTransactions',
            'totalIncome',
            'totalExpense',
            'totalProfit',
            'totalIncomeTransactions',
            'totalExpenseTransactions',
            'profitMargin',
            'averageIncomePerTransaction',
            'averageExpensePerTransaction',
            'bestDay',
            'worstDay',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Mengekspor laporan laba rugi ke PDF
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Http\Response
     */
    public function exportProfitPdf($startDate, $endDate)
    {
        try {
            $incomeData = Sale::select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(total) as amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date')
                ->get();

            $expenseData = Restock::select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(total) as amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date')
                ->get();

            $dates = [];
            $incomes = [];
            $expenses = [];
            $profits = [];

            $allDates = collect($incomeData->pluck('date'))
                ->merge($expenseData->pluck('date'))
                ->unique()
                ->sort();

            foreach ($allDates as $date) {
                $dates[] = Carbon::parse($date)->format('d/m/Y');

                $income = $incomeData->where('date', $date)->first();
                $incomes[] = $income ? (float) $income->amount : 0;

                $expense = $expenseData->where('date', $date)->first();
                $expenses[] = $expense ? (float) $expense->amount : 0;

                $profits[] = ($income ? $income->amount : 0) - ($expense ? $expense->amount : 0);
            }

            $totalIncome = $incomeData->sum('amount');
            $totalExpense = $expenseData->sum('amount');
            $totalProfit = $totalIncome - $totalExpense;
            $profitMargin = $totalIncome > 0 ? ($totalProfit / $totalIncome) * 100 : 0;

            $dailyData = [];
            for ($i = 0; $i < count($dates); $i++) {
                $dailyData[] = [
                    'date' => $dates[$i],
                    'income' => $incomes[$i],
                    'expense' => $expenses[$i],
                    'profit' => $profits[$i],
                    'margin' => $incomes[$i] > 0 ? ($profits[$i] / $incomes[$i]) * 100 : 0
                ];
            }

            $data = [
                'title' => 'Laporan Laba Rugi',
                'period' => Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y'),
                'dates' => $dates,
                'incomes' => $incomes,
                'expenses' => $expenses,
                'profits' => $profits,
                'dailyData' => $dailyData,
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'totalProfit' => $totalProfit,
                'profitMargin' => $profitMargin,
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'generatedBy' => Auth::user()?->name ?? 'System',
            ];

            $pdf = Pdf::loadView('exports.profit-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            $filename = 'laporan-laba-rugi_' . $startDate . '_' . $endDate . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error exporting profit PDF: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan laporan stok
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function stock(Request $request)
    {
        $stockStatus = $request->get('status', 'all');
        $search = $request->get('search', '');

        try {
            $query = Ingredient::query();

            if ($stockStatus !== 'all') {
                switch ($stockStatus) {
                    case 'low':
                        $query->where('stock', '>', 0)
                            ->whereRaw('stock < min_stock');
                        break;
                    case 'out':
                        $query->where('stock', '<=', 0);
                        break;
                    case 'sufficient':
                        $query->whereRaw('stock >= min_stock');
                        break;
                }
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $ingredients = $query->orderBy('stock', 'asc')->orderBy('name')->get();

            $totalStockValue = $ingredients->sum(function ($ingredient) {
                return $ingredient->stock * $ingredient->price;
            });

            $stockStatistics = [
                'total_items' => $ingredients->count(),
                'out_of_stock' => $ingredients->where('stock', '<=', 0)->count(),
                'low_stock' => $ingredients->filter(function ($ingredient) {
                    return $ingredient->stock > 0 && $ingredient->stock < $ingredient->min_stock;
                })->count(),
                'sufficient_stock' => $ingredients->filter(function ($ingredient) {
                    return $ingredient->stock >= $ingredient->min_stock;
                })->count(),
            ];

            $stockMovement = $this->getStockMovementData();

            $warningItems = $ingredients->filter(function ($ingredient) {
                return $ingredient->stock <= 0 || $ingredient->stock < $ingredient->min_stock;
            })->sortBy('stock');

            $stockValueByCategory = [];
            if ($ingredients->count() > 0 && isset($ingredients->first()->category)) {
                $stockValueByCategory = $ingredients->groupBy('category')->map(function ($group) {
                    return $group->sum(function ($item) {
                        return $item->stock * $item->price;
                    });
                })->sortDesc();
            }
        } catch (\Exception $e) {
            Log::error('Error generating stock report: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengambil data laporan stok: ' . $e->getMessage())
                ->withInput();
        }

        return view('reports.stock', compact(
            'ingredients',
            'totalStockValue',
            'stockStatistics',
            'stockMovement',
            'warningItems',
            'stockValueByCategory',
            'stockStatus',
            'search'
        ));
    }

    /**
     * Mengekspor laporan stok ke PDF
     *
     * @return \Illuminate\Http\Response
     */
    public function exportStockPdf()
    {
        try {
            $ingredients = Ingredient::orderBy('stock', 'asc')->orderBy('name')->get();

            $totalStockValue = $ingredients->sum(function ($ingredient) {
                return $ingredient->stock * $ingredient->price;
            });

            $stockStatistics = [
                'total_items' => $ingredients->count(),
                'out_of_stock' => $ingredients->where('stock', '<=', 0)->count(),
                'low_stock' => $ingredients->filter(function ($ingredient) {
                    return $ingredient->stock > 0 && $ingredient->stock < $ingredient->min_stock;
                })->count(),
                'sufficient_stock' => $ingredients->filter(function ($ingredient) {
                    return $ingredient->stock >= $ingredient->min_stock;
                })->count(),
            ];

            $stockMovement = $this->getStockMovementData();

            $data = [
                'title' => 'Laporan Stok Bahan Baku',
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'generatedBy' => Auth::user()?->name ?? 'System',
                'ingredients' => $ingredients,
                'totalStockValue' => $totalStockValue,
                'stockStatistics' => $stockStatistics,
                'stockMovement' => $stockMovement,
            ];

            $pdf = Pdf::loadView('exports.stock-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            $filename = 'laporan-stok_' . now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error exporting stock PDF: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan data pergerakan stok dalam 30 hari terakhir
     *
     * @return array
     */
    private function getStockMovementData()
    {
        try {
            $restocks = RestockItem::select(
                'ingredient_id',
                DB::raw('SUM(quantity) as total_in')
            )
                ->whereHas('restock', function ($query) {
                    $query->whereDate('date', '>=', now()->subDays(30));
                })
                ->groupBy('ingredient_id')
                ->get()
                ->keyBy('ingredient_id');

            $sales = collect();

            try {
                $sales = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->join('menu_ingredient', 'sale_items.menu_id', '=', 'menu_ingredient.menu_id')
                    ->select(
                        'menu_ingredient.ingredient_id',
                        DB::raw('SUM(sale_items.quantity * menu_ingredient.quantity) as total_out')
                    )
                    ->whereDate('sales.date', '>=', now()->subDays(30))
                    ->groupBy('menu_ingredient.ingredient_id')
                    ->get()
                    ->keyBy('ingredient_id');
            } catch (\Exception $e) {
                Log::warning('Could not use menu_ingredient table: ' . $e->getMessage());

                $recentSales = Sale::with(['items.menu.ingredients'])
                    ->whereDate('date', '>=', now()->subDays(30))
                    ->get();

                $ingredientUsage = [];

                foreach ($recentSales as $sale) {
                    foreach ($sale->items as $item) {
                        if ($item->menu && $item->menu->ingredients) {
                            foreach ($item->menu->ingredients as $ingredient) {
                                $pivotQuantity = $ingredient->pivot->quantity ?? 0;
                                $totalUsage = $item->quantity * $pivotQuantity;

                                if (!isset($ingredientUsage[$ingredient->id])) {
                                    $ingredientUsage[$ingredient->id] = 0;
                                }
                                $ingredientUsage[$ingredient->id] += $totalUsage;
                            }
                        }
                    }
                }

                foreach ($ingredientUsage as $ingredientId => $totalOut) {
                    $sales->put($ingredientId, (object) [
                        'ingredient_id' => $ingredientId,
                        'total_out' => $totalOut
                    ]);
                }
            }

            $ingredients = Ingredient::all();

            $movementData = [];
            foreach ($ingredients as $ingredient) {
                $totalIn = $restocks->get($ingredient->id)->total_in ?? 0;
                $totalOut = $sales->get($ingredient->id)->total_out ?? 0;
                $netMovement = $totalIn - $totalOut;

                $movementData[] = [
                    'ingredient' => $ingredient->name,
                    'ingredient_id' => $ingredient->id,
                    'stock_in' => $totalIn,
                    'stock_out' => $totalOut,
                    'net_movement' => $netMovement,
                    'movement_percentage' => $netMovement > 0 ? 100 : ($netMovement < 0 ? -100 : 0),
                    'current_stock' => $ingredient->stock,
                    'min_stock' => $ingredient->min_stock,
                    'unit' => $ingredient->unit,
                ];
            }

            usort($movementData, function ($a, $b) {
                return $b['net_movement'] <=> $a['net_movement'];
            });

            return $movementData;
        } catch (\Exception $e) {
            Log::error('Error in getStockMovementData: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mengekspor laporan
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $type = $request->get('type');
        $format = $request->get('format', 'pdf');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if (!$type) {
            return redirect()->back()
                ->with('error', 'Jenis laporan harus dipilih.')
                ->withInput();
        }

        if ($startDate > $endDate) {
            return redirect()->back()
                ->with('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.')
                ->withInput();
        }

        try {
            switch ($type) {
                case 'income':
                    if ($format === 'pdf') {
                        return $this->exportIncomePdf($startDate, $endDate);
                    }
                    break;
                case 'expense':
                    if ($format === 'pdf') {
                        return $this->exportExpensePdf($startDate, $endDate);
                    }
                    break;
                case 'profit':
                    if ($format === 'pdf') {
                        return $this->exportProfitPdf($startDate, $endDate);
                    }
                    break;
                case 'stock':
                    if ($format === 'pdf') {
                        return $this->exportStockPdf();
                    }
                    break;
                default:
                    return redirect()->back()
                        ->with('error', 'Jenis laporan tidak valid.')
                        ->withInput();
            }

            return redirect()->back()
                ->with('error', 'Format ekspor tidak didukung.')
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error exporting report: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengekspor laporan: ' . $e->getMessage())
                ->withInput();
        }
    }
}
