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
            border-bottom: 2px solid #ef4444;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #b91c1c;
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
            border-left: 4px solid #ef4444;
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
            background: #ef4444;
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
        .text-red {
            color: #b91c1c;
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
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .progress-bar {
            width: 100%;
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
        .progress-fill {
            background: #ef4444;
            height: 8px;
            border-radius: 4px;
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
        <strong>Ringkasan Laporan Pengeluaran</strong><br>
        Laporan ini menampilkan seluruh transaksi restock/pengeluaran pada periode yang dipilih.
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Total Pengeluaran</div>
            <div class="info-value">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
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

    <h3 style="color: #b91c1c; margin-bottom: 15px;">Rekap Harian</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah Transaksi</th>
                <th class="text-right">Total Pengeluaran</th>
                <th class="text-right">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailySummary as $daily)
            <tr>
                <td>{{ \Carbon\Carbon::parse($daily->date)->format('d/m/Y') }}</td>
                <td>{{ number_format($daily->transaction_count, 0, ',', '.') }}</td>
                <td class="text-right text-red">Rp {{ number_format($daily->total_amount, 0, ',', '.') }}</td>
                <td class="text-right">
                    Rp {{ number_format($daily->transaction_count > 0 ? $daily->total_amount / $daily->transaction_count : 0, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data pengeluaran pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($dailySummary->count() > 0)
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td colspan="2">Total</td>
                <td class="text-right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="page-break-before: always;"></div>

    <h3 style="color: #b91c1c; margin-bottom: 15px;">Analisis Bahan Baku</h3>
    <p style="margin-bottom: 15px; font-size: 11px; color: #6b7280;">
        Bahan baku dengan pengeluaran terbesar pada periode ini
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>Bahan Baku</th>
                <th class="text-right">Total Qty</th>
                <th class="text-right">Rata-rata Harga</th>
                <th class="text-right">Total Biaya</th>
                <th class="text-right">% Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ingredientSummary as $ingredient)
            @php
                $percentage = $totalExpense > 0 ? ($ingredient->total_cost / $totalExpense) * 100 : 0;
            @endphp
            <tr>
                <td>
                    <strong>{{ $ingredient->name }}</strong>
                    <div style="font-size: 10px; color: #6b7280;">{{ $ingredient->unit }}</div>
                </td>
                <td class="text-right">{{ number_format($ingredient->total_quantity, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($ingredient->average_price, 0, ',', '.') }}</td>
                <td class="text-right text-red">Rp {{ number_format($ingredient->total_cost, 0, ',', '.') }}</td>
                <td class="text-right">
                    {{ number_format($percentage, 1) }}%
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $percentage }}%;"></div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data pengeluaran bahan pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($ingredientSummary->count() > 0)
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td colspan="3">Total</td>
                <td class="text-right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                <td class="text-right">100%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="margin-top: 30px;">
        <h3 style="color: #b91c1c; margin-bottom: 15px;">Transaksi Restock Terbaru</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>No. Restock</th>
                    <th>Tanggal</th>
                    <th>PIC</th>
                    <th>Items</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($restocks->take(20) as $restock)
                <tr>
                    <td>#{{ str_pad($restock->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $restock->date->format('d/m/Y') }} {{ $restock->created_at->format('H:i') }}</td>
                    <td>{{ $restock->user->name ?? '-' }}</td>
                    <td>{{ $restock->items->count() }} bahan</td>
                    <td class="text-right text-red">Rp {{ number_format($restock->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">
                        Tidak ada transaksi restock pada periode ini
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
