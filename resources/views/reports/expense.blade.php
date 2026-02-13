<!-- resources/views/reports/expense.blade.php -->
@extends('layouts.app')

@section('page-title', 'Laporan Pengeluaran')
@section('page-description', 'Analisis pengeluaran restoran')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('reports.expense') }}" class="text-gray-500 hover:text-gray-700">Laporan</a>
<span>/</span>
<span class="text-gray-700">Pengeluaran</span>
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
                <a href="{{ route('reports.expense') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
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
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-red-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="truck" class="w-6 h-6 text-blue-600"></i>
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
                    <i data-lucide="package" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Rata-rata per Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800">
                        Rp {{ number_format($totalTransactions > 0 ? $totalExpense / $totalTransactions : 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Summary -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Rekap Harian</h3>
            <p class="text-sm text-gray-600 mt-1">Ringkasan pengeluaran per hari</p>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pengeluaran</th>
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
                                <div class="font-bold text-red-600">Rp {{ number_format($daily->total_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900">
                                    Rp {{ number_format($daily->transaction_count > 0 ? $daily->total_amount / $daily->transaction_count : 0, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('restocks.index') }}?start_date={{ $daily->date }}&end_date={{ $daily->date }}"
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
                            Tidak ada data pengeluaran pada periode ini
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Ingredient Analysis -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Analisis Bahan Baku</h3>
            <p class="text-sm text-gray-600 mt-1">Bahan baku dengan pengeluaran terbesar</p>
        </div>

        <div class="p-6">
            @if($ingredientSummary->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Harga</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Biaya</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% dari Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($ingredientSummary as $ingredient)
                            @php
                                $percentage = $totalExpense > 0 ? ($ingredient->total_cost / $totalExpense) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $ingredient->name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ number_format($ingredient->total_quantity, 2, ',', '.') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900">Rp {{ number_format($ingredient->average_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-red-600">Rp {{ number_format($ingredient->total_cost, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                            <div class="bg-red-600 h-2.5 rounded-full"
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
                        <canvas id="ingredientChart"></canvas>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-8">
                <i data-lucide="package-x" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                <p class="text-gray-600">Tidak ada data pengeluaran bahan pada periode ini</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Transaksi Restock Terbaru</h3>
            <p class="text-sm text-gray-600 mt-1">{{ $restocks->count() }} transaksi terbaru</p>
        </div>

        <div class="p-6">
            @if($restocks->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($restocks as $restock)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('restocks.show', $restock) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    #{{ str_pad($restock->id, 6, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $restock->date->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $restock->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $restock->user->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $restock->items->count() }} bahan</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-red-600">Rp {{ number_format($restock->total, 0, ',', '.') }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <i data-lucide="truck" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                <p class="text-gray-600">Tidak ada transaksi restock pada periode ini</p>
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

    const url = '{{ route("reports.expense.pdf", ["startDate" => "PLACEHOLDER_START", "endDate" => "PLACEHOLDER_END"]) }}'
        .replace('PLACEHOLDER_START', encodedStartDate)
        .replace('PLACEHOLDER_END', encodedEndDate);

    window.location.href = url;
}

function printReport() {
    window.print();
}

// Ingredient Analysis Chart
document.addEventListener('DOMContentLoaded', function() {
    @if($ingredientSummary->count() > 0)
    const ctx = document.getElementById('ingredientChart').getContext('2d');

    const labels = {!! json_encode($ingredientSummary->pluck('name')->toArray()) !!};
    const data = {!! json_encode($ingredientSummary->pluck('total_cost')->toArray()) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Biaya',
                data: data,
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
