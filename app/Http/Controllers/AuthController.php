<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectSesuaiRole(Auth::user());
        }

        return view('auth.login');
    }

    public function showAdminLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $identifier = $request->input('nomor_induk')
            ?? $request->input('login');

        if (! $identifier) {
            return back()
                ->withErrors([
                    'nomor_induk' => 'NISN atau NIP wajib diisi.',
                    'login' => 'NISN atau NIP wajib diisi.',
                ])
                ->withInput();
        }

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password wajib diisi.',
        ]);

        // Siswa dan guru login menggunakan NISN/NIP (nomor_induk)
        $user = User::where('nomor_induk', $identifier)->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors([
                    'nomor_induk' => 'NISN, NIP, atau password salah.',
                    'login' => 'NISN, NIP, atau password salah.',
                ])
                ->withInput();
        }

        // Administrator wajib login melalui portal khusus admin
        if ($user->role === 'admin') {
            return back()
                ->withErrors([
                    'nomor_induk' => 'Akun Administrator silakan masuk melalui halaman login khusus Admin.',
                    'login' => 'Akun Administrator silakan masuk melalui halaman login khusus Admin.',
                ])
                ->withInput();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'nomor_induk' => 'Akun Anda sudah tidak aktif. Hubungi admin sekolah.',
                    'login' => 'Akun Anda sudah tidak aktif. Hubungi admin sekolah.',
                ])
                ->onlyInput('nomor_induk', 'login');
        }

        $request->session()->put('tampilkan_splash_login', true);

        if ($user->wajib_ganti_password) {
            return redirect()->route('password.wajib');
        }

        return $this->redirectSesuaiRole($user);
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['required', 'string'],
        ], [
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $identifier = $credentials['login'] ?? $credentials['email'] ?? null;

        if (! $identifier) {
            return back()
                ->withErrors([
                    'login' => 'Username atau email admin wajib diisi.',
                    'email' => 'Username atau email admin wajib diisi.',
                ])
                ->withInput();
        }

        $user = User::query()
            ->where('role', 'admin')
            ->where('nomor_induk', $identifier)
            ->first()
            ?? User::query()
                ->where('role', 'admin')
                ->where('email', $identifier)
                ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'login' => 'Username, email, atau password administrator salah.',
                    'email' => 'Email atau password administrator salah.',
                ])
                ->onlyInput('login', 'email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'login' => 'Akun administrator Anda sudah tidak aktif. Hubungi pengelola sistem.',
                    'email' => 'Akun administrator Anda sudah tidak aktif. Hubungi pengelola sistem.',
                ])
                ->onlyInput('login', 'email');
        }

        $request->session()->put('tampilkan_splash_login', true);

        if ($user->wajib_ganti_password) {
            return redirect()->route('password.wajib');
        }

        return redirect()->route('admin.dashboard');
    }

    private function redirectSesuaiRole(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'guru') {
            return redirect()->route('dashboard');
        }

        if ($user->role === 'siswa') {
            return redirect()->route('siswa.dashboard');
        }

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
