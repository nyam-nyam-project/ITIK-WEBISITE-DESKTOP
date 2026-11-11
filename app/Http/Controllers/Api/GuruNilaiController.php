<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mengerjakan;
use App\Models\Soal;
use App\Services\KuisEssayGradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuruNilaiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Mengerjakan::query()
            ->dikelolaOlehGuru($request->user())
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

    public function show(Request $request, Mengerjakan $mengerjakan): JsonResponse
    {
        abort_unless($mengerjakan->dikelolaOleh($request->user()), 403);

        return response()->json([
            'data' => $mengerjakan->load([
                'siswa:id_user,nama_lengkap,nomor_induk,kelas',
                'kuis:id_kuis,judul,kategori,kkm',
                'validator:id_user,nama_lengkap',
            ]),
        ]);
    }

    public function nilaiEssay(
        Request $request,
        Mengerjakan $mengerjakan,
        Soal $soal,
        KuisEssayGradingService $penilaian
    ): JsonResponse {
        $hasil = $penilaian->nilaiEssay($mengerjakan, $soal, $request->user(), $request->all());

        return response()->json([
            'message' => 'Penilaian essay berhasil disimpan.',
            'data' => $hasil->load('validator:id_user,nama_lengkap'),
        ]);
    }

    public function validasi(Request $request, Mengerjakan $mengerjakan): JsonResponse
    {
        abort_unless($mengerjakan->dikelolaOleh($request->user()), 403);

        if ($mengerjakan->status_pengerjaan !== 'final') {
            abort(409, 'Pengerjaan ini belum final.');
        }

        if ($mengerjakan->status_validasi !== 'belum divalidasi') {
            abort(409, 'Nilai ini tidak menunggu validasi.');
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
