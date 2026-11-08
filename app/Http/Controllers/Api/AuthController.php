<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

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
     * @return array{id_user: string, nomor_induk: ?string, nama_lengkap: string, role: string, kelas: ?string, email: ?string}
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
        ];
    }
}
