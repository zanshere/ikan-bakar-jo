<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // Ambil credentials
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Coba login dengan credentials
        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session untuk keamanan
            $request->session()->regenerate();

            // Log untuk debugging (hapus di production)
            Log::info('User logged in successfully', [
                'email' => $request->email,
                'user_id' => Auth::id()
            ]);

            // Redirect ke halaman yang dimaksud atau dashboard
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name);
        }

        // Login gagal - log untuk debugging
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        // Kembalikan dengan error
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $userName = Auth::user()->name ?? 'User';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout. Sampai jumpa, ' . $userName . '!');
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ], [
            'name.required' => 'Nama harus diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'current_password.required_with' => 'Password saat ini harus diisi jika ingin mengubah password',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update data user
        $user->name = $request->name;
        $user->email = $request->email;

        // Update password jika ada
        if ($request->filled('new_password')) {
            // Cek password saat ini
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah'])
                    ->withInput();
            }

            // Update password baru
            $user->password = Hash::make($request->new_password);
        }

        // Simpan perubahan
        $user->save();

        return redirect()->route('profile')
            ->with('success', 'Profil berhasil diperbarui');
    }
}
