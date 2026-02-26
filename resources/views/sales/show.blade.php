{{-- resources/views/sales/show.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Detail Transaksi')
@section('page-description', 'Detail transaksi penjualan')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('sales.index') }}" class="text-gray-500 hover:text-gray-700">Penjualan</a>
<span>/</span>
<span class="text-gray-700">Detail</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    @if($sale->payment_status === 'paid')
        <a href="{{ route('sales.print', $sale) }}" target="_blank"
           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
            Cetak Struk
        </a>
    @endif
    <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Status Alert --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Status Card --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center
                    @if($sale->payment_status === 'paid') bg-green-100
                    @else bg-yellow-100 @endif">
                    <i data-lucide="credit-card" class="w-6 h-6
                        @if($sale->payment_status === 'paid') text-green-600
                        @else text-yellow-600 @endif"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status Pembayaran</p>
                    <div class="flex items-center">
                        {!! $sale->payment_status_badge !!}
                        @if($sale->payment_method)
                            <span class="ml-2 text-sm text-gray-600">
                                ({{ $sale->payment_method_text }})
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if($sale->order)
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center
                    @if($sale->order->status === 'completed') bg-green-100
                    @elseif($sale->order->status === 'accepted') bg-blue-100
                    @elseif($sale->order->status === 'rejected') bg-red-100
                    @else bg-yellow-100 @endif">
                    <i data-lucide="shopping-bag" class="w-6 h-6
                        @if($sale->order->status === 'completed') text-green-600
                        @elseif($sale->order->status === 'accepted') text-blue-600
                        @elseif($sale->order->status === 'rejected') text-red-600
                        @else text-yellow-600 @endif"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status Pesanan</p>
                    {!! $sale->order->status_badge !!}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment Form (if not paid) --}}
    @if($sale->payment_status !== 'paid' && $sale->order && $sale->order->status === 'accepted')
    <div class="mb-6 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i data-lucide="credit-card" class="w-5 h-5 mr-2 text-blue-600"></i>
            Proses Pembayaran
        </h3>

        <form action="{{ route('sales.mark-as-paid', $sale) }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Metode Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            onchange="toggleCashField()">
                        <option value="">Pilih Metode</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    </select>
                </div>

                <div id="cash_received_field" class="{{ old('payment_method') == 'cash' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jumlah Uang Diterima <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="cash_received" id="cash_received"
                           value="{{ old('cash_received') }}"
                           min="{{ $sale->total }}"
                           step="1000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan jumlah uang">
                    <p class="text-xs text-gray-500 mt-1">Minimal: Rp {{ number_format($sale->total, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-700">Total yang Harus Dibayar:</span>
                    <span class="text-xl font-bold text-blue-600">{{ $sale->formatted_total }}</span>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                    Konfirmasi Pembayaran
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Receipt -->
    <div class="bg-white rounded-xl shadow-sm">
        <!-- Receipt Header -->
        <div class="p-8 border-b">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">SEAFOOD RESTAURANT</h1>
                <p class="text-gray-600">Jl. Seafood No. 123, Jakarta</p>
                <p class="text-gray-600">Telp: (021) 123-4567</p>
            </div>

            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm text-gray-500">No. Transaksi</div>
                    <div class="text-xl font-bold text-gray-800">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
                    @if($sale->order)
                        <div class="text-sm text-gray-500 mt-1">No. Order</div>
                        <div class="font-medium text-gray-800">{{ $sale->order->order_number }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Tanggal</div>
                    <div class="text-lg font-semibold text-gray-800">{{ $sale->date->format('d/m/Y') }}</div>
                    <div class="text-sm text-gray-500">{{ $sale->created_at->format('H:i:s') }}</div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Kasir</div>
                    <div class="font-medium text-gray-800">{{ $sale->user->name }}</div>
                </div>
                @if($sale->order && $sale->order->user)
                <div>
                    <div class="text-sm text-gray-500">Pelanggan</div>
                    <div class="font-medium text-gray-800">{{ $sale->order->user->name }}</div>
                </div>
                @endif
            </div>

            @if($sale->payment_method)
            <div class="mt-4 p-3 bg-green-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">Metode Pembayaran:</span>
                    <span class="text-sm font-semibold text-green-600">
                        {{ $sale->payment_method_text }}
                        @if($sale->payment_method === 'cash' && $sale->cash_received)
                            (Rp {{ number_format($sale->cash_received, 0, ',', '.') }})
                        @endif
                    </span>
                </div>
                @if($sale->payment_method === 'cash' && $sale->change > 0)
                <div class="flex justify-between items-center mt-1">
                    <span class="text-sm font-medium text-gray-700">Kembalian:</span>
                    <span class="text-sm font-semibold text-green-600">
                        Rp {{ number_format($sale->change, 0, ',', '.') }}
                    </span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Items -->
        <div class="p-8">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="pb-3 text-left font-medium text-gray-700">Item</th>
                            <th class="pb-3 text-center font-medium text-gray-700">Qty</th>
                            <th class="pb-3 text-right font-medium text-gray-700">Harga</th>
                            <th class="pb-3 text-right font-medium text-gray-700">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr class="border-b">
                            <td class="py-3">
                                <div class="font-medium text-gray-800">{{ $item->menu->name }}</div>
                                @if($item->sauce)
                                <div class="text-xs text-gray-500">Saus: {{ $item->sauce->name }}</div>
                                @endif
                                <div class="text-xs text-gray-400">{{ $item->menu->code }}</div>
                            </td>
                            <td class="py-3 text-center">{{ $item->quantity }}</td>
                            <td class="py-3 text-right">
                                Rp {{ number_format($item->price + $item->additional_price, 0, ',', '.') }}
                                @if($item->additional_price > 0)
                                <div class="text-xs text-green-600">(+ Rp {{ number_format($item->additional_price, 0, ',', '.') }} saus)</div>
                                @endif
                            </td>
                            <td class="py-3 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="p-8 bg-gray-50 rounded-b-xl">
            <div class="max-w-md ml-auto">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xl font-bold border-t pt-3">
                        <span>TOTAL</span>
                        <span class="text-green-600">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if($sale->notes)
            <div class="mt-6 pt-6 border-t">
                <div class="text-sm text-gray-500 mb-1">Catatan:</div>
                <div class="text-gray-700">{{ $sale->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Transaction Info -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Transaksi</h3>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID Transaksi</span>
                    <span class="font-medium">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                @if($sale->order)
                <div class="flex justify-between">
                    <span class="text-gray-600">No. Order</span>
                    <span class="font-medium">{{ $sale->order->order_number }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal</span>
                    <span class="font-medium">{{ $sale->date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Waktu</span>
                    <span class="font-medium">{{ $sale->created_at->format('H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Kasir</span>
                    <span class="font-medium">{{ $sale->user->name }}</span>
                </div>
                @if($sale->order && $sale->order->user)
                <div class="flex justify-between">
                    <span class="text-gray-600">Pelanggan</span>
                    <span class="font-medium">{{ $sale->order->user->name }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Items</span>
                    <span class="font-medium">{{ $sale->items->sum('quantity') }} item</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jenis Menu</span>
                    <span class="font-medium">{{ $sale->items->count() }} jenis</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Dibuat</span>
                    <span class="font-medium">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($sale->completed_at)
                <div class="flex justify-between">
                    <span class="text-gray-600">Dibayar</span>
                    <span class="font-medium">{{ $sale->completed_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Rincian Bahan Terpakai</h3>

            <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                @php
                    $ingredientUsage = [];
                    foreach ($sale->items as $item) {
                        // Bahan dari menu utama
                        foreach ($item->menu->ingredients as $ingredient) {
                            $quantity = $ingredient->pivot->quantity * $item->quantity;
                            if (!isset($ingredientUsage[$ingredient->id])) {
                                $ingredientUsage[$ingredient->id] = [
                                    'name' => $ingredient->name,
                                    'unit' => $ingredient->unit,
                                    'quantity' => 0
                                ];
                            }
                            $ingredientUsage[$ingredient->id]['quantity'] += $quantity;
                        }

                        // Bahan dari saus (dengan batch system)
                        if ($item->sauce) {
                            $batches = ceil($item->quantity / 5);
                            foreach ($item->sauce->ingredients as $ingredient) {
                                $quantity = $ingredient->pivot->quantity * $batches;
                                if (!isset($ingredientUsage[$ingredient->id])) {
                                    $ingredientUsage[$ingredient->id] = [
                                        'name' => $ingredient->name,
                                        'unit' => $ingredient->unit,
                                        'quantity' => 0
                                    ];
                                }
                                $ingredientUsage[$ingredient->id]['quantity'] += $quantity;
                            }
                        }
                    }
                @endphp

                @foreach($ingredientUsage as $usage)
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800">{{ $usage['name'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-700">
                            {{ number_format($usage['quantity'], 2, ',', '.') }} {{ $usage['unit'] }}
                        </div>
                    </div>
                </div>
                @endforeach

                @if(empty($ingredientUsage))
                <div class="text-center py-4 text-gray-500">
                    <i data-lucide="package" class="w-8 h-8 mx-auto mb-2"></i>
                    <p>Tidak ada data bahan terpakai</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div>
                <p class="text-sm text-gray-600">
                    @if($sale->payment_status === 'paid')
                        Transaksi ini sudah lunas dan mengurangi stok bahan secara otomatis.
                    @else
                        Transaksi ini belum lunas. Silakan proses pembayaran terlebih dahulu.
                    @endif
                </p>
            </div>
            <div class="flex space-x-3">
                @if($sale->payment_status === 'paid')
                    <a href="{{ route('sales.print', $sale) }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                        Cetak Ulang
                    </a>
                @endif
                <form action="{{ route('sales.destroy', $sale) }}" method="POST"
                      onsubmit="return confirm('Hapus transaksi ini akan mengembalikan stok bahan. Lanjutkan?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                        Hapus Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Print-only styles -->
<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        font-size: 12px;
    }

    .receipt {
        width: 80mm;
        margin: 0 auto;
        padding: 10px;
        background: white;
    }
}
</style>
@endsection

@push('scripts')
<script>
function toggleCashField() {
    const paymentMethod = document.getElementById('payment_method').value;
    const cashField = document.getElementById('cash_received_field');
    const cashInput = document.getElementById('cash_received');

    if (paymentMethod === 'cash') {
        cashField.classList.remove('hidden');
        cashInput.required = true;
    } else {
        cashField.classList.add('hidden');
        cashInput.required = false;
        cashInput.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleCashField();

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
