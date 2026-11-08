<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mengerjakan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuruNilaiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Mengerjakan::query()
            ->with(['kuis:id_kuis,judul,kategori,kkm', 'siswa:id_user,nama_lengkap,nomor_induk,kelas', 'validator:id_user,nama_lengkap'])
            ->where('status_pengerjaan', 'final')
            ->orderBy('status_validasi')
            ->orderByDesc('tanggal');

        if ($request->filled('status_validasi')) {
            $validated = $request->validate([
                'status_validasi' => ['string', 'in:belum divalidasi,sudah divalidasi'],
            ]);
            $query->where('status_validasi', $validated['status_validasi']);
        }

        return response()->json($query->paginate(20));
    }

    public function show(Mengerjakan $mengerjakan): JsonResponse
    {
        return response()->json([
            'data' => $mengerjakan->load([
                'siswa:id_user,nama_lengkap,nomor_induk,kelas',
                'kuis:id_kuis,judul,kategori,kkm',
                'validator:id_user,nama_lengkap',
            ]),
        ]);
    }

    public function validasi(Request $request, Mengerjakan $mengerjakan): JsonResponse
    {
        if ($mengerjakan->status_pengerjaan !== 'final') {
            abort(409, 'Pengerjaan ini belum final.');
        }

        if ($mengerjakan->status_validasi === 'sudah divalidasi') {
            abort(409, 'Nilai ini sudah divalidasi.');
        }

        $mengerjakan->update([
            'status_validasi' => 'sudah divalidasi',
            'id_validator' => $request->user()->id_user,
        ]);

        return response()->json([
            'message' => 'Nilai siswa berhasil divalidasi.',
            'data' => $mengerjakan->fresh()->load('validator:id_user,nama_lengkap'),
        ]);
    }
}
