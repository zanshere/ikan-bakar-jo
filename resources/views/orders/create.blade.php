{{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Buat Pesanan Baru')
@section('page-description', 'Pilih menu dan saus yang ingin dipesan')

@section('breadcrumb')
    <span>/</span>
    <a href="{{ route('orders.index') }}" class="text-gray-500 hover:text-gray-700">Pesanan</a>
    <span>/</span>
    <span class="text-gray-700">Baru</span>
@endsection

@section('header-buttons')
    <a href="{{ route('orders.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Kembali
    </a>
@endsection

@section('styles')
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

        .menu-item {
            transition: all 0.2s ease;
        }

        .menu-item.available {
            cursor: pointer;
        }

        .menu-item.available:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .menu-item.unavailable {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .menu-item.unavailable:hover {
            border-color: #e5e7eb;
            box-shadow: none;
        }

        .sauce-option {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .sauce-option:hover {
            border-color: #3b82f6;
            background-color: #f0f9ff;
        }

        .sauce-option.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .cart-item {
            transition: all 0.2s ease;
        }

        .cart-item:hover {
            background-color: #f9fafb;
        }

        .badge-available {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-unavailable {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-sauce {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .price-tag {
            font-size: 1.1rem;
            font-weight: 600;
            color: #059669;
        }

        .modal {
            transition: opacity 0.2s ease;
        }

        .modal-content {
            transform: scale(1);
            transition: transform 0.2s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }
    </style>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- Loading Overlay --}}
        <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-xl shadow-xl p-8">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                        <p class="text-gray-700 font-medium">Memproses pesanan...</p>
                        <p class="text-sm text-gray-500 mt-1">Harap tunggu sebentar</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
            <div class="flex">
                <div class="shrink-0">
                    <i data-lucide="info" class="h-5 w-5 text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Setelah membuat pesanan, owner akan mengkonfirmasi ketersediaan bahan.
                        Pembayaran dilakukan di kasir setelah pesanan selesai diproses.
                    </p>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Menu Selection Column --}}
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

                    {{-- Category Filters --}}
                    <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
                        <button id="allCategory" class="px-4 py-2 bg-blue-600 text-white rounded-lg whitespace-nowrap"
                            onclick="filterMenu('all')">
                            Semua
                        </button>
                        <button id="makananCategory"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 whitespace-nowrap"
                            onclick="filterMenu('makanan')">
                            Makanan
                        </button>
                        <button id="minumanCategory"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 whitespace-nowrap"
                            onclick="filterMenu('minuman')">
                            Minuman
                        </button>
                        <button id="lainnyaCategory"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 whitespace-nowrap"
                            onclick="filterMenu('lainnya')">
                            Lainnya
                        </button>
                    </div>

                    {{-- Menu Grid --}}
                    <div id="menuGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($menus as $menu)
                            @php
                                $category = strtolower($menu->category ?? 'lainnya');
                                $isMenuAvailable = $menu->is_available ?? false;

                                // Pastikan available_sauces adalah collection, bukan null
                                $availableSauces = $menu->availableSauces ?? collect([]);
                                $hasAvailableSauces =
                                    $availableSauces
                                        ->filter(function ($sauce) {
                                            return isset($sauce->is_available) ? $sauce->is_available : false;
                                        })
                                        ->count() > 0;

                                $isAvailable = $isMenuAvailable && $hasAvailableSauces;
                            @endphp
                            <div class="border rounded-lg p-4 transition-all menu-item {{ $isAvailable ? 'available' : 'unavailable' }}"
                                data-category="{{ $category }}" data-menu-id="{{ $menu->id }}"
                                data-menu-name="{{ $menu->name }}" data-menu-price="{{ $menu->price }}"
                                data-menu-available="{{ $isAvailable ? 'true' : 'false' }}"
                                onclick="{{ $isAvailable ? "showSauceSelection({$menu->id})" : 'showUnavailableToast()' }}">
                                @if ($menu->image)
                                    <div class="h-32 w-full mb-3 rounded overflow-hidden">
                                        <img src="{{ Storage::url($menu->image) }}" alt="{{ $menu->name }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="h-32 w-full mb-3 bg-gray-100 rounded flex items-center justify-center">
                                        <i data-lucide="utensils" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                @endif

                                <div class="text-center">
                                    <h4 class="font-medium text-gray-800 mb-1">{{ $menu->name }}</h4>
                                    <p class="text-sm text-gray-600 mb-2">{{ $menu->formatted_price }}</p>
                                    <div class="flex items-center justify-center text-xs">
                                        @if ($isAvailable)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">Tersedia</span>
                                        @elseif(!$isMenuAvailable)
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">Stok Menu
                                                Habis</span>
                                        @elseif(!$hasAvailableSauces)
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">Stok Saus
                                                Habis</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">Habis</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($menus->isEmpty())
                        <div class="text-center py-12">
                            <i data-lucide="utensils-crossed" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                            <p class="text-gray-600">Belum ada menu yang tersedia</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cart Column --}}
            <div>
                <form id="orderForm" method="POST" action="{{ route('orders.store') }}" class="space-y-6">
                    @csrf

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Keranjang Pesanan</h3>
                            <button type="button" onclick="clearCart()"
                                class="text-sm text-red-600 hover:text-red-800 flex items-center hidden" id="clearCartBtn">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>
                                Kosongkan
                            </button>
                        </div>

                        <div id="cartItems" class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                            <div class="text-center py-8 text-gray-500" id="emptyCartMessage">
                                <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3"></i>
                                <p>Keranjang kosong</p>
                                <p class="text-sm mt-1">Pilih menu untuk menambahkannya ke keranjang</p>
                            </div>
                        </div>

                        {{-- Total Section --}}
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

                        <input type="hidden" id="itemsInput" name="items" value="[]">
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan Pesanan</h3>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan
                                (Opsional)</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Tambahkan catatan untuk pesanan Anda..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Contoh: Tidak pedas, extra sambal, dll.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Items: <span id="itemCount" class="font-medium">0</span>
                                </p>
                                <p class="text-xl font-bold text-gray-800" id="finalTotalDisplay">Rp 0</p>
                            </div>
                            <button type="button" id="submitButton"
                                class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                onclick="validateAndSubmitOrder()" disabled>
                                <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i>
                                Buat Pesanan
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-3 text-center">
                            Dengan membuat pesanan, Anda menyetujui bahwa pesanan akan diproses setelah dikonfirmasi oleh
                            owner.
                            Pembayaran dilakukan di kasir setelah pesanan selesai.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sauce Selection Modal --}}
    <div id="sauceModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800" id="modalMenuName"></h3>
                        <button type="button" onclick="hideSauceModal()" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <p class="text-sm text-gray-600 mb-2">Harga Menu: <span class="font-semibold text-blue-600"
                            id="modalMenuPrice"></span></p>
                    <p class="text-xs text-blue-600 mb-4">* Pilih saus yang tersedia untuk menu ini (harga sudah termasuk
                        saus)</p>

                    <div class="space-y-3 max-h-96 overflow-y-auto pr-2" id="sauceList">
                        {{-- Sauce options will be loaded here dynamically --}}
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                        <button type="button" onclick="hideSauceModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quantity Modal --}}
    <div id="quantityModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2" id="modalMenuNameQuantity"></h3>
                    <p class="text-sm text-gray-600 mb-2" id="modalSauceNameQuantity"></p>
                    <p class="text-sm text-gray-600 mb-4" id="modalPriceDetail"></p>

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

{{-- Hidden data from controller --}}
@push('scripts')
    <script>
        window.menusData = @json($menus);

        // Pastikan menusData tidak undefined
        if (typeof window.menusData === 'undefined') {
            window.menusData = [];
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // =========================================================================
        // Global Variables
        // =========================================================================
        let cart = [];
        let currentSelectedMenu = null;
        let currentSelectedSauce = null;
        let currentMenuPrice = 0;
        let currentMenuName = '';
        let currentSauceName = '';
        let menus = window.menusData || [];

        // Pastikan menus adalah array
        if (!Array.isArray(menus)) {
            menus = [];
            console.error('menusData bukan array:', window.menusData);
        }

        // =========================================================================
        // Utility Functions
        // =========================================================================
        function showToast(message, type = 'success', duration = 3000) {
            const config = {
                text: message,
                duration: duration,
                close: true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
            };

            switch (type) {
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

        function formatCurrency(amount) {
            return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
        }

        // =========================================================================
        // Menu Filter and Search Functions
        // =========================================================================
        function filterMenu(category) {
            const menuItems = document.querySelectorAll('.menu-item');
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

            menuItems.forEach(item => {
                const itemCategory = item.dataset.category || 'lainnya';
                if (category === 'all' || itemCategory === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });

            applySearchFilter();
        }

        function applySearchFilter() {
            const searchTerm = document.getElementById('menuSearch').value.toLowerCase();
            const menuItems = document.querySelectorAll('.menu-item');

            menuItems.forEach(item => {
                if (item.style.display !== 'none') {
                    const menuName = item.dataset.menuName.toLowerCase();
                    if (menuName.includes(searchTerm)) {
                        item.style.opacity = '1';
                        item.style.pointerEvents = 'auto';
                    } else {
                        item.style.opacity = '0.3';
                        item.style.pointerEvents = 'none';
                    }
                }
            });
        }

        document.getElementById('menuSearch').addEventListener('input', function(e) {
            applySearchFilter();
        });

        // =========================================================================
        // Sauce Selection Modal Functions
        // =========================================================================
        function showSauceSelection(menuId) {
            console.log('showSauceSelection called with menuId:', menuId);

            const menu = menus.find(m => m.id === menuId);
            if (!menu) {
                showErrorToast('Menu tidak ditemukan');
                return;
            }

            currentSelectedMenu = menu;
            currentMenuName = menu.name;
            console.log('Current selected menu:', currentSelectedMenu);

            // Pastikan available_sauces ada dan merupakan array
            const availableSauces = (menu.available_sauces || []).filter(sauce => sauce.is_available === true);

            if (availableSauces.length === 0) {
                showErrorToast('Tidak ada saus tersedia untuk menu ini');
                return;
            }

            document.getElementById('modalMenuName').textContent = menu.name;
            document.getElementById('modalMenuPrice').textContent = formatCurrency(menu.price);

            let sauceListHTML = '';

            availableSauces.forEach(sauce => {
                const totalPrice = menu.price;
                const isDefault = sauce.pivot && sauce.pivot.is_default ? true : false;
                const defaultBadge = isDefault ?
                    '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2">Default</span>' :
                    '';

                sauceListHTML += `
                    <div class="border rounded-lg p-4 hover:border-blue-500 transition-colors cursor-pointer sauce-option"
                         data-sauce-id="${sauce.id}"
                         data-sauce-name="${sauce.name}"
                         onclick="selectSauce(this, ${menu.id}, ${sauce.id}, '${sauce.name}', ${menu.price})">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-800">${sauce.name}</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Harga Menu: ${formatCurrency(menu.price)} (sudah termasuk saus)
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Stok Saus: ${sauce.formatted_stock || 'Tersedia'}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-blue-600">${formatCurrency(totalPrice)}</p>
                                ${defaultBadge}
                            </div>
                        </div>
                    </div>
                `;
            });

            document.getElementById('sauceList').innerHTML = sauceListHTML;
            document.getElementById('sauceModal').classList.remove('hidden');
        }

        function hideSauceModal() {
            document.getElementById('sauceModal').classList.add('hidden');
            // Jangan reset currentSelectedMenu di sini karena masih diperlukan
        }

        function selectSauce(element, menuId, sauceId, sauceName, menuPrice) {
            console.log('selectSauce called:', {
                menuId,
                sauceId,
                sauceName,
                menuPrice
            });

            document.querySelectorAll('.sauce-option').forEach(opt => {
                opt.classList.remove('border-blue-500', 'bg-blue-50');
            });

            element.classList.add('border-blue-500', 'bg-blue-50');

            currentSelectedSauce = {
                id: sauceId,
                name: sauceName
            };
            currentSauceName = sauceName;
            currentMenuPrice = menuPrice;

            console.log('Current selected sauce:', currentSelectedSauce);
            console.log('Current selected menu:', currentSelectedMenu);

            hideSauceModal();

            // Dapatkan nama menu dari currentSelectedMenu
            const menuName = currentSelectedMenu ? currentSelectedMenu.name : 'Menu';

            showQuantityModal(menuId, menuName, sauceName, menuPrice);
        }

        // =========================================================================
        // Quantity Modal Functions
        // =========================================================================
        function showQuantityModal(menuId, menuName, sauceName, menuPrice) {
            console.log('showQuantityModal called:', {
                menuId,
                menuName,
                sauceName,
                menuPrice
            });

            const totalPrice = menuPrice;

            document.getElementById('modalMenuNameQuantity').textContent = menuName;
            document.getElementById('modalSauceNameQuantity').textContent = 'Saus: ' + sauceName;
            document.getElementById('modalPriceDetail').innerHTML = `
                Harga Menu: ${formatCurrency(menuPrice)} (sudah termasuk saus)<br>
                <span class="font-semibold">Total per Item: ${formatCurrency(totalPrice)}</span>
            `;
            document.getElementById('itemQuantity').value = 1;
            document.getElementById('decreaseBtn').disabled = true;

            updateModalSubtotal(totalPrice);

            document.getElementById('quantityModal').classList.remove('hidden');
        }

        function hideQuantityModal() {
            document.getElementById('quantityModal').classList.add('hidden');
            // Jangan reset currentSelectedSauce di sini karena masih diperlukan untuk addToCart
        }

        function increaseQuantity() {
            const input = document.getElementById('itemQuantity');
            const currentValue = parseInt(input.value);
            if (currentValue < 999) {
                input.value = currentValue + 1;
                updateQuantityDisplay();
                document.getElementById('decreaseBtn').disabled = false;
            }
        }

        function decreaseQuantity() {
            const input = document.getElementById('itemQuantity');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateQuantityDisplay();
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
            updateQuantityDisplay();
            document.getElementById('decreaseBtn').disabled = value === 1;
        }

        function updateQuantityDisplay() {
            const quantity = parseInt(document.getElementById('itemQuantity').value);
            const totalPrice = currentMenuPrice * quantity;
            updateModalSubtotal(totalPrice);
        }

        function updateModalSubtotal(subtotal) {
            document.getElementById('modalSubtotal').textContent = formatCurrency(subtotal);
        }

        // =========================================================================
        // Cart Functions
        // =========================================================================
        function addItemToCart() {
            console.log('addItemToCart called');
            console.log('Current state:', {
                currentSelectedMenu: currentSelectedMenu,
                currentSelectedSauce: currentSelectedSauce,
                currentMenuPrice: currentMenuPrice,
                currentMenuName: currentMenuName,
                currentSauceName: currentSauceName
            });

            // Validasi dengan pengecekan yang lebih detail
            if (!currentSelectedMenu) {
                showErrorToast('Pilih menu terlebih dahulu');
                console.error('currentSelectedMenu is null');
                return;
            }

            if (!currentSelectedSauce) {
                showErrorToast('Pilih saus terlebih dahulu');
                console.error('currentSelectedSauce is null');
                return;
            }

            const quantity = parseInt(document.getElementById('itemQuantity').value);

            if (isNaN(quantity) || quantity < 1) {
                showErrorToast('Jumlah tidak valid');
                return;
            }

            console.log('Adding to cart:', {
                menu_id: currentSelectedMenu.id,
                menu_name: currentSelectedMenu.name,
                menu_price: currentMenuPrice,
                sauce_id: currentSelectedSauce.id,
                sauce_name: currentSelectedSauce.name,
                quantity: quantity
            });

            const existingIndex = cart.findIndex(item =>
                item.menu_id === currentSelectedMenu.id &&
                item.sauce_id === currentSelectedSauce.id
            );

            if (existingIndex > -1) {
                cart[existingIndex].quantity += quantity;
                showInfoToast(
                    `Jumlah ${currentSelectedMenu.name} (${currentSelectedSauce.name}) diperbarui: ${cart[existingIndex].quantity}`
                );
            } else {
                cart.push({
                    menu_id: currentSelectedMenu.id,
                    menu_name: currentSelectedMenu.name,
                    menu_price: currentMenuPrice,
                    sauce_id: currentSelectedSauce.id,
                    sauce_name: currentSelectedSauce.name,
                    quantity: quantity
                });
                showSuccessToast(`${currentSelectedMenu.name} (${currentSelectedSauce.name}) ditambahkan ke keranjang`);
            }

            // Reset setelah berhasil menambahkan ke cart
            currentSelectedMenu = null;
            currentSelectedSauce = null;
            currentMenuPrice = 0;
            currentMenuName = '';
            currentSauceName = '';

            hideQuantityModal();
            updateCartDisplay();
        }

        function removeFromCart(index) {
            const itemName = cart[index].menu_name;
            const sauceName = cart[index].sauce_name;
            cart.splice(index, 1);
            updateCartDisplay();
            showWarningToast(`${itemName} (${sauceName}) dihapus dari keranjang`);
        }

        function updateQuantity(index, newQuantity) {
            if (newQuantity < 1) {
                removeFromCart(index);
            } else if (newQuantity > 999) {
                showErrorToast('Maksimal jumlah per item adalah 999');
                return;
            } else {
                const itemName = cart[index].menu_name;
                const sauceName = cart[index].sauce_name;
                cart[index].quantity = newQuantity;
                updateCartDisplay();
                showInfoToast(`Jumlah ${itemName} (${sauceName}) diperbarui: ${newQuantity}`);
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
            const cartContainer = document.getElementById('cartItems');
            const submitButton = document.getElementById('submitButton');
            const clearCartBtn = document.getElementById('clearCartBtn');

            // Ambil elemen-elemen total
            const subtotalDisplay = document.getElementById('subtotalDisplay');
            const totalDisplay = document.getElementById('totalDisplay');
            const finalTotalDisplay = document.getElementById('finalTotalDisplay');
            const itemCount = document.getElementById('itemCount');
            const itemsInput = document.getElementById('itemsInput');

            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="text-center py-8 text-gray-500" id="emptyCartMessage">
                        <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3"></i>
                        <p>Keranjang kosong</p>
                        <p class="text-sm mt-1">Pilih menu untuk menambahkannya ke keranjang</p>
                    </div>
                `;
                submitButton.disabled = true;
                clearCartBtn.classList.add('hidden');

                // Reset total display
                if (subtotalDisplay) subtotalDisplay.textContent = formatCurrency(0);
                if (totalDisplay) totalDisplay.textContent = formatCurrency(0);
                if (finalTotalDisplay) finalTotalDisplay.textContent = formatCurrency(0);
                if (itemCount) itemCount.textContent = 0;
                if (itemsInput) itemsInput.value = JSON.stringify([]);
            } else {
                let cartHTML = '';
                let subtotal = 0;

                cart.forEach((item, index) => {
                    // Hitung total per item: harga menu * quantity
                    const itemTotal = item.menu_price * item.quantity;
                    subtotal += itemTotal;

                    cartHTML += `
                        <div class="cart-item flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200">
                            <div class="flex-1">
                                <div class="font-medium text-gray-800">${item.menu_name}</div>
                                <div class="text-sm text-gray-600">Saus: ${item.sauce_name}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    ${formatCurrency(item.menu_price)} × ${item.quantity}
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="updateQuantity(${index}, ${item.quantity - 1})"
                                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-100 ${item.quantity === 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                                        <i data-lucide="minus" class="w-3 h-3"></i>
                                    </button>
                                    <div class="w-12 text-center">
                                        <span class="text-sm font-medium">${item.quantity}</span>
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

                cartContainer.innerHTML = cartHTML;
                submitButton.disabled = false;
                clearCartBtn.classList.remove('hidden');

                // Update semua display total
                if (subtotalDisplay) subtotalDisplay.textContent = formatCurrency(subtotal);
                if (totalDisplay) totalDisplay.textContent = formatCurrency(subtotal);
                if (finalTotalDisplay) finalTotalDisplay.textContent = formatCurrency(subtotal);
                if (itemCount) itemCount.textContent = cart.length;

                // Update hidden input dengan data cart
                const itemsArray = cart.map(item => ({
                    menu_id: parseInt(item.menu_id),
                    sauce_id: parseInt(item.sauce_id),
                    quantity: parseInt(item.quantity)
                }));

                if (itemsInput) itemsInput.value = JSON.stringify(itemsArray);

                // Re-initialize Lucide icons untuk tombol minus/plus yang baru
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            }
        }

        // =========================================================================
        // Order Submission Functions
        // =========================================================================
        function validateAndSubmitOrder() {
            if (cart.length === 0) {
                showErrorToast('Keranjang kosong. Silakan pilih menu terlebih dahulu.');
                return;
            }

            for (let i = 0; i < cart.length; i++) {
                if (!cart[i].sauce_id) {
                    showErrorToast('Setiap menu harus memiliki saus. Periksa kembali keranjang Anda.');
                    return;
                }
            }

            submitOrderForm();
        }

        function submitOrderForm() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.remove('hidden');

            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;

            const itemsArray = cart.map(item => ({
                menu_id: parseInt(item.menu_id),
                sauce_id: parseInt(item.sauce_id),
                quantity: parseInt(item.quantity)
            }));

            const itemsJson = JSON.stringify(itemsArray);
            document.getElementById('itemsInput').value = itemsJson;

            const form = document.getElementById('orderForm');
            const formData = new FormData(form);

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
                            showSuccessToast(result.data.message || 'Pesanan berhasil dibuat!');

                            setTimeout(() => {
                                if (result.data.redirect) {
                                    window.location.href = result.data.redirect;
                                } else if (result.data.order_id) {
                                    window.location.href = '/orders/' + result.data.order_id;
                                } else {
                                    window.location.href = '{{ route('orders.index') }}';
                                }
                            }, 1500);
                        } else {
                            showErrorToast(result.data.message || 'Terjadi kesalahan pada server');
                        }
                    } else if (result.status === 422) {
                        if (result.data && result.data.errors) {
                            let errorMessages = [];
                            Object.values(result.data.errors).forEach(errors => {
                                errors.forEach(error => {
                                    errorMessages.push(error);
                                });
                            });

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
                        showInfoToast('Mengirim data...');
                        form.submit();
                    }
                })
                .catch(error => {
                    loadingOverlay.classList.add('hidden');
                    submitButton.disabled = false;

                    showErrorToast('Terjadi kesalahan jaringan. Silakan coba lagi.');
                    console.error('Fetch error:', error);
                });
        }

        // =========================================================================
        // Document Ready Event
        // =========================================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document ready, menus data:', menus);
            updateCartDisplay();

            const sauceModal = document.getElementById('sauceModal');
            if (sauceModal) {
                sauceModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideSauceModal();
                        // Reset jika modal ditutup tanpa memilih
                        currentSelectedMenu = null;
                        currentSelectedSauce = null;
                    }
                });
            }

            const quantityModal = document.getElementById('quantityModal');
            if (quantityModal) {
                quantityModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideQuantityModal();
                        // Reset jika modal ditutup tanpa menambah ke cart
                        currentSelectedSauce = null;
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (!sauceModal.classList.contains('hidden')) {
                        hideSauceModal();
                        currentSelectedMenu = null;
                        currentSelectedSauce = null;
                    }
                    if (!quantityModal.classList.contains('hidden')) {
                        hideQuantityModal();
                        currentSelectedSauce = null;
                    }
                }
            });

            @if (session('success'))
                showSuccessToast('{{ session('success') }}');
            @endif

            @if (session('error'))
                showErrorToast('{{ session('error') }}');
            @endif
        });
    </script>
@endpush
