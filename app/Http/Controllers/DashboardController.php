<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Restock;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isOwner()) {
            return $this->ownerDashboard($request);
        } else {
            return $this->userDashboard($request);
        }
    }

    /**
     * Owner dashboard.
     */
    private function ownerDashboard(Request $request)
    {
        // Filter date range
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Today's statistics
        $todayIncome = Sale::whereDate('date', today())
            ->where('payment_status', 'paid')
            ->sum('total');
        $todayExpense = Restock::whereDate('date', today())->sum('total');
        $todayProfit = $todayIncome - $todayExpense;
        $todayOrders = Order::whereDate('order_date', today())->count();

        // Pending orders count
        $pendingOrders = Order::where('status', 'pending')->count();

        // This month statistics
        $monthIncome = Sale::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('payment_status', 'paid')
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

        // Out of stock ingredients
        $outOfStockIngredients = Ingredient::outOfStock()
            ->orderBy('name')
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
            ->where('payment_status', 'paid')
            ->latest()
            ->limit(10)
            ->get();

        // Recent orders
        $recentOrders = Order::with('user')
            ->latest('order_date')
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

        // User statistics
        $totalUsers = User::count();
        $totalCustomers = User::where('role', 'user')->count();

        return view('dashboard.owner', compact(
            'todayIncome',
            'todayExpense',
            'todayProfit',
            'todayOrders',
            'pendingOrders',
            'monthIncome',
            'monthExpense',
            'monthProfit',
            'lowStockIngredients',
            'outOfStockIngredients',
            'topMenus',
            'recentSales',
            'recentOrders',
            'recentRestocks',
            'incomeChartData',
            'expenseChartData',
            'totalStockValue',
            'totalUsers',
            'totalCustomers',
            'startDate',
            'endDate'
        ));
    }

    /**
     * User dashboard.
     */
    private function userDashboard(Request $request)
    {
        $user = Auth::user();

        // User statistics
        $totalOrders = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('status', 'pending')->count();
        $acceptedOrders = Order::where('user_id', $user->id)->where('status', 'accepted')->count();
        $completedOrders = Order::where('user_id', $user->id)->where('status', 'completed')->count();
        $rejectedOrders = Order::where('user_id', $user->id)->where('status', 'rejected')->count();

        // Total spent
        $totalSpent = Sale::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->sum('total');

        // Recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with('items.menu')
            ->latest('order_date')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $order->formatted_total = 'Rp ' . number_format($order->total, 0, ',', '.');
                $order->formatted_date = $order->order_date->format('d/m/Y H:i');
                return $order;
            });

        // Favorite menus
        $favoriteMenus = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $user->id)
            ->select(
                'menus.id',
                'menus.name',
                'menus.image',
                DB::raw('SUM(order_items.quantity) as total_ordered')
            )
            ->groupBy('menus.id', 'menus.name', 'menus.image')
            ->orderBy('total_ordered', 'desc')
            ->limit(5)
            ->get();

        // Recommended menus (based on popular items)
        $recommendedMenus = Menu::where('is_active', true)
            ->where('type', Menu::TYPE_MAIN)
            ->with(['ingredients' => function($query) {
                $query->where('stock', '>', 0);
            }])
            ->get()
            ->filter(function($menu) {
                return $menu->ingredients->every(function($ingredient) {
                    return $ingredient->stock > 0;
                }) && $menu->ingredients->count() > 0;
            })
            ->take(6)
            ->map(function($menu) {
                $menu->formatted_price = 'Rp ' . number_format($menu->price, 0, ',', '.');
                return $menu;
            });

        // Monthly spending chart
        $monthlySpending = Sale::select(
                DB::raw('MONTH(date) as month'),
                DB::raw('YEAR(date) as year'),
                DB::raw('SUM(total) as total')
            )
            ->where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereYear('date', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $spendingLabels = [];
        $spendingValues = [];

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        foreach ($monthlySpending as $data) {
            $spendingLabels[] = $monthNames[$data->month] ?? 'Unknown';
            $spendingValues[] = (float) $data->total;
        }

        return view('dashboard.user', compact(
            'totalOrders',
            'pendingOrders',
            'acceptedOrders',
            'completedOrders',
            'rejectedOrders',
            'totalSpent',
            'recentOrders',
            'favoriteMenus',
            'recommendedMenus',
            'spendingLabels',
            'spendingValues'
        ));
    }

    /**
     * Get income chart data.
     */
    private function getIncomeChartData($days = 30)
    {
        $data = Sale::select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(total) as total')
            )
            ->whereDate('date', '>=', now()->subDays($days))
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayData = $data->firstWhere('date', $date);
            $labels[] = now()->subDays($i)->format('d/m');
            $values[] = $dayData ? (float) $dayData->total : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get expense chart data.
     */
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

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayData = $data->firstWhere('date', $date);
            $labels[] = now()->subDays($i)->format('d/m');
            $values[] = $dayData ? (float) $dayData->total : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Calculate total cost of goods sold for a specific date including sauces.
     */
    private function calculateCostForDate($date)
    {
        try {
            // Get all paid sales for the date with their items, menus, and sauces
            $sales = Sale::with([
                    'items.menu.ingredients',
                    'items.sauce.ingredients'
                ])
                ->whereDate('date', $date)
                ->where('payment_status', 'paid')
                ->get();

            $totalCost = 0;

            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    // Calculate cost for menu ingredients
                    if ($item->menu && $item->menu->ingredients) {
                        foreach ($item->menu->ingredients as $ingredient) {
                            $cost = $ingredient->price * $ingredient->pivot->quantity * $item->quantity;
                            $totalCost += $cost;
                        }
                    }

                    // Calculate cost for sauce ingredients if sauce exists
                    if ($item->sauce && $item->sauce->ingredients) {
                        foreach ($item->sauce->ingredients as $ingredient) {
                            $cost = $ingredient->price * $ingredient->pivot->quantity * $item->quantity;
                            $totalCost += $cost;
                        }
                    }
                }
            }

            return $totalCost;
        } catch (\Exception $e) {
            Log::error('Error calculating cost for date: ' . $e->getMessage(), [
                'date' => $date,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return 0;
        }
    }

    /**
     * Get profit chart data with accurate cost calculation.
     */
    private function getProfitChartData($days = 30)
    {
        $labels = [];
        $incomes = [];
        $costs = [];
        $profits = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');

            // Get income for the date
            $income = Sale::whereDate('date', $date)
                ->where('payment_status', 'paid')
                ->sum('total');

            // Get cost for the date using our new method
            $cost = $this->calculateCostForDate($date);

            $labels[] = now()->subDays($i)->format('d/m');
            $incomes[] = (float) $income;
            $costs[] = (float) $cost;
            $profits[] = (float) ($income - $cost);
        }

        return [
            'labels' => $labels,
            'incomes' => $incomes,
            'costs' => $costs,
            'profits' => $profits,
        ];
    }

    /**
     * Get quick stats for dashboard (AJAX).
     */
    public function getQuickStats(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', 'today');

        if ($user->isOwner()) {
            return $this->getOwnerQuickStats($period);
        } else {
            return $this->getUserQuickStats($user->id);
        }
    }

    /**
     * Get owner quick stats.
     */
    private function getOwnerQuickStats($period)
    {
        switch ($period) {
            case 'today':
                $income = Sale::whereDate('date', today())
                    ->where('payment_status', 'paid')
                    ->sum('total');
                $expense = Restock::whereDate('date', today())->sum('total');
                $orders = Order::whereDate('order_date', today())->count();
                $pendingOrders = Order::whereDate('order_date', today())
                    ->where('status', 'pending')
                    ->count();
                break;

            case 'week':
                $income = Sale::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->where('payment_status', 'paid')
                    ->sum('total');
                $expense = Restock::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('total');
                $orders = Order::whereBetween('order_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count();
                $pendingOrders = Order::whereBetween('order_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->where('status', 'pending')
                    ->count();
                break;

            case 'month':
                $income = Sale::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->where('payment_status', 'paid')
                    ->sum('total');
                $expense = Restock::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->sum('total');
                $orders = Order::whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year)
                    ->count();
                $pendingOrders = Order::whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year)
                    ->where('status', 'pending')
                    ->count();
                break;

            case 'year':
                $income = Sale::whereYear('date', now()->year)
                    ->where('payment_status', 'paid')
                    ->sum('total');
                $expense = Restock::whereYear('date', now()->year)->sum('total');
                $orders = Order::whereYear('order_date', now()->year)->count();
                $pendingOrders = Order::whereYear('order_date', now()->year)
                    ->where('status', 'pending')
                    ->count();
                break;

            default:
                $income = 0;
                $expense = 0;
                $orders = 0;
                $pendingOrders = 0;
        }

        $profit = $income - $expense;

        return response()->json([
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
            'orders' => $orders,
            'pending_orders' => $pendingOrders,
            'formatted_income' => 'Rp ' . number_format($income, 0, ',', '.'),
            'formatted_expense' => 'Rp ' . number_format($expense, 0, ',', '.'),
            'formatted_profit' => 'Rp ' . number_format($profit, 0, ',', '.'),
        ]);
    }

    /**
     * Get user quick stats.
     */
    private function getUserQuickStats($userId)
    {
        $totalOrders = Order::where('user_id', $userId)->count();
        $pendingOrders = Order::where('user_id', $userId)->where('status', 'pending')->count();
        $completedOrders = Order::where('user_id', $userId)->where('status', 'completed')->count();
        $totalSpent = Sale::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->sum('total');

        return response()->json([
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'total_spent' => $totalSpent,
            'formatted_total_spent' => 'Rp ' . number_format($totalSpent, 0, ',', '.'),
        ]);
    }
}
