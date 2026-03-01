{{-- resources/views/menus/show.blade.php --}}
@extends('layouts.app')

@section('page-title', $menu->name)
@section('page-description', 'Detail menu makanan')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('menus.index') }}" class="text-gray-500 hover:text-gray-700">Menu</a>
<span>/</span>
<span class="text-gray-700">Detail</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <a href="{{ route('menus.edit', $menu) }}"
        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
        <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
        Edit
    </a>
    <a href="{{ route('menus.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Menu Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm">
            <!-- Header -->
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl font-bold text-gray-800">{{ $menu->name }}</div>
                            {!! $menu->status_badge !!}
                            @if($menu->type === 'main')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Menu Utama</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Saus</span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600">
                            <span class="flex items-center">
                                <i data-lucide="hash" class="w-4 h-4 mr-1"></i>
                                {{ $menu->code }}
                            </span>
                            <span class="flex items-center">
                                <i data-lucide="tag" class="w-4 h-4 mr-1"></i>
                                {{ $menu->formatted_price }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-blue-600">{{ $menu->formatted_price }}</div>
                        <div class="text-sm text-gray-500">Harga Jual</div>
                    </div>
                </div>
            </div>

            <!-- Image & Description -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if ($menu->image)
                        <div class="rounded-lg overflow-hidden border">
                            <img src="{{ Storage::url($menu->image) }}" alt="{{ $menu->name }}"
                                class="w-full h-64 object-cover">
                        </div>
                    @else
                        <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center h-64">
                            <div class="text-center">
                                <i data-lucide="image" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                                <p class="text-gray-500">Tidak ada gambar</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Deskripsi</h3>
                        <p class="text-gray-700 whitespace-pre-line">
                            {{ $menu->description ?: 'Tidak ada deskripsi' }}
                        </p>

                        @if($menu->type === 'sauce')
                            <div class="mt-4 p-4 bg-purple-50 rounded-lg">
                                <div class="flex items-center">
                                    <i data-lucide="info" class="w-5 h-5 text-purple-600 mr-2"></i>
                                    <div>
                                        <p class="text-sm text-purple-800">
                                            <strong>Menu tipe Saus</strong> - Item ini adalah saus yang dapat dipilih untuk menu utama.
                                        </p>
                                        <p class="text-xs text-purple-600 mt-1">
                                            Saus ini akan tersedia sebagai pilihan untuk menu utama yang telah dikonfigurasi.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingredients Section (only for main menu) -->
        @if($menu->type === 'main')
        <div class="mt-6 bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Komposisi Bahan</h3>
                <p class="text-sm text-gray-600 mt-1">Daftar bahan baku yang digunakan</p>
            </div>

            <div class="p-6">
                @if($menu->ingredients->isEmpty())
                    <div class="text-center py-8">
                        <i data-lucide="package" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <p class="text-gray-600">Belum ada bahan baku untuk menu ini</p>
                        <a href="{{ route('menus.edit', $menu) }}"
                           class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                            Tambah Bahan
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Biaya</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Tersedia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($menu->ingredients as $ingredient)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 bg-blue-100 rounded flex items-center justify-center mr-3">
                                                    <i data-lucide="package" class="w-4 h-4 text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ $ingredient->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $ingredient->code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">
                                                {{ number_format($ingredient->pivot->quantity, 2, ',', '.') }}
                                                {{ $ingredient->unit }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">{{ $ingredient->formatted_price }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">
                                                Rp {{ number_format($ingredient->price * $ingredient->pivot->quantity, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <span class="font-medium text-gray-900 mr-2">{{ $ingredient->formatted_stock }}</span>
                                                {!! $ingredient->stock_status_badge !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">Total Biaya Bahan:</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-blue-600">{{ $menu->formatted_cost }}</div>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Available Sauces Section (only for main menu) -->
        @if($menu->type === 'main')
        <div class="mt-6 bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Pilihan Saus Tersedia</h3>
                        <p class="text-sm text-gray-600 mt-1">Saus yang dapat dipilih pelanggan untuk menu ini</p>
                    </div>
                    <a href="{{ route('menus.manage-sauces', $menu) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                        Kelola Saus
                    </a>
                </div>
            </div>

            <div class="p-6">
                @php
                    $availableSauces = $menu->availableSauces;
                @endphp

                @if($availableSauces->isEmpty())
                    <div class="text-center py-8">
                        <i data-lucide="empty" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <p class="text-gray-600">Belum ada saus yang tersedia untuk menu ini</p>
                        <p class="text-sm text-gray-500 mt-1">
                            Kelola saus untuk menambahkan pilihan saus yang dapat dipilih pelanggan.
                        </p>
                        <a href="{{ route('menus.manage-sauces', $menu) }}"
                           class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                            Atur Pilihan Saus
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($availableSauces as $sauce)
                            <div class="border rounded-lg p-4 hover:border-blue-300 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <h4 class="font-medium text-gray-800">{{ $sauce->name }}</h4>
                                            @if($sauce->pivot->is_default)
                                                <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">Default</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">Kode: {{ $sauce->code }}</p>
                                        <p class="text-sm text-gray-600 mt-1">Harga: {{ $sauce->formatted_price }}</p>

                                        <div class="mt-3 space-y-1">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">Stok Saus:</span>
                                                <span class="font-medium">{{ $sauce->formatted_stock }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center text-xs">
                                            {!! $sauce->stock_status_badge !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Main Menus Using This Sauce (only for sauce type) -->
        @if($menu->type === 'sauce')
        <div class="mt-6 bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Digunakan Pada Menu Utama</h3>
                <p class="text-sm text-gray-600 mt-1">Menu utama yang menyediakan saus ini sebagai pilihan</p>
            </div>

            <div class="p-6">
                @php
                    $mainMenus = $menu->mainMenus;
                @endphp

                @if($mainMenus->isEmpty())
                    <div class="text-center py-8">
                        <i data-lucide="utensils" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <p class="text-gray-600">Saus ini belum digunakan pada menu utama manapun</p>
                        <p class="text-sm text-gray-500 mt-1">
                            Saus akan tersedia setelah dikonfigurasi pada menu utama.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($mainMenus as $mainMenu)
                            <div class="border rounded-lg p-4 hover:border-blue-300 transition-colors">
                                <div class="flex items-start">
                                    @if($mainMenu->image)
                                        <div class="h-12 w-12 rounded-lg overflow-hidden mr-3">
                                            <img src="{{ Storage::url($mainMenu->image) }}"
                                                 alt="{{ $mainMenu->name }}"
                                                 class="h-full w-full object-cover">
                                        </div>
                                    @else
                                        <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <i data-lucide="utensils" class="w-6 h-6 text-blue-600"></i>
                                        </div>
                                    @endif

                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <h4 class="font-medium text-gray-800">{{ $mainMenu->name }}</h4>
                                            @if($mainMenu->pivot->is_default)
                                                <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">Default</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">Kode: {{ $mainMenu->code }}</p>
                                        <p class="text-sm text-gray-600 mt-1">Harga: {{ $mainMenu->formatted_price }}</p>

                                        <div class="mt-3">
                                            <a href="{{ route('menus.show', $mainMenu) }}"
                                               class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                                Lihat Detail Menu
                                                <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column - Stats & Info -->
    <div class="space-y-6">
        <!-- Profit Analysis -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Analisis Profit</h3>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Harga Jual</span>
                    <span class="font-semibold text-gray-800">{{ $menu->formatted_price }}</span>
                </div>

                @if($menu->type === 'main')
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Biaya Bahan</span>
                        <span class="font-semibold text-gray-800">{!! $menu->formatted_cost !!}</span>
                    </div>

                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Profit</span>
                            <span class="text-xl font-bold {{ $menu->profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {!! $menu->formatted_profit !!}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Margin Profit</span>
                            <span class="text-lg font-semibold {{ $menu->profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $menu->profit_percentage }}%
                            </span>
                        </div>
                    </div>

                    <!-- Profit Bar -->
                    <div class="mt-4">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500"
                                style="width: {{ min(max($menu->profit_percentage, 0), 100) }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>0%</span>
                            <span>{{ number_format($menu->profit_percentage, 1) }}%</span>
                            <span>100%</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <div class="flex items-center">
                            <i data-lucide="info" class="w-5 h-5 text-purple-600 mr-2"></i>
                            <div>
                                <p class="text-sm text-purple-800">
                                    <strong>Menu tipe Saus</strong> - Profit dan biaya bahan tidak ditampilkan secara terpisah karena akan digabungkan dengan menu utama saat pemesanan.
                                </p>
                                <p class="text-xs text-purple-600 mt-1">
                                    Harga saus sudah termasuk dalam harga menu utama yang menggunakannya.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sales Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Penjualan</h3>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i data-lucide="shopping-cart" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Total Terjual</div>
                            <div class="text-xl font-bold text-gray-800">{{ $totalSold }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i data-lucide="dollar-sign" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Total Pendapatan</div>
                            <div class="text-xl font-bold text-gray-800">
                                {{ 'Rp ' . number_format($totalRevenue, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                @if($totalSold > 0)
                    <div class="pt-4 border-t">
                        <div class="text-sm text-gray-600 mb-2">Rata-rata per Penjualan</div>
                        <div class="flex space-x-4">
                            <div class="flex-1 text-center p-2 bg-blue-50 rounded-lg">
                                <div class="text-xs text-gray-500">Kuantitas</div>
                                <div class="font-semibold text-blue-600">
                                    {{ number_format($totalSold / max($menu->saleItems->count(), 1), 2) }}
                                </div>
                            </div>
                            <div class="flex-1 text-center p-2 bg-green-50 rounded-lg">
                                <div class="text-xs text-gray-500">Nilai</div>
                                <div class="font-semibold text-green-600">
                                    {{ $totalSold > 0 ? 'Rp ' . number_format($totalRevenue / $totalSold, 0, ',', '.') : 'Rp 0' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Menu Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Menu</h3>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Status</span>
                    <span class="font-medium">{!! $menu->status_badge !!}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Tipe</span>
                    <span class="font-medium">
                        @if($menu->type === 'main')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Menu Utama</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Saus</span>
                        @endif
                    </span>
                </div>

                @if($menu->type === 'main')
                    <div class="flex justify-between">
                        <span class="text-gray-600">Jumlah Bahan</span>
                        <span class="font-medium">{{ $menu->ingredients->count() }} bahan</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Jumlah Saus</span>
                        <span class="font-medium">{{ $menu->availableSauces->count() }} saus</span>
                    </div>
                @endif

                <div class="flex justify-between">
                    <span class="text-gray-600">Dibuat</span>
                    <span class="font-medium">{{ $menu->created_at->format('d/m/Y H:i') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Terakhir Diubah</span>
                    <span class="font-medium">{{ $menu->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi</h3>

            <div class="space-y-2">
                <a href="{{ route('menus.edit', $menu) }}"
                    class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                    Edit Menu
                </a>

                @if($menu->type === 'main')
                    <a href="{{ route('menus.manage-sauces', $menu) }}"
                        class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                        Kelola Saus
                    </a>
                @endif

                <form action="{{ route('menus.toggle-status', $menu) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center px-4 py-2 {{ $menu->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg">
                        <i data-lucide="{{ $menu->is_active ? 'toggle-left' : 'toggle-right' }}" class="w-4 h-4 mr-2"></i>
                        {{ $menu->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>

                <form action="{{ route('menus.destroy', $menu) }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                        Hapus Menu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sales -->
<div class="mt-6 bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Penjualan Terakhir</h3>
            <a href="{{ route('sales.index') }}?search={{ $menu->name }}"
                class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                Lihat Semua
                <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>
    </div>

    <div class="p-6">
        @php
            // Filter sale items yang memiliki relasi sale yang valid
            $validSaleItems = $menu->saleItems->filter(function ($item) {
                return $item->sale !== null && $item->sale->date !== null;
            });
        @endphp

        @if ($validSaleItems->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($validSaleItems as $item)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ optional($item->sale)->date ? $item->sale->date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($item->sale && $item->sale->id)
                                        <a href="{{ route('sales.show', $item->sale->id) }}"
                                            class="text-blue-600 hover:text-blue-800">
                                            #{{ str_pad($item->sale->id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $item->quantity }} porsi
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $item->formatted_subtotal ?? 'Rp ' . number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ optional(optional($item->sale)->user)->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i data-lucide="shopping-cart" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                <p class="text-gray-600">Belum ada penjualan untuk menu ini</p>
            </div>
        @endif
    </div>
</div>
@endsection
