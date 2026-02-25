{{-- resources/views/menus/create.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Tambah Menu Baru')
@section('page-description', 'Tambahkan menu makanan atau saus baru')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('menus.index') }}" class="text-gray-500 hover:text-gray-700">Menu</a>
<span>/</span>
<span class="text-gray-700">Tambah</span>
@endsection

@section('header-buttons')
<a href="{{ route('menus.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Kembali
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('menus.store') }}"
              enctype="multipart/form-data" class="space-y-6 show-loading">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Menu</label>
                        <input type="text" id="code" name="code"
                               value="{{ old('code', 'Otomatis') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                               readonly>
                        <p class="mt-1 text-xs text-gray-500">Kode akan digenerate otomatis</p>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Menu *</label>
                        <select id="type" name="type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                onchange="toggleTypeInfo()">
                            @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
                        <input type="number" id="price" name="price" min="0" step="100"
                               value="{{ old('price') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror"
                               required>
                        @error('price')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Image & Status -->
                <div class="space-y-4">
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                        <div class="mt-1 flex items-center space-x-4">
                            <div class="h-32 w-32 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center" id="imagePreviewContainer">
                                <i data-lucide="image" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <div class="flex-1">
                                <input type="file" id="image" name="image"
                                       accept="image/*"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       onchange="previewImage(this)">
                                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF. Maks: 2MB</p>
                                @error('image')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_active" class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Aktifkan menu</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type Info -->
            <div id="typeInfoMain" class="p-4 bg-blue-50 rounded-lg {{ old('type') === 'sauce' ? 'hidden' : '' }}">
                <div class="flex items-center">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-2"></i>
                    <div>
                        <p class="text-sm text-blue-800">
                            <strong>Menu Utama</strong> - Item ini akan ditampilkan di halaman pemesanan dan dapat dipilih oleh pelanggan.
                        </p>
                    </div>
                </div>
            </div>

            <div id="typeInfoSauce" class="p-4 bg-purple-50 rounded-lg {{ old('type') === 'sauce' ? '' : 'hidden' }}">
                <div class="flex items-center">
                    <i data-lucide="info" class="w-5 h-5 text-purple-600 mr-2"></i>
                    <div>
                        <p class="text-sm text-purple-800">
                            <strong>Saus</strong> - Item ini adalah saus yang dapat dipilih sebagai pelengkap menu utama.
                            Saus tetap memerlukan bahan baku seperti cabai, bawang, bumbu dapur, dll.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ingredients Section (untuk semua tipe menu) -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Bahan Baku <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 ml-2">(Minimal 1 bahan)</span>
                    </label>
                    <button type="button" onclick="addIngredientRow()"
                            class="inline-flex items-center px-3 py-1.5 text-sm bg-green-100 text-green-800 rounded-lg hover:bg-green-200">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                        Tambah Bahan
                    </button>
                </div>

                <div id="ingredients-container" class="space-y-3">
                    <!-- Empty row template -->
                    <div class="ingredient-row grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Bahan</label>
                            <select name="ingredients[0][id]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 ingredient-select"
                                    onchange="updateIngredientPrice(this, 0)" required>
                                <option value="">Pilih Bahan</option>
                                @foreach($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}"
                                        data-price="{{ $ingredient->price }}"
                                        data-unit="{{ $ingredient->unit }}"
                                        data-stock="{{ $ingredient->stock }}">
                                    {{ $ingredient->name }} ({{ $ingredient->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('ingredients.0.id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Kuantitas</label>
                            <div class="relative">
                                <input type="number" name="ingredients[0][quantity]"
                                       step="0.01" min="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       onchange="calculateIngredientCost(0)" required>
                                <span class="absolute right-3 top-2 text-sm text-gray-500 unit-display"
                                      id="unit-0">unit</span>
                            </div>
                            @error('ingredients.0.quantity')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-end space-x-2">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Biaya</label>
                                <input type="text" id="cost-0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                                       value="Rp 0" readonly>
                            </div>
                            <button type="button" onclick="removeIngredientRow(this)"
                                    class="px-3 py-2 text-red-600 hover:text-red-800">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Total Biaya Bahan:</p>
                            <p class="text-lg font-bold text-blue-600" id="total-cost">Rp 0</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Harga Menu:</p>
                            <p class="text-lg font-bold text-gray-800" id="menu-price-display">Rp 0</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Estimasi Profit:</p>
                            <p class="text-lg font-bold text-green-600" id="profit-display">Rp 0</p>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-2">
                    <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                    Profit dihitung dari harga menu dikurangi total biaya bahan.
                </p>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('menus.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // =========================================================================
    // Global Variables
    // =========================================================================
    let ingredientCount = 1;
    let selectedIngredients = new Set();

    // =========================================================================
    // Type Info Toggle
    // =========================================================================
    function toggleTypeInfo() {
        const type = document.getElementById('type').value;
        const infoMain = document.getElementById('typeInfoMain');
        const infoSauce = document.getElementById('typeInfoSauce');

        if (type === 'sauce') {
            infoMain.classList.add('hidden');
            infoSauce.classList.remove('hidden');
        } else {
            infoMain.classList.remove('hidden');
            infoSauce.classList.add('hidden');
        }
    }

    // =========================================================================
    // Ingredient Row Management
    // =========================================================================
    function addIngredientRow() {
        const index = ingredientCount;
        const container = document.getElementById('ingredients-container');
        const template = `
            <div class="ingredient-row grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-lg">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Bahan</label>
                    <select name="ingredients[${index}][id]"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 ingredient-select"
                            onchange="updateIngredientPrice(this, ${index})" required>
                        <option value="">Pilih Bahan</option>
                        @foreach($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}"
                                data-price="{{ $ingredient->price }}"
                                data-unit="{{ $ingredient->unit }}"
                                data-stock="{{ $ingredient->stock }}">
                            {{ $ingredient->name }} ({{ $ingredient->code }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kuantitas</label>
                    <div class="relative">
                        <input type="number" name="ingredients[${index}][quantity]"
                               step="0.01" min="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               onchange="calculateIngredientCost(${index})" required>
                        <span class="absolute right-3 top-2 text-sm text-gray-500 unit-display"
                              id="unit-${index}">unit</span>
                    </div>
                </div>
                <div class="flex items-end space-x-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Biaya</label>
                        <input type="text" id="cost-${index}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                               value="Rp 0" readonly>
                    </div>
                    <button type="button" onclick="removeIngredientRow(this)"
                            class="px-3 py-2 text-red-600 hover:text-red-800">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        ingredientCount++;
        updateTotalCost();
    }

    function removeIngredientRow(button) {
        const row = button.closest('.ingredient-row');
        const select = row.querySelector('.ingredient-select');
        if (select && select.value) {
            selectedIngredients.delete(select.value);
        }
        row.remove();
        updateTotalCost();
    }

    // =========================================================================
    // Ingredient Price Calculation
    // =========================================================================
    function updateIngredientPrice(select, index) {
        const selectedOption = select.options[select.selectedIndex];
        const unit = selectedOption.dataset.unit || '';
        const price = parseFloat(selectedOption.dataset.price) || 0;

        document.getElementById(`unit-${index}`).textContent = unit;

        if (select.value) {
            if (selectedIngredients.has(select.value)) {
                alert('Bahan ini sudah dipilih!');
                select.value = '';
                document.getElementById(`unit-${index}`).textContent = 'unit';
                return;
            }
            selectedIngredients.add(select.value);
        }

        calculateIngredientCost(index);
    }

    function calculateIngredientCost(index) {
        const select = document.querySelector(`select[name="ingredients[${index}][id]"]`);
        const quantityInput = document.querySelector(`input[name="ingredients[${index}][quantity]"]`);
        const costInput = document.getElementById(`cost-${index}`);

        if (!select || !select.value || !quantityInput || !quantityInput.value) {
            if (costInput) costInput.value = 'Rp 0';
            updateTotalCost();
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const price = parseFloat(selectedOption.dataset.price) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const stock = parseFloat(selectedOption.dataset.stock) || 0;

        if (quantity > stock) {
            alert(`Stok tidak mencukupi! Stok tersedia: ${stock} ${selectedOption.dataset.unit}`);
            quantityInput.value = stock;
            quantityInput.focus();
        }

        const cost = price * quantity;
        costInput.value = 'Rp ' + cost.toLocaleString('id-ID');

        updateTotalCost();
    }

    // =========================================================================
    // Total Cost and Profit Calculation
    // =========================================================================
    function updateTotalCost() {
        let totalCost = 0;

        for (let i = 0; i < ingredientCount; i++) {
            const costInput = document.getElementById(`cost-${i}`);
            if (costInput) {
                const costText = costInput.value.replace('Rp ', '').replace(/\./g, '');
                const cost = parseFloat(costText) || 0;
                totalCost += cost;
            }
        }

        document.getElementById('total-cost').textContent = 'Rp ' + totalCost.toLocaleString('id-ID');

        const priceInput = document.getElementById('price');
        const menuPrice = parseFloat(priceInput.value) || 0;
        document.getElementById('menu-price-display').textContent = 'Rp ' + menuPrice.toLocaleString('id-ID');

        const profit = menuPrice - totalCost;
        const profitElement = document.getElementById('profit-display');
        profitElement.textContent = 'Rp ' + profit.toLocaleString('id-ID');

        if (profit >= 0) {
            profitElement.classList.remove('text-red-600');
            profitElement.classList.add('text-green-600');
        } else {
            profitElement.classList.remove('text-green-600');
            profitElement.classList.add('text-red-600');
        }
    }

    // =========================================================================
    // Image Preview
    // =========================================================================
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('imagePreviewContainer');
                container.innerHTML = `<img src="${e.target.result}" class="h-32 w-32 object-cover rounded-lg">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // =========================================================================
    // Document Ready Event
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const priceInput = document.getElementById('price');
        if (priceInput) {
            priceInput.addEventListener('input', updateTotalCost);
        }

        // Initialize type info
        toggleTypeInfo();

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Initialize selected ingredients from existing rows
        document.querySelectorAll('.ingredient-select').forEach(select => {
            if (select.value) {
                selectedIngredients.add(select.value);
            }
        });
    });
</script>
@endpush
