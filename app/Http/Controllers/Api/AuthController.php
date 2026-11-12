<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $identifier = $request->input('nomor_induk');

        if (! is_string($identifier) || $identifier === '') {
            $this->throwLoginError('NISN atau NIP wajib diisi.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('nomor_induk', $identifier)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $this->throwLoginError('NISN, NIP, atau password salah.');
        }

        if ($user->role === 'admin') {
            $this->throwLoginError('Akun Administrator silakan masuk melalui halaman login khusus Admin.');
        }

        // if ($user->status !== 'aktif') {
        //     $this->throwLoginError('Akun Anda sudah tidak aktif. Hubungi admin sekolah.');
        // }

        $token = $user->createToken('flutter-mobile');

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userPayload($request->user()),
        ]);
    }

    // public function updateProfile(Request $request): JsonResponse
    // {
    //     /** @var User $user */
    //     $user = $request->user();

    //     $validated = $request->validate([
    //         'nama_lengkap' => ['sometimes', 'required', 'string', 'max:100'],
    //         'email' => [
    //             'sometimes',
    //             'nullable',
    //             'email',
    //             'max:100',
    //             Rule::unique('tm_user', 'email')->ignore($user->id_user, 'id_user'),
    //         ],
    //     ], [
    //         'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
    //         'email.email' => 'Format email tidak valid.',
    //         'email.unique' => 'Email sudah digunakan akun lain.',
    //     ]);

    //     if ($validated === []) {
    //         throw ValidationException::withMessages([
    //             'profile' => ['Isi minimal satu data profil untuk diperbarui.'],
    //         ]);
    //     }

    //     $user->update($validated);

    //     return response()->json([
    //         'message' => 'Profil berhasil diperbarui.',
    //         'data' => $this->userPayload($user->fresh()),
    //     ]);
    // }

    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'different:password_lama',
            ],
        ], [
            'password_lama.required' => 'Password saat ini wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password_baru.different' => 'Password baru harus berbeda dengan password saat ini.',
        ]);

        if (! Hash::check($validated['password_lama'], $user->password)) {
            throw ValidationException::withMessages([
                'password_lama' => ['Password saat ini tidak sesuai.'],
            ]);
        }

        $user->update([
            'password' => $validated['password_baru'],
            'wajib_ganti_password' => false,
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        abort_unless($token, 401, 'Token API tidak ditemukan.');
        $token->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * @return array{id_user: string, nomor_induk: ?string, nama_lengkap: string, role: string, kelas: ?string, email: ?string, wajib_ganti_password: bool, foto_url: ?string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id_user' => $user->id_user,
            'nomor_induk' => $user->nomor_induk,
            'nama_lengkap' => $user->nama_lengkap,
            'role' => $user->role,
            'kelas' => $user->kelas,
            'email' => $user->email,
            'wajib_ganti_password' => $user->wajib_ganti_password,
        ];
    }

    private function throwLoginError(string $message): never
    {
        throw ValidationException::withMessages([
            'nomor_induk' => [$message],
        ]);
    }
}
