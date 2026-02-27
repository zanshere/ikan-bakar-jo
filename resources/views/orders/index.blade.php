{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Daftar Pesanan')
@section('page-description', 'Lihat semua pesanan Anda')

@section('breadcrumb')
<span>/</span>
<span class="text-gray-700">Pesanan</span>
@endsection

@section('header-buttons')
<a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
    Pesan Menu
</a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Pesanan</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Menunggu</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $pendingOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Diterima</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $acceptedOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Selesai</p>
                    <p class="text-2xl font-bold text-green-600">{{ $completedOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600">{{ $rejectedOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-0">
                <input type="text" name="search" placeholder="Cari pesanan..." value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-48">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Diterima</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="w-40">
                <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Tanggal Mulai"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-40">
                <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="Tanggal Akhir"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i>
                    Filter
                </button>
                <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pesanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600">{{ $order->order_date->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {!! $order->status_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $order->formatted_total }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-xs truncate">{{ $order->notes ?: '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <a href="{{ route('orders.show', $order) }}"
                                   class="text-blue-600 hover:text-blue-900 p-1"
                                   title="Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if($order->status === 'pending')
                                <form action="{{ route('orders.cancel', $order) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1" title="Batalkan">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="shopping-bag" class="w-12 h-12 mx-auto mb-3"></i>
                            <p class="text-lg font-medium">Belum ada pesanan</p>
                            <p class="text-sm mt-1">Mulai pesan menu favorit Anda sekarang!</p>
                            <a href="{{ route('orders.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                Pesan Menu
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
