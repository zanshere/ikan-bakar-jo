<!-- resources/views/restocks/create.blade.php -->
@extends('layouts.app')

@section('page-title', 'Restock Baru')
@section('page-description', 'Buat transaksi restock bahan baku')

@section('breadcrumb')
    <span>/</span>
    <a href="{{ route('restocks.index') }}" class="text-gray-500 hover:text-gray-700">Restock</a>
    <span>/</span>
    <span class="text-gray-700">Baru</span>
@endsection

@section('header-buttons')
    <a href="{{ route('restocks.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Ingredient Selection -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Pilih Bahan Baku</h3>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <input type="text" id="ingredientSearch"
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64"
                                placeholder="Cari bahan baku...">
                        </div>
                    </div>

                    <!-- Low Stock Warning -->
                    <div id="lowStockWarning" class="mb-6 hidden">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mr-2"></i>
                                <span class="font-medium text-amber-800">Beberapa bahan memiliki stok rendah:</span>
                            </div>
                            <div id="lowStockList" class="mt-2 grid grid-cols-2 gap-2"></div>
                        </div>
                    </div>

                    <!-- Ingredient Grid -->
                    <div id="ingredientGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($ingredients as $ingredient)
                            <div class="border rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer ingredient-item"
                                data-id="{{ $ingredient->id }}" data-name="{{ $ingredient->name }}"
                                data-price="{{ $ingredient->price }}" data-unit="{{ $ingredient->unit }}"
                                data-stock="{{ $ingredient->stock }}">
                                <div class="h-12 w-12 mb-3 bg-blue-100 rounded-lg flex items-center justify-center mx-auto">
                                    <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
                                </div>

                                <div class="text-center">
                                    <h4 class="font-medium text-gray-800 mb-1">{{ $ingredient->name }}</h4>
                                    <p class="text-sm text-gray-600 mb-2">{{ $ingredient->formatted_price }}</p>
                                    <div class="flex items-center justify-center text-xs">
                                        <span
                                            class="px-2 py-1 {{ $ingredient->stock_status == 'low' ? 'bg-amber-100 text-amber-800' : ($ingredient->stock_status == 'empty' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }} rounded-full mr-2">
                                            {{ $ingredient->formatted_stock }}
                                        </span>
                                        <span class="text-gray-500">min: {{ $ingredient->formatted_min_stock }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($ingredients->isEmpty())
                        <div class="text-center py-12">
                            <i data-lucide="package-x" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                            <p class="text-gray-600">Belum ada bahan baku yang tersedia</p>
                            <a href="{{ route('ingredients.create') }}"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                Tambah Bahan Baku
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column - Restock Form -->
            <div>
                <form id="restockForm" method="POST" action="{{ route('restocks.store') }}" class="space-y-6">
                    @csrf

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Restock</h3>

                        <!-- Restock Items -->
                        <div id="restockItems" class="space-y-3 mb-6">
                            <!-- Items will be added here dynamically -->
                            <div class="text-center py-8 text-gray-500" id="emptyRestockMessage">
                                <i data-lucide="package" class="w-12 h-12 mx-auto mb-3"></i>
                                <p>Belum ada bahan dipilih</p>
                                <p class="text-sm mt-1">Pilih bahan untuk menambahkannya ke daftar restock</p>
                            </div>
                        </div>

                        <!-- Restock Summary -->
                        <div class="space-y-3 border-t pt-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium" id="subtotal">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span id="total" class="text-red-600">Rp 0</span>
                            </div>
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <input type="hidden" id="itemsInput" name="items">
                    </div>

                    <!-- Transaction Details -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Transaksi</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                                <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                    required>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan
                                    (Opsional)</label>
                                <textarea id="notes" name="notes" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Catatan untuk transaksi restock ini..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Info (Optional) -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Supplier (Opsional)</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="supplier_name" class="block text-sm font-medium text-gray-700 mb-1">Nama
                                    Supplier</label>
                                <input type="text" id="supplier_name" name="supplier_name"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="supplier_contact" class="block text-sm font-medium text-gray-700 mb-1">Kontak
                                    Supplier</label>
                                <input type="text" id="supplier_contact" name="supplier_contact"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Items: <span id="itemCount">0</span></p>
                                <p class="text-xl font-bold text-red-600" id="finalTotal">Rp 0</p>
                            </div>
                            <button type="submit" id="submitButton"
                                class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                                <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i>
                                Simpan Restock
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Item Quantity Modal -->
    <div id="quantityModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2" id="modalIngredientName"></h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Harga satuan: <span id="modalIngredientPrice"></span><br>
                        Stok saat ini: <span id="modalCurrentStock"></span>
                    </p>

                    <div class="mb-4">
                        <label for="restockQuantity"
                            class="block text-sm font-medium text-gray-700 mb-1">Kuantitas</label>
                        <input type="number" id="restockQuantity" step="0.01" min="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="0">
                        <p class="mt-1 text-xs text-gray-500" id="modalUnit"></p>
                    </div>

                    <div class="mb-4">
                        <label for="restockPrice" class="block text-sm font-medium text-gray-700 mb-1">Harga
                            (Opsional)</label>
                        <input type="number" id="restockPrice" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Kosongkan untuk menggunakan harga default">
                        <p class="mt-1 text-xs text-gray-500">Biarkan kosong untuk menggunakan harga saat ini</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideQuantityModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="button" onclick="addItemToRestock()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Tambah ke Daftar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let restockCart = [];
        let currentIngredient = null;

        // Load low stock warning on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkLowStockIngredients();
            initializeEventListeners();
        });

        function initializeEventListeners() {
            // Ingredient search functionality
            document.getElementById('ingredientSearch').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const ingredientItems = document.querySelectorAll('.ingredient-item');

                ingredientItems.forEach(item => {
                    const ingredientName = item.querySelector('h4').textContent.toLowerCase();
                    if (ingredientName.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // Click event for ingredient items
            document.querySelectorAll('.ingredient-item').forEach(item => {
                item.addEventListener('click', function() {
                    const ingredientId = this.getAttribute('data-id');
                    const ingredientName = this.getAttribute('data-name');
                    const ingredientPrice = parseFloat(this.getAttribute('data-price'));
                    const ingredientUnit = this.getAttribute('data-unit');
                    const currentStock = parseFloat(this.getAttribute('data-stock'));

                    addToRestock(ingredientId, ingredientName, ingredientPrice, ingredientUnit,
                        currentStock);
                });
            });

            // Close modal on outside click
            document.getElementById('quantityModal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    hideQuantityModal();
                }
            });
        }

        function checkLowStockIngredients() {
            const lowStockWarning = document.getElementById('lowStockWarning');
            const lowStockList = document.getElementById('lowStockList');

            // Check from ingredient items
            const lowStockIngredients = [];
            document.querySelectorAll('.ingredient-item').forEach(item => {
                const statusSpan = item.querySelector('.bg-amber-100, .bg-red-100');
                if (statusSpan && (statusSpan.classList.contains('bg-amber-100') || statusSpan.classList.contains(
                        'bg-red-100'))) {
                    const name = item.querySelector('h4').textContent;
                    const stock = statusSpan.textContent;
                    lowStockIngredients.push({
                        name,
                        stock
                    });
                }
            });

            if (lowStockIngredients.length > 0) {
                lowStockWarning.classList.remove('hidden');
                lowStockList.innerHTML = '';

                lowStockIngredients.slice(0, 4).forEach(ingredient => {
                    lowStockList.innerHTML += `
                <div class="text-sm text-amber-700">
                    • ${ingredient.name} (${ingredient.stock})
                </div>
            `;
                });
            }
        }

        function addToRestock(ingredientId, ingredientName, price, unit, currentStock) {
            currentIngredient = {
                id: ingredientId,
                name: ingredientName,
                price: price,
                unit: unit,
                currentStock: currentStock,
                quantity: 0,
                inputPrice: null
            };

            document.getElementById('modalIngredientName').textContent = ingredientName;
            document.getElementById('modalIngredientPrice').textContent = formatCurrency(price);
            document.getElementById('modalCurrentStock').textContent = formatNumber(currentStock) + ' ' + unit;
            document.getElementById('modalUnit').textContent = 'Satuan: ' + unit;
            document.getElementById('restockQuantity').value = '';
            document.getElementById('restockPrice').value = '';
            document.getElementById('restockQuantity').focus();

            document.getElementById('quantityModal').classList.remove('hidden');
        }

        function hideQuantityModal() {
            document.getElementById('quantityModal').classList.add('hidden');
            currentIngredient = null;
        }

        function addItemToRestock() {
            if (!currentIngredient) return;

            const quantityInput = document.getElementById('restockQuantity');
            const priceInput = document.getElementById('restockPrice');

            const quantity = parseFloat(quantityInput.value);
            const inputPrice = priceInput.value.trim();

            if (!quantity || quantity <= 0) {
                alert('Masukkan kuantitas yang valid');
                quantityInput.focus();
                return;
            }

            // Check if item already in cart
            const existingIndex = restockCart.findIndex(item => item.id === currentIngredient.id);

            if (existingIndex > -1) {
                // Update existing item
                restockCart[existingIndex].quantity = quantity;
                restockCart[existingIndex].inputPrice = inputPrice ? parseFloat(inputPrice) : null;
            } else {
                // Add new item
                restockCart.push({
                    ...currentIngredient,
                    quantity: quantity,
                    inputPrice: inputPrice ? parseFloat(inputPrice) : null
                });
            }

            hideQuantityModal();
            updateRestockDisplay();
        }

        function removeFromRestock(index) {
            if (confirm('Hapus item dari daftar restock?')) {
                restockCart.splice(index, 1);
                updateRestockDisplay();
            }
        }

        function updateQuantity(index, newQuantity) {
            newQuantity = parseFloat(newQuantity);
            if (isNaN(newQuantity) || newQuantity <= 0) {
                removeFromRestock(index);
            } else {
                // Ensure the quantity has at most 2 decimal places
                newQuantity = Math.round(newQuantity * 100) / 100;
                restockCart[index].quantity = newQuantity;
                updateRestockDisplay();
            }
        }

        function updateRestockDisplay() {
            const restockItemsContainer = document.getElementById('restockItems');
            const emptyRestockMessage = document.getElementById('emptyRestockMessage');
            const submitButton = document.getElementById('submitButton');

            if (restockCart.length === 0) {
                restockItemsContainer.innerHTML = `
            <div class="text-center py-8 text-gray-500" id="emptyRestockMessage">
                <i data-lucide="package" class="w-12 h-12 mx-auto mb-3"></i>
                <p>Belum ada bahan dipilih</p>
                <p class="text-sm mt-1">Pilih bahan untuk menambahkannya ke daftar restock</p>
            </div>
        `;
                submitButton.disabled = true;
                updateTotals(0);
            } else {
                let cartHTML = '';
                let subtotal = 0;

                restockCart.forEach((item, index) => {
                    const price = item.inputPrice || item.price;
                    const itemTotal = price * item.quantity;
                    subtotal += itemTotal;

                    cartHTML += `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800">${item.name}</div>
                        <div class="text-sm text-gray-600">
                            @ ${formatCurrency(price)} / ${item.unit}
                            ${item.inputPrice ? ' (harga custom)' : ''}
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <button type="button" onclick="decreaseQuantity(${index})"
                                    class="w-6 h-6 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100">
                                <i data-lucide="minus" class="w-3 h-3"></i>
                            </button>
                            <input type="number" value="${formatNumberForInput(item.quantity)}" step="0.01" min="0.01"
                                   onchange="updateQuantity(${index}, this.value)"
                                   class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                            <button type="button" onclick="increaseQuantity(${index})"
                                    class="w-6 h-6 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100">
                                <i data-lucide="plus" class="w-3 h-3"></i>
                            </button>
                        </div>
                        <div class="w-24 text-right font-medium">
                            ${formatCurrency(itemTotal)}
                        </div>
                        <button type="button" onclick="removeFromRestock(${index})"
                                class="text-red-600 hover:text-red-800 p-1">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;
                });

                restockItemsContainer.innerHTML = cartHTML;
                submitButton.disabled = false;
                updateTotals(subtotal);
            }

            // Update hidden items input for form submission
            document.getElementById('itemsInput').value = JSON.stringify(
                restockCart.map(item => ({
                    ingredient_id: item.id,
                    quantity: item.quantity,
                    price: item.inputPrice || item.price
                }))
            );
        }

        function increaseQuantity(index) {
            if (index < 0 || index >= restockCart.length) return;
            const currentQty = restockCart[index].quantity;
            const newQty = currentQty + 1;
            updateQuantity(index, newQty);
        }

        function decreaseQuantity(index) {
            if (index < 0 || index >= restockCart.length) return;
            const currentQty = restockCart[index].quantity;
            const newQty = currentQty - 1;
            updateQuantity(index, newQty);
        }

        function updateTotals(subtotal) {
            // Update totals with proper number handling
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('total').textContent = formatCurrency(subtotal);
            document.getElementById('finalTotal').textContent = formatCurrency(subtotal);
            document.getElementById('itemCount').textContent = restockCart.length;
        }

        function formatCurrency(amount) {
            if (isNaN(amount)) {
                return 'Rp 0';
            }
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
        }

        function formatNumber(num) {
            if (isNaN(num)) {
                return '0';
            }
            // Format number with 2 decimal places
            return parseFloat(num).toFixed(2);
        }

        function formatNumberForInput(num) {
            if (isNaN(num)) {
                return '0';
            }
            // For input fields, we need to remove trailing zeros
            const formatted = parseFloat(num).toFixed(2);
            // Remove unnecessary trailing zeros
            return parseFloat(formatted).toString();
        }

        // Form submission handler
        document.getElementById('restockForm').addEventListener('submit', function(e) {
            if (restockCart.length === 0) {
                e.preventDefault();
                alert('Tambahkan minimal 1 item untuk restock');
                return false;
            }

            // Validate all items
            for (const item of restockCart) {
                if (item.quantity <= 0 || isNaN(item.quantity)) {
                    e.preventDefault();
                    alert(`Kuantitas untuk ${item.name} tidak valid`);
                    return false;
                }

                const price = item.inputPrice || item.price;
                if (price < 0 || isNaN(price)) {
                    e.preventDefault();
                    alert(`Harga untuk ${item.name} tidak valid`);
                    return false;
                }
            }

            // Show loading state
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.innerHTML =
                '<i data-lucide="loader" class="w-5 h-5 inline mr-2 animate-spin"></i> Menyimpan...';

            return true;
        });
    </script>
@endpush
1
