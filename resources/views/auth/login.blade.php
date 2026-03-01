<!-- resources/views/auth/login.blade.php -->
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-linear-to-br from-blue-50 to-cyan-100 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-cyan-400 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-md w-full space-y-8 bg-white/95 backdrop-blur-sm p-8 rounded-2xl shadow-2xl relative z-10">
        <!-- Logo and Header -->
        <div class="text-center">
            <div class="mx-auto h-32 w-32 flex items-center justify-center rounded-full bg-linear-to-br from-blue-500 to-cyan-500 shadow-lg">
                <img src="{{ asset('images/IMG_1120.PNG') }}" alt="Ikan Bakar Jo Logo"
                     class="h-32 w-32 object-contain">
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Selamat Datang
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Masuk ke akun Anda untuk melanjutkan
            </p>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg animate-pulse">
            <div class="flex items-center">
                <i data-lucide="check-circle" class="h-5 w-5 mr-2 text-green-500"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
            <div class="flex items-center mb-2">
                <i data-lucide="alert-circle" class="h-5 w-5 mr-2 text-red-500"></i>
                <p class="text-sm font-medium">Terjadi kesalahan:</p>
            </div>
            <ul class="list-disc list-inside text-sm ml-7">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Login Form -->
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf

            <div class="space-y-5">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Email
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors duration-200"></i>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="appearance-none relative block w-full px-3 py-3 pl-10 border-2 border-gray-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 @error('email') border-red-300 bg-red-50 @enderror"
                               placeholder="nama@email.com"
                               value="{{ old('email') }}">
                        @if(!$errors->has('email'))
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i data-lucide="check-circle" class="h-5 w-5 text-green-500 opacity-0 group-focus-within:opacity-100 transition-opacity duration-200"></i>
                        </div>
                        @endif
                    </div>
                    @error('email')
                    <p class="mt-1 text-sm text-red-600 flex items-center">
                        <i data-lucide="alert-triangle" class="h-4 w-4 mr-1"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors duration-200"></i>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="appearance-none relative block w-full px-3 py-3 pl-10 border-2 border-gray-200 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 @error('password') border-red-300 bg-red-50 @enderror"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center focus:outline-none group">
                            <i data-lucide="eye" id="password-eye-open" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors duration-200"></i>
                            <i data-lucide="eye-off" id="password-eye-closed" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors duration-200 hidden"></i>
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-1 text-sm text-red-600 flex items-center">
                        <i data-lucide="alert-triangle" class="h-4 w-4 mr-1"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-700 cursor-pointer hover:text-gray-900 transition-colors duration-200">
                        Ingat saya
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                        Lupa password?
                    </a>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-linear-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i data-lucide="log-in" class="h-5 w-5 text-blue-300 group-hover:text-blue-200 transition-colors duration-200"></i>
                    </span>
                    Masuk ke Akun
                </button>
            </div>

            <!-- Additional Info -->
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Dengan masuk, Anda menyetujui
                    <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">Syarat & Ketentuan</a>
                    dan
                    <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">Kebijakan Privasi</a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle Password Visibility
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('password-eye-open');
        const eyeClosed = document.getElementById('password-eye-closed');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }

    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // Auto-hide success message after 5 seconds
    const successAlert = document.querySelector('.bg-green-50');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s ease';
            successAlert.style.opacity = '0';
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 500);
        }, 5000);
    }
</script>
@endpush

@push('styles')
<style>
    /* Custom animations */
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Focus styles */
    input:focus + .focus-ring {
        opacity: 1;
    }

    /* Loading state for button (optional) */
    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        top: 50%;
        left: 50%;
        margin-left: -10px;
        margin-top: -10px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Gradient text animation */
    .gradient-text {
        background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
@endpush
