<!-- resources/views/sales/create.blade.php -->
@extends('layouts.app')

@section('page-title', 'Transaksi Penjualan Baru')
@section('page-description', 'Buat transaksi penjualan baru')

@section('breadcrumb')
<span>/</span>
<a href="{{ route('sales.index') }}" class="text-gray-500 hover:text-gray-700">Penjualan</a>
<span>/</span>
<span class="text-gray-700">Baru</span>
@endsection

@section('header-buttons')
<a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Kembali
</a>
@endsection

@section('styles')
<!-- Toastify CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<style>
    .toastify {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .toastify-success {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .toastify-error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .toastify-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .toastify-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    /* Custom scrollbar for cart */
    #cartItems::-webkit-scrollbar {
        width: 6px;
    }

    #cartItems::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    #cartItems::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    #cartItems::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Menu item styling */
    .menu-item.unavailable {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .menu-item.unavailable:hover {
        border-color: #e5e7eb;
        box-shadow: none;
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-xl shadow-xl p-8">
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-gray-700 font-medium">Memproses transaksi...</p>
                    <p class="text-sm text-gray-500 mt-1">Harap tunggu sebentar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Menu Selection -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Pilih Menu</h3>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" id="menuSearch"
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64"
                               placeholder="Cari menu...">
                    </div>
                </div>

                <!-- Menu Categories -->
                <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
                    <button id="allCategory" class="px-4 py-2 bg-blue-600 text-white rounded-lg whitespace-nowrap" onclick="filterMenu('all')">
                        Semua
                    </button>
                    <button id="makananCategory" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 whitespace-nowrap" onclick="filterMenu('makanan')">
                        Makanan
                    </button>
                    <button id="minumanCategory" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 whitespace-nowrap" onclick="filterMenu('minuman')">
                        Minuman
                    </button>
                    <button id="lainnyaCategory" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 whitespace-nowrap" onclick="filterMenu('lainnya')">
                        Lainnya
                    </button>
                </div>

                <!-- Menu Grid -->
                <div id="menuGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($menus as $menu)
                    @php
                        $category = strtolower($menu->category ?? 'lainnya');
                        $isAvailable = $menu->is_active && $menu->ingredients->every(function($ingredient) {
                            return $ingredient->stock > 0;
                        });
                    @endphp
                    <div class="border rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer menu-item {{ $isAvailable ? 'available' : 'unavailable' }}"
                         data-category="{{ $category }}"
                         data-menu-id="{{ $menu->id }}"
                         onclick="{{ $isAvailable ? "addToCart({$menu->id}, '" . addslashes($menu->name) . "', {$menu->price}, '{$category}')" : "showUnavailableToast()" }}">
                        @if($menu->image)
                        <div class="h-32 w-full mb-3 rounded overflow-hidden">
                            <img src="{{ Storage::url($menu->image) }}"
                                 alt="{{ $menu->name }}"
                                 class="w-full h-full object-cover">
                        </div>
                        @else
                        <div class="h-32 w-full mb-3 bg-gray-100 rounded flex items-center justify-center">
                            <i data-lucide="utensils" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        @endif

                        <div class="text-center">
                            <h4 class="font-medium text-gray-800 mb-1">{{ $menu->name }}</h4>
                            <p class="text-sm text-gray-600 mb-2">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                            <div class="flex items-center justify-center text-xs">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full mr-2">
                                    {{ $menu->ingredients->count() }} bahan
                                </span>
                                @if($isAvailable)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">Tersedia</span>
                                @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">Habis</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($menus->isEmpty())
                <div class="text-center py-12">
                    <i data-lucide="utensils-crossed" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                    <p class="text-gray-600">Belum ada menu yang tersedia</p>
                    <a href="{{ route('menus.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Tambah Menu
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column - Cart & Payment -->
        <div>
            <form id="saleForm" method="POST" action="{{ route('sales.store') }}" class="space-y-6">
                @csrf

                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Keranjang</h3>
                        <button type="button" onclick="clearCart()"
                                class="text-sm text-red-600 hover:text-red-800 flex items-center {{ count($menus) > 0 ? '' : 'hidden' }}"
                                id="clearCartBtn">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>
                            Kosongkan
                        </button>
                    </div>

                    <!-- Cart Items -->
                    <div id="cartItems" class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                        <div class="text-center py-8 text-gray-500" id="emptyCartMessage">
                            <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3"></i>
                            <p>Keranjang kosong</p>
                            <p class="text-sm mt-1">Pilih menu untuk menambahkannya ke keranjang</p>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="space-y-3 border-t pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-lg" id="subtotalDisplay">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold border-t pt-2">
                            <span>Total</span>
                            <span id="totalDisplay" class="text-blue-600">Rp 0</span>
                        </div>
                    </div>

                    <!-- Hidden inputs - Sesuaikan dengan model -->
                    <input type="hidden" id="itemsInput" name="items" value="[]">
                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="cash">
                    <input type="hidden" name="cash_received" id="cashReceivedInput" value="0">
                    <input type="hidden" name="change" id="changeInput" value="0">
                </div>

                <!-- Transaction Details -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Transaksi</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                            <input type="date" id="date" name="date"
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                            <textarea id="notes" name="notes" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Catatan untuk transaksi ini..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pembayaran</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="payment_method_ui" value="cash" class="sr-only peer" checked onchange="togglePaymentFields()">
                                    <div class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <i data-lucide="dollar-sign" class="w-5 h-5 text-green-600 mx-auto mb-1"></i>
                                        <span class="text-sm font-medium">Tunai</span>
                                    </div>
                                </label>
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" name="payment_method_ui" value="transfer" class="sr-only peer" onchange="togglePaymentFields()">
                                    <div class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <i data-lucide="credit-card" class="w-5 h-5 text-blue-600 mx-auto mb-1"></i>
                                        <span class="text-sm font-medium">Transfer</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="cashPaymentSection">
                            <div class="mb-3">
                                <label for="cashReceived" class="block text-sm font-medium text-gray-700 mb-1">Uang Diterima *</label>
                                <input type="number" id="cashReceived"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0" min="0" step="1000" oninput="updateCashChange()">
                                <p class="text-xs text-gray-500 mt-1">Masukkan jumlah uang yang diterima dari pelanggan</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kembalian</label>
                                <div class="px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                    <span id="changeAmount" class="text-lg font-bold text-blue-600">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Items: <span id="itemCount" class="font-medium">0</span></p>
                            <p class="text-xl font-bold text-gray-800" id="finalTotalDisplay">Rp 0</p>
                            <p class="text-xs text-gray-500 mt-1" id="paymentStatus"></p>
                        </div>
                        <button type="button" id="submitButton"
                                class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                onclick="validateAndSubmitSale()"
                                disabled>
                            <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i>
                            Proses Transaksi
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
                <h3 class="text-lg font-semibold text-gray-800 mb-2" id="modalMenuName"></h3>
                <p class="text-sm text-gray-600 mb-4" id="modalMenuPrice"></p>
                <p class="text-xs text-gray-500 mb-2" id="modalMenuInfo"></p>

                <div class="mb-6">
                    <label for="itemQuantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah *</label>
                    <div class="flex items-center space-x-3">
                        <button type="button" onclick="decreaseQuantity()"
                                class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                id="decreaseBtn">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <input type="number" id="itemQuantity" value="1" min="1" max="999"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-center focus:ring-blue-500 focus:border-blue-500"
                               oninput="validateQuantity()">
                        <button type="button" onclick="increaseQuantity()"
                                class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-xs text-gray-500" id="quantityInfo">
                        Min: 1, Max: 999
                    </div>
                </div>

                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold text-blue-600" id="modalSubtotal">Rp 0</span>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideQuantityModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="button" onclick="addItemToCart()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <i data-lucide="shopping-cart" class="w-4 h-4 mr-2"></i>
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
// Global variables
let cart = [];
let currentItem = null;
let currentItemPrice = 0;

// Toastify helper functions
function showToast(message, type = 'success', duration = 3000) {
    const config = {
        text: message,
        duration: duration,
        close: true,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
    };

    switch(type) {
        case 'success':
            config.className = "toastify-success";
            break;
        case 'error':
            config.className = "toastify-error";
            break;
        case 'warning':
            config.className = "toastify-warning";
            break;
        case 'info':
            config.className = "toastify-info";
            break;
    }

    Toastify(config).showToast();
}

function showErrorToast(message) {
    showToast(message, 'error', 5000);
}

function showSuccessToast(message) {
    showToast(message, 'success', 3000);
}

function showWarningToast(message) {
    showToast(message, 'warning', 4000);
}

function showInfoToast(message) {
    showToast(message, 'info', 3000);
}

function showUnavailableToast() {
    showErrorToast('Menu tidak tersedia atau stok bahan habis');
}

// Format currency helper
function formatCurrency(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}

// Filter menu by category
function filterMenu(category) {
    const menuItems = document.querySelectorAll('.menu-item');

    // Update active button
    const categories = ['all', 'makanan', 'minuman', 'lainnya'];
    categories.forEach(cat => {
        const btn = document.getElementById(cat + 'Category');
        if (btn) {
            if (cat === category) {
                btn.classList.remove('bg-gray-100', 'text-gray-700');
                btn.classList.add('bg-blue-600', 'text-white');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            }
        }
    });

    // Show/hide items
    menuItems.forEach(item => {
        const itemCategory = item.dataset.category || 'lainnya';
        if (category === 'all' || itemCategory === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Menu search functionality
document.getElementById('menuSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const menuItems = document.querySelectorAll('.menu-item');

    menuItems.forEach(item => {
        const menuName = item.querySelector('h4').textContent.toLowerCase();
        const isVisible = menuName.includes(searchTerm) && item.style.display !== 'none';
        item.style.opacity = isVisible ? '1' : '0.3';
        item.style.pointerEvents = isVisible ? 'auto' : 'none';
    });
});

// Cart functions
function addToCart(menuId, menuName, price, category) {
    currentItem = {
        id: menuId,
        name: menuName,
        price: price,
        quantity: 1,
        category: category
    };

    currentItemPrice = price;

    document.getElementById('modalMenuName').textContent = menuName;
    document.getElementById('modalMenuPrice').textContent = formatCurrency(price);
    document.getElementById('modalMenuInfo').textContent = `Kategori: ${category.charAt(0).toUpperCase() + category.slice(1)}`;
    document.getElementById('itemQuantity').value = 1;
    document.getElementById('decreaseBtn').disabled = true;

    // Update modal subtotal
    updateModalSubtotal();

    // Show modal
    document.getElementById('quantityModal').classList.remove('hidden');
}

function hideQuantityModal() {
    document.getElementById('quantityModal').classList.add('hidden');
    currentItem = null;
    currentItemPrice = 0;
}

function increaseQuantity() {
    const input = document.getElementById('itemQuantity');
    const currentValue = parseInt(input.value);
    if (currentValue < 999) {
        input.value = currentValue + 1;
        updateModalSubtotal();
        document.getElementById('decreaseBtn').disabled = false;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('itemQuantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
        updateModalSubtotal();
        if (currentValue - 1 === 1) {
            document.getElementById('decreaseBtn').disabled = true;
        }
    }
}

function validateQuantity() {
    const input = document.getElementById('itemQuantity');
    let value = parseInt(input.value);

    if (isNaN(value) || value < 1) {
        value = 1;
    } else if (value > 999) {
        value = 999;
    }

    input.value = value;
    updateModalSubtotal();
    document.getElementById('decreaseBtn').disabled = value === 1;
}

function updateModalSubtotal() {
    const quantity = parseInt(document.getElementById('itemQuantity').value);
    const subtotal = currentItemPrice * quantity;
    document.getElementById('modalSubtotal').textContent = formatCurrency(subtotal);
}

function addItemToCart() {
    if (!currentItem) return;

    const quantity = parseInt(document.getElementById('itemQuantity').value);

    // Check if item already in cart
    const existingIndex = cart.findIndex(item => item.id === currentItem.id);

    if (existingIndex > -1) {
        cart[existingIndex].quantity += quantity;
        showInfoToast(`Jumlah ${currentItem.name} diperbarui: ${cart[existingIndex].quantity}`);
    } else {
        cart.push({
            ...currentItem,
            quantity: quantity
        });
        showSuccessToast(`${currentItem.name} ditambahkan ke keranjang`);
    }

    hideQuantityModal();
    updateCartDisplay();
}

function removeFromCart(index) {
    const itemName = cart[index].name;
    cart.splice(index, 1);
    updateCartDisplay();
    showWarningToast(`${itemName} dihapus dari keranjang`);
}

function updateQuantity(index, newQuantity) {
    if (newQuantity < 1) {
        removeFromCart(index);
    } else if (newQuantity > 999) {
        showErrorToast('Maksimal jumlah per item adalah 999');
        return;
    } else {
        const itemName = cart[index].name;
        cart[index].quantity = newQuantity;
        updateCartDisplay();
        showInfoToast(`Jumlah ${itemName} diperbarui: ${newQuantity}`);
    }
}

function clearCart() {
    if (cart.length === 0) return;

    if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
        cart = [];
        updateCartDisplay();
        showWarningToast('Keranjang telah dikosongkan');
    }
}

function updateCartDisplay() {
    const cartItemsContainer = document.getElementById('cartItems');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const submitButton = document.getElementById('submitButton');
    const clearCartBtn = document.getElementById('clearCartBtn');

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="text-center py-8 text-gray-500" id="emptyCartMessage">
                <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3"></i>
                <p>Keranjang kosong</p>
                <p class="text-sm mt-1">Pilih menu untuk menambahkannya ke keranjang</p>
            </div>
        `;
        submitButton.disabled = true;
        clearCartBtn.classList.add('hidden');
    } else {
        let cartHTML = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            cartHTML += `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800">${item.name}</div>
                        <div class="text-sm text-gray-600">${formatCurrency(item.price)} × ${item.quantity}</div>
                        <div class="text-xs text-gray-500 mt-1">${item.category.charAt(0).toUpperCase() + item.category.slice(1)}</div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <button type="button" onclick="updateQuantity(${index}, ${item.quantity - 1})"
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-100 ${item.quantity === 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                                <i data-lucide="minus" class="w-3 h-3"></i>
                            </button>
                            <div class="w-10 text-center">
                                <input type="number"
                                       value="${item.quantity}"
                                       min="1"
                                       max="999"
                                       class="w-full px-1 py-1 border border-gray-300 rounded text-center text-sm focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateQuantity(${index}, parseInt(this.value))"
                                       onblur="if(this.value === '') this.value = 1; updateQuantity(${index}, parseInt(this.value))">
                            </div>
                            <button type="button" onclick="updateQuantity(${index}, ${item.quantity + 1})"
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-100 ${item.quantity === 999 ? 'opacity-50 cursor-not-allowed' : ''}">
                                <i data-lucide="plus" class="w-3 h-3"></i>
                            </button>
                        </div>
                        <div class="w-28 text-right">
                            <div class="font-semibold text-blue-600">${formatCurrency(itemTotal)}</div>
                        </div>
                        <button type="button" onclick="removeFromCart(${index})"
                                class="text-red-600 hover:text-red-800 p-1 ml-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        cartItemsContainer.innerHTML = cartHTML;
        submitButton.disabled = false;
        clearCartBtn.classList.remove('hidden');
    }

    // Calculate total (tanpa PPN)
    const total = subtotal;

    // Update display
    document.getElementById('subtotalDisplay').textContent = formatCurrency(subtotal);
    document.getElementById('totalDisplay').textContent = formatCurrency(total);
    document.getElementById('finalTotalDisplay').textContent = formatCurrency(total);
    document.getElementById('itemCount').textContent = cart.length;

    // Update cash change
    updateCashChange();

    // Update payment status
    updatePaymentStatus();

    // Update hidden items input for form submission
    const itemsArray = cart.map(item => ({
        menu_id: parseInt(item.id),
        quantity: parseInt(item.quantity)
    }));

    document.getElementById('itemsInput').value = JSON.stringify(itemsArray);
}

function updateCashChange() {
    const cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;

    // Calculate total from cart
    let total = 0;
    cart.forEach(item => {
        total += item.price * item.quantity;
    });

    const change = cashReceived - total;

    const changeAmount = document.getElementById('changeAmount');

    if (change > 0) {
        changeAmount.textContent = formatCurrency(change);
        changeAmount.className = 'text-lg font-bold text-green-600';
    } else if (change === 0) {
        changeAmount.textContent = formatCurrency(0);
        changeAmount.className = 'text-lg font-bold text-blue-600';
    } else {
        changeAmount.textContent = formatCurrency(Math.abs(change)) + ' kurang';
        changeAmount.className = 'text-lg font-bold text-red-600';
    }

    // Update hidden inputs
    document.getElementById('cashReceivedInput').value = cashReceived;
    document.getElementById('changeInput').value = Math.max(change, 0);

    return { cashReceived, total, change };
}

function updatePaymentStatus() {
    const paymentStatus = document.getElementById('paymentStatus');
    const paymentMethod = document.querySelector('input[name="payment_method_ui"]:checked');

    if (!paymentMethod || cart.length === 0) {
        paymentStatus.textContent = '';
        return;
    }

    const method = paymentMethod.value;
    const cashData = updateCashChange();

    // Update hidden payment method input
    document.getElementById('paymentMethodInput').value = method;

    if (method === 'cash') {
        if (cashData.cashReceived === 0) {
            paymentStatus.textContent = 'Tunai: Belum diisi';
            paymentStatus.className = 'text-xs text-yellow-600 mt-1';
        } else if (cashData.cashReceived >= cashData.total) {
            paymentStatus.textContent = `Tunai: ${formatCurrency(cashData.cashReceived)} (Cukup)`;
            paymentStatus.className = 'text-xs text-green-600 mt-1';
        } else {
            paymentStatus.textContent = `Tunai: ${formatCurrency(cashData.cashReceived)} (Kurang)`;
            paymentStatus.className = 'text-xs text-red-600 mt-1';
        }
    } else {
        paymentStatus.textContent = 'Transfer: Siap diproses';
        paymentStatus.className = 'text-xs text-blue-600 mt-1';
    }
}

function togglePaymentFields() {
    const cashPaymentSection = document.getElementById('cashPaymentSection');
    const paymentMethod = document.querySelector('input[name="payment_method_ui"]:checked').value;

    // Update hidden payment method input
    document.getElementById('paymentMethodInput').value = paymentMethod;

    if (paymentMethod === 'cash') {
        cashPaymentSection.style.display = 'block';
        document.getElementById('cashReceived').required = true;
    } else {
        cashPaymentSection.style.display = 'none';
        document.getElementById('cashReceived').required = false;
        // Reset cash fields
        document.getElementById('cashReceived').value = '';
        document.getElementById('changeAmount').textContent = formatCurrency(0);
        document.getElementById('cashReceivedInput').value = 0;
        document.getElementById('changeInput').value = 0;
    }

    updatePaymentStatus();
}

// Validation and form submission
function validateAndSubmitSale() {
    // Validate cart
    if (cart.length === 0) {
        showErrorToast('Keranjang kosong. Silakan tambahkan menu terlebih dahulu.');
        return;
    }

    // Validate date
    const dateInput = document.getElementById('date');
    if (!dateInput.value) {
        showErrorToast('Tanggal harus diisi');
        dateInput.focus();
        return;
    }

    // Validate payment method
    const paymentMethod = document.querySelector('input[name="payment_method_ui"]:checked').value;

    if (paymentMethod === 'cash') {
        const cashData = updateCashChange();
        const cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;

        if (cashReceived === 0) {
            showErrorToast('Uang diterima harus diisi untuk pembayaran tunai');
            document.getElementById('cashReceived').focus();
            return;
        }

        if (cashData.cashReceived < cashData.total) {
            if (!confirm(`Uang diterima (${formatCurrency(cashReceived)}) kurang dari total (${formatCurrency(cashData.total)}). Lanjutkan transaksi?`)) {
                document.getElementById('cashReceived').focus();
                return;
            }
        }
    }

    // Submit the form
    submitSaleForm();
}

function submitSaleForm() {
    // Show loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.remove('hidden');

    // Disable submit button
    const submitButton = document.getElementById('submitButton');
    submitButton.disabled = true;

    // Update items input (ensure it's properly formatted)
    const itemsArray = cart.map(item => ({
        menu_id: parseInt(item.id),
        quantity: parseInt(item.quantity)
    }));

    const itemsJson = JSON.stringify(itemsArray);
    document.getElementById('itemsInput').value = itemsJson;

    // Prepare form data
    const form = document.getElementById('saleForm');
    const formData = new FormData(form);

    // Submit via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');

        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
                return {
                    status: response.status,
                    ok: response.ok,
                    data: data
                };
            });
        } else {
            return {
                status: response.status,
                ok: response.ok,
                data: null
            };
        }
    })
    .then(result => {
        loadingOverlay.classList.add('hidden');
        submitButton.disabled = false;

        if (result.ok && result.data) {
            if (result.data.success) {
                // Show success message
                showSuccessToast(result.data.message || 'Transaksi berhasil disimpan!');

                // Redirect to sale detail page
                setTimeout(() => {
                    if (result.data.redirect) {
                        window.location.href = result.data.redirect;
                    } else if (result.data.sale_id) {
                        window.location.href = '/sales/' + result.data.sale_id;
                    } else {
                        window.location.href = '{{ route("sales.index") }}';
                    }
                }, 1500);
            } else {
                // Show error from server
                showErrorToast(result.data.message || 'Terjadi kesalahan pada server');
            }
        } else if (result.status === 422) {
            // Validation errors
            if (result.data && result.data.errors) {
                let errorMessages = [];
                Object.values(result.data.errors).forEach(errors => {
                    errors.forEach(error => {
                        errorMessages.push(error);
                    });
                });

                // Show first error in toast
                if (errorMessages.length > 0) {
                    showErrorToast(errorMessages[0]);
                }
            } else if (result.data && result.data.message) {
                showErrorToast(result.data.message);
            } else {
                showErrorToast('Validasi data gagal. Periksa kembali input Anda.');
            }
        } else if (result.status === 500) {
            showErrorToast('Terjadi kesalahan server. Silakan coba lagi.');
        } else {
            // Fallback: submit form normally (for non-AJAX responses)
            showInfoToast('Mengirim data...');
            form.submit();
        }
    })
    .catch(error => {
        loadingOverlay.classList.add('hidden');
        submitButton.disabled = false;

        showErrorToast('Terjadi kesalahan jaringan. Silakan coba lagi.');
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const dateInput = document.getElementById('date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    // Initialize cart display
    updateCartDisplay();

    // Initialize payment fields
    togglePaymentFields();

    // Close modal on outside click
    const quantityModal = document.getElementById('quantityModal');
    if (quantityModal) {
        quantityModal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideQuantityModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !quantityModal.classList.contains('hidden')) {
            hideQuantityModal();
        }

        // Ctrl+Enter to submit form
        if (e.ctrlKey && e.key === 'Enter' && !submitButton.disabled) {
            validateAndSubmitSale();
        }
    });

    // Update payment status when cash received changes
    document.getElementById('cashReceived').addEventListener('input', function() {
        updatePaymentStatus();
    });

    // Update payment status when payment method changes
    document.querySelectorAll('input[name="payment_method_ui"]').forEach(radio => {
        radio.addEventListener('change', updatePaymentStatus);
    });

    // Show success/error messages from session
    @if(session('success'))
        showSuccessToast('{{ session("success") }}');
    @endif

    @if(session('error'))
        showErrorToast('{{ session("error") }}');
    @endif
});
</script>
@endpush
