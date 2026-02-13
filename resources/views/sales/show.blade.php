<!-- resources/views/sales/show.blade.php -->
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
    <a href="{{ route('sales.print', $sale) }}" target="_blank"
       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
        <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
        Cetak Struk
    </a>
    <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
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
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Tanggal</div>
                    <div class="text-lg font-semibold text-gray-800">{{ $sale->date->format('d/m/Y') }}</div>
                    <div class="text-sm text-gray-500">{{ $sale->created_at->format('H:i:s') }}</div>
                </div>
            </div>

            <div class="mt-4">
                <div class="text-sm text-gray-500">Kasir</div>
                <div class="font-medium text-gray-800">{{ $sale->user->name }}</div>
            </div>
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
                                <div class="text-sm text-gray-600">{{ $item->menu->code }}</div>
                            </td>
                            <td class="py-3 text-center">{{ $item->quantity }}</td>
                            <td class="py-3 text-right">Rp {{ number_format($item->menu->price, 0, ',', '.') }}</td>
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
                        <span class="font-medium">Rp {{ number_format($sale->total / 1.1, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">PPN (10%)</span>
                        <span class="font-medium">Rp {{ number_format($sale->total / 1.1 * 0.1, 0, ',', '.') }}</span>
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
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Rincian Bahan Terpakai</h3>

            <div class="space-y-3">
                @php
                    $ingredientUsage = [];
                    foreach ($sale->items as $item) {
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
                <p class="text-sm text-gray-600">Transaksi ini mengurangi stok bahan secara otomatis</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('sales.print', $sale) }}" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                    Cetak Ulang
                </a>
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
