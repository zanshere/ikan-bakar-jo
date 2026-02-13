<!-- resources/views/sales/print-daily-report.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Harian - {{ $formattedDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2563eb;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .summary {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        .summary-value.total {
            color: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .page-break {
            page-break-after: always;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN HARIAN</h1>
        <h2>{{ $formattedDate }}</h2>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Pendapatan</div>
            <div class="summary-value total">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Transaksi</div>
            <div class="summary-value">{{ $sales->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Item Terjual</div>
            <div class="summary-value">{{ $totalItems }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Rata-rata per Transaksi</div>
            <div class="summary-value">
                Rp {{ number_format($sales->count() > 0 ? $totalSales / $sales->count() : 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    @if(count($itemsBreakdown) > 0)
    <h3 style="font-size: 16px; margin-bottom: 10px;">Rincian Menu Terjual</h3>
    <table>
        <thead>
            <tr>
                <th>Menu</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemsBreakdown as $menuName => $details)
            <tr>
                <td>{{ $menuName }}</td>
                <td class="text-right">{{ $details['quantity'] }}</td>
                <td class="text-right">Rp {{ number_format($details['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h3 style="font-size: 16px; margin-bottom: 10px;">Detail Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Waktu</th>
                <th>Kasir</th>
                <th class="text-right">Item</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $sale->created_at->format('H:i') }}</td>
                <td>{{ $sale->user->name ?? 'Unknown' }}</td>
                <td class="text-right">{{ $sale->items->sum('quantity') }} item</td>
                <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">
                    Tidak ada transaksi pada tanggal ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print();" style="padding: 10px 20px; background-color: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            Cetak Laporan
        </button>
        <button onclick="window.close();" style="padding: 10px 20px; background-color: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Tutup
        </button>
    </div>

    <script>
        window.onload = function() {
            // Auto print
            window.print();
        };
    </script>
</body>
</html>
