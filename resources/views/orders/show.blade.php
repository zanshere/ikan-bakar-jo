{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Detail Pesanan')
@section('page-description', 'Pesanan #' . $order->order_number)

@section('breadcrumb')
<span>/</span>
<a href="{{ route('orders.index') }}" class="text-gray-500 hover:text-gray-700">Pesanan</a>
<span>/</span>
<span class="text-gray-700">{{ $order->order_number }}</span>
@endsection

@section('header-buttons')
<a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Kembali
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Detail Pesanan</h2>
                    <p class="text-sm text-gray-600">Dibuat pada {{ $order->order_date->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    {!! $order->status_badge !!}
                    @if($order->status === 'completed' && $order->sale)
                        <a href="{{ route('sales.print', $order->sale) }}" target="_blank"
                           class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            <i data-lucide="printer" class="w-4 h-4 mr-1"></i>
                            Cetak Struk
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Items --}}
            <div>
                <h3 class="text-md font-semibold text-gray-800 mb-3">Item Pesanan</h3>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="font-medium text-gray-800">{{ $item->menu->name }}</div>
                            <div class="text-sm text-gray-600">Saus: {{ $item->sauce->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                                @if($item->additional_price > 0)
                                    + Rp {{ number_format($item->additional_price, 0, ',', '.') }}
                                @endif
                                × {{ $item->quantity }}
                            </div>
                        </div>
                        <div class="font-semibold text-blue-600">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Notes --}}
            @if($order->notes)
            <div>
                <h3 class="text-md font-semibold text-gray-800 mb-2">Catatan</h3>
                <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $order->notes }}</p>
            </div>
            @endif

            {{-- Summary --}}
            <div class="border-t pt-4">
                <div class="flex justify-between items-center text-lg font-bold">
                    <span>Total</span>
                    <span class="text-blue-600">{{ $order->formatted_total }}</span>
                </div>
            </div>

            {{-- Status Info --}}
            <div class="border-t pt-4">
                <h3 class="text-md font-semibold text-gray-800 mb-3">Informasi Status</h3>
                <div class="grid grid-cols-2 gap-4">
                    @if($order->processed_at)
                    <div>
                        <p class="text-sm text-gray-600">Diproses oleh</p>
                        <p class="font-medium">{{ $order->processor->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $order->processed_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif

                    @if($order->completed_at)
                    <div>
                        <p class="text-sm text-gray-600">Selesai pada</p>
                        <p class="font-medium">{{ $order->completed_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif

                    @if($order->rejected_reason)
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Alasan Ditolak</p>
                        <p class="text-red-600 bg-red-50 p-2 rounded">{{ $order->rejected_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if($order->status === 'pending')
            <div class="border-t pt-4 flex justify-end">
                <form action="{{ route('orders.cancel', $order) }}" method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i data-lucide="x-circle" class="w-4 h-4 inline mr-2"></i>
                        Batalkan Pesanan
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
