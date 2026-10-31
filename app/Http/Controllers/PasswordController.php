<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Tampilkan formulir wajib ganti password (saat login pertama / password sementara).
     */
    public function formWajibGanti()
    {
        $user = Auth::user();

        if (! $user->wajib_ganti_password) {
            return $this->redirectSesuaiRole($user);
        }

        return view('auth.wajib-ganti-password');
    }

    /**
     * Simpan password baru dari halaman wajib ganti password.
     */
    public function simpanWajibGanti(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => $request->password,
            'wajib_ganti_password' => false,
        ]);

        return $this->redirectSesuaiRole($user)
            ->with('success', 'Password berhasil diperbarui. Selamat datang di ITIK!');
    }

    /**
     * Perbarui password dari modal pop-up di halaman profil (Siswa & Guru).
     */
    public function updateProfilPassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:6', 'confirmed', 'different:password_lama'],
        ], [
            'password_lama.required' => 'Password saat ini wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password_baru.different' => 'Password baru harus berbeda dengan password saat ini.',
        ]);

        if (! Hash::check($request->password_lama, $user->password)) {
            return back()
                ->withErrors(['password_lama' => 'Password saat ini tidak sesuai.'])
                ->withInput();
        }

        $user->update([
            'password' => $request->password_baru,
            'wajib_ganti_password' => false,
        ]);

        return back()->with('success', 'Password Anda berhasil diperbarui.');
    }

    /**
     * Helper redirect ke halaman utama masing-masing role.
     */
    private function redirectSesuaiRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'guru') {
            return redirect()->route('materi.index');
        }

        if ($user->role === 'siswa') {
            return redirect()->route('siswa.dashboard');
        }

        return redirect()->route('login');
    }
}

