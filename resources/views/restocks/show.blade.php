<!-- resources/views/restocks/show.blade.php -->
@extends('layouts.app')

@section('page-title', 'Detail Restock')
@section('page-description', 'Detail transaksi restock bahan baku')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('restocks.index') }}" class="text-gray-500 hover:text-gray-700">Restock</a>
<span>/</span>
<span class="text-gray-700">Detail</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <a href="{{ route('restocks.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Restock Details -->
    <div class="bg-white rounded-xl shadow-sm">
        <!-- Header -->
        <div class="p-8 border-b">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm text-gray-500">No. Restock</div>
                    <div class="text-xl font-bold text-gray-800">#{{ str_pad($restock->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Tanggal</div>
                    <div class="text-lg font-semibold text-gray-800">{{ $restock->date->format('d/m/Y') }}</div>
                    <div class="text-sm text-gray-500">{{ $restock->created_at->format('H:i:s') }}</div>
                </div>
            </div>

            <div class="mt-4">
                <div class="text-sm text-gray-500">PIC</div>
                <div class="font-medium text-gray-800">{{ $restock->user->name }}</div>
            </div>
        </div>

        <!-- Items -->
        <div class="p-8">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="pb-3 text-left font-medium text-gray-700">Bahan Baku</th>
                            <th class="pb-3 text-center font-medium text-gray-700">Qty</th>
                            <th class="pb-3 text-right font-medium text-gray-700">Harga Satuan</th>
                            <th class="pb-3 text-right font-medium text-gray-700">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($restock->items as $item)
                        <tr class="border-b">
                            <td class="py-3">
                                <div class="font-medium text-gray-800">{{ $item->ingredient->name }}</div>
                                <div class="text-sm text-gray-600">{{ $item->ingredient->code }}</div>
                            </td>
                            <td class="py-3 text-center">
                                {{ number_format($item->quantity, 2, ',', '.') }} {{ $item->ingredient->unit }}
                            </td>
                            <td class="py-3 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="p-8 bg-red-50 rounded-b-xl">
            <div class="max-w-md ml-auto">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Items</span>
                        <span class="font-medium">{{ $restock->items->count() }} bahan</span>
                    </div>
                    <div class="flex justify-between text-xl font-bold border-t pt-3">
                        <span>TOTAL PENGELUARAN</span>
                        <span class="text-red-600">Rp {{ number_format($restock->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if($restock->notes)
            <div class="mt-6 pt-6 border-t">
                <div class="text-sm text-gray-500 mb-1">Catatan:</div>
                <div class="text-gray-700">{{ $restock->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Restock Info -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Restock</h3>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID Restock</span>
                    <span class="font-medium">#{{ str_pad($restock->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal</span>
                    <span class="font-medium">{{ $restock->date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Waktu</span>
                    <span class="font-medium">{{ $restock->created_at->format('H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">PIC</span>
                    <span class="font-medium">{{ $restock->user->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Items</span>
                    <span class="font-medium">{{ $restock->items->count() }} bahan</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Kuantitas</span>
                    <span class="font-medium">{{ number_format($restock->items->sum('quantity'), 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Dibuat</span>
                    <span class="font-medium">{{ $restock->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaruh terhadap Stok</h3>

            <div class="space-y-3">
                @foreach($restock->items as $item)
                @php
                    $before = $item->ingredient->stock - $item->quantity;
                    $after = $item->ingredient->stock;
                @endphp
                <div class="p-3 bg-gray-50 rounded">
                    <div class="font-medium text-gray-800 mb-1">{{ $item->ingredient->name }}</div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Sebelum:</span>
                        <span class="font-medium">{{ number_format($before, 2, ',', '.') }} {{ $item->ingredient->unit }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Ditambahkan:</span>
                        <span class="font-medium text-green-600">+{{ number_format($item->quantity, 2, ',', '.') }} {{ $item->ingredient->unit }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold border-t pt-1 mt-1">
                        <span class="text-gray-700">Sesudah:</span>
                        <span class="text-blue-600">{{ number_format($after, 2, ',', '.') }} {{ $item->ingredient->unit }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div>
                <p class="text-sm text-gray-600">Transaksi ini menambah stok bahan secara otomatis</p>
            </div>
            <div class="flex space-x-3">
                <form action="{{ route('restocks.destroy', $restock) }}" method="POST"
                      onsubmit="return confirm('Hapus transaksi ini akan mengurangi stok bahan. Lanjutkan?')">
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
@endsection
