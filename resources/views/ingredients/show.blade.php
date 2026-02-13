<!-- resources/views/ingredients/show.blade.php -->
@extends('layouts.app')

@section('page-title', $ingredient->name)
@section('page-description', 'Detail bahan baku')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('ingredients.index') }}" class="text-gray-500 hover:text-gray-700">Bahan Baku</a>
<span>/</span>
<span class="text-gray-700">Detail</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <a href="{{ route('ingredients.edit', $ingredient) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
        <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
        Edit
    </a>
    <button onclick="showAdjustStockModal({{ $ingredient->id }}, '{{ $ingredient->name }}')"
            class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-amber-700">
        <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
        Sesuaikan Stok
    </button>
    <a href="{{ route('ingredients.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Ingredient Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm">
            <!-- Header -->
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-3">
                            <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-800">{{ $ingredient->name }}</div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                                    <span class="flex items-center">
                                        <i data-lucide="hash" class="w-4 h-4 mr-1"></i>
                                        {{ $ingredient->code }}
                                    </span>
                                    <span class="flex items-center">
                                        <i data-lucide="scale" class="w-4 h-4 mr-1"></i>
                                        {{ $ingredient->unit }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-blue-600">{{ $ingredient->formatted_price }}</div>
                        <div class="text-sm text-gray-500">Harga per {{ $ingredient->unit }}</div>
                    </div>
                </div>
            </div>

            <!-- Stock Info -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Stok</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-gray-500 mb-1">Stok Saat Ini</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $ingredient->formatted_stock }}</div>
                    </div>

                    <div class="p-4 bg-amber-50 rounded-lg">
                        <div class="text-sm text-gray-500 mb-1">Stok Minimum</div>
                        <div class="text-2xl font-bold text-amber-600">{{ $ingredient->formatted_min_stock }}</div>
                    </div>

                    <div class="p-4 bg-green-50 rounded-lg">
                        <div class="text-sm text-gray-500 mb-1">Nilai Stok</div>
                        <div class="text-2xl font-bold text-green-600">{{ $ingredient->formatted_total_value }}</div>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-700">Status Stok</div>
                            <div class="flex items-center mt-1">
                                {!! $ingredient->stock_status_badge !!}
                                <span class="ml-2 text-sm text-gray-600">
                                    @if($ingredient->stock_status == 'empty')
                                    Stok telah habis, segera restock
                                    @elseif($ingredient->stock_status == 'low')
                                    Stok di bawah minimum, segera restock
                                    @else
                                    Stok dalam kondisi baik
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-700">Sisa Stok</div>
                            <div class="text-xl font-bold {{ $ingredient->stock >= $ingredient->min_stock ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $ingredient->stock - $ingredient->min_stock }} {{ $ingredient->unit }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage in Menus -->
        <div class="mt-6 bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Digunakan di Menu</h3>
                <p class="text-sm text-gray-600 mt-1">Daftar menu yang menggunakan bahan ini</p>
            </div>

            <div class="p-6">
                @if($menuUsage->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas per Porsi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya per Porsi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Menu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($menuUsage as $menu)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('menus.show', $menu) }}"
                                       class="flex items-center hover:text-blue-600">
                                        @if($menu->image)
                                        <div class="h-8 w-8 shrink-0 mr-3">
                                            <img class="h-8 w-8 rounded object-cover"
                                                 src="{{ Storage::url($menu->image) }}"
                                                 alt="{{ $menu->name }}">
                                        </div>
                                        @else
                                        <div class="h-8 w-8 bg-blue-100 rounded flex items-center justify-center mr-3">
                                            <i data-lucide="utensils" class="w-4 h-4 text-blue-600"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="font-medium">{{ $menu->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $menu->code }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        {{ number_format($menu->pivot->quantity, 2, ',', '.') }} {{ $ingredient->unit }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        Rp {{ number_format($ingredient->price * $menu->pivot->quantity, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $menu->formatted_price }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    {!! $menu->status_badge !!}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right font-semibold text-gray-700">Total Menu:</td>
                                <td class="px-4 py-3 font-bold text-blue-600">{{ $menuUsage->count() }} menu</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <i data-lucide="utensils-crossed" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600">Bahan ini belum digunakan di menu manapun</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column - Stats & History -->
    <div class="space-y-6">
        <!-- Restock History -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Riwayat Restock</h3>
                    <a href="{{ route('restocks.index') }}?search={{ $ingredient->name }}"
                       class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                        Lihat Semua
                        <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="p-6">
                @if($ingredient->restockItems->count() > 0)
                <div class="space-y-4">
                    @foreach($ingredient->restockItems->sortByDesc('created_at')->take(5) as $item)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">
                                {{ $item->restock->date->format('d/m/Y') }}
                            </span>
                            <span class="text-sm font-bold text-green-600">
                                +{{ $item->formatted_quantity }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>@ {{ $item->formatted_price }}</span>
                            <span>{{ $item->formatted_subtotal }}</span>
                        </div>
                        @if($item->restock->notes)
                        <div class="mt-2 text-xs text-gray-500">
                            {{ Str::limit($item->restock->notes, 50) }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Summary -->
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Total Restock</div>
                            <div class="text-lg font-bold text-blue-600">{{ number_format($totalRestocked, 2, ',', '.') }} {{ $ingredient->unit }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Total Biaya</div>
                            <div class="text-lg font-bold text-green-600">Rp {{ number_format($totalRestockCost, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <i data-lucide="package" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600">Belum ada riwayat restock</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Ingredient Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Bahan</h3>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Kode</span>
                    <span class="font-medium">{{ $ingredient->code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Satuan</span>
                    <span class="font-medium">{{ $ingredient->unit }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Harga Satuan</span>
                    <span class="font-medium">{{ $ingredient->formatted_price }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Minimal Stok</span>
                    <span class="font-medium">{{ $ingredient->formatted_min_stock }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Dibuat</span>
                    <span class="font-medium">{{ $ingredient->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Terakhir Diubah</span>
                    <span class="font-medium">{{ $ingredient->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi</h3>

            <div class="space-y-2">
                <a href="{{ route('ingredients.edit', $ingredient) }}"
                   class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                    Edit Bahan
                </a>

                <button onclick="showAdjustStockModal({{ $ingredient->id }}, '{{ $ingredient->name }}')"
                        class="w-full flex items-center justify-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                    Sesuaikan Stok
                </button>

                <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus bahan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                        Hapus Bahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div id="adjustStockModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Sesuaikan Stok</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $ingredient->name }}</p>
            </div>

            <form action="{{ route('ingredients.adjust-stock', $ingredient) }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Penyesuaian</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="relative flex cursor-pointer">
                            <input type="radio" name="type" value="increase" class="sr-only peer" checked>
                            <div class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <i data-lucide="plus" class="w-5 h-5 text-green-600 mx-auto mb-1"></i>
                                <span class="text-sm font-medium">Tambah</span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer">
                            <input type="radio" name="type" value="decrease" class="sr-only peer">
                            <div class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <i data-lucide="minus" class="w-5 h-5 text-red-600 mx-auto mb-1"></i>
                                <span class="text-sm font-medium">Kurangi</span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer">
                            <input type="radio" name="type" value="set" class="sr-only peer">
                            <div class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <i data-lucide="edit" class="w-5 h-5 text-blue-600 mx-auto mb-1"></i>
                                <span class="text-sm font-medium">Set Manual</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Kuantitas ({{ $ingredient->unit }})</label>
                    <input type="number" id="quantity" name="quantity" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Stok saat ini: {{ $ingredient->formatted_stock }}</p>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                    <textarea id="notes" name="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Contoh: Koreksi stok, hasil opname, dll."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideAdjustStockModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showAdjustStockModal() {
    document.getElementById('adjustStockModal').classList.remove('hidden');
}

function hideAdjustStockModal() {
    document.getElementById('adjustStockModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('adjustStockModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideAdjustStockModal();
    }
});
</script>
@endpush
