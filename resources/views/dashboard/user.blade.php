{{-- resources/views/dashboard/user.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Dashboard User')
@section('page-description', 'Selamat datang kembali, {{ Auth::user()->name }}!')

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
                    <p class="text-sm text-gray-600">Diproses</p>
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
                    <p class="text-sm text-gray-600">Total Belanja</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($totalSpent, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('orders.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                    <i data-lucide="plus" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Pesan Menu</p>
                    <p class="text-xs text-gray-600">Buat pesanan baru</p>
                </div>
            </a>

            <a href="{{ route('orders.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Pesanan Saya</p>
                    <p class="text-xs text-gray-600">Lihat status pesanan</p>
                </div>
            </a>

            <a href="{{ route('menus.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                    <i data-lucide="utensils" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Lihat Menu</p>
                    <p class="text-xs text-gray-600">Jelajahi menu tersedia</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Pesanan Terbaru</h3>
            <a href="{{ route('orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
        </div>
        <div class="p-6">
            @if($recentOrders->isEmpty())
                <div class="text-center py-8">
                    <i data-lucide="shopping-bag" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600">Belum ada pesanan</p>
                    <a href="{{ route('orders.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Pesan Sekarang
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pesanan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $order->order_number }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-gray-600">{{ $order->formatted_date }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    {!! $order->status_badge !!}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800">{{ $order->formatted_total }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Recommended Menus --}}
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Rekomendasi Menu</h3>
            <a href="{{ route('menus.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
        </div>
        <div class="p-6">
            @if($recommendedMenus->isEmpty())
                <p class="text-gray-500 text-center py-4">Belum ada menu tersedia</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recommendedMenus as $menu)
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            @if($menu->image)
                            <div class="h-32 w-full mb-3 rounded overflow-hidden">
                                <img src="{{ Storage::url($menu->image) }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
                            </div>
                            @else
                            <div class="h-32 w-full mb-3 bg-gray-100 rounded flex items-center justify-center">
                                <i data-lucide="utensils" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            @endif
                            <h4 class="font-medium text-gray-800 mb-1">{{ $menu->name }}</h4>
                            <p class="text-sm text-blue-600 font-semibold">{{ $menu->formatted_price }}</p>
                            <a href="{{ route('orders.create') }}?menu={{ $menu->id }}" class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                Pesan Sekarang
                                <i data-lucide="arrow-right" class="w-3 h-3 ml-1"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
