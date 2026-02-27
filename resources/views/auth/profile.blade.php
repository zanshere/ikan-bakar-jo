{{-- resources/views/auth/profile.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Profil Saya')
@section('page-description', 'Kelola informasi profil Anda')

@section('breadcrumb')
<span>/</span>
<span class="text-gray-700">Profil</span>
@endsection

@section('header-buttons')
<a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
    Kembali ke Dashboard
</a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
        <div class="flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Profile Card & Info --}}
        <div class="lg:col-span-1">
            {{-- Profile Card --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden sticky top-6">
                <div class="px-6 py-8 bg-linear-to-r from-blue-600 to-blue-700 text-center">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-lg">
                        <i data-lucide="user" class="w-12 h-12 text-blue-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-white">{{ $user->name }}</h2>
                    <p class="text-blue-100 text-sm mt-1">{{ $user->email }}</p>
                    <div class="mt-3">
                        @if($user->isOwner())
                            <span class="px-3 py-1 bg-white text-blue-700 rounded-full text-xs font-semibold">Owner</span>
                        @else
                            <span class="px-3 py-1 bg-white text-blue-700 rounded-full text-xs font-semibold">User</span>
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Informasi Akun</h3>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Member Sejak</p>
                                <p class="font-medium text-gray-800">{{ $user->created_at->format('d F Y') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i data-lucide="clock" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Terakhir Diperbarui</p>
                                <p class="font-medium text-gray-800">{{ $user->updated_at->format('d F Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i data-lucide="shield" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Role Akun</p>
                                <p class="font-medium text-gray-800">
                                    @if($user->isOwner())
                                        Owner
                                    @else
                                        User
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($user->email_verified_at)
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status Email</p>
                                <p class="font-medium text-green-600">Terverifikasi</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Forms --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Informasi Dasar Form --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Informasi Dasar
                    </h3>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="p-6">
                    @csrf
                    <input type="hidden" name="form_type" value="basic">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nama --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                                   required>
                            @error('email')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Update Informasi Dasar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Ubah Password Form --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i data-lucide="lock" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Ubah Password
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="p-6">
                    @csrf
                    <input type="hidden" name="form_type" value="password">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Current Password --}}
                        <div class="md:col-span-2">
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password Saat Ini
                            </label>
                            <div class="relative">
                                <input type="password"
                                       id="current_password"
                                       name="current_password"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                                       placeholder="Masukkan password saat ini">
                                <button type="button"
                                        onclick="togglePassword('current_password')"
                                        class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700">
                                    <i data-lucide="eye" class="w-5 h-5" id="eye-current_password"></i>
                                    <i data-lucide="eye-off" class="w-5 h-5 hidden" id="eye-off-current_password"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- New Password --}}
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password Baru
                            </label>
                            <div class="relative">
                                <input type="password"
                                       id="new_password"
                                       name="new_password"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('new_password') border-red-500 @enderror"
                                       placeholder="Minimal 8 karakter">
                                <button type="button"
                                        onclick="togglePassword('new_password')"
                                        class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700">
                                    <i data-lucide="eye" class="w-5 h-5" id="eye-new_password"></i>
                                    <i data-lucide="eye-off" class="w-5 h-5 hidden" id="eye-off-new_password"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm New Password --}}
                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Konfirmasi Password Baru
                            </label>
                            <div class="relative">
                                <input type="password"
                                       id="new_password_confirmation"
                                       name="new_password_confirmation"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Masukkan ulang password baru">
                                <button type="button"
                                        onclick="togglePassword('new_password_confirmation')"
                                        class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700">
                                    <i data-lucide="eye" class="w-5 h-5" id="eye-new_password_confirmation"></i>
                                    <i data-lucide="eye-off" class="w-5 h-5 hidden" id="eye-off-new_password_confirmation"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                            <i data-lucide="key" class="w-4 h-4 mr-2"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- Catatan Keamanan --}}
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <div class="flex items-start">
                    <i data-lucide="shield" class="w-5 h-5 text-blue-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-800">Tips Keamanan</h4>
                        <ul class="text-xs text-blue-700 mt-1 space-y-1 list-disc list-inside">
                            <li>Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol</li>
                            <li>Jangan gunakan password yang sama dengan akun lain</li>
                            <li>Ganti password secara berkala untuk keamanan</li>
                            <li>Pastikan email yang digunakan masih aktif</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(`eye-${fieldId}`);
    const eyeOffIcon = document.getElementById(`eye-off-${fieldId}`);

    if (field.type === 'password') {
        field.type = 'text';
        if (eyeIcon) eyeIcon.classList.add('hidden');
        if (eyeOffIcon) eyeOffIcon.classList.remove('hidden');
    } else {
        field.type = 'password';
        if (eyeIcon) eyeIcon.classList.remove('hidden');
        if (eyeOffIcon) eyeOffIcon.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
