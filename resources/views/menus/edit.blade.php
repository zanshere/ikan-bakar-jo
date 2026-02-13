<!-- resources/views/menus/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Edit Menu - Seafood Management')

@section('page-title', 'Edit Menu')
@section('page-description', 'Edit data menu "' . $menu->name . '"')

@section('breadcrumb')
    <i data-lucide="chevron-right" class="w-4 h-4"></i>
    <a href="{{ route('menus.index') }}" class="text-gray-500 hover:text-gray-700">Menu</a>
    <i data-lucide="chevron-right" class="w-4 h-4"></i>
    <span class="text-gray-700 font-medium">Edit</span>
@endsection

@section('header-buttons')
    <a href="{{ route('menus.show', $menu) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="eye" class="w-4 h-4 mr-2"></i>Lihat Detail
    </a>
    <a href="{{ route('menus.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg shadow-sm text-sm font-medium hover:bg-gray-700">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>Kembali
    </a>
@endsection

@push('styles')
    <style>
        .ingredient-row {
            transition: all 0.3s ease;
            position: relative;
        }
        .ingredient-row.removing {
            opacity: 0.5;
            background-color: #fee2e2;
            transform: translateX(10px);
        }
        .ingredient-row.border-red-500 {
            border: 2px solid #ef4444 !important;
        }
        .toastify {
            padding: 12px 20px;
            color: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4">
                <div class="flex">
                    <div class="shrink-0"><i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i></div>
                    <div class="ml-3"><p class="text-sm font-medium text-green-800">{{ session('success') }}</p></div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 p-4">
                <div class="flex">
                    <div class="shrink-0"><i data-lucide="alert-circle" class="w-5 h-5 text-red-400"></i></div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} kesalahan:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('menus.update', $menu) }}" method="POST" enctype="multipart/form-data" id="menuForm">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="p-6">
                    <!-- Basic Information -->
                    <div class="pb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-600"></i>Informasi Dasar
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Menu <span class="text-red-500">*</span></label>
                                <input type="text" name="code" value="{{ old('code', $menu->code) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Menu <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $menu->name) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" name="price" id="price" value="{{ old('price', $menu->price) }}" required min="0" step="100" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <div class="flex items-center h-11">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }} class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                                    <label for="is_active" class="ml-3 text-sm text-gray-700">Menu Aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $menu->description) }}</textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Menu</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-6">
                                <div class="shrink-0 relative group" id="imagePreviewWrapper">
                                    @if($menu->image && Storage::disk('public')->exists($menu->image))
                                        <img id="imagePreview" src="{{ Storage::url($menu->image) }}" alt="{{ $menu->name }}" class="h-40 w-40 object-cover rounded-lg border-2 border-gray-200 shadow-sm">
                                        <button type="button" onclick="removeImage()" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-700">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    @else
                                        <div id="imagePreviewContainer" class="h-40 w-40 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                                            <i data-lucide="image" class="w-12 h-12 text-gray-400 mb-2"></i>
                                            <span class="text-xs text-gray-500">Belum ada gambar</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/jpg,image/gif" class="hidden">
                                    <button type="button" onclick="document.getElementById('image').click()" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        <i data-lucide="upload" class="w-4 h-4 mr-2"></i>Pilih Gambar
                                    </button>
                                    <span id="fileName" class="ml-3 text-sm text-gray-500">
                                        @if($menu->image){{ basename($menu->image) }}@else Belum ada file dipilih @endif
                                    </span>
                                    <p class="text-xs text-gray-500 mt-2"><i data-lucide="info" class="w-3 h-3 inline mr-1"></i>Format: JPG, PNG, GIF (Maks. 2MB, 400x400px)</p>
                                </div>
                            </div>
                            <input type="hidden" name="remove_image" id="removeImageInput" value="0">
                        </div>
                    </div>

                    <!-- Ingredients Section -->
                    <div class="pb-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i data-lucide="package" class="w-5 h-5 mr-2 text-green-600"></i>
                                Bahan-bahan
                                <span id="ingredientCount" class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">{{ count($currentIngredients) }}</span>
                            </h3>
                            <button type="button" onclick="addIngredientRow()" class="inline-flex items-center px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>Tambah Bahan
                            </button>
                        </div>

                        <div id="ingredientsContainer" class="space-y-4">
                            @php $ingredientCounter = 0; @endphp
                            @foreach($currentIngredients as $ingredientId => $quantity)
                                @php
                                    $ingredient = $ingredients->firstWhere('id', $ingredientId);
                                    if (!$ingredient) continue;
                                @endphp
                                <div class="ingredient-row bg-gray-50 p-4 rounded-lg border border-gray-200" data-index="{{ $ingredientCounter }}">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                        <div class="md:col-span-5">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bahan <span class="text-red-500">*</span></label>
                                            <select name="ingredients[{{ $ingredientCounter }}][id]" onchange="updateIngredientRow(this)" class="ingredient-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                                <option value="">Pilih Bahan</option>
                                                @foreach($ingredients as $ing)
                                                    <option value="{{ $ing->id }}" {{ $ingredientId == $ing->id ? 'selected' : '' }} data-unit="{{ $ing->unit }}" data-price="{{ $ing->price }}">{{ $ing->name }} (Stok: {{ $ing->formatted_stock }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah <span class="text-red-500">*</span></label>
                                            <div class="flex">
                                                <input type="number" name="ingredients[{{ $ingredientCounter }}][quantity]" value="{{ $quantity }}" step="0.01" min="0.01" required oninput="updateIngredientRow(this)" class="quantity-input flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <span class="unit-display inline-flex items-center px-4 border border-l-0 border-gray-300 bg-gray-100 text-gray-600 rounded-r-lg">{{ $ingredient->unit }}</span>
                                            </div>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                                            <div class="p-3 bg-white border border-gray-300 rounded-lg">
                                                <span class="subtotal-display text-sm font-medium text-gray-900">Rp {{ number_format($quantity * $ingredient->price, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        <div class="md:col-span-1">
                                            <button type="button" onclick="removeIngredientRow(this)" class="remove-ingredient-btn w-full h-10 inline-flex items-center justify-center px-3 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @php $ingredientCounter++; @endphp
                            @endforeach
                        </div>

                        <!-- Empty State -->
                        <div id="emptyIngredientsState" class="text-center py-8 {{ $ingredientCounter > 0 ? 'hidden' : '' }}">
                            <div class="bg-gray-50 rounded-lg p-8 border-2 border-dashed border-gray-300">
                                <i data-lucide="package" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                                <p class="text-gray-600 font-medium">Belum ada bahan yang ditambahkan</p>
                                <p class="text-sm text-gray-500 mt-1">Klik tombol "Tambah Bahan" untuk mulai menambahkan</p>
                            </div>
                        </div>

                        <!-- Total Cost Summary -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-600 mb-2">Total Biaya Bahan</label>
                                    <span id="totalCostDisplay" class="text-2xl font-bold text-gray-900">Rp 0</span>
                                </div>
                                <div class="bg-blue-50 p-5 rounded-xl border border-blue-200">
                                    <label class="block text-sm font-medium text-blue-700 mb-2">Harga Menu</label>
                                    <span id="priceDisplay" class="text-2xl font-bold text-blue-900">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                </div>
                                <div id="profitContainer" class="bg-green-50 p-5 rounded-xl border border-green-200">
                                    <label class="block text-sm font-medium text-green-700 mb-2">Estimasi Laba</label>
                                    <span id="profitDisplay" class="text-2xl font-bold text-green-900">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('menus.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                            <i data-lucide="x" class="w-4 h-4 mr-2 inline"></i>Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i data-lucide="save" class="w-4 h-4 mr-2 inline"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        // ================ GLOBAL VARIABLES ================
        let ingredientCounter = {{ $ingredientCounter }};

        // ================ INITIALIZATION ================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== EDIT MENU INITIALIZED ===');

            // Setup price input listener
            const priceInput = document.getElementById('price');
            if (priceInput) {
                priceInput.addEventListener('input', calculateTotal);
            }

            // Setup file input listener
            const fileInput = document.getElementById('image');
            if (fileInput) {
                fileInput.addEventListener('change', previewImage);
            }

            // Initial calculations
            calculateTotal();
            updateIngredientCount();
            checkEmptyState();

            // Refresh Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            console.log('Ready. Ingredient rows:', document.querySelectorAll('.ingredient-row').length);
        });

        // ================ IMAGE FUNCTIONS ================
        function previewImage(event) {
            console.log('previewImage called');
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.match('image.*')) {
                showToast('error', 'File harus berupa gambar');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showToast('error', 'Ukuran file maksimal 2MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.getElementById('imagePreviewWrapper');
                const existingImg = document.getElementById('imagePreview');
                const existingContainer = document.getElementById('imagePreviewContainer');
                const fileNameSpan = document.getElementById('fileName');

                if (fileNameSpan) fileNameSpan.textContent = file.name;

                if (existingImg) {
                    existingImg.src = e.target.result;
                } else if (existingContainer) {
                    const img = document.createElement('img');
                    img.id = 'imagePreview';
                    img.src = e.target.result;
                    img.alt = 'Preview';
                    img.className = 'h-40 w-40 object-cover rounded-lg border-2 border-blue-500 shadow-sm';
                    existingContainer.replaceWith(img);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.setAttribute('onclick', 'removeImage()');
                    removeBtn.className = 'absolute top-2 right-2 bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-700';
                    removeBtn.innerHTML = '<i data-lucide="x" class="w-4 h-4"></i>';
                    wrapper.appendChild(removeBtn);
                }

                document.getElementById('removeImageInput').value = '0';

                if (typeof lucide !== 'undefined') lucide.createIcons();
                showToast('success', 'Gambar berhasil dipilih');
            };

            reader.readAsDataURL(file);
        }

        function removeImage() {
            console.log('removeImage called');
            if (!confirm('Hapus gambar ini?')) return;

            const wrapper = document.getElementById('imagePreviewWrapper');
            const img = document.getElementById('imagePreview');
            const removeBtn = document.querySelector('#imagePreviewWrapper button');
            const fileNameSpan = document.getElementById('fileName');
            const fileInput = document.getElementById('image');

            if (img) img.remove();
            if (removeBtn) removeBtn.remove();

            const container = document.createElement('div');
            container.id = 'imagePreviewContainer';
            container.className = 'h-40 w-40 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center';
            container.innerHTML = '<i data-lucide="image" class="w-12 h-12 text-gray-400 mb-2"></i><span class="text-xs text-gray-500">Belum ada gambar</span>';
            wrapper.appendChild(container);

            if (fileNameSpan) fileNameSpan.textContent = 'Belum ada file dipilih';
            if (fileInput) fileInput.value = '';

            document.getElementById('removeImageInput').value = '1';

            if (typeof lucide !== 'undefined') lucide.createIcons();
            showToast('success', 'Gambar dihapus');
        }

        // ================ INGREDIENT FUNCTIONS ================
        function addIngredientRow() {
            console.log('addIngredientRow called. Counter:', ingredientCounter);

            const container = document.getElementById('ingredientsContainer');
            if (!container) return;

            const index = ingredientCounter;
            const row = document.createElement('div');
            row.className = 'ingredient-row bg-gray-50 p-4 rounded-lg border border-gray-200';
            row.setAttribute('data-index', index);

            let options = '<option value="">Pilih Bahan</option>';
            @foreach($ingredients as $ingredient)
                options += `<option value="{{ $ingredient->id }}" data-unit="{{ $ingredient->unit }}" data-price="{{ $ingredient->price }}">{{ $ingredient->name }} (Stok: {{ $ingredient->formatted_stock }})</option>`;
            @endforeach

            row.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bahan <span class="text-red-500">*</span></label>
                        <select name="ingredients[${index}][id]" onchange="updateIngredientRow(this)" class="ingredient-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            ${options}
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="number" name="ingredients[${index}][quantity]" value="1" step="0.01" min="0.01" required oninput="updateIngredientRow(this)" class="quantity-input flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <span class="unit-display inline-flex items-center px-4 border border-l-0 border-gray-300 bg-gray-100 text-gray-600 rounded-r-lg">unit</span>
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                        <div class="p-3 bg-white border border-gray-300 rounded-lg">
                            <span class="subtotal-display text-sm font-medium text-gray-900">Rp 0</span>
                        </div>
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" onclick="removeIngredientRow(this)" class="remove-ingredient-btn w-full h-10 inline-flex items-center justify-center px-3 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(row);

            ingredientCounter++;

            if (typeof lucide !== 'undefined') lucide.createIcons();

            calculateTotal();
            updateIngredientCount();
            checkEmptyState();

            showToast('success', 'Bahan ditambahkan');
            console.log('Row added. New counter:', ingredientCounter);
        }

        function removeIngredientRow(button) {
            console.log('removeIngredientRow called');

            const row = button.closest('.ingredient-row');
            if (!row) return;

            if (!confirm('Hapus bahan ini?')) return;

            row.classList.add('removing');

            setTimeout(function() {
                row.remove();
                reindexRows();
                calculateTotal();
                updateIngredientCount();
                checkEmptyState();
                showToast('success', 'Bahan dihapus');
                console.log('Row removed. Remaining:', document.querySelectorAll('.ingredient-row').length);
            }, 300);
        }

        function reindexRows() {
            const rows = document.querySelectorAll('.ingredient-row');
            let newIndex = 0;

            rows.forEach(function(row) {
                row.setAttribute('data-index', newIndex);

                const select = row.querySelector('.ingredient-select');
                if (select) select.setAttribute('name', `ingredients[${newIndex}][id]`);

                const quantity = row.querySelector('.quantity-input');
                if (quantity) quantity.setAttribute('name', `ingredients[${newIndex}][quantity]`);

                newIndex++;
            });

            ingredientCounter = newIndex;
            console.log('Rows reindexed. New counter:', ingredientCounter);
        }

        function updateIngredientRow(element) {
            const row = element.closest('.ingredient-row');
            if (!row) return;

            const select = row.querySelector('.ingredient-select');
            const quantity = row.querySelector('.quantity-input');
            const unitDisplay = row.querySelector('.unit-display');
            const subtotalDisplay = row.querySelector('.subtotal-display');

            if (!select || !quantity || !unitDisplay || !subtotalDisplay) return;

            const selectedOption = select.options[select.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                unitDisplay.textContent = 'unit';
                subtotalDisplay.textContent = 'Rp 0';
                return;
            }

            const price = parseFloat(selectedOption.dataset.price) || 0;
            const unit = selectedOption.dataset.unit || 'unit';
            const qty = parseFloat(quantity.value) || 0;

            unitDisplay.textContent = unit;
            subtotalDisplay.textContent = 'Rp ' + Math.round(price * qty).toLocaleString('id-ID');

            calculateTotal();
        }

        // ================ CALCULATION FUNCTIONS ================
        function calculateTotal() {
            let totalCost = 0;
            const rows = document.querySelectorAll('.ingredient-row');

            rows.forEach(function(row) {
                const select = row.querySelector('.ingredient-select');
                const quantity = row.querySelector('.quantity-input');

                if (select && quantity && select.value) {
                    const selectedOption = select.options[select.selectedIndex];
                    if (selectedOption) {
                        const price = parseFloat(selectedOption.dataset.price) || 0;
                        const qty = parseFloat(quantity.value) || 0;
                        totalCost += price * qty;
                    }
                }
            });

            const price = parseFloat(document.getElementById('price').value) || 0;
            const profit = price - totalCost;

            document.getElementById('totalCostDisplay').textContent = 'Rp ' + Math.round(totalCost).toLocaleString('id-ID');
            document.getElementById('priceDisplay').textContent = 'Rp ' + Math.round(price).toLocaleString('id-ID');

            const profitDisplay = document.getElementById('profitDisplay');
            profitDisplay.textContent = 'Rp ' + Math.round(profit).toLocaleString('id-ID');

            const profitContainer = document.getElementById('profitContainer');
            if (profit < 0) {
                profitContainer.className = 'bg-red-50 p-5 rounded-xl border border-red-200';
                profitDisplay.className = 'text-2xl font-bold text-red-900';
            } else {
                profitContainer.className = 'bg-green-50 p-5 rounded-xl border border-green-200';
                profitDisplay.className = 'text-2xl font-bold text-green-900';
            }
        }

        function updateIngredientCount() {
            const count = document.querySelectorAll('.ingredient-row').length;
            const countElement = document.getElementById('ingredientCount');
            if (countElement) countElement.textContent = count;
        }

        function checkEmptyState() {
            const rows = document.querySelectorAll('.ingredient-row');
            const emptyState = document.getElementById('emptyIngredientsState');
            if (emptyState) {
                emptyState.classList.toggle('hidden', rows.length > 0);
            }
        }

        // ================ TOAST NOTIFICATION ================
        function showToast(type, message) {
            if (typeof Toastify === 'undefined') {
                alert(message);
                return;
            }

            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: type === 'success' ? '#10B981' : '#EF4444',
                stopOnFocus: true
            }).showToast();
        }

        // ================ FORM VALIDATION ================
        document.getElementById('menuForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.ingredient-row');

            if (rows.length === 0) {
                e.preventDefault();
                showToast('error', 'Minimal satu bahan harus ditambahkan');
                return false;
            }

            let valid = true;
            rows.forEach(function(row) {
                const select = row.querySelector('.ingredient-select');
                const quantity = row.querySelector('.quantity-input');

                row.classList.remove('border-red-500', 'border-2');

                if (!select || !select.value) {
                    valid = false;
                    row.classList.add('border-red-500', 'border-2');
                }

                if (!quantity || !quantity.value || parseFloat(quantity.value) <= 0) {
                    valid = false;
                    row.classList.add('border-red-500', 'border-2');
                }
            });

            if (!valid) {
                e.preventDefault();
                showToast('error', 'Semua bahan harus diisi dengan valid');
                return false;
            }

            return true;
        });
    </script>
@endpush
