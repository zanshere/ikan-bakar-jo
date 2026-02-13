<!-- resources/views/ingredients/create.blade.php & resources/views/ingredients/edit.blade.php -->
@extends('layouts.app')

@section('page-title', isset($ingredient) ? 'Edit Bahan Baku' : 'Tambah Bahan Baru')
@section('page-description', isset($ingredient) ? 'Perbarui informasi bahan baku' : 'Tambahkan bahan baku baru')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('ingredients.index') }}" class="text-gray-500 hover:text-gray-700">Bahan Baku</a>
<span>/</span>
<span class="text-gray-700">{{ isset($ingredient) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('header-buttons')
<a href="{{ route('ingredients.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Kembali
</a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ isset($ingredient) ? route('ingredients.update', $ingredient) : route('ingredients.store') }}"
              class="space-y-6 show-loading">
            @csrf
            @if(isset($ingredient))
                @method('PUT')
            @endif

            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan *</label>
                <input type="text" id="code" name="code"
                       value="{{ old('code', $ingredient->code ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                       readonly>
                <p class="mt-1 text-xs text-gray-500">Kode akan digenerate otomatis</p>
            </div>

            <!-- Name & Unit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $ingredient->name ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Satuan *</label>
                    <select id="unit" name="unit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Pilih Satuan</option>
                        <option value="kg" {{ old('unit', $ingredient->unit ?? '') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="gr" {{ old('unit', $ingredient->unit ?? '') == 'gr' ? 'selected' : '' }}>Gram (gr)</option>
                        <option value="liter" {{ old('unit', $ingredient->unit ?? '') == 'liter' ? 'selected' : '' }}>Liter (l)</option>
                        <option value="ml" {{ old('unit', $ingredient->unit ?? '') == 'ml' ? 'selected' : '' }}>Mililiter (ml)</option>
                        <option value="pcs" {{ old('unit', $ingredient->unit ?? '') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                        <option value="pack" {{ old('unit', $ingredient->unit ?? '') == 'pack' ? 'selected' : '' }}>Pack (pack)</option>
                        <option value="buah" {{ old('unit', $ingredient->unit ?? '') == 'buah' ? 'selected' : '' }}>Buah</option>
                        <option value="ikat" {{ old('unit', $ingredient->unit ?? '') == 'ikat' ? 'selected' : '' }}>Ikat</option>
                        <option value="bungkus" {{ old('unit', $ingredient->unit ?? '') == 'bungkus' ? 'selected' : '' }}>Bungkus</option>
                        <option value="botol" {{ old('unit', $ingredient->unit ?? '') == 'botol' ? 'selected' : '' }}>Botol</option>
                    </select>
                </div>
            </div>

            <!-- Stock Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stok Saat Ini *</label>
                    <input type="number" id="stock" name="stock" step="0.01" min="0"
                           value="{{ old('stock', $ingredient->stock ?? '0') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                <div>
                    <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-1">Stok Minimum *</label>
                    <input type="number" id="min_stock" name="min_stock" step="0.01" min="0"
                           value="{{ old('min_stock', $ingredient->min_stock ?? '10') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Sistem akan memberi peringatan jika stok di bawah angka ini</p>
                </div>
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan (Rp) *</label>
                <input type="number" id="price" name="price" min="0"
                       value="{{ old('price', $ingredient->price ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                       required>
                <p class="mt-1 text-xs text-gray-500">Harga per satuan (misal: per kg, per liter, dll)</p>
            </div>

            <!-- Stock Value Preview -->
            <div class="p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Stok</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Nilai Total Stok</p>
                        <p class="text-lg font-bold text-blue-600" id="totalValuePreview">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status Stok</p>
                        <p class="text-lg font-bold" id="stockStatusPreview">-</p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('ingredients.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ isset($ingredient) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
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
    statusElement.textContent = status;
    statusElement.className = `text-lg font-bold ${statusColor}`;
}

// Update preview on input
document.getElementById('stock').addEventListener('input', calculateStockValue);
document.getElementById('price').addEventListener('input', calculateStockValue);
document.getElementById('min_stock').addEventListener('input', calculateStockValue);

// Initialize on page load
document.addEventListener('DOMContentLoaded', calculateStockValue);
</script>
@endpush
