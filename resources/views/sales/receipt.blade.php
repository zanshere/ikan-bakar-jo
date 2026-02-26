<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Struk Penjualan #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', 'Consolas', monospace;
            font-size: 12px;
            line-height: 1.5;
            color: #000000;
            background-color: #ffffff;
            padding: 15px;
            max-width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .receipt-container {
            width: 100%;
            max-width: 80mm; /* Standar lebar struk thermal */
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 15px 12px;
        }

        /* Header */
        .header {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px dashed #333333;
            margin-bottom: 12px;
        }

        .store-name {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            color: #1e3c72;
        }

        .store-address {
            font-size: 11px;
            color: #4a5568;
            margin-bottom: 3px;
        }

        .store-contact {
            font-size: 11px;
            color: #4a5568;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
            color: #2d3748;
        }

        /* Info Transaksi */
        .info-section {
            margin-bottom: 15px;
            padding: 0 2px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 11px;
        }

        .info-label {
            font-weight: 700;
            color: #2d3748;
            min-width: 90px;
        }

        .info-value {
            font-weight: 500;
            color: #1a202c;
            text-align: right;
        }

        .divider {
            border-top: 1px dashed #a0aec0;
            margin: 12px 0;
        }

        .divider-dotted {
            border-top: 1px dotted #cbd5e0;
            margin: 8px 0;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 15px;
        }

        .items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-top: 2px solid #2d3748;
            border-bottom: 2px solid #2d3748;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            background-color: #f7fafc;
            margin-bottom: 5px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .item-name-col {
            flex: 2;
            padding-right: 8px;
        }

        .item-name-main {
            font-weight: 600;
            font-size: 12px;
            color: #1a202c;
            margin-bottom: 2px;
        }

        .item-sauce {
            font-size: 10px;
            color: #4a5568;
            font-style: italic;
        }

        .item-qty-col {
            flex: 0.5;
            text-align: center;
            font-weight: 500;
            font-size: 12px;
        }

        .item-price-col {
            flex: 1;
            text-align: right;
            padding-right: 8px;
            font-size: 11px;
            color: #2d3748;
        }

        .item-total-col {
            flex: 0.8;
            text-align: right;
            font-weight: 700;
            font-size: 12px;
            color: #1e3c72;
        }

        /* Totals */
        .totals-section {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 2px solid #2d3748;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            font-size: 12px;
        }

        .total-label {
            font-weight: 600;
            color: #2d3748;
        }

        .total-value {
            font-weight: 600;
            color: #1a202c;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 2px solid #2d3748;
            font-size: 16px;
            font-weight: 800;
            color: #1e3c72;
        }

        .grand-total-label {
            text-transform: uppercase;
        }

        .grand-total-value {
            color: #1e3c72;
        }

        /* Payment Info */
        .payment-section {
            margin: 12px 0;
            padding: 10px;
            background-color: #f0f9ff;
            border-radius: 6px;
            border: 1px solid #bee3f8;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .payment-label {
            font-weight: 600;
            color: #2c5282;
        }

        .payment-value {
            font-weight: 600;
            color: #2b6cb0;
        }

        /* Notes */
        .notes-section {
            margin: 12px 0;
            padding: 10px;
            background-color: #fffff0;
            border: 1px dashed #d69e2e;
            border-radius: 6px;
            font-size: 11px;
        }

        .notes-label {
            font-weight: 700;
            color: #b7791f;
            margin-bottom: 4px;
            display: block;
        }

        .notes-content {
            color: #744210;
            font-style: italic;
            word-wrap: break-word;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 2px dashed #4a5568;
            font-size: 11px;
        }

        .thank-you {
            font-size: 16px;
            font-weight: 800;
            color: #1e3c72;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cashier-info {
            color: #4a5568;
            margin-bottom: 4px;
        }

        .print-time {
            color: #718096;
            font-size: 9px;
        }

        /* Print Controls */
        .print-controls {
            margin-top: 25px;
            text-align: center;
            padding: 15px;
            background: #edf2f7;
            border-radius: 12px;
            width: 100%;
            max-width: 80mm;
        }

        .btn {
            padding: 12px 24px;
            margin: 5px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-print {
            background: #1e3c72;
            color: white;
        }

        .btn-print:hover {
            background: #2c5282;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-back {
            background: #38a169;
            color: white;
        }

        .btn-back:hover {
            background: #2f855a;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-close {
            background: #e53e3e;
            color: white;
        }

        .btn-close:hover {
            background: #c53030;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        /* Print Styles */
        @media print {
            @page {
                size: 80mm auto;
                margin: 2mm;
            }

            body {
                padding: 0;
                background-color: white;
                display: block;
            }

            .receipt-container {
                max-width: 100%;
                box-shadow: none;
                border-radius: 0;
                padding: 5px;
            }

            .print-controls {
                display: none !important;
            }

            .payment-section {
                background-color: #f0f9ff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .notes-section {
                background-color: #fffff0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Responsive */
        @media (max-width: 360px) {
            .receipt-container {
                padding: 10px 8px;
            }

            .store-name {
                font-size: 18px;
            }

            .item-name-main {
                font-size: 11px;
            }

            .item-total-col {
                font-size: 11px;
            }

            .grand-total {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="store-name">🍖 Ikan Bakar Jo</div>
            <div class="store-address">Permata Cibubur Blok B5 No 10</div>
            <div class="store-contact">📞 +62 856-9235-3330</div>
            <div class="receipt-title">BUKTI TRANSAKSI</div>
        </div>

        <!-- Info Transaksi -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">No. Transaksi</span>
                <span class="info-value">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            @if($sale->order && $sale->order->order_number)
            <div class="info-row">
                <span class="info-label">No. Order</span>
                <span class="info-value">{{ $sale->order->order_number }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($sale->date)->translatedFormat('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir</span>
                <span class="info-value">Subandi</span>
            </div>
            @if($sale->order && $sale->order->user)
            <div class="info-row">
                <span class="info-label">Pelanggan</span>
                <span class="info-value">{{ $sale->order->user->name }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <div class="items-section">
            <div class="items-header">
                <span class="item-name-col">Item</span>
                <span class="item-qty-col">Qty</span>
                <span class="item-price-col">Harga</span>
                <span class="item-total-col">Total</span>
            </div>

            @foreach($sale->items as $item)
            <div class="item-row">
                <div class="item-name-col">
                    <div class="item-name-main">{{ $item->menu->name }}</div>
                    @if($item->sauce)
                    <div class="item-sauce">+ {{ $item->sauce->name }}</div>
                    @endif
                </div>
                <div class="item-qty-col">{{ $item->quantity }}</div>
                <div class="item-price-col">
                    {{ number_format($item->price + $item->additional_price, 0, ',', '.') }}
                </div>
                <div class="item-total-col">
                    {{ number_format($item->subtotal, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="divider-dotted"></div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">Subtotal</span>
                <span class="total-value">Rp {{ number_format($sale->items->sum('subtotal'), 0, ',', '.') }}</span>
            </div>

            <div class="grand-total">
                <span class="grand-total-label">TOTAL</span>
                <span class="grand-total-value">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Payment Information -->
        @if($sale->payment_method)
        <div class="payment-section">
            <div class="payment-row">
                <span class="payment-label">Metode Pembayaran</span>
                <span class="payment-value">
                    @if($sale->payment_method === 'cash')
                        💵 Tunai
                    @else
                        💳 Transfer Bank
                    @endif
                </span>
            </div>
            @if($sale->payment_method === 'cash' && $sale->cash_received)
            <div class="payment-row">
                <span class="payment-label">Uang Diterima</span>
                <span class="payment-value">Rp {{ number_format($sale->cash_received, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($sale->change > 0)
            <div class="payment-row">
                <span class="payment-label">Kembalian</span>
                <span class="payment-value">Rp {{ number_format($sale->change, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($sale->completed_at)
            <div class="payment-row">
                <span class="payment-label">Waktu Bayar</span>
                <span class="payment-value">{{ \Carbon\Carbon::parse($sale->completed_at)->format('H:i:s') }}</span>
            </div>
            @endif
        </div>
        @endif

        <!-- Notes -->
        @if($sale->notes)
        <div class="notes-section">
            <span class="notes-label">📝 Catatan:</span>
            <div class="notes-content">{{ $sale->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Terima Kasih</div>
            <div class="cashier-info">Pemesan : {{ $sale->user->name ?? 'Admin' }}</div>
            <div class="print-time">
                Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Print Controls (only visible on screen) -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Cetak Struk
        </button>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-back">
            ← Kembali
        </a>
        <a href="{{ route('sales.index') }}" class="btn btn-close">
            ✕ Tutup
        </a>
    </div>

    <script>
        // Auto print if specified
        @if(request()->get('autoprint') === '1')
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
        @endif

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + P or Cmd + P untuk print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Escape key untuk kembali ke daftar
            if (e.key === 'Escape') {
                window.location.href = "{{ route('sales.index') }}";
            }
        });

        // Prevent zoom on mobile
        document.addEventListener('touchmove', function(e) {
            if (e.scale !== 1) { e.preventDefault(); }
        }, { passive: false });

        // Tambahkan class untuk print
        window.onbeforeprint = function() {
            document.body.style.background = 'white';
        };

        window.onafterprint = function() {
            document.body.style.background = '';
        };
    </script>
</body>
</html>
