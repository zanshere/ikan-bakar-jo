<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Menu;
use App\Models\Restock;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filter date range
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Today's statistics
        $todayIncome = Sale::whereDate('date', today())->sum('total');
        $todayExpense = Restock::whereDate('date', today())->sum('total');
        $todayProfit = $todayIncome - $todayExpense;

        // This month statistics
        $monthIncome = Sale::whereMonth('date', now()->month)
                          ->whereYear('date', now()->year)
                          ->sum('total');
        $monthExpense = Restock::whereMonth('date', now()->month)
                              ->whereYear('date', now()->year)
                              ->sum('total');
        $monthProfit = $monthIncome - $monthExpense;

        // Low stock ingredients
        $lowStockIngredients = Ingredient::lowStock()
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        // Top selling menus this month
        $topMenus = DB::table('sale_items')
            ->join('menus', 'sale_items.menu_id', '=', 'menus.id')
            ->select(
                'menus.id',
                'menus.name',
                'menus.code',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_sales')
            )
            ->whereMonth('sale_items.created_at', now()->month)
            ->whereYear('sale_items.created_at', now()->year)
            ->groupBy('menus.id', 'menus.name', 'menus.code')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // Recent sales
        $recentSales = Sale::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Recent restocks
        $recentRestocks = Restock::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Income chart data (last 30 days)
        $incomeChartData = $this->getIncomeChartData(30);
        $expenseChartData = $this->getExpenseChartData(30);

        // Stock value
        $totalStockValue = Ingredient::sum(DB::raw('stock * price'));

        return view('dashboard.index', compact(
            'todayIncome',
            'todayExpense',
            'todayProfit',
            'monthIncome',
            'monthExpense',
            'monthProfit',
            'lowStockIngredients',
            'topMenus',
            'recentSales',
            'recentRestocks',
            'incomeChartData',
            'expenseChartData',
            'totalStockValue',
            'startDate',
            'endDate'
        ));
    }

    private function getIncomeChartData($days = 30)
    {
        $data = Sale::select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(total) as total')
            )
            ->whereDate('date', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];

        foreach ($data as $item) {
            $labels[] = Carbon::parse($item->date)->format('d/m');
            $values[] = (float) $item->total;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function getExpenseChartData($days = 30)
    {
        $data = Restock::select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(total) as total')
            )
            ->whereDate('date', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];

        foreach ($data as $item) {
            $labels[] = Carbon::parse($item->date)->format('d/m');
            $values[] = (float) $item->total;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function getQuickStats(Request $request)
    {
        $period = $request->get('period', 'today');

        switch ($period) {
            case 'today':
                $income = Sale::whereDate('date', today())->sum('total');
                $expense = Restock::whereDate('date', today())->sum('total');
                break;

            case 'week':
                $income = Sale::whereBetween('date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->sum('total');

                $expense = Restock::whereBetween('date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->sum('total');
                break;

            case 'month':
                $income = Sale::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->sum('total');

                $expense = Restock::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->sum('total');
                break;

            case 'year':
                $income = Sale::whereYear('date', now()->year)->sum('total');
                $expense = Restock::whereYear('date', now()->year)->sum('total');
                break;

            default:
                $income = 0;
                $expense = 0;
        }

        $profit = $income - $expense;

        return response()->json([
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
            'formatted_income' => 'Rp ' . number_format($income, 0, ',', '.'),
            'formatted_expense' => 'Rp ' . number_format($expense, 0, ',', '.'),
            'formatted_profit' => 'Rp ' . number_format($profit, 0, ',', '.'),
        ]);
    }
}
