<!-- resources/views/ingredients/edit.blade.php -->
@extends('layouts.app')

@section('page-title', 'Edit Bahan Baku')
@section('page-description', 'Perbarui informasi bahan baku')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('ingredients.index') }}" class="text-gray-500 hover:text-gray-700">Bahan Baku</a>
<span>/</span>
<a href="{{ route('ingredients.show', $ingredient) }}" class="text-gray-500 hover:text-gray-700">{{ $ingredient->name }}</a>
<span>/</span>
<span class="text-gray-700">Edit</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <a href="{{ route('ingredients.show', $ingredient) }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700">
        <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
        Detail
    </a>
    <a href="{{ route('ingredients.index') }}"
       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('ingredients.update', $ingredient) }}"
              class="space-y-6 show-loading">
            @csrf
            @method('PUT')

            <!-- Header -->
            <div class="border-b pb-4 mb-6">
                <div class="flex items-center">
                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Edit Bahan Baku</h2>
                        <p class="text-sm text-gray-600">Perbarui informasi bahan baku "{{ $ingredient->name }}"</p>
                    </div>
                </div>
            </div>

            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan *</label>
                <input type="text" id="code" name="code"
                       value="{{ old('code', $ingredient->code) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                       readonly>
                <p class="mt-1 text-xs text-gray-500">Kode tidak dapat diubah</p>
            </div>

            <!-- Name & Unit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $ingredient->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Satuan *</label>
                    <select id="unit" name="unit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Pilih Satuan</option>
                        <option value="kg" {{ old('unit', $ingredient->unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="gr" {{ old('unit', $ingredient->unit) == 'gr' ? 'selected' : '' }}>Gram (gr)</option>
                        <option value="liter" {{ old('unit', $ingredient->unit) == 'liter' ? 'selected' : '' }}>Liter (l)</option>
                        <option value="ml" {{ old('unit', $ingredient->unit) == 'ml' ? 'selected' : '' }}>Mililiter (ml)</option>
                        <option value="pcs" {{ old('unit', $ingredient->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                        <option value="pack" {{ old('unit', $ingredient->unit) == 'pack' ? 'selected' : '' }}>Pack (pack)</option>
                        <option value="buah" {{ old('unit', $ingredient->unit) == 'buah' ? 'selected' : '' }}>Buah</option>
                        <option value="ikat" {{ old('unit', $ingredient->unit) == 'ikat' ? 'selected' : '' }}>Ikat</option>
                        <option value="bungkus" {{ old('unit', $ingredient->unit) == 'bungkus' ? 'selected' : '' }}>Bungkus</option>
                        <option value="botol" {{ old('unit', $ingredient->unit) == 'botol' ? 'selected' : '' }}>Botol</option>
                        <option value="kaleng" {{ old('unit', $ingredient->unit) == 'kaleng' ? 'selected' : '' }}>Kaleng</option>
                        <option value="sachet" {{ old('unit', $ingredient->unit) == 'sachet' ? 'selected' : '' }}>Sachet</option>
                        <option value="ikat" {{ old('unit', $ingredient->unit) == 'ikat' ? 'selected' : '' }}>Ikat</option>
                    </select>
                    @error('unit')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Stock Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stok Saat Ini *</label>
                    <div class="relative">
                        <input type="number" id="stock" name="stock" step="0.01" min="0"
                               value="{{ old('stock', $ingredient->stock) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">{{ $ingredient->unit }}</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Stok aktual saat ini</p>
                    @error('stock')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-1">Stok Minimum *</label>
                    <div class="relative">
                        <input type="number" id="min_stock" name="min_stock" step="0.01" min="0"
                               value="{{ old('min_stock', $ingredient->min_stock) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">{{ $ingredient->unit }}</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Sistem akan memberi peringatan jika stok di bawah angka ini</p>
                    @error('min_stock')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan (Rp) *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">Rp</span>
                    </div>
                    <input type="number" id="price" name="price" min="0" step="100"
                           value="{{ old('price', $ingredient->price) }}"
                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           required>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">per {{ $ingredient->unit }}</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Harga per satuan (misal: per kg, per liter, dll)</p>
                @error('price')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Stock Value Preview -->
            <div class="p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Stok</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Nilai Total Stok</p>
                        <p class="text-lg font-bold text-blue-600" id="totalValuePreview">
                            Rp {{ number_format($ingredient->total_value, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status Stok</p>
                        <p class="text-lg font-bold" id="stockStatusPreview">
                            @if($ingredient->stock <= 0)
                            <span class="text-red-600">Habis</span>
                            @elseif($ingredient->stock < $ingredient->min_stock)
                            <span class="text-amber-600">Rendah</span>
                            @else
                            <span class="text-green-600">Cukup</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t">
                    <p class="text-xs text-gray-500 mb-1">Sisa Stok</p>
                    <div class="flex items-center">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $ingredient->stock_status == 'empty' ? 'bg-red-600' : ($ingredient->stock_status == 'low' ? 'bg-amber-600' : 'bg-green-600') }}"
                                 style="width: {{ $ingredient->min_stock > 0 ? min(($ingredient->stock / $ingredient->min_stock) * 100, 100) : 100 }}%">
                            </div>
                        </div>
                        <span class="ml-2 text-sm font-medium {{ $ingredient->stock_status == 'empty' ? 'text-red-600' : ($ingredient->stock_status == 'low' ? 'text-amber-600' : 'text-green-600') }}">
                            {{ $ingredient->stock - $ingredient->min_stock }} {{ $ingredient->unit }}
                        </span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0 {{ $ingredient->unit }}</span>
                        <span>{{ $ingredient->min_stock }} {{ $ingredient->unit }} (minimum)</span>
                        <span>{{ $ingredient->stock }} {{ $ingredient->unit }} (saat ini)</span>
                    </div>
                </div>
            </div>

            <!-- Used in Menus -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Digunakan di Menu</h4>
                @if($ingredient->menus->count() > 0)
                <div class="space-y-2">
                    @foreach($ingredient->menus->take(3) as $menu)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <div class="h-6 w-6 bg-blue-100 rounded flex items-center justify-center mr-2">
                                <i data-lucide="utensils" class="w-3 h-3 text-blue-600"></i>
                            </div>
                            <span class="font-medium">{{ $menu->name }}</span>
                        </div>
                        <span class="text-gray-600">{{ number_format($menu->pivot->quantity, 2, ',', '.') }} {{ $ingredient->unit }}/porsi</span>
                    </div>
                    @endforeach
                    @if($ingredient->menus->count() > 3)
                    <div class="text-center">
                        <span class="text-xs text-gray-500">
                            +{{ $ingredient->menus->count() - 3 }} menu lainnya
                        </span>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-2">
                    <i data-lucide="utensils-crossed" class="w-6 h-6 text-gray-400 mx-auto mb-1"></i>
                    <p class="text-xs text-gray-500">Bahan ini belum digunakan di menu manapun</p>
                </div>
                @endif
            </div>

            <!-- Restock History -->
            <div class="p-4 bg-green-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Riwayat Restock Terakhir</h4>
                @if($ingredient->restockItems->count() > 0)
                @php
                    $lastRestock = $ingredient->restockItems->sortByDesc('created_at')->first();
                @endphp
                <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-medium">{{ $lastRestock->restock->date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Kuantitas:</span>
                        <span class="font-medium text-green-600">+{{ number_format($lastRestock->quantity, 2, ',', '.') }} {{ $ingredient->unit }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Harga:</span>
                        <span class="font-medium">Rp {{ number_format($lastRestock->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-bold">Rp {{ number_format($lastRestock->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                @else
                <div class="text-center py-2">
                    <i data-lucide="package" class="w-6 h-6 text-gray-400 mx-auto mb-1"></i>
                    <p class="text-xs text-gray-500">Belum ada riwayat restock</p>
                </div>
                @endif
            </div>

            <!-- Last Updated Info -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Dibuat</p>
                        <p class="font-medium">{{ $ingredient->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Terakhir Diubah</p>
                        <p class="font-medium">{{ $ingredient->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Submit & Actions -->
            <div class="flex justify-between items-center pt-6 border-t">
                <div class="text-sm text-gray-600">
                    <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                    Perubahan akan mempengaruhi semua menu yang menggunakan bahan ini
                </div>

                <div class="flex space-x-3">
                    <a href="{{ route('ingredients.show', $ingredient) }}"
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                        Perbarui
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-red-200">
        <div class="p-6 border-b border-red-200">
            <div class="flex items-center">
                <div class="h-10 w-10 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-red-800">Zona Bahaya</h3>
                    <p class="text-sm text-red-600 mt-1">Aksi ini tidak dapat dibatalkan</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="space-y-4">
                <!-- Delete Form -->
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Hapus Bahan Baku</h4>
                    <p class="text-sm text-gray-600 mb-4">
                        Menghapus bahan ini akan mempengaruhi semua menu yang menggunakannya.
                        Pastikan bahan ini tidak digunakan dalam menu aktif sebelum menghapus.
                    </p>

                    <div class="bg-red-50 p-4 rounded-lg mb-4">
                        <div class="flex items-start">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mr-2 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-red-700 font-medium">Peringatan!</p>
                                <p class="text-sm text-red-600 mt-1">
                                    Bahan ini digunakan dalam {{ $ingredient->menus->count() }} menu.
                                    Hapus bahan hanya jika Anda yakin.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST"
                          onsubmit="return confirmDelete()">
                        @csrf
                        @method('DELETE')

                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <label for="confirm_delete" class="block text-sm font-medium text-gray-700 mb-1">
                                    Ketik "HAPUS" untuk konfirmasi
                                </label>
                                <input type="text" id="confirm_delete"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                                       placeholder="HAPUS">
                            </div>
                            <button type="submit" id="deleteButton"
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                <i data-lucide="trash-2" class="w-4 h-4 inline mr-2"></i>
                                Hapus
                            </button>
                        </div>
                    </form>
                </div>
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
function calculateStockValue() {
    const stock = parseFloat(document.getElementById('stock').value) || 0;
    const price = parseFloat(document.getElementById('price').value) || 0;
    const minStock = parseFloat(document.getElementById('min_stock').value) || 0;

    // Calculate total value
    const totalValue = stock * price;
    document.getElementById('totalValuePreview').textContent =
        'Rp ' + totalValue.toLocaleString('id-ID');

    // Determine stock status
    let status = 'Cukup';
    let statusColor = 'text-green-600';

    if (stock <= 0) {
        status = 'Habis';
        statusColor = 'text-red-600';
    } else if (stock < minStock) {
        status = 'Rendah';
        statusColor = 'text-amber-600';
    }

    const statusElement = document.getElementById('stockStatusPreview');
    statusElement.innerHTML = `<span class="${statusColor}">${status}</span>`;
}

// Update preview on input
document.getElementById('stock').addEventListener('input', calculateStockValue);
document.getElementById('price').addEventListener('input', calculateStockValue);
document.getElementById('min_stock').addEventListener('input', calculateStockValue);

// Initialize on page load
document.addEventListener('DOMContentLoaded', calculateStockValue);

// Delete confirmation
function confirmDelete() {
    const confirmInput = document.getElementById('confirm_delete');
    if (confirmInput.value !== 'HAPUS') {
        alert('Silakan ketik "HAPUS" untuk mengonfirmasi penghapusan');
        return false;
    }
    return confirm('Apakah Anda yakin ingin menghapus bahan ini? Tindakan ini tidak dapat dibatalkan.');
}

// Enable/disable delete button based on confirmation input
document.getElementById('confirm_delete').addEventListener('input', function() {
    const deleteButton = document.getElementById('deleteButton');
    deleteButton.disabled = this.value !== 'HAPUS';
});

// Adjust Stock Modal Functions
function showAdjustStockModal() {
    document.getElementById('adjustStockModal').classList.remove('hidden');
}

function hideAdjustStockModal() {
    document.getElementById('adjustStockModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('adjustStockModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAdjustStockModal();
    }
});

// Auto-calculate when unit changes
document.getElementById('unit').addEventListener('change', function() {
    const unit = this.value;
    const stockInput = document.getElementById('stock');
    const minStockInput = document.getElementById('min_stock');

    // Update placeholders and labels if needed
    if (unit) {
        document.querySelectorAll('.absolute.right-0 span.text-gray-500').forEach(span => {
            span.textContent = unit;
        });
    }
});
</script>
@endpush

@push('styles')
<style>
/* Custom styles for better UX */
input:invalid, select:invalid {
    border-color: #F87171;
}

input:valid, select:valid {
    border-color: #10B981;
}

.form-group {
    transition: all 0.2s ease;
}

.form-group:focus-within {
    transform: translateY(-1px);
}

.danger-zone {
    border-left: 4px solid #EF4444;
}
</style>
@endpush
