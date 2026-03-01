<!-- resources/views/ingredients/index.blade.php -->
@extends('layouts.app')

@section('page-title', 'Manajemen Bahan Baku')
@section('page-description', 'Kelola stok bahan baku restoran')

@section('breadcrumb')
    <span>/</span>
    <span class="text-gray-700">Bahan Baku</span>
@endsection

@section('header-buttons')
    <div class="flex space-x-2">
        <a href="{{ route('ingredients.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Tambah Bahan
        </a>
        <a href="{{ route('reports.stock.pdf') }}" target="_blank"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
            <i data-lucide="download" class="w-4 h-4 mr-2"></i>
            Export
        </a>
    </div>
@endsection

@section('content')
    <!-- Alert untuk menampilkan pesan sukses -->
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg" id="success-alert">
            <div class="flex items-center">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600 mr-2"></i>
                <span class="text-green-800">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Alert untuk menampilkan pesan error -->
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg" id="error-alert">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mr-2"></i>
                <span class="text-red-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm">
        <!-- Filters -->
        <div class="p-6 border-b">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari Bahan</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nama atau kode...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Stok</label>
                    <select name="stock_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Rendah</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Stok Habis</option>
                        <option value="sufficient" {{ request('stock_status') == 'sufficient' ? 'selected' : '' }}>Stok
                            Cukup</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                    <select name="unit"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Satuan</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Stok</label>
                    <input type="number" name="min_stock" value="{{ request('min_stock') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0" min="0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Stok</label>
                    <input type="number" name="max_stock" value="{{ request('max_stock') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="1000" min="0">
                </div>

                <div class="md:col-span-5 flex space-x-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                        Filter
                    </button>
                    <a href="{{ route('ingredients.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats -->
        <div class="p-6 border-b bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                    $totalIngredients = $ingredients->total();
                    $lowStockCount = $ingredients->where('stock_status', 'low')->count();
                    $outOfStockCount = $ingredients->where('stock_status', 'empty')->count();
                    $totalStockValue = $ingredients->sum('total_value');
                @endphp

                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Total Bahan</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalIngredients }}</p>
                </div>

                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Stok Rendah</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $lowStockCount }}</p>
                </div>

                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Stok Habis</p>
                    <p class="text-2xl font-bold text-red-600">{{ $outOfStockCount }}</p>
                </div>

                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Nilai Total Stok</p>
                    <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan
                            Baku</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga
                            Satuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai
                            Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Digunakan
                            di Menu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ingredients as $ingredient)
                        <tr class="hover:bg-gray-50" id="ingredient-row-{{ $ingredient->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 shrink-0 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ingredient->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $ingredient->code }}</div>
                                        <div class="text-xs text-gray-400">{{ $ingredient->unit }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $ingredient->formatted_stock }}</div>
                                <div class="text-xs text-gray-500">Min: {{ $ingredient->formatted_min_stock }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {!! $ingredient->stock_status_badge !!}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $ingredient->formatted_price }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-blue-600">{{ $ingredient->formatted_total_value }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $ingredient->menus->count() }} menu</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('ingredients.show', $ingredient) }}"
                                        class="text-blue-600 hover:text-blue-900 p-1">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('ingredients.edit', $ingredient) }}"
                                        class="text-green-600 hover:text-green-900 p-1">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                    <button onclick="deleteIngredient({{ $ingredient->id }}, '{{ $ingredient->name }}')"
                                        class="text-red-600 hover:text-red-900 p-1">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i data-lucide="package-x" class="w-12 h-12 mx-auto mb-4"></i>
                                    <p class="text-lg">Belum ada bahan baku</p>
                                    <p class="text-sm mt-2">Mulai dengan menambahkan bahan baku pertama Anda</p>
                                    <a href="{{ route('ingredients.create') }}"
                                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                        Tambah Bahan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($ingredients->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $ingredients->links() }}
            </div>
        @endif
    </div>

    <!-- Adjust Stock Modal -->
    <div id="adjustStockModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Sesuaikan Stok</h3>
                    <p class="text-sm text-gray-600 mt-1" id="ingredientName"></p>
                </div>

                <form id="adjustStockForm" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" id="ingredientId" name="ingredient_id">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Penyesuaian</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="relative flex cursor-pointer">
                                <input type="radio" name="type" value="increase" class="sr-only peer" checked>
                                <div
                                    class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                    <i data-lucide="plus" class="w-5 h-5 text-green-600 mx-auto mb-1"></i>
                                    <span class="text-sm font-medium">Tambah</span>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer">
                                <input type="radio" name="type" value="decrease" class="sr-only peer">
                                <div
                                    class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                    <i data-lucide="minus" class="w-5 h-5 text-red-600 mx-auto mb-1"></i>
                                    <span class="text-sm font-medium">Kurangi</span>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer">
                                <input type="radio" name="type" value="set" class="sr-only peer">
                                <div
                                    class="w-full p-3 text-center border rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                    <i data-lucide="edit" class="w-5 h-5 text-blue-600 mx-auto mb-1"></i>
                                    <span class="text-sm font-medium">Set Manual</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Kuantitas</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500" id="currentStock"></p>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan
                            (Opsional)</label>
                        <textarea id="notes" name="notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="hideAdjustStockModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
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
        // Fungsi untuk menampilkan modal adjust stock
        function showAdjustStockModal(ingredientId, ingredientName) {
            fetch(`/ingredients/get/details?id=${ingredientId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('ingredientId').value = ingredientId;
                    document.getElementById('ingredientName').textContent = ingredientName;
                    document.getElementById('currentStock').textContent = `Stok saat ini: ${data.formatted_stock}`;
                    document.getElementById('quantity').placeholder = `Masukkan kuantitas dalam ${data.unit}`;

                    document.getElementById('adjustStockForm').action = `/ingredients/${ingredientId}/adjust-stock`;
                    document.getElementById('adjustStockModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Gagal mengambil data bahan baku');
                });
        }

        // Fungsi untuk menyembunyikan modal adjust stock
        function hideAdjustStockModal() {
            document.getElementById('adjustStockModal').classList.add('hidden');
        }

        // Fungsi untuk menghapus bahan baku
        async function deleteIngredient(ingredientId, ingredientName) {
            // Konfirmasi sebelum menghapus
            const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus bahan "${ingredientName}"?`);

            if (!confirmDelete) {
                return;
            }

            try {
                // Menampilkan loading
                const deleteButton = event.target.closest('button');
                const originalContent = deleteButton.innerHTML;
                deleteButton.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>';
                deleteButton.disabled = true;

                // Mengirim request DELETE
                const response = await fetch(`/ingredients/${ingredientId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Mengembalikan tampilan tombol
                deleteButton.innerHTML = originalContent;
                deleteButton.disabled = false;

                // Mengecek apakah response berhasil
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    // Menampilkan pesan sukses
                    showToast('success', result.message || 'Bahan baku berhasil dihapus');

                    // Menghapus baris dari tabel
                    const row = document.getElementById(`ingredient-row-${ingredientId}`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s ease';
                        row.style.opacity = '0';

                        setTimeout(() => {
                            row.remove();

                            // Cek jika tabel kosong setelah penghapusan
                            const tbody = document.querySelector('tbody');
                            if (tbody && tbody.children.length === 1 && tbody.children[0].querySelector(
                                    '.text-center')) {
                                // Tabel sudah kosong, reload halaman untuk menampilkan pesan empty state
                                window.location.reload();
                            }
                        }, 300);
                    } else {
                        // Jika tidak menemukan row, reload halaman
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    // Menampilkan pesan error
                    showToast('error', result.message || 'Gagal menghapus bahan baku');
                }

            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
            }
        }

        // Fungsi untuk menampilkan toast notification
        function showToast(type, message) {
            // Menghapus toast yang sudah ada
            const existingToast = document.getElementById('toast-notification');
            if (existingToast) {
                existingToast.remove();
            }

            // Membuat elemen toast baru
            const toast = document.createElement('div');
            toast.id = 'toast-notification';
            toast.className =
                `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;

            // Menentukan warna berdasarkan tipe
            let bgColor, borderColor, textColor, icon;

            switch (type) {
                case 'success':
                    bgColor = 'bg-green-50';
                    borderColor = 'border-green-200';
                    textColor = 'text-green-800';
                    icon = 'check-circle';
                    break;
                case 'error':
                    bgColor = 'bg-red-50';
                    borderColor = 'border-red-200';
                    textColor = 'text-red-800';
                    icon = 'alert-circle';
                    break;
                case 'info':
                    bgColor = 'bg-blue-50';
                    borderColor = 'border-blue-200';
                    textColor = 'text-blue-800';
                    icon = 'info';
                    break;
                default:
                    bgColor = 'bg-gray-50';
                    borderColor = 'border-gray-200';
                    textColor = 'text-gray-800';
                    icon = 'info';
            }

            toast.className += ` ${bgColor} ${borderColor} ${textColor} border`;

            // Menambahkan konten ke toast
            toast.innerHTML = `
        <div class="flex items-center">
            <i data-lucide="${icon}" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
        </div>
    `;

            // Menambahkan toast ke body
            document.body.appendChild(toast);

            // Menginisialisasi ikon Lucide
            if (window.lucide) {
                lucide.createIcons();
            }

            // Animasi masuk
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
                toast.classList.add('translate-x-0');
            }, 10);

            // Auto remove setelah 5 detik
            setTimeout(() => {
                toast.classList.remove('translate-x-0');
                toast.classList.add('translate-x-full');

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 5000);
        }

        // Fungsi untuk menampilkan loading
        function showLoading() {
            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'loading-overlay';
            loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            loadingOverlay.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-blue-600 mr-3"></i>
                <span class="text-gray-800">Memproses...</span>
            </div>
        </div>
    `;
            document.body.appendChild(loadingOverlay);

            if (window.lucide) {
                lucide.createIcons();
            }
        }

        // Fungsi untuk menyembunyikan loading
        function hideLoading() {
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }

        // Menutup modal adjust stock saat klik di luar modal
        document.addEventListener('DOMContentLoaded', function() {
            const adjustStockModal = document.getElementById('adjustStockModal');
            if (adjustStockModal) {
                adjustStockModal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        hideAdjustStockModal();
                    }
                });
            }

            // Auto hide alert setelah 5 detik
            const successAlert = document.getElementById('success-alert');
            const errorAlert = document.getElementById('error-alert');

            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.transition = 'opacity 0.5s ease';
                    successAlert.style.opacity = '0';
                    setTimeout(() => {
                        if (successAlert.parentNode) {
                            successAlert.remove();
                        }
                    }, 500);
                }, 5000);
            }

            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.transition = 'opacity 0.5s ease';
                    errorAlert.style.opacity = '0';
                    setTimeout(() => {
                        if (errorAlert.parentNode) {
                            errorAlert.remove();
                        }
                    }, 500);
                }, 5000);
            }
        });
    </script>
@endpush
