<!-- resources/views/menus/index.blade.php -->
@extends('layouts.app')

@section('page-title', 'Manajemen Menu')
@section('page-description', 'Kelola menu makanan dan minuman')

@section('breadcrumb')
<span>/</span>
<span class="text-gray-700">Menu</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <a href="{{ route('menus.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
        Tambah Menu
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <!-- Filters -->
    <div class="p-6 border-b">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Menu</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Nama atau kode menu...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Minimal</label>
                <input type="number" name="min_price" value="{{ request('min_price') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                       placeholder="0">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Maksimal</label>
                <input type="number" name="max_price" value="{{ request('max_price') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                       placeholder="1000000">
            </div>

            <div class="md:col-span-4 flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                    Filter
                </button>
                <a href="{{ route('menus.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="p-6 border-b bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <p class="text-sm text-gray-500">Total Menu</p>
                <p class="text-2xl font-bold text-gray-800">{{ $menus->total() }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-500">Menu Aktif</p>
                <p class="text-2xl font-bold text-green-600">{{ $menus->where('is_active', true)->count() }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-500">Menu Nonaktif</p>
                <p class="text-2xl font-bold text-red-600">{{ $menus->where('is_active', false)->count() }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-500">Harga Rata-rata</p>
                <p class="text-2xl font-bold text-blue-600">
                    Rp {{ number_format($menus->avg('price') ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya Bahan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($menus as $menu)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($menu->image)
                            <div class="h-10 w-10 shrink-0">
                                <img class="h-10 w-10 rounded-lg object-cover"
                                     src="{{ Storage::url($menu->image) }}"
                                     alt="{{ $menu->name }}">
                            </div>
                            @else
                            <div class="h-10 w-10 shrink-0 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="utensils" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            @endif
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $menu->name }}</div>
                                <div class="text-sm text-gray-500">{{ $menu->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900">{{ $menu->formatted_price }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $menu->formatted_cost }}</div>
                        <div class="text-xs text-gray-500">{{ $menu->ingredients->count() }} bahan</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold {{ $menu->profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $menu->formatted_profit }}
                        </div>
                        <div class="text-xs text-gray-500">{{ $menu->profit_percentage }}% margin</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {!! $menu->status_badge !!}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('menus.show', $menu) }}"
                               class="text-blue-600 hover:text-blue-900 p-1">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('menus.edit', $menu) }}"
                               class="text-green-600 hover:text-green-900 p-1">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('menus.toggle-status', $menu) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-amber-600 hover:text-amber-900 p-1">
                                    @if($menu->is_active)
                                    <i data-lucide="toggle-left" class="w-4 h-4"></i>
                                    @else
                                    <i data-lucide="toggle-right" class="w-4 h-4"></i>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('menus.destroy', $menu) }}" method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-1">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-400">
                            <i data-lucide="utensils-crossed" class="w-12 h-12 mx-auto mb-4"></i>
                            <p class="text-lg">Belum ada menu</p>
                            <p class="text-sm mt-2">Mulai dengan menambahkan menu pertama Anda</p>
                            <a href="{{ route('menus.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                Tambah Menu
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($menus->hasPages())
    <div class="px-6 py-4 border-t">
        {{ $menus->links() }}
    </div>
    @endif
</div>
@endsection
