<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mengerjakan;
use App\Models\RemedialSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GuruNilaiController extends Controller
{
    public function index(): JsonResponse
    {
        $hasil = Mengerjakan::query()
            ->with(['kuis', 'siswa', 'validator'])
            ->where('status_pengerjaan', 'final')
            ->orderBy('status_validasi')
            ->orderByDesc('tanggal')
            ->paginate(15);

        return response()->json([
            'data' => $hasil->items(),
            'meta' => [
                'current_page' => $hasil->currentPage(),
                'last_page' => $hasil->lastPage(),
                'per_page' => $hasil->perPage(),
                'total' => $hasil->total(),
            ],
        ]);
    }

    public function show(string $mengerjakan): JsonResponse
    {
        $hasil = Mengerjakan::with([
            'siswa',
            'kuis',
            'validator',
            'jawabanSiswa.soal',
        ])->findOrFail($mengerjakan);

        $remedial = null;
        if ($hasil->kuis?->kategori === 'remedial') {
            $remedial = RemedialSiswa::query()
                ->with('mengerjakanAsal.kuis')
                ->where('id_kuis_remedial', $hasil->id_kuis)
                ->whereHas(
                    'mengerjakanAsal',
                    fn ($query) => $query->where('id_user', $hasil->id_user),
                )
                ->first();
        }

        return response()->json([
            'data' => $hasil,
            'remedial' => $remedial,
        ]);
    }

    public function validateScore(Request $request, string $mengerjakan): JsonResponse
    {
        $hasil = Mengerjakan::findOrFail($mengerjakan);

        if ($hasil->nilai === null) {
            throw ValidationException::withMessages([
                'nilai' => 'Jawaban essay belum memiliki nilai untuk divalidasi.',
            ]);
        }
        if ($hasil->status_validasi === 'sudah divalidasi') {
            abort(409, 'Nilai ini sudah divalidasi.');
        }
        if ($hasil->status_validasi === 'lulus kkm otomatis') {
            abort(409, 'Nilai ini sudah lulus KKM secara otomatis.');
        }
        if ($hasil->status_pengerjaan !== 'final') {
            abort(409, 'Pengerjaan ini belum selesai.');
        }

        $hasil->update([
            'status_validasi' => 'sudah divalidasi',
            'id_validator' => $request->user()->id_user,
        ]);

        return response()->json([
            'data' => $hasil->refresh()->load(['kuis', 'siswa', 'validator']),
            'message' => 'Nilai siswa berhasil divalidasi.',
        ]);
    }
}
