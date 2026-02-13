<!-- resources/views/reports/stock.blade.php -->
@extends('layouts.app')

@section('page-title', 'Laporan Stok')
@section('page-description', 'Analisis stok bahan baku')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('reports.stock') }}" class="text-gray-500 hover:text-gray-700">Laporan</a>
<span>/</span>
<span class="text-gray-700">Stok</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <button onclick="exportReport()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
        Export PDF
    </button>
    <button onclick="printReport()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700">
        <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
        Cetak
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Stok</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        onchange="this.form.submit()">
                    <option value="all" {{ $stockStatus == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="low" {{ $stockStatus == 'low' ? 'selected' : '' }}>Stok Rendah</option>
                    <option value="out" {{ $stockStatus == 'out' ? 'selected' : '' }}>Stok Habis</option>
                    <option value="sufficient" {{ $stockStatus == 'sufficient' ? 'selected' : '' }}>Stok Cukup</option>
                </select>
            </div>

            <div class="md:col-span-3 flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                    Filter
                </button>
                <a href="{{ route('reports.stock') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Bahan</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $ingredients->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nilai Total Stok</p>
                    <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Stok Rendah</p>
                    <p class="text-2xl font-bold text-amber-600">
                        {{ $ingredients->where('stock_status', 'low')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Stok Habis</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $ingredients->where('stock_status', 'empty')->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Overview -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Overview Stok</h3>
            <p class="text-sm text-gray-600 mt-1">Status stok bahan baku saat ini</p>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Minimum</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($ingredients as $ingredient)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 bg-blue-100 rounded flex items-center justify-center mr-3">
                                        <i data-lucide="package" class="w-4 h-4 text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $ingredient->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $ingredient->code }} • {{ $ingredient->unit }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $ingredient->formatted_stock }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900">{{ $ingredient->formatted_min_stock }}</div>
                            </td>
                            <td class="px-4 py-3">
                                {!! $ingredient->stock_status_badge !!}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900">{{ $ingredient->formatted_price }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-blue-600">{{ $ingredient->formatted_total_value }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($ingredients->isEmpty())
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data stok yang sesuai dengan filter
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Movement -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Pergerakan Stok (30 Hari Terakhir)</h3>
            <p class="text-sm text-gray-600 mt-1">Analisis masuk dan keluar stok</p>
        </div>

        <div class="p-6">
            @if(!empty($stockMovement))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Keluar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Movement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($stockMovement as $movement)
                        @php
                            $trendClass = $movement['net_movement'] > 0 ? 'text-green-600' : ($movement['net_movement'] < 0 ? 'text-red-600' : 'text-gray-600');
                            $trendIcon = $movement['net_movement'] > 0 ? 'trending-up' : ($movement['net_movement'] < 0 ? 'trending-down' : 'minus');
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $movement['ingredient'] }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-green-600 font-medium">+{{ number_format($movement['stock_in'], 2, ',', '.') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-red-600 font-medium">-{{ number_format($movement['stock_out'], 2, ',', '.') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold {{ $trendClass }}">
                                    {{ $movement['net_movement'] >= 0 ? '+' : '' }}{{ number_format($movement['net_movement'], 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ number_format($movement['current_stock'], 2, ',', '.') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center {{ $trendClass }}">
                                    <i data-lucide="{{ $trendIcon }}" class="w-4 h-4 mr-1"></i>
                                    <span class="text-sm">
                                        @if($movement['net_movement'] > 0)
                                        Meningkat
                                        @elseif($movement['net_movement'] < 0)
                                        Menurun
                                        @else
                                        Stabil
                                        @endif
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <i data-lucide="bar-chart-3" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                <p class="text-gray-600">Tidak ada data pergerakan stok</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Stock Value Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock Value Distribution -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Distribusi Nilai Stok</h3>
                <p class="text-sm text-gray-600 mt-1">Persentase nilai stok per bahan</p>
            </div>

            <div class="p-6">
                <div class="h-64">
                    <canvas id="stockValueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Stock Status Distribution -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Distribusi Status Stok</h3>
                <p class="text-sm text-gray-600 mt-1">Persentase bahan berdasarkan status stok</p>
            </div>

            <div class="p-6">
                <div class="h-64">
                    <canvas id="stockStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Rekomendasi</h3>
            <p class="text-sm text-gray-600 mt-1">Saran untuk manajemen stok yang lebih baik</p>
        </div>

        <div class="p-6">
            @php
                $lowStockCount = $ingredients->where('stock_status', 'low')->count();
                $outOfStockCount = $ingredients->where('stock_status', 'empty')->count();
                $totalIngredients = $ingredients->count();
            @endphp

            <div class="space-y-4">
                @if($outOfStockCount > 0)
                <div class="flex items-start p-4 bg-red-50 rounded-lg">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="font-medium text-red-800">Segera Restock!</h4>
                        <p class="text-sm text-red-700 mt-1">
                            Ada {{ $outOfStockCount }} bahan yang stoknya sudah habis. Segera lakukan restock untuk menghindari gangguan operasional.
                        </p>
                        <div class="mt-2">
                            @foreach($ingredients->where('stock_status', 'empty') as $ingredient)
                            <span class="inline-block px-2 py-1 text-xs bg-red-100 text-red-800 rounded mr-2 mb-2">
                                {{ $ingredient->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if($lowStockCount > 0)
                <div class="flex items-start p-4 bg-amber-50 rounded-lg">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="font-medium text-amber-800">Stok Rendah</h4>
                        <p class="text-sm text-amber-700 mt-1">
                            Ada {{ $lowStockCount }} bahan dengan stok di bawah minimum. Pertimbangkan untuk melakukan restock dalam waktu dekat.
                        </p>
                        <div class="mt-2">
                            @foreach($ingredients->where('stock_status', 'low')->take(5) as $ingredient)
                            <span class="inline-block px-2 py-1 text-xs bg-amber-100 text-amber-800 rounded mr-2 mb-2">
                                {{ $ingredient->name }} ({{ $ingredient->formatted_stock }})
                            </span>
                            @endforeach
                            @if($lowStockCount > 5)
                            <span class="inline-block px-2 py-1 text-xs bg-amber-100 text-amber-800 rounded">
                                +{{ $lowStockCount - 5 }} lainnya
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($outOfStockCount == 0 && $lowStockCount == 0)
                <div class="flex items-start p-4 bg-green-50 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="font-medium text-green-800">Stok dalam Kondisi Baik</h4>
                        <p class="text-sm text-green-700 mt-1">
                            Semua bahan baku memiliki stok yang mencukupi. Tidak ada bahan yang perlu segera direstock.
                        </p>
                    </div>
                </div>
                @endif

                <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-blue-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="font-medium text-blue-800">Tips Manajemen Stok</h4>
                        <ul class="text-sm text-blue-700 mt-1 space-y-1">
                            <li>• Lakukan stock opname secara berkala untuk memastikan akurasi data</li>
                            <li>• Setel stok minimum yang realistis berdasarkan pola penggunaan</li>
                            <li>• Pertimbangkan untuk melakukan bulk ordering untuk bahan dengan turnover tinggi</li>
                            <li>• Monitor pergerakan stok secara rutin untuk mengidentifikasi pola penggunaan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportReport() {
    window.location.href = '{{ route("reports.stock.pdf") }}';
}

function printReport() {
    window.print();
}

// Stock Value Chart
document.addEventListener('DOMContentLoaded', function() {
    @if($ingredients->count() > 0)
    // Stock Value Distribution Chart
    const stockValueCtx = document.getElementById('stockValueChart').getContext('2d');

    const valueLabels = {!! json_encode($ingredients->sortByDesc(function($item) {
        return $item->stock * $item->price;
    })->take(8)->pluck('name')->toArray()) !!};

    const valueData = {!! json_encode($ingredients->sortByDesc(function($item) {
        return $item->stock * $item->price;
    })->take(8)->map(function($item) {
        return $item->stock * $item->price;
    })->toArray()) !!};

    new Chart(stockValueCtx, {
        type: 'bar',
        data: {
            labels: valueLabels,
            datasets: [{
                label: 'Nilai Stok',
                data: valueData,
                backgroundColor: '#3B82F6',
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
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
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
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // Stock Status Distribution Chart
    const stockStatusCtx = document.getElementById('stockStatusChart').getContext('2d');

    const statusCounts = {
        'Cukup': {{ $stockStatistics['sufficient_stock'] }},
        'Rendah': {{ $stockStatistics['low_stock'] }},
        'Habis': {{ $stockStatistics['out_of_stock'] }}
    };

    new Chart(stockStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cukup', 'Rendah', 'Habis'],
            datasets: [{
                data: Object.values(statusCounts),
                backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    @endif
});
</script>
@endpush

@push('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        font-size: 12px;
    }

    .bg-white {
        background: white !important;
        border: 1px solid #e5e7eb !important;
    }

    .shadow-sm {
        box-shadow: none !important;
    }
}
</style>
@endpush
