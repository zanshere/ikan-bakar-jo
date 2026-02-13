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
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #1e40af;
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
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 0 8px 8px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
            font-size: 18px;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table th {
            background: #3b82f6;
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
        .page-break {
            page-break-after: always;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background: #fed7aa;
            color: #92400e;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
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
        <strong>Ringkasan Laporan Pendapatan</strong><br>
        Laporan ini menampilkan seluruh transaksi penjualan pada periode yang dipilih.
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Total Pendapatan</div>
            <div class="info-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Transaksi</div>
            <div class="info-value">{{ number_format($totalTransactions, 0, ',', '.') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Rata-rata per Transaksi</div>
            <div class="info-value">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</div>
        </div>
    </div>

    <h3 style="color: #1e40af; margin-bottom: 15px;">Rekap Harian</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah Transaksi</th>
                <th class="text-right">Total Pendapatan</th>
                <th class="text-right">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailySummary as $daily)
            <tr>
                <td>{{ \Carbon\Carbon::parse($daily->date)->format('d/m/Y') }}</td>
                <td>{{ number_format($daily->transaction_count, 0, ',', '.') }}</td>
                <td class="text-right text-green">Rp {{ number_format($daily->total_amount, 0, ',', '.') }}</td>
                <td class="text-right">
                    Rp {{ number_format($daily->transaction_count > 0 ? $daily->total_amount / $daily->transaction_count : 0, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data pendapatan pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($dailySummary->count() > 0)
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td colspan="2">Total</td>
                <td class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="page-break-before: always;"></div>

    <h3 style="color: #1e40af; margin-bottom: 15px;">Performa Menu Terlaris</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nama Menu</th>
                <th class="text-right">Jumlah Terjual</th>
                <th class="text-right">Total Pendapatan</th>
                <th class="text-right">% Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menuSummary as $index => $menu)
            @php
                $percentage = $totalIncome > 0 ? ($menu->total_sales / $totalIncome) * 100 : 0;
            @endphp
            <tr>
                <td>
                    <strong>{{ $menu->name }}</strong>
                    @if($index < 3)
                    <span style="margin-left: 5px; padding: 2px 6px; background: #f59e0b; color: white; border-radius: 4px; font-size: 9px;">
                        Top {{ $index + 1 }}
                    </span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($menu->total_quantity, 0, ',', '.') }}</td>
                <td class="text-right text-green">Rp {{ number_format($menu->total_sales, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($percentage, 1) }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data penjualan menu pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($menuSummary->count() > 0)
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td colspan="2">Total</td>
                <td class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-right">100%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="margin-top: 30px;">
        <h3 style="color: #1e40af; margin-bottom: 15px;">Transaksi Terbaru</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Items</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales->take(20) as $sale)
                <tr>
                    <td>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $sale->date->format('d/m/Y') }} {{ $sale->created_at->format('H:i') }}</td>
                    <td>{{ $sale->user->name ?? '-' }}</td>
                    <td>{{ $sale->items->sum('quantity') }} item</td>
                    <td class="text-right text-green">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">
                        Tidak ada transaksi pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
