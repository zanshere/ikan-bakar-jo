<!-- resources/views/reports/profit.blade.php -->
@extends('layouts.app')

@section('page-title', 'Laporan Profit')
@section('page-description', 'Analisis profit/loss restoran')

@section('breadcrumb')
    <span>/</span>
    <a href="{{ route('reports.profit') }}" class="text-gray-500 hover:text-gray-700">Laporan</a>
    <span>/</span>
    <span class="text-gray-700">Profit</span>
@endsection

@section('header-buttons')
    <div class="flex space-x-2">
        <button onclick="exportReport()"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
            <i data-lucide="download" class="w-4 h-4 mr-2"></i>
            Export PDF
        </button>
        <button onclick="printReport()"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700">
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
                    <a href="{{ route('reports.profit') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
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
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Pendapatan</p>
                        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="trending-down" class="w-6 h-6 text-red-600"></i>
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
                        <i data-lucide="dollar-sign" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Profit</p>
                        <p class="text-2xl font-bold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($totalProfit, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="percent" class="w-6 h-6 text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Profit Margin</p>
                        <p class="text-2xl font-bold {{ $profitMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($profitMargin, 2) }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit Chart -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Grafik Profit Harian</h3>
                <p class="text-sm text-gray-600 mt-1">Perbandingan pendapatan, pengeluaran, dan profit</p>
            </div>

            <div class="p-6">
                <div class="h-96">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Profit Analysis -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Analisis Profit Harian</h3>
                <p class="text-sm text-gray-600 mt-1">Detail profit per hari</p>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pendapatan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pengeluaran</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Profit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Margin</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $dailyData = [];
                                for ($i = 0; $i < count($dates); $i++) {
                                    $dailyData[] = [
                                        'date' => $dates[$i],
                                        'income' => $incomes[$i],
                                        'expense' => $expenses[$i],
                                        'profit' => $profits[$i],
                                        'margin' => $incomes[$i] > 0 ? ($profits[$i] / $incomes[$i]) * 100 : 0,
                                    ];
                                }
                            @endphp

                            @foreach ($dailyData as $daily)
                                @php
                                    $profitClass = $daily['profit'] >= 0 ? 'text-green-600' : 'text-red-600';
                                    $marginClass = $daily['margin'] >= 0 ? 'text-green-600' : 'text-red-600';
                                    $status = $daily['profit'] >= 0 ? 'Profit' : 'Loss';
                                    $statusClass =
                                        $daily['profit'] >= 0
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $daily['date'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900">Rp {{ number_format($daily['income'], 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900">Rp {{ number_format($daily['expense'], 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold {{ $profitClass }}">Rp
                                            {{ number_format($daily['profit'], 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium {{ $marginClass }}">
                                            {{ number_format($daily['margin'], 2) }}%</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if (empty($dailyData))
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Tidak ada data profit pada periode ini
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Profit Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profit Distribution -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Distribusi Profit</h3>
                    <p class="text-sm text-gray-600 mt-1">Bagaimana profit terbentuk</p>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        @php
                            $incomePercentage = $totalIncome > 0 ? 100 : 0;

                            // Hitung persentase pengeluaran terhadap pendapatan
                            $expensePercentageRaw = $totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 0;
                            // Batasi maksimal 100% untuk tampilan bar
                            $expenseDisplayPercentage = min($expensePercentageRaw, 100);

                            // Hitung persentase profit terhadap pendapatan
                            $profitPercentageRaw = $totalIncome > 0 ? ($totalProfit / $totalIncome) * 100 : 0;
                            // Batasi maksimal 100% untuk tampilan bar
                            $profitDisplayPercentage = min(abs($profitPercentageRaw), 100);

                            // Tentukan kelas warna untuk profit bar
                            $profitBarClass = $profitPercentageRaw >= 0 ? 'bg-green-600' : 'bg-red-600';
                            $profitTextClass = $profitPercentageRaw >= 0 ? 'text-green-600' : 'text-red-600';

                            // Tentukan apakah mengalami kerugian (pengeluaran > pendapatan)
                            $isLoss = $totalExpense > $totalIncome;
                        @endphp

                        <!-- Bar Pendapatan (selalu 100%) -->
                        <div>
                            <div class="flex flex-wrap items-center justify-between mb-1 gap-1">
                                <span class="text-sm font-medium text-gray-700">Pendapatan</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-green-600 whitespace-nowrap">100%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-green-600 h-2.5 rounded-full" style="width: 100%"></div>
                            </div>
                            <div class="flex justify-end mt-1">
                                <span class="text-sm text-gray-500 font-medium whitespace-nowrap">
                                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Bar Pengeluaran (dibatasi maksimal 100%) -->
                        <div>
                            <div class="flex flex-wrap items-center justify-between mb-1 gap-1">
                                <span class="text-sm font-medium text-gray-700">Pengeluaran</span>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-medium text-red-600 whitespace-nowrap">
                                        {{ number_format($expensePercentageRaw, 1) }}%
                                    </span>
                                    @if ($expensePercentageRaw > 100)
                                        <span
                                            class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded-full whitespace-nowrap">
                                            +{{ number_format($expensePercentageRaw - 100, 1) }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <!-- Bar dengan lebar maksimal 100% -->
                                <div class="bg-red-600 h-2.5 rounded-full"
                                    style="width: {{ $expenseDisplayPercentage }}%;">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center justify-between mt-1 gap-1">
                                <span class="text-xs text-red-600 break-word max-w-[60%]">
                                    @if ($isLoss)
                                        ⚠️ {{ number_format($expensePercentageRaw - 100, 1) }}% di atas pendapatan
                                    @endif
                                </span>
                                <span
                                    class="text-sm {{ $isLoss ? 'text-red-600 font-semibold' : 'text-gray-500' }} whitespace-nowrap">
                                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Bar Profit (bisa positif atau negatif) -->
                        <div>
                            <div class="flex flex-wrap items-center justify-between mb-1 gap-1">
                                <span class="text-sm font-medium text-gray-700">Profit</span>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-medium {{ $profitTextClass }} whitespace-nowrap">
                                        {{ $profitPercentageRaw >= 0 ? '+' : '' }}{{ number_format($profitPercentageRaw, 1) }}%
                                    </span>
                                    @if ($profitPercentageRaw < 0)
                                        <span
                                            class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded-full whitespace-nowrap">
                                            Rugi
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($profitPercentageRaw >= 0)
                                <!-- Profit Positif -->
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="{{ $profitBarClass }} h-2.5 rounded-full"
                                        style="width: {{ $profitDisplayPercentage }}%"></div>
                                </div>
                                <div class="flex justify-end mt-1">
                                    <span class="text-sm {{ $profitTextClass }} font-semibold whitespace-nowrap">
                                        + Rp {{ number_format($totalProfit, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <!-- Profit Negatif (Loss) -->
                                <div class="relative">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-red-600 h-2.5 rounded-full"
                                            style="width: {{ $profitDisplayPercentage }}%; margin-left: {{ 100 - $profitDisplayPercentage }}%;">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center justify-between mt-1 gap-1">
                                    <span class="text-xs text-red-600 break-word max-w-[60%]">
                                        {{ number_format(abs($profitPercentageRaw), 1) }}% dari pendapatan
                                    </span>
                                    <span class="text-sm text-red-600 font-semibold whitespace-nowrap">
                                        - Rp {{ number_format(abs($totalProfit), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Ringkasan tambahan jika terjadi kerugian -->
                        @if ($isLoss)
                            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start">
                                    <i data-lucide="alert-triangle"
                                        class="w-5 h-5 text-red-600 mr-2 shrink-0 mt-0.5"></i>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-semibold text-red-800">Ringkasan Kerugian</h4>
                                        <div class="text-xs text-red-700 mt-1 space-y-0.5">
                                            <div class="flex justify-between gap-2">
                                                <span>Pendapatan:</span>
                                                <span class="font-medium whitespace-nowrap">Rp
                                                    {{ number_format($totalIncome, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between gap-2">
                                                <span>Pengeluaran:</span>
                                                <span class="font-medium whitespace-nowrap">Rp
                                                    {{ number_format($totalExpense, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between gap-2 pt-1 border-t border-red-200 mt-1">
                                                <span class="font-semibold">Kerugian:</span>
                                                <span class="font-semibold whitespace-nowrap">Rp
                                                    {{ number_format(abs($totalProfit), 0, ',', '.') }}
                                                    ({{ number_format(abs($profitPercentageRaw), 1) }}%)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Metrik Kunci</h3>
                    <p class="text-sm text-gray-600 mt-1">Indikator kinerja bisnis</p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">Hari Profit</div>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ collect($dailyData)->where('profit', '>', 0)->count() }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">dari {{ count($dailyData) }} hari</div>
                        </div>

                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">Hari Loss</div>
                            <div class="text-2xl font-bold text-red-600">
                                {{ collect($dailyData)->where('profit', '<', 0)->count() }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">dari {{ count($dailyData) }} hari</div>
                        </div>

                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">Profit Tertinggi</div>
                            <div class="text-xl font-bold text-green-600 break-all whitespace-normal">
                                Rp {{ number_format(collect($dailyData)->max('profit') ?? 0, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="text-center p-4 bg-amber-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">Profit Terendah</div>
                            <div class="text-xl font-bold text-amber-600 break-all whitespace-normal">
                                Rp {{ number_format(collect($dailyData)->min('profit') ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t">
                        <div class="text-sm text-gray-500 mb-2">Rekomendasi:</div>
                        @if ($profitMargin >= 20)
                            <div class="text-sm text-green-600 bg-green-50 p-3 rounded-lg">
                                <div class="flex items-start">
                                    <i data-lucide="check-circle"
                                        class="w-4 h-4 text-green-600 mr-2 shrink-0 mt-0.5"></i>
                                    <span class="break-word">Bisnis dalam kondisi sangat sehat! Profit margin di atas 20%
                                        menunjukkan operasi yang efisien.</span>
                                </div>
                            </div>
                        @elseif($profitMargin >= 10)
                            <div class="text-sm text-blue-600 bg-blue-50 p-3 rounded-lg">
                                <div class="flex items-start">
                                    <i data-lucide="alert-circle"
                                        class="w-4 h-4 text-blue-600 mr-2 shrink-0 mt-0.5"></i>
                                    <span class="break-word">Bisnis dalam kondisi baik. Pertimbangkan untuk mengurangi
                                        biaya operasional untuk meningkatkan margin.</span>
                                </div>
                            </div>
                        @elseif($profitMargin >= 0)
                            <div class="text-sm text-amber-600 bg-amber-50 p-3 rounded-lg">
                                <div class="flex items-start">
                                    <i data-lucide="alert-triangle"
                                        class="w-4 h-4 text-amber-600 mr-2 shrink-0 mt-0.5"></i>
                                    <span class="break-word">Profit margin rendah. Evaluasi pengeluaran dan pertimbangkan
                                        penyesuaian harga.</span>
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                                <div class="flex items-start">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-red-600 mr-2 shrink-0 mt-0.5"></i>
                                    <span class="break-word">Bisnis mengalami kerugian. Segera evaluasi biaya operasional
                                        dan strategi harga.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function exportReport() {
            // Ambil parameter tanggal dari URL atau dari variabel yang tersedia
            const urlParams = new URLSearchParams(window.location.search);
            let startDate = urlParams.get('start_date');
            let endDate = urlParams.get('end_date');

            // Jika tidak ada di URL, gunakan nilai default dari server
            if (!startDate) {
                startDate = '{{ $startDate }}';
            }
            if (!endDate) {
                endDate = '{{ $endDate }}';
            }

            // Encode tanggal untuk keamanan URL
            const encodedStartDate = encodeURIComponent(startDate);
            const encodedEndDate = encodeURIComponent(endDate);

            // Buat URL dengan parameter yang benar
            const url =
                '{{ route('reports.profit.pdf', ['startDate' => 'PLACEHOLDER_START', 'endDate' => 'PLACEHOLDER_END']) }}'
                .replace('PLACEHOLDER_START', encodedStartDate)
                .replace('PLACEHOLDER_END', encodedEndDate);

            window.location.href = url;
        }

        function printReport() {
            window.print();
        }

        // Profit Chart
        document.addEventListener('DOMContentLoaded', function() {
            @if (isset($dates) && count($dates) > 0)
                const ctx = document.getElementById('profitChart').getContext('2d');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($dates) !!},
                        datasets: [{
                                label: 'Pendapatan',
                                data: {!! json_encode($incomes) !!},
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Pengeluaran',
                                data: {!! json_encode($expenses) !!},
                                borderColor: '#EF4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Profit',
                                data: {!! json_encode($profits) !!},
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: false,
                                pointBackgroundColor: '#3B82F6',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': Rp ' + context.parsed.y
                                            .toLocaleString('id-ID');
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
