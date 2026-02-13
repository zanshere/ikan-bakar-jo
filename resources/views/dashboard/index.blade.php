<!-- resources/views/dashboard/index.blade.php -->
@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan kinerja restoran')

@section('breadcrumb')
<span>/</span>
<span class="text-gray-700">Dashboard</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <button onclick="printPage()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
        Cetak
    </button>

    <div class="relative">
        <button id="dateRangeButton" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i data-lucide="calendar" class="w-4 h-4 mr-2"></i>
            Filter Tanggal
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pendapatan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">Rp {{ number_format($todayIncome, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pengeluaran Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">Rp {{ number_format($todayExpense, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i data-lucide="trending-down" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Profit Hari Ini</p>
                    <p class="text-2xl font-bold mt-2 {{ $todayProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        Rp {{ number_format($todayProfit, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Nilai Stok</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i data-lucide="package" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Pendapatan 30 Hari Terakhir</h3>
                <div class="flex space-x-2">
                    <button class="p-2 hover:bg-gray-100 rounded-lg" onclick="refreshIncomeChart()">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Pengeluaran 30 Hari Terakhir</h3>
                <div class="flex space-x-2">
                    <button class="p-2 hover:bg-gray-100 rounded-lg" onclick="refreshExpenseChart()">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Alerts & Top Menus -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 mr-2"></i>
                    Stok Rendah
                </h3>
                <a href="{{ route('ingredients.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    Lihat Semua
                    <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($lowStockIngredients as $ingredient)
                <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-800">{{ $ingredient->name }}</p>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-800">
                                {{ $ingredient->stock_status_badge }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span class="text-gray-600">
                                Stok: {{ $ingredient->formatted_stock }}
                            </span>
                            <span class="text-gray-600">
                                Minimal: {{ $ingredient->formatted_min_stock }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
                    </div>
                    <p class="text-gray-600">Semua stok bahan dalam kondisi baik</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Top Menus -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="trophy" class="w-5 h-5 text-yellow-500 mr-2"></i>
                    Menu Terlaris Bulan Ini
                </h3>
                <a href="{{ route('menus.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    Lihat Semua
                    <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                @foreach($topMenus as $index => $menu)
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <span class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-semibold mr-3
                            {{ $index < 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="font-medium text-gray-800">{{ $menu->name }}</p>
                            <p class="text-xs text-gray-500">{{ $menu->code }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-800">{{ $menu->total_quantity }} terjual</p>
                        <p class="text-sm text-gray-600">Rp {{ number_format($menu->total_sales, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Penjualan Terbaru</h3>
                <a href="{{ route('sales.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    Lihat Semua
                    <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                @foreach($recentSales as $sale)
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">#{{ $sale->id }} - {{ $sale->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $sale->formatted_date }} • {{ $sale->created_at->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-800">{{ $sale->formatted_total }}</p>
                        <p class="text-sm text-gray-600">{{ $sale->items->count() }} item</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Restocks -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Restock Terbaru</h3>
                <a href="{{ route('restocks.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    Lihat Semua
                    <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                @foreach($recentRestocks as $restock)
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">#{{ $restock->id }} - {{ $restock->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $restock->formatted_date }} • {{ $restock->created_at->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-800">{{ $restock->formatted_total }}</p>
                        <p class="text-sm text-gray-600">{{ $restock->items->count() }} item</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Income Chart
let incomeChart;
function initIncomeChart() {
    const ctx = document.getElementById('incomeChart').getContext('2d');
    incomeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($incomeChartData['labels']) !!},
            datasets: [{
                label: 'Pendapatan',
                data: {!! json_encode($incomeChartData['values']) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    },
                    grid: {
                        borderDash: [2, 2]
                    }
                }
            }
        }
    });
}

// Expense Chart
let expenseChart;
function initExpenseChart() {
    const ctx = document.getElementById('expenseChart').getContext('2d');
    expenseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($expenseChartData['labels']) !!},
            datasets: [{
                label: 'Pengeluaran',
                data: {!! json_encode($expenseChartData['values']) !!},
                backgroundColor: '#EF4444',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    },
                    grid: {
                        borderDash: [2, 2]
                    }
                }
            }
        }
    });
}

// Refresh functions
function refreshIncomeChart() {
    showLoading();
    fetch('{{ route("dashboard.quick-stats") }}?period=month')
        .then(response => response.json())
        .then(data => {
            // Update chart data here if needed
            hideLoading();
        });
}

function refreshExpenseChart() {
    showLoading();
    // Similar to refreshIncomeChart
    hideLoading();
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initIncomeChart();
    initExpenseChart();

    // Date range picker
    const dateRangeButton = document.getElementById('dateRangeButton');
    if (dateRangeButton) {
        new HSDatepicker(dateRangeButton, {
            range: true,
            format: 'dd/mm/yyyy',
            clearButton: true,
            todayButton: true,
        });
    }
});
</script>
@endpush
@endsection
