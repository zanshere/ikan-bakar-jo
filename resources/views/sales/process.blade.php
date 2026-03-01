{{-- resources/views/sales/process.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Proses Pesanan')
@section('page-description', 'Terima atau tolak pesanan dari pelanggan')

@section('breadcrumb')
    <span>/</span>
    <a href="{{ route('sales.index') }}" class="text-gray-500 hover:text-gray-700">Penjualan</a>
    <span>/</span>
    <span class="text-gray-700">Proses Pesanan</span>
@endsection

@section('header-buttons')
    <a href="{{ route('sales.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold">Pesanan #{{ $sale->order->order_number }}</h2>
                        <p class="text-blue-100 text-sm mt-1">Dibuat oleh: {{ $sale->user->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-blue-100">Tanggal Pesan</p>
                        <p class="font-medium">{{ $sale->order->order_date->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Pesanan</h3>

                <div class="space-y-3">
                    @foreach ($sale->order->items as $item)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i data-lucide="utensils" class="w-5 h-5 text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">{{ $item->menu->name }}</h4>
                                        <p class="text-sm text-gray-600">Saus: {{ $item->sauce->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-8">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Jumlah</p>
                                    <p class="font-semibold">{{ $item->quantity }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Harga</p>
                                    <p class="font-semibold text-blue-600">
                                        {{ 'Rp ' . number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Subtotal</p>
                                    <p class="font-semibold text-green-600">
                                        {{ 'Rp ' . number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($sale->order->notes)
                    <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700">Catatan Pelanggan:</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $sale->order->notes }}</p>
                    </div>
                @endif

                <div class="mt-4 flex justify-end">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Total Pesanan</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $sale->formatted_total }}</p>
                    </div>
                </div>
            </div>

            {{-- Stock Info --}}
            <div class="p-6 border-b bg-blue-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-600"></i>
                    Informasi Stok Saus (Batch System)
                </h3>
                <p class="text-sm text-blue-700 mb-4">
                    Stok saus akan berkurang per 5 pesanan (1 batch = 5 porsi). Jika pesanan kurang dari 5, tetap dihitung 1 batch.
                </p>

                <div class="space-y-3">
                    @foreach ($sale->order->items as $item)
                        @if ($item->sauce && $item->sauce->ingredients->count() > 0)
                            <div class="bg-white p-3 rounded-lg">
                                <p class="font-medium text-gray-800 mb-2">{{ $item->sauce->name }} ({{ $item->quantity }}
                                    porsi)</p>
                                @php
                                    $batches = ceil($item->quantity / 5);
                                @endphp
                                <p class="text-sm text-gray-600 mb-2">Jumlah batch: <span
                                        class="font-semibold">{{ $batches }}</span> batch (1 batch = 5 porsi)</p>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    @foreach ($item->sauce->ingredients as $ingredient)
                                        @php
                                            $required = $batches * $ingredient->pivot->quantity;
                                            $status = $ingredient->stock >= $required ? 'sufficient' : 'insufficient';
                                        @endphp
                                        <div class="flex justify-between items-center p-2 rounded
                                            {{ $status === 'sufficient' ? 'bg-green-50' : 'bg-red-50' }}">
                                            <span>{{ $ingredient->name }}</span>
                                            <div class="text-right">
                                                <span class="font-medium {{ $status === 'sufficient' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($required, 2, ',', '.') }} {{ $ingredient->unit }}
                                                </span>
                                                <span class="text-xs text-gray-500 block">
                                                    Stok: {{ $ingredient->formatted_stock }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Accept Form --}}
                    <form action="{{ route('sales.accept', $sale) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="accept_notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan
                                (Opsional)</label>
                            <textarea id="accept_notes" name="notes" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Tambahkan catatan untuk pesanan ini..."></textarea>
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                            Terima Pesanan
                        </button>
                    </form>

                    {{-- Reject Form --}}
                    <form action="{{ route('sales.reject', $sale) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="rejected_reason" class="block text-sm font-medium text-gray-700 mb-1">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea id="rejected_reason" name="rejected_reason" rows="2" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                                placeholder="Contoh: Stok bahan tidak mencukupi..."></textarea>
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center"
                            onclick="return confirm('Apakah Anda yakin ingin menolak pesanan ini?')">
                            <i data-lucide="x-circle" class="w-5 h-5 mr-2"></i>
                            Tolak Pesanan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
