<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan #{{ $sale->id }}</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
            padding: 10px;
            max-width: 80mm;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .store-address {
            font-size: 10px;
            margin-bottom: 5px;
        }

        /* Receipt Info */
        .receipt-info {
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: bold;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table th {
            text-align: left;
            padding: 5px 0;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .items-table td {
            padding: 5px 0;
            border-bottom: 1px dashed #ccc;
        }

        .items-table .item-name {
            width: 50%;
        }

        .items-table .item-qty {
            width: 15%;
            text-align: center;
        }

        .items-table .item-price {
            width: 20%;
            text-align: right;
        }

        .items-table .item-total {
            width: 15%;
            text-align: right;
            font-weight: bold;
        }

        /* Totals */
        .totals {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .total-label {
            font-weight: bold;
        }

        .grand-total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .thank-you {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .cashier-info {
            margin-bottom: 5px;
        }

        .print-time {
            color: #666;
        }

        /* Print Styles */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                padding: 5mm;
                width: 80mm;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Controls */
        .print-controls {
            margin-top: 20px;
            text-align: center;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: #4CAF50;
            color: white;
        }

        .btn-close {
            background: #f44336;
            color: white;
        }

        .btn-back {
            background: #2196F3;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Receipt Content -->
    <div class="header">
        <div class="store-name">Ikan Bakar Jo</div>
        <div class="store-address">Jl. Seafood No. 123, Jakarta</div>
        <div class="store-address">Telp: (021) 1234-5678</div>
    </div>

    <div class="receipt-info">
        <div class="info-row">
            <span class="info-label">No. Transaksi:</span>
            <span>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal:</span>
            <span>{{ \Carbon\Carbon::parse($sale->date)->translatedFormat('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kasir:</span>
            <span>{{ $sale->user->name ?? 'Admin' }}</span>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="item-name">Item</th>
                <th class="item-qty">Qty</th>
                <th class="item-price">Harga</th>
                <th class="item-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td class="item-name">{{ $item->menu->name }}</td>
                <td class="item-qty">{{ $item->quantity }}</td>
                <td class="item-price">Rp {{ number_format($item->menu->price, 0, ',', '.') }}</td>
                <td class="item-total">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span class="total-label">Subtotal:</span>
            <span>Rp {{ number_format($sale->items->sum('subtotal'), 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">PPN (10%):</span>
            <span>Rp {{ number_format($sale->items->sum('subtotal') * 0.1, 0, ',', '.') }}</span>
        </div>
        <div class="grand-total total-row">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($sale->notes)
    <div class="notes" style="margin-top: 10px; padding: 10px; border: 1px dashed #ccc; border-radius: 4px;">
        <strong>Catatan:</strong> {{ $sale->notes }}
    </div>
    @endif

    <div class="footer">
        <div class="thank-you">TERIMA KASIH</div>
        <div class="cashier-info">Kasir: {{ $sale->user->name ?? 'Admin' }}</div>
        <div class="print-time">
            Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Print Controls (only visible on screen) -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Cetak Struk
        </button>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-back">
            ← Kembali ke Detail
        </a>
        <a href="{{ route('sales.index') }}" class="btn btn-close">
            ✕ Tutup
        </a>
    </div>

    <script>
        // Auto print if specified
        @if(request()->get('autoprint') === '1')
        window.onload = function() {
            window.print();

            // Optional: Close window after print
            setTimeout(function() {
                // window.close();
            }, 1000);
        }
        @endif

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + P or Cmd + P
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Escape key to go back
            if (e.key === 'Escape') {
                window.location.href = "{{ route('sales.index') }}";
            }
        });
    </script>
</body>
</html>
