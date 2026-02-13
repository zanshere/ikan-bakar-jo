<!-- resources/views/menus/create.blade.php & resources/views/menus/edit.blade.php -->
@extends('layouts.app')

@section('page-title', isset($menu) ? 'Edit Menu' : 'Tambah Menu Baru')
@section('page-description', isset($menu) ? 'Perbarui informasi menu' : 'Tambahkan menu makanan baru')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('menus.index') }}" class="text-gray-500 hover:text-gray-700">Menu</a>
<span>/</span>
<span class="text-gray-700">{{ isset($menu) ? 'Edit' : 'Tambah' }}</span>
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
        <form method="POST" action="{{ isset($menu) ? route('menus.update', $menu) : route('menus.store') }}"
              enctype="multipart/form-data" class="space-y-6 show-loading">
            @csrf
            @if(isset($menu))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Menu *</label>
                        <input type="text" id="code" name="code"
                               value="{{ old('code', $menu->code ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                               readonly>
                        <p class="mt-1 text-xs text-gray-500">Kode akan digenerate otomatis</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Menu *</label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name', $menu->name ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
                        <input type="number" id="price" name="price" min="0" step="100"
                               value="{{ old('price', $menu->price ?? '') }}"
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
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar Menu</label>
                        <div class="mt-1 flex items-center space-x-4">
                            @if(isset($menu) && $menu->image)
                            <div class="relative">
                                <img id="imagePreview" src="{{ Storage::url($menu->image) }}"
                                     alt="{{ $menu->name }}"
                                     class="h-32 w-32 object-cover rounded-lg border">
                                <button type="button" onclick="removeImage()"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </div>
                            @else
                            <div class="h-32 w-32 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                <i data-lucide="image" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            @endif
                            <div class="flex-1">
                                <input type="file" id="image" name="image"
                                       accept="image/*"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('image') border-red-500 @enderror"
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
                                   {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}
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
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $menu->description ?? '') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ingredients -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">Bahan Baku *</label>
                    <button type="button" onclick="addIngredientRow()"
                            class="inline-flex items-center px-3 py-1.5 text-sm bg-green-100 text-green-800 rounded-lg hover:bg-green-200">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                        Tambah Bahan
                    </button>
                </div>

                <div id="ingredients-container" class="space-y-3">
                    <!-- Existing ingredients will be loaded here -->
                    @if(isset($menu) && $menu->ingredients->count() > 0)
                        @foreach($menu->ingredients as $index => $ingredient)
                        <div class="ingredient-row grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-lg">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Bahan</label>
                                <select name="ingredients[{{ $index }}][id]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 ingredient-select"
                                        onchange="updateIngredientPrice(this, {{ $index }})" required>
                                    <option value="">Pilih Bahan</option>
                                    @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}"
                                            data-price="{{ $ing->price }}"
                                            data-unit="{{ $ing->unit }}"
                                            data-stock="{{ $ing->stock }}"
                                            {{ $ing->id == $ingredient->id ? 'selected' : '' }}>
                                        {{ $ing->name }} ({{ $ing->code }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Kuantitas</label>
                                <div class="relative">
                                    <input type="number" name="ingredients[{{ $index }}][quantity]"
                                           value="{{ old('ingredients.' . $index . '.quantity', $ingredient->pivot->quantity) }}"
                                           step="0.01" min="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                           onchange="calculateIngredientCost({{ $index }})" required>
                                    <span class="absolute right-3 top-2 text-sm text-gray-500 unit-display"
                                          id="unit-{{ $index }}">
                                        {{ $ingredient->unit }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-end space-x-2">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Biaya</label>
                                    <input type="text" id="cost-{{ $index }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                                           value="Rp {{ number_format($ingredient->price * $ingredient->pivot->quantity, 0, ',', '.') }}"
                                           readonly>
                                </div>
                                <button type="button" onclick="removeIngredientRow(this)"
                                        class="px-3 py-2 text-red-600 hover:text-red-800">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
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
                    @endif
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
                            <p class="text-sm font-medium text-gray-700">Profit:</p>
                            <p class="text-lg font-bold text-green-600" id="profit-display">Rp 0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('menus.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ isset($menu) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let ingredientCount = {{ isset($menu) ? $menu->ingredients->count() : 1 }};
let selectedIngredients = new Set();

function addIngredientRow() {
    ingredientCount++;
    const template = `
        <div class="ingredient-row grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-lg">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Bahan</label>
                <select name="ingredients[${ingredientCount - 1}][id]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 ingredient-select"
                        onchange="updateIngredientPrice(this, ${ingredientCount - 1})" required>
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
                    <input type="number" name="ingredients[${ingredientCount - 1}][quantity]"
                           step="0.01" min="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           onchange="calculateIngredientCost(${ingredientCount - 1})" required>
                    <span class="absolute right-3 top-2 text-sm text-gray-500 unit-display"
                          id="unit-${ingredientCount - 1}">unit</span>
                </div>
            </div>
            <div class="flex items-end space-x-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Biaya</label>
                    <input type="text" id="cost-${ingredientCount - 1}"
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

    document.getElementById('ingredients-container').insertAdjacentHTML('beforeend', template);
    updateTotalCost();
}

function removeIngredientRow(button) {
    const row = button.closest('.ingredient-row');
    const select = row.querySelector('.ingredient-select');
    if (select.value) {
        selectedIngredients.delete(select.value);
    }
    row.remove();
    updateTotalCost();
}

function updateIngredientPrice(select, index) {
    const selectedOption = select.options[select.selectedIndex];
    const unit = selectedOption.dataset.unit || '';
    const price = parseFloat(selectedOption.dataset.price) || 0;

    // Update unit display
    document.getElementById(`unit-${index}`).textContent = unit;

    // Check if ingredient already selected
    if (select.value) {
        if (selectedIngredients.has(select.value)) {
            alert('Bahan ini sudah dipilih!');
            select.value = '';
            document.getElementById(`unit-${index}`).textContent = 'unit';
            return;
        }
        selectedIngredients.add(select.value);
    } else {
        // Remove from selected if empty
        selectedIngredients.delete(select.value);
    }

    // Calculate cost
    calculateIngredientCost(index);
}

function calculateIngredientCost(index) {
    const select = document.querySelector(`select[name="ingredients[${index}][id]"]`);
    const quantityInput = document.querySelector(`input[name="ingredients[${index}][quantity]"]`);
    const costInput = document.getElementById(`cost-${index}`);

    if (!select.value || !quantityInput.value) {
        costInput.value = 'Rp 0';
        updateTotalCost();
        return;
    }

    const selectedOption = select.options[select.selectedIndex];
    const price = parseFloat(selectedOption.dataset.price) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    const stock = parseFloat(selectedOption.dataset.stock) || 0;

    if (quantity > stock) {
        alert(`Stok tidak mencukupi! Stok tersedia: ${stock}`);
        quantityInput.value = Math.min(quantity, stock);
        quantityInput.focus();
    }

    const cost = price * quantity;
    costInput.value = 'Rp ' + cost.toLocaleString('id-ID');

    updateTotalCost();
}

function updateTotalCost() {
    let totalCost = 0;

    // Calculate total from all rows
    for (let i = 0; i < ingredientCount; i++) {
        const costInput = document.getElementById(`cost-${i}`);
        if (costInput) {
            const costText = costInput.value.replace('Rp ', '').replace(/\./g, '');
            const cost = parseFloat(costText) || 0;
            totalCost += cost;
        }
    }

    // Update total cost display
    document.getElementById('total-cost').textContent = 'Rp ' + totalCost.toLocaleString('id-ID');

    // Get menu price
    const priceInput = document.getElementById('price');
    const menuPrice = parseFloat(priceInput.value) || 0;
    document.getElementById('menu-price-display').textContent = 'Rp ' + menuPrice.toLocaleString('id-ID');

    // Calculate and update profit
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

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById('imagePreview');
            if (!preview) {
                const imageContainer = input.closest('.flex');
                const imagePlaceholder = imageContainer.querySelector('.h-32.w-32.bg-gray-100');
                if (imagePlaceholder) {
                    imagePlaceholder.remove();
                }
                preview = document.createElement('img');
                preview.id = 'imagePreview';
                preview.className = 'h-32 w-32 object-cover rounded-lg border';
                imageContainer.querySelector('.flex-1').insertAdjacentHTML('beforebegin', preview.outerHTML);
                preview = document.getElementById('imagePreview');
            }
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const preview = document.getElementById('imagePreview');
    if (preview) {
        preview.remove();
    }
    document.getElementById('image').value = '';

    // Re-add placeholder
    const imageContainer = document.querySelector('.flex.items-center.space-x-4');
    const placeholder = document.createElement('div');
    placeholder.className = 'h-32 w-32 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center';
    placeholder.innerHTML = '<i data-lucide="image" class="w-8 h-8 text-gray-400"></i>';
    imageContainer.querySelector('.flex-1').insertAdjacentHTML('beforebegin', placeholder.outerHTML);

    // Add hidden input to indicate image removal
    const form = document.querySelector('form');
    let removeInput = document.querySelector('input[name="remove_image"]');
    if (!removeInput) {
        removeInput = document.createElement('input');
        removeInput.type = 'hidden';
        removeInput.name = 'remove_image';
        removeInput.value = '1';
        form.appendChild(removeInput);
    }
}

// Initialize selected ingredients
document.addEventListener('DOMContentLoaded', function() {
    // Add existing ingredients to selected set
    document.querySelectorAll('.ingredient-select').forEach(select => {
        if (select.value) {
            selectedIngredients.add(select.value);
        }
    });

    // Update total cost on page load
    updateTotalCost();

    // Watch price input for changes
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', updateTotalCost);
    }

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
