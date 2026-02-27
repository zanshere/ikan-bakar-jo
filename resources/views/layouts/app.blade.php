<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seafood Management')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Toastify CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .sidebar-link.active {
            background-color: #3b82f6;
            color: white;
        }

        .sidebar-link:hover:not(.active) {
            background-color: #f3f4f6;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .print-only {
            display: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            #sidebar,
            #sidebarToggle,
            #sidebarOverlay,
            header,
            nav,
            .sidebar-link,
            [x-data] {
                display: none !important;
            }

            main {
                margin-left: 0 !important;
            }

            main > div {
                padding: 0 !important;
            }

            .bg-white,
            .shadow-sm,
            .rounded-xl {
                box-shadow: none !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-75 z-50 flex items-center justify-center hidden">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Memuat...</p>
        </div>
    </div>

    @if (Auth::check())
        <!-- Mobile Sidebar Toggle -->
        <button id="sidebarToggle" class="lg:hidden fixed top-4 left-4 z-40 p-2 bg-white rounded-lg shadow-md">
            <i data-lucide="menu"></i>
        </button>

        <!-- Sidebar -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
            <!-- Logo -->
            <div class="p-6 border-b">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="fish" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Seafood Manager</h1>
                        <p class="text-xs text-gray-500">Restaurant Management</p>
                    </div>
                </div>
            </div>

            <!-- Navigation - Berdasarkan Role -->
            <nav class="p-4 space-y-1">
                @if (Auth::user()->isOwner())
                    {{-- NAVIGASI UNTUK OWNER --}}

                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Manajemen
                    </div>

                    <!-- Menu Management -->
                    <a href="{{ route('menus.index') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('menus.*') ? 'active' : '' }}">
                        <i data-lucide="utensils" class="w-5 h-5"></i>
                        <span>Manajemen Menu</span>
                    </a>

                    <!-- Ingredients Management -->
                    <a href="{{ route('ingredients.index') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('ingredients.*') ? 'active' : '' }}">
                        <i data-lucide="package" class="w-5 h-5"></i>
                        <span>Bahan Baku</span>
                    </a>

                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Transaksi
                    </div>

                    <!-- Sales Management (Kelola Pesanan) -->
                    <a href="{{ route('sales.index') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                        <span>Kelola Pesanan</span>
                    </a>

                    <!-- Restocks -->
                    <a href="{{ route('restocks.index') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('restocks.*') ? 'active' : '' }}">
                        <i data-lucide="truck" class="w-5 h-5"></i>
                        <span>Restock</span>
                    </a>

                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Laporan
                    </div>

                    <!-- Reports -->
                    <a href="{{ route('reports.income') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('reports.income') ? 'active' : '' }}">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                        <span>Pendapatan</span>
                    </a>

                    <a href="{{ route('reports.expense') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('reports.expense') ? 'active' : '' }}">
                        <i data-lucide="trending-down" class="w-5 h-5"></i>
                        <span>Pengeluaran</span>
                    </a>

                    <a href="{{ route('reports.profit') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('reports.profit') ? 'active' : '' }}">
                        <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                        <span>Laba Rugi</span>
                    </a>

                    <a href="{{ route('reports.stock') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        <span>Laporan Stok</span>
                    </a>

                @else
                    {{-- NAVIGASI UNTUK USER --}}

                    <!-- Dashboard User -->
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Pesanan
                    </div>

                    <!-- Buat Pesanan Baru -->
                    <a href="{{ route('orders.create') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('orders.create') ? 'active' : '' }}">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        <span>Pesan Menu</span>
                    </a>

                    <!-- Riwayat Pesanan -->
                    <a href="{{ route('orders.index') }}"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 {{ request()->routeIs('orders.index') || request()->routeIs('orders.show') ? 'active' : '' }}">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        <span>Riwayat Pesanan</span>
                    </a>

                @endif
            </nav>

            <!-- User Profile (Sama untuk semua role) -->
            <div class="absolute bottom-0 w-full p-4 border-t">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</p>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1 hover:bg-gray-100 rounded-lg">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </button>

                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute bottom-full right-0 mb-2 w-48 bg-white rounded-lg shadow-lg border z-50">
                            @if (Auth::user()->isOwner())
                                <a href="{{ route('profile') }}"
                                    class="flex items-center space-x-2 px-4 py-3 hover:bg-gray-50">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                    <span>Profil</span>
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center space-x-2 px-4 py-3 text-red-600 hover:bg-red-50">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="lg:ml-64 min-h-screen">
            <div class="p-4 md:p-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">@yield('page-title')</h1>
                            <p class="text-gray-600 mt-1">@yield('page-description')</p>
                        </div>

                        <div class="flex items-center space-x-3">
                            @yield('header-buttons')

                            <div class="text-sm text-gray-500">
                                <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                                {{ now()->format('d F Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Breadcrumb -->
                    @hasSection('breadcrumb')
                        <nav class="mt-4 flex space-x-2 text-sm">
                            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
                                <i data-lucide="home" class="w-4 h-4 inline mr-1"></i>
                                Dashboard
                            </a>
                            @yield('breadcrumb')
                        </nav>
                    @endif
                </div>

                <!-- Content -->
                @yield('content')
            </div>
        </main>

        <!-- Mobile Sidebar Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden"></div>
    @else
        <!-- Guest Layout -->
        <main class="min-h-screen">
            @yield('content')
        </main>
    @endif

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Inisialisasi Lucide Icons setelah semua library di-load
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Lucide Icons
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }

            // Mobile sidebar toggle
            document.getElementById('sidebarToggle')?.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');

                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });

            document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');

                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        });

        // Toast notifications
        @if (session('success'))
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: "{{ session('success') }}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#10B981",
                    className: "shadow-lg",
                }).showToast();
            } else {
                console.log("Toastify not available. Message:", "{{ session('success') }}");
            }
        @endif

        @if (session('error'))
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: "{{ session('error') }}",
                    duration: 4000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EF4444",
                    className: "shadow-lg",
                }).showToast();
            } else {
                console.error("Toastify not available. Error:", "{{ session('error') }}");
            }
        @endif

        @if (session('warning'))
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: "{{ session('warning') }}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#F59E0B",
                    className: "shadow-lg",
                }).showToast();
            } else {
                console.warn("Toastify not available. Warning:", "{{ session('warning') }}");
            }
        @endif

        // Loading overlay
        window.showLoading = function() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        };

        window.hideLoading = function() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        };

        // Form submission loading
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.classList.contains('show-loading')) {
                showLoading();
            }
        });

        // Handle back button to hide loading
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoading();
            }
        });

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert-auto-hide').forEach(function(alert) {
            setTimeout(function() {
                alert.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });

        // Print function
        window.printPage = function() {
            window.print();
        };

        // Chart.js helper function
        window.createChart = function(canvasId, config) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                console.error('Canvas element not found:', canvasId);
                return null;
            }

            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return null;
            }

            return new Chart(canvas, config);
        };
    </script>

    @stack('scripts')
</body>

</html>
