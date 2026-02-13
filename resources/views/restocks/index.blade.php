<!-- resources/views/restocks/index.blade.php -->
@extends('layouts.app')

@section('page-title', 'Manajemen Restock')
@section('page-description', 'Kelola restock bahan baku')

@section('breadcrumb')
<span>/</span>
<span class="text-gray-700">Restock</span>
@endsection

@section('header-buttons')
<div class="flex space-x-2">
    <a href="{{ route('restocks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700">
        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
        Restock Baru
    </a>
    <button onclick="printMonthlyReport()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700">
        <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
        Laporan Bulanan
    </button>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <!-- Filters -->
    <div class="p-6 border-b">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua PIC</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                       placeholder="ID restock atau nama bahan...">
            </div>

            <div class="md:col-span-4 flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                    Filter
                </button>
                <a href="{{ route('restocks.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="p-6 border-b bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                <p class="text-sm text-gray-500">Total Transaksi</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalTransactions }}</p>
            </div>

            <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                <p class="text-sm text-gray-500">Total Pengeluaran</p>
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalRestocks, 0, ',', '.') }}</p>
            </div>

            <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                <p class="text-sm text-gray-500">Rata-rata per Transaksi</p>
                <p class="text-2xl font-bold text-blue-600">
                    Rp {{ number_format($totalTransactions > 0 ? $totalRestocks / $totalTransactions : 0, 0, ',', '.') }}
                </p>
            </div>

            <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                <p class="text-sm text-gray-500">Restock Hari Ini</p>
                <p class="text-2xl font-bold text-purple-600">
                    {{ \App\Models\Restock::whereDate('date', today())->count() }}
                </p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($restocks as $restock)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 shrink-0 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="truck" class="w-5 h-5 text-orange-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">#{{ str_pad($restock->id, 6, '0', STR_PAD_LEFT) }}</div>
                                @if($restock->notes)
                                <div class="text-xs text-gray-500">{{ Str::limit($restock->notes, 30) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $restock->date->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $restock->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <span class="text-sm text-gray-900">{{ $restock->user->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $restock->items->count() }} bahan</div>
                        <div class="text-xs text-gray-500">{{ $restock->items->sum('quantity') }} {{ $restock->items->first()?->ingredient?->unit ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-lg font-bold text-red-600">{{ $restock->formatted_total }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('restocks.show', $restock) }}"
                               class="text-blue-600 hover:text-blue-900 p-1">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('restocks.destroy', $restock) }}" method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi restock ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-1">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-400">
                            <i data-lucide="truck" class="w-12 h-12 mx-auto mb-4"></i>
                            <p class="text-lg">Belum ada transaksi restock</p>
                            <p class="text-sm mt-2">Mulai dengan membuat transaksi restock pertama Anda</p>
                            <a href="{{ route('restocks.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                                Restock Baru
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($restocks->hasPages())
    <div class="px-6 py-4 border-t">
        {{ $restocks->links() }}
    </div>
    @endif
</div>

<!-- Monthly Report Modal -->
<div id="monthlyReportModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Laporan Bulanan</h3>
                <p class="text-sm text-gray-600 mt-1">Pilih bulan untuk melihat laporan</p>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select id="reportYear" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            @for($year = date('Y'); $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select id="reportMonth" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            @for($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}" {{ $month == date('m') ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                            </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div id="reportResult" class="hidden">
                    <!-- Report content will be loaded here -->
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideMonthlyReportModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="button" onclick="loadMonthlyReport()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                        Tampilkan Laporan
                    </button>
                    <button type="button" onclick="printReport()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i data-lucide="printer" class="w-4 h-4 inline mr-1"></i>
                        Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Simple debugging
console.log('Restocks page loaded');

function printMonthlyReport() {
    document.getElementById('monthlyReportModal').classList.remove('hidden');
}

function hideMonthlyReportModal() {
    document.getElementById('monthlyReportModal').classList.add('hidden');
    // Reset report result
    const reportResult = document.getElementById('reportResult');
    reportResult.classList.add('hidden');
    reportResult.innerHTML = '';
}

function showLoading() {
    const reportResult = document.getElementById('reportResult');
    reportResult.classList.remove('hidden');
    reportResult.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
            <span class="ml-2 text-gray-600">Memuat laporan...</span>
        </div>
    `;
}

function loadMonthlyReport() {
    const year = document.getElementById('reportYear').value;
    const month = document.getElementById('reportMonth').value;

    if (!year || !month) {
        alert('Silakan pilih tahun dan bulan');
        return;
    }

    showLoading();

    // PERBAIKAN UTAMA: Gunakan URL yang sudah dipastikan benar
    // URL: /restocks/monthly-report (bukan /restocks/monthly-report)
    const url = `/restocks/monthly-report?year=${year}&month=${month}`;
    console.log('Fetching URL:', url);

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response URL:', response.url);

        if (!response.ok) {
            // Jika 404, berarti route tidak ditemukan
            if (response.status === 404) {
                throw new Error('Route tidak ditemukan. Silakan cek routes/web.php');
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (!data.success) {
            throw new Error(data.message || 'Gagal memuat laporan');
        }

        hideLoading();

        const reportResult = document.getElementById('reportResult');
        reportResult.classList.remove('hidden');

        let itemsHtml = '';
        if (data.ingredients_breakdown && Object.keys(data.ingredients_breakdown).length > 0) {
            itemsHtml = `
                <div class="mt-4">
                    <h4 class="font-medium text-gray-700 mb-2">Rincian Bahan yang Direstock:</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-2 text-left">Bahan</th>
                                    <th class="px-3 py-2 text-left">Qty</th>
                                    <th class="px-3 py-2 text-left">Rata-rata Harga</th>
                                    <th class="px-3 py-2 text-left">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            for (const [ingredientName, details] of Object.entries(data.ingredients_breakdown)) {
                itemsHtml += `
                    <tr>
                        <td class="px-3 py-2">${ingredientName}</td>
                        <td class="px-3 py-2">${details.quantity ? details.quantity.toFixed(2) : '0'} ${details.unit || ''}</td>
                        <td class="px-3 py-2">Rp ${details.average_price ? details.average_price.toLocaleString('id-ID') : '0'}</td>
                        <td class="px-3 py-2">Rp ${details.total ? details.total.toLocaleString('id-ID') : '0'}</td>
                    </tr>
                `;
            }

            itemsHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        reportResult.innerHTML = `
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Pengeluaran</p>
                        <p class="text-2xl font-bold text-red-600">${data.formatted_total_restocks || 'Rp 0'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Transaksi</p>
                        <p class="text-2xl font-bold text-blue-600">${data.total_transactions || 0}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Item Direstock</p>
                        <p class="text-xl font-semibold text-gray-800">${data.total_items || 0}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Periode</p>
                        <p class="text-xl font-semibold text-gray-800">
                            ${data.month_name || ''} ${data.year || ''}
                        </p>
                    </div>
                </div>
                ${itemsHtml}
            </div>
        `;
    })
    .catch(error => {
        hideLoading();
        console.error('Fetch error:', error);

        const reportResult = document.getElementById('reportResult');
        reportResult.classList.remove('hidden');
        reportResult.innerHTML = `
            <div class="bg-red-50 p-4 rounded-lg text-red-600">
                <p class="font-semibold">Error</p>
                <p class="text-sm mt-1">Gagal memuat laporan: ${error.message}</p>
                <p class="text-xs mt-2">URL: /restocks/monthly-report?year=${year}&month=${month}</p>
                <p class="text-xs mt-1">Pastikan route sudah didaftarkan di routes/web.php</p>
            </div>
        `;

        alert('Gagal memuat laporan: ' + error.message);
    });
}

function printReport() {
    const year = document.getElementById('reportYear').value;
    const month = document.getElementById('reportMonth').value;

    if (!year || !month) {
        alert('Silakan pilih tahun dan bulan terlebih dahulu');
        return;
    }

    // URL untuk cetak
    const printUrl = `/restocks/monthly-report/print?year=${year}&month=${month}`;
    console.log('Print URL:', printUrl);
    window.open(printUrl, '_blank');
}

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('monthlyReportModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideMonthlyReportModal();
            }
        });
    }
});
</script>
@endpush
