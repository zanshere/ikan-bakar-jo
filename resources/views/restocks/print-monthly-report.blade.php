<!-- resources/views/restocks/print-monthly-report.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Restock Bulanan - {{ $period }}</title>
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
            color: #ea580c;
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
            background-color: #fff7ed;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            border: 1px solid #fed7aa;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 14px;
            color: #9a3412;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #7c2d12;
        }
        .summary-value.total {
            color: #ea580c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #ea580c;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #fed7aa;
            font-size: 13px;
        }
        tr:nth-child(even) {
            background-color: #fff7ed;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #fed7aa;
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
        <h1>LAPORAN RESTOCK BULANAN</h1>
        <h2>{{ $period }}</h2>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Pengeluaran</div>
            <div class="summary-value total">Rp {{ number_format($totalRestocks, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Transaksi</div>
            <div class="summary-value">{{ $totalTransactions }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Item Direstock</div>
            <div class="summary-value">{{ $totalItems }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Kuantitas</div>
            <div class="summary-value">{{ number_format($totalQuantity, 2, ',', '.') }}</div>
        </div>
    </div>

    @if(count($ingredientsBreakdown) > 0)
    <h3 style="font-size: 16px; margin-bottom: 10px;">Rincian Bahan yang Direstock</h3>
    <table>
        <thead>
            <tr>
                <th>Bahan</th>
                <th class="text-right">Kuantitas</th>
                <th class="text-right">Rata-rata Harga</th>
                <th class="text-right">Total Biaya</th>
                <th class="text-right">Frekuensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ingredientsBreakdown as $ingredientName => $details)
            <tr>
                <td>{{ $ingredientName }}</td>
                <td class="text-right">{{ number_format($details['quantity'], 2, ',', '.') }} {{ $details['unit'] }}</td>
                <td class="text-right">Rp {{ number_format($details['average_price'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($details['total'], 0, ',', '.') }}</td>
                <td class="text-right">{{ $details['times_restocked'] }}x</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h3 style="font-size: 16px; margin-bottom: 10px;">Detail Transaksi Restock</h3>
    <table>
        <thead>
            <tr>
                <th>No. Restock</th>
                <th>Tanggal</th>
                <th>PIC</th>
                <th class="text-right">Jumlah Item</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($restocks as $restock)
            <tr>
                <td>#{{ str_pad($restock->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $restock->date->format('d/m/Y') }}</td>
                <td>{{ $restock->user->name ?? 'Unknown' }}</td>
                <td class="text-right">{{ $restock->items->count() }} bahan</td>
                <td class="text-right">Rp {{ number_format($restock->total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">
                    Tidak ada transaksi restock pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print();" style="padding: 10px 20px; background-color: #ea580c; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
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
