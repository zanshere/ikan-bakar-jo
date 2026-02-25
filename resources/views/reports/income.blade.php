<!-- resources/views/reports/income.blade.php -->
@extends('layouts.app')

@section('page-title', 'Laporan Pendapatan')
@section('page-description', 'Analisis pendapatan restoran')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('reports.income') }}" class="text-gray-500 hover:text-gray-700">Laporan</a>
<span>/</span>
<span class="text-gray-700">Pendapatan</span>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-2 flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                    Filter
                </button>
                <a href="{{ route('reports.income') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="receipt" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalTransactions }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="shopping-cart" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Rata-rata per Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800">
                        Rp {{ number_format($totalTransactions > 0 ? $totalIncome / $totalTransactions : 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Summary -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Rekap Harian</h3>
            <p class="text-sm text-gray-600 mt-1">Ringkasan pendapatan per hari</p>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pendapatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($dailySummary as $daily)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($daily->date)->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($daily->date)->format('l') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $daily->transaction_count }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-green-600">Rp {{ number_format($daily->total_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900">
                                    Rp {{ number_format($daily->transaction_count > 0 ? $daily->total_amount / $daily->transaction_count : 0, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('sales.index') }}?start_date={{ $daily->date }}&end_date={{ $daily->date }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($dailySummary->isEmpty())
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data pendapatan pada periode ini
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Menu Performance -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Performa Menu</h3>
            <p class="text-sm text-gray-600 mt-1">Menu terlaris berdasarkan pendapatan</p>
        </div>

        <div class="p-6">
            @if($menuSummary->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Rata-rata</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% dari Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($menuSummary as $menu)
                            @php
                                $percentage = $totalIncome > 0 ? ($menu->total_sales / $totalIncome) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $menu->name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $menu->total_quantity }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-green-600">Rp {{ number_format($menu->total_sales, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900">Rp {{ number_format($menu->average_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium">{{ number_format($percentage, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Chart -->
                <div>
                    <div class="h-64">
                        <canvas id="menuChart"></canvas>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-8">
                <i data-lucide="utensils-crossed" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                <p class="text-gray-600">Tidak ada data penjualan menu pada periode ini</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Transaksi Terbaru</h3>
            <p class="text-sm text-gray-600 mt-1">{{ $sales->count() }} transaksi terbaru</p>
        </div>

        <div class="p-6">
            @if($sales->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kasir</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($sales as $sale)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                                </a>
                                @if($sale->order && $sale->order->order_number)
                                <div class="text-xs text-gray-500">Order: {{ $sale->order->order_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $sale->date->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $sale->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $sale->user->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $sale->items->sum('quantity') }} item</div>
                                <div class="text-xs text-gray-500">{{ $sale->items->count() }} jenis</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-green-600">Rp {{ number_format($sale->total, 0, ',', '.') }}</div>
                                @if($sale->payment_method)
                                <div class="text-xs text-gray-500">{{ $sale->payment_method_text }}</div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <i data-lucide="receipt" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                <p class="text-gray-600">Tidak ada transaksi pada periode ini</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportReport() {
    const urlParams = new URLSearchParams(window.location.search);
    let startDate = urlParams.get('start_date');
    let endDate = urlParams.get('end_date');

    if (!startDate) {
        startDate = '{{ $startDate }}';
    }
    if (!endDate) {
        endDate = '{{ $endDate }}';
    }

    const encodedStartDate = encodeURIComponent(startDate);
    const encodedEndDate = encodeURIComponent(endDate);

    const url = '{{ route("reports.income.pdf", ["startDate" => "PLACEHOLDER_START", "endDate" => "PLACEHOLDER_END"]) }}'
        .replace('PLACEHOLDER_START', encodedStartDate)
        .replace('PLACEHOLDER_END', encodedEndDate);

    window.location.href = url;
}

function printReport() {
    window.print();
}

// Menu Performance Chart
document.addEventListener('DOMContentLoaded', function() {
    @if($menuSummary->count() > 0)
    const ctx = document.getElementById('menuChart').getContext('2d');

    const labels = {!! json_encode($menuSummary->pluck('name')->toArray()) !!};
    const data = {!! json_encode($menuSummary->pluck('total_sales')->toArray()) !!};

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                    '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#8B5CF6'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: Rp ${value.toLocaleString('id-ID')} (${percentage}%)`;
                        }
                    }
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

    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }
}
</style>
@endpush
