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
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #047857;
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
            border-left: 4px solid #10b981;
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
            font-size: 18px;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table th {
            background: #10b981;
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
        .text-amber {
            color: #b45309;
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
            padding: 4px 8px;
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
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-indicator {
            display: flex;
            align-items: center;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-dot-success {
            background: #10b981;
        }
        .status-dot-warning {
            background: #f59e0b;
        }
        .status-dot-danger {
            background: #ef4444;
        }
        .recommendation-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .recommendation-title {
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .recommendation-list {
            margin: 0;
            padding-left: 20px;
        }
        .recommendation-list li {
            margin-bottom: 5px;
        }
        .trend-up {
            color: #059669;
        }
        .trend-down {
            color: #b91c1c;
        }
        .trend-stable {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Status per {{ now()->format('d/m/Y H:i:s') }}</div>
        <div class="subtitle">Tanggal Cetak: {{ $generatedAt }}</div>
        <div class="subtitle">Dicetak oleh: {{ $generatedBy }}</div>
    </div>

    <div class="info-box">
        <strong>Ringkasan Laporan Stok</strong><br>
        Laporan ini menampilkan status stok bahan baku terkini dan analisis pergerakan stok 30 hari terakhir.
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Total Bahan</div>
            <div class="info-value">{{ number_format($stockStatistics['total_items'], 0, ',', '.') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Nilai Total Stok</div>
            <div class="info-value">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Stok Rendah</div>
            <div class="info-value" style="color: #b45309;">{{ number_format($stockStatistics['low_stock'], 0, ',', '.') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Stok Habis</div>
            <div class="info-value" style="color: #b91c1c;">{{ number_format($stockStatistics['out_of_stock'], 0, ',', '.') }}</div>
        </div>
    </div>

    <h3 style="color: #047857; margin-bottom: 15px;">Status Stok Saat Ini</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Bahan Baku</th>
                <th class="text-right">Stok Saat Ini</th>
                <th class="text-right">Stok Minimum</th>
                <th>Status</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Nilai Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ingredients as $ingredient)
            @php
                $statusClass = '';
                $statusText = '';
                $statusDotClass = '';

                if ($ingredient->stock <= 0) {
                    $statusClass = 'badge-danger';
                    $statusText = 'Habis';
                    $statusDotClass = 'status-dot-danger';
                } elseif ($ingredient->stock < $ingredient->min_stock) {
                    $statusClass = 'badge-warning';
                    $statusText = 'Rendah';
                    $statusDotClass = 'status-dot-warning';
                } else {
                    $statusClass = 'badge-success';
                    $statusText = 'Cukup';
                    $statusDotClass = 'status-dot-success';
                }
            @endphp
            <tr>
                <td>
                    <strong>{{ $ingredient->name }}</strong>
                    <div style="font-size: 10px; color: #6b7280;">{{ $ingredient->code ?? '-' }} • {{ $ingredient->unit }}</div>
                </td>
                <td class="text-right">{{ number_format($ingredient->stock, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($ingredient->min_stock, 2, ',', '.') }}</td>
                <td>
                    <div class="status-indicator">
                        <div class="status-dot {{ $statusDotClass }}"></div>
                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                    </div>
                </td>
                <td class="text-right">Rp {{ number_format($ingredient->price, 0, ',', '.') }}</td>
                <td class="text-right text-blue">Rp {{ number_format($ingredient->stock * $ingredient->price, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data stok
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f3f4f6; font-weight: bold;">
                <td colspan="5" class="text-right">Total Nilai Stok</td>
                <td class="text-right text-blue">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-before: always;"></div>

    <h3 style="color: #047857; margin-bottom: 15px;">Pergerakan Stok (30 Hari Terakhir)</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Bahan Baku</th>
                <th class="text-right">Stok Masuk</th>
                <th class="text-right">Stok Keluar</th>
                <th class="text-right">Net Movement</th>
                <th class="text-right">Stok Saat Ini</th>
                <th>Trend</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockMovement as $movement)
            @php
                $trendClass = $movement['net_movement'] > 0 ? 'text-green' : ($movement['net_movement'] < 0 ? 'text-red' : '');
                $trendIcon = $movement['net_movement'] > 0 ? '▲' : ($movement['net_movement'] < 0 ? '▼' : '•');
                $trendText = $movement['net_movement'] > 0 ? 'Meningkat' : ($movement['net_movement'] < 0 ? 'Menurun' : 'Stabil');
            @endphp
            <tr>
                <td>
                    <strong>{{ $movement['ingredient'] }}</strong>
                    <div style="font-size: 10px; color: #6b7280;">{{ $movement['unit'] }}</div>
                </td>
                <td class="text-right text-green">+{{ number_format($movement['stock_in'], 2, ',', '.') }}</td>
                <td class="text-right text-red">-{{ number_format($movement['stock_out'], 2, ',', '.') }}</td>
                <td class="text-right {{ $trendClass }}">
                    {{ $movement['net_movement'] >= 0 ? '+' : '' }}{{ number_format($movement['net_movement'], 2, ',', '.') }}
                </td>
                <td class="text-right">{{ number_format($movement['current_stock'], 2, ',', '.') }}</td>
                <td>
                    <span class="{{ $trendClass }}">
                        {{ $trendIcon }} {{ $trendText }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data pergerakan stok dalam 30 hari terakhir
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <h3 style="color: #047857; margin-bottom: 15px;">Rekomendasi Manajemen Stok</h3>

        @if($stockStatistics['out_of_stock'] > 0)
        <div class="recommendation-card" style="border-left: 4px solid #ef4444;">
            <div class="recommendation-title" style="color: #b91c1c;">
                ⚠ Segera Restock! ({{ $stockStatistics['out_of_stock'] }} Bahan)
            </div>
            <p style="margin-bottom: 10px;">Bahan berikut sudah habis dan perlu segera di-restock:</p>
            <ul class="recommendation-list">
                @foreach($ingredients->where('stock', '<=', 0)->take(10) as $ingredient)
                <li><strong>{{ $ingredient->name }}</strong> (Stok: {{ number_format($ingredient->stock, 2, ',', '.') }} {{ $ingredient->unit }})</li>
                @endforeach
                @if($stockStatistics['out_of_stock'] > 10)
                <li>... dan {{ $stockStatistics['out_of_stock'] - 10 }} bahan lainnya</li>
                @endif
            </ul>
        </div>
        @endif

        @if($stockStatistics['low_stock'] > 0)
        <div class="recommendation-card" style="border-left: 4px solid #f59e0b;">
            <div class="recommendation-title" style="color: #b45309;">
                ⚠ Stok Rendah ({{ $stockStatistics['low_stock'] }} Bahan)
            </div>
            <p style="margin-bottom: 10px;">Bahan berikut memiliki stok di bawah minimum:</p>
            <ul class="recommendation-list">
                @foreach($ingredients->filter(function($i) { return $i->stock > 0 && $i->stock < $i->min_stock; })->take(10) as $ingredient)
                <li>
                    <strong>{{ $ingredient->name }}</strong>
                    (Stok: {{ number_format($ingredient->stock, 2, ',', '.') }} {{ $ingredient->unit }} /
                    Min: {{ number_format($ingredient->min_stock, 2, ',', '.') }} {{ $ingredient->unit }})
                </li>
                @endforeach
                @if($stockStatistics['low_stock'] > 10)
                <li>... dan {{ $stockStatistics['low_stock'] - 10 }} bahan lainnya</li>
                @endif
            </ul>
        </div>
        @endif

        <div class="recommendation-card" style="border-left: 4px solid #3b82f6;">
            <div class="recommendation-title" style="color: #1e40af;">
                💡 Tips Manajemen Stok
            </div>
            <ul class="recommendation-list">
                <li>Lakukan stock opname secara berkala minimal seminggu sekali untuk memastikan akurasi data</li>
                <li>Setel stok minimum yang realistis berdasarkan pola penggunaan dan lead time pemesanan</li>
                <li>Pertimbangkan untuk melakukan bulk ordering untuk bahan dengan turnover tinggi dan masa simpan panjang</li>
                <li>Monitor pergerakan stok secara rutin untuk mengidentifikasi pola penggunaan musiman</li>
                <li>Terapkan metode FIFO (First In First Out) untuk mengurangi risiko bahan kadaluarsa</li>
                <li>Buat sistem peringatan otomatis untuk stok yang mencapai level minimum</li>
            </ul>
        </div>

        <div class="recommendation-card" style="background: #ecfdf5; border-left: 4px solid #10b981;">
            <div class="recommendation-title" style="color: #047857;">
                📊 Ringkasan Stok
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                <div>
                    <div style="color: #6b7280; font-size: 11px;">Total Nilai Stok</div>
                    <div style="font-size: 18px; font-weight: bold; color: #047857;">
                        Rp {{ number_format($totalStockValue, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 11px;">Rata-rata Nilai per Bahan</div>
                    <div style="font-size: 18px; font-weight: bold; color: #047857;">
                        Rp {{ number_format($stockStatistics['total_items'] > 0 ? $totalStockValue / $stockStatistics['total_items'] : 0, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 11px;">Stok Cukup</div>
                    <div style="font-size: 18px; font-weight: bold; color: #10b981;">
                        {{ $stockStatistics['sufficient_stock'] }} bahan
                    </div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 11px;">Stok Bermasalah</div>
                    <div style="font-size: 18px; font-weight: bold; color: #b45309;">
                        {{ $stockStatistics['low_stock'] + $stockStatistics['out_of_stock'] }} bahan
                    </div>
                </div>
            </div>
        </div>
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
