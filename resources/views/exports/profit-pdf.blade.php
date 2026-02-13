<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #6d28d9;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #6b7280;
            margin: 5px 0;
            font-size: 12px;
        }
        .info-box {
            background: #f3f4f6;
            border-left: 4px solid #8b5cf6;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 0 8px 8px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .info-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .info-label {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .info-value {
            color: #1f2937;
            font-size: 16px;
            font-weight: bold;
        }
        .info-value.profit {
            color: #059669;
        }
        .info-value.loss {
            color: #b91c1c;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table th {
            background: #8b5cf6;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
        }
        .table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .table tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-green {
            color: #059669;
            font-weight: bold;
        }
        .text-red {
            color: #b91c1c;
            font-weight: bold;
        }
        .text-blue {
            color: #2563eb;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }
        .badge-profit {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-loss {
            background: #fee2e2;
            color: #991b1b;
        }
        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 14px;
        }
        .progress-bar {
            width: 100%;
            background: #e5e7eb;
            height: 12px;
            border-radius: 6px;
            margin: 5px 0;
        }
        .progress-fill-green {
            background: #10b981;
            height: 12px;
            border-radius: 6px;
        }
        .progress-fill-red {
            background: #ef4444;
            height: 12px;
            border-radius: 6px;
        }
        .progress-fill-blue {
            background: #3b82f6;
            height: 12px;
            border-radius: 6px;
        }
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .metric-item {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .metric-value {
            font-size: 20px;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Periode: {{ $period }}</div>
        <div class="subtitle">Tanggal Cetak: {{ $generatedAt }}</div>
        <div class="subtitle">Dicetak oleh: {{ $generatedBy }}</div>
    </div>

    <div class="info-box">
        <strong>Ringkasan Laporan Laba Rugi</strong><br>
        Laporan ini menampilkan analisis pendapatan, pengeluaran, dan profit pada periode yang dipilih.
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Total Pendapatan</div>
            <div class="info-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Pengeluaran</div>
            <div class="info-value">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Profit</div>
            <div class="info-value {{ $totalProfit >= 0 ? 'profit' : 'loss' }}">
                Rp {{ number_format($totalProfit, 0, ',', '.') }}
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">Profit Margin</div>
            <div class="info-value {{ $profitMargin >= 0 ? 'profit' : 'loss' }}">
                {{ number_format($profitMargin, 2) }}%
            </div>
        </div>
    </div>

    <div class="summary-card">
        <h3 style="color: #6d28d9; margin-top: 0; margin-bottom: 15px;">Analisis Profit</h3>

        <div class="summary-row">
            <span>Pendapatan (100%)</span>
            <span class="text-green">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill-green" style="width: 100%;"></div>
        </div>

        <div class="summary-row">
            <span>Pengeluaran ({{ number_format($totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 0, 1) }}%)</span>
            <span class="text-red">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill-red" style="width: {{ $totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 0 }}%;"></div>
        </div>

        <div class="summary-row" style="border-bottom: none;">
            <span>Profit ({{ number_format($profitMargin, 1) }}%)</span>
            <span class="{{ $totalProfit >= 0 ? 'text-green' : 'text-red' }}">
                Rp {{ number_format($totalProfit, 0, ',', '.') }}
            </span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill-blue" style="width: {{ abs($profitMargin) }}%;"></div>
        </div>
    </div>

    <div style="page-break-before: always;"></div>

    <h3 style="color: #6d28d9; margin-bottom: 15px;">Analisis Profit Harian</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">Pengeluaran</th>
                <th class="text-right">Profit</th>
                <th class="text-right">Margin</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyData as $daily)
            @php
                $profitClass = $daily['profit'] >= 0 ? 'text-green' : 'text-red';
                $marginClass = $daily['margin'] >= 0 ? 'text-green' : 'text-red';
                $status = $daily['profit'] >= 0 ? 'Profit' : 'Loss';
                $statusClass = $daily['profit'] >= 0 ? 'badge-profit' : 'badge-loss';
            @endphp
            <tr>
                <td>{{ $daily['date'] }}</td>
                <td class="text-right text-green">Rp {{ number_format($daily['income'], 0, ',', '.') }}</td>
                <td class="text-right text-red">Rp {{ number_format($daily['expense'], 0, ',', '.') }}</td>
                <td class="text-right {{ $profitClass }}">Rp {{ number_format($daily['profit'], 0, ',', '.') }}</td>
                <td class="text-right {{ $marginClass }}">{{ number_format($daily['margin'], 2) }}%</td>
                <td>
                    <span class="badge {{ $statusClass }}">
                        {{ $status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data profit pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td>Total</td>
                <td class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                <td class="text-right {{ $totalProfit >= 0 ? 'text-green' : 'text-red' }}">
                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                </td>
                <td class="text-right {{ $profitMargin >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($profitMargin, 2) }}%
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="metric-grid">
        @php
            $profitDays = collect($dailyData)->where('profit', '>', 0)->count();
            $lossDays = collect($dailyData)->where('profit', '<', 0)->count();
            $maxProfit = collect($dailyData)->max('profit') ?? 0;
            $minProfit = collect($dailyData)->min('profit') ?? 0;
        @endphp

        <div class="metric-item">
            <div style="color: #6b7280; font-size: 11px;">Hari Profit</div>
            <div class="metric-value text-green">{{ $profitDays }}</div>
            <div style="color: #6b7280; font-size: 10px; margin-top: 5px;">dari {{ count($dailyData) }} hari</div>
        </div>
        <div class="metric-item">
            <div style="color: #6b7280; font-size: 11px;">Hari Loss</div>
            <div class="metric-value text-red">{{ $lossDays }}</div>
            <div style="color: #6b7280; font-size: 10px; margin-top: 5px;">dari {{ count($dailyData) }} hari</div>
        </div>
        <div class="metric-item">
            <div style="color: #6b7280; font-size: 11px;">Profit Tertinggi</div>
            <div class="metric-value text-green">Rp {{ number_format($maxProfit, 0, ',', '.') }}</div>
        </div>
        <div class="metric-item">
            <div style="color: #6b7280; font-size: 11px;">Profit Terendah</div>
            <div class="metric-value {{ $minProfit >= 0 ? 'text-green' : 'text-red' }}">
                Rp {{ number_format($minProfit, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; background: #eff6ff; padding: 20px; border-radius: 8px;">
        <h4 style="color: #1e40af; margin-top: 0; margin-bottom: 10px;">Rekomendasi</h4>
        @if($profitMargin >= 20)
        <p style="color: #065f46; margin: 0;">
            <strong>✓ Bisnis dalam kondisi sangat sehat!</strong> Profit margin di atas 20% menunjukkan operasi yang efisien.
            Pertahankan strategi ini dan optimalkan menu dengan margin tertinggi.
        </p>
        @elseif($profitMargin >= 10)
        <p style="color: #1e40af; margin: 0;">
            <strong>ℹ Bisnis dalam kondisi baik.</strong> Profit margin di atas 10% menunjukkan performa yang positif.
            Pertimbangkan untuk mengurangi biaya operasional atau meningkatkan harga menu premium.
        </p>
        @elseif($profitMargin >= 0)
        <p style="color: #92400e; margin: 0;">
            <strong>⚠ Profit margin rendah.</strong> Margin di bawah 10% perlu dievaluasi.
            Lakukan analisis biaya bahan baku dan efisiensi operasional.
        </p>
        @else
        <p style="color: #991b1b; margin: 0;">
            <strong>✗ Bisnis mengalami kerugian.</strong> Segera lakukan evaluasi menyeluruh terhadap:
            harga menu, biaya bahan baku, dan efisiensi operasional.
        </p>
        @endif
    </div>

    <div class="signature">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div style="margin-top: 60px;">{{ $generatedBy }}</div>
            <div style="border-top: 1px solid #000; padding-top: 5px; margin-top: 5px;">
                {{ auth()->user()->role ?? 'Manajer' }}
            </div>
        </div>
    </div>

    <div class="footer">
        <div>Laporan ini digenerate secara otomatis dari sistem pada {{ $generatedAt }}</div>
        <div style="margin-top: 5px;">© {{ date('Y') }} Restoran - Sistem Manajemen Restoran</div>
    </div>
</body>
</html>
