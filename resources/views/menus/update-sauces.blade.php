{{-- resources/views/menus/update-sauces.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Update Pilihan Saus - ' . $menu->name)
@section('page-description', 'Perbarui pengaturan saus untuk menu ' . $menu->name)

@section('breadcrumb')
<span>/</span>
<a href="{{ route('menus.index') }}" class="text-gray-500 hover:text-gray-700">Menu</a>
<span>/</span>
<a href="{{ route('menus.show', $menu) }}" class="text-gray-500 hover:text-gray-700">{{ $menu->name }}</a>
<span>/</span>
<span class="text-gray-700">Update Saus</span>
@endsection

@section('header-buttons')
<a href="{{ route('menus.show', $menu) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Kembali
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('menus.update-sauces', $menu) }}" method="POST">
            @csrf

            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Informasi Menu</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama Menu</p>
                            <p class="font-medium">{{ $menu->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Harga Dasar</p>
                            <p class="font-medium text-blue-600">{{ $menu->formatted_price }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Pilihan Saus</h3>
                    <p class="text-sm text-gray-500">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        Centang saus yang tersedia untuk menu ini
                    </p>
                </div>

                @if($sauces->isEmpty())
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <i data-lucide="empty" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600">Belum ada data saus.</p>
                    <p class="text-sm text-gray-500 mt-1">Tambahkan saus terlebih dahulu melalui halaman Tambah Menu.</p>
                    <a href="{{ route('menus.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Tambah Saus Baru
                    </a>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($sauces as $sauce)
                    @php
                        $isAttached = isset($attachedSauces[$sauce->id]);
                        $additionalPrice = $isAttached ? $attachedSauces[$sauce->id]->pivot->additional_price : 0;
                        $isDefault = $isAttached ? $attachedSauces[$sauce->id]->pivot->is_default : false;
                    @endphp
                    <div class="border rounded-lg p-4 hover:border-blue-300 transition-colors">
                        <div class="flex items-start space-x-4">
                            <div class="pt-1">
                                <input type="checkbox"
                                       name="sauces[{{ $loop->index }}][id]"
                                       value="{{ $sauce->id }}"
                                       {{ $isAttached ? 'checked' : '' }}
                                       class="sauce-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                       onchange="toggleSauceFields(this, {{ $loop->index }})">
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-800">{{ $sauce->name }}</h4>
                                        <p class="text-sm text-gray-600">Kode: {{ $sauce->code }}</p>
                                        <p class="text-sm text-gray-600 mt-1">Harga: {{ $sauce->formatted_price }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600">Stok: {{ $sauce->formatted_stock }}</p>
                                        <p class="text-xs text-gray-500">Min Stok: {{ $sauce->formatted_min_stock }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-4 sauce-fields" id="fields-{{ $loop->index }}" style="{{ !$isAttached ? 'display: none;' : '' }}">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Tambahan</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500">Rp</span>
                                            </div>
                                            <input type="number"
                                                   name="sauces[{{ $loop->index }}][additional_price]"
                                                   value="{{ $additionalPrice }}"
                                                   min="0"
                                                   step="1000"
                                                   class="pl-12 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                                   {{ !$isAttached ? 'disabled' : '' }}>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Biaya tambahan untuk saus ini</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Saus Default</label>
                                        <div class="flex items-center h-10">
                                            <input type="radio"
                                                   name="default_sauce"
                                                   value="{{ $sauce->id }}"
                                                   {{ $isDefault ? 'checked' : '' }}
                                                   class="default-radio rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                   {{ !$isAttached ? 'disabled' : '' }}
                                                   onchange="setDefaultSauce(this, {{ $loop->index }})">
                                            <span class="ml-2 text-sm text-gray-600">Jadikan default</span>
                                        </div>
                                        <input type="hidden"
                                               name="sauces[{{ $loop->index }}][is_default]"
                                               id="default-{{ $sauce->id }}"
                                               value="{{ $isDefault ? '1' : '0' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('menus.show', $menu) }}"
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSauceFields(checkbox, index) {
    const fields = document.getElementById('fields-' + index);
    const priceInput = document.querySelector(`input[name="sauces[${index}][additional_price]"]`);
    const defaultRadio = document.querySelector(`input[type="radio"][value="${checkbox.value}"]`);
    const defaultHidden = document.getElementById('default-' + checkbox.value);

    if (checkbox.checked) {
        fields.style.display = 'grid';
        priceInput.disabled = false;
        defaultRadio.disabled = false;
    } else {
        fields.style.display = 'none';
        priceInput.disabled = true;
        defaultRadio.disabled = true;
        defaultRadio.checked = false;
        defaultHidden.value = '0';
    }
}

function setDefaultSauce(radio, index) {
    // Uncheck all other radio buttons and update hidden inputs
    document.querySelectorAll('.default-radio').forEach(r => {
        const hidden = document.getElementById('default-' + r.value);
        if (hidden) {
            hidden.value = r.checked ? '1' : '0';
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sauce-checkbox').forEach(checkbox => {
        if (checkbox.checked) {
            const match = checkbox.name.match(/\d+/);
            if (match) {
                toggleSauceFields(checkbox, parseInt(match[0]));
            }
        }
    });

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
