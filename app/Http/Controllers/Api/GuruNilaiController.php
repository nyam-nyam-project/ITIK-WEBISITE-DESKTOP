<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mengerjakan;
use App\Models\SesiKuis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuruNilaiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Mengerjakan::query()
            ->where('status_pengerjaan', 'final')
            ->where(function ($query): void {
                $query->whereNull('status_validasi')
                    ->orWhere('status_validasi', '!=', 'sudah divalidasi');
            })
            ->whereNotNull('nilai')
            ->whereHas('kuis', function ($query): void {
                $query->whereNotNull('kkm')
                    ->whereColumn('tm_kuis.kkm', '<=', 'tr_mengerjakan.nilai');
            })
            ->update([
                'status_validasi' => 'sudah divalidasi',
                'id_validator' => null,
            ]);

        $query = Mengerjakan::query()
            ->with([
                'kuis:id_kuis,judul,kategori,kkm',
                'siswa:id_user,nama_lengkap,nomor_induk,kelas',
                'validator:id_user,nama_lengkap',
                'remedialSiswa.kuisRemedial:id_kuis,judul,deskripsi,alokasi_waktu,kkm,status_publikasi,is_aktif',
            ])
            ->where('status_pengerjaan', 'final')
            ->orderBy('status_validasi')
            ->orderByDesc('tanggal');

        if ($request->filled('status_validasi')) {
            $validated = $request->validate([
                'status_validasi' => ['string', 'in:belum divalidasi,sudah divalidasi'],
            ]);
            $query->where('status_validasi', $validated['status_validasi']);
        }

        $nilai = $query->paginate(20);
        $hasilPadaHalaman = $nilai->getCollection();

        if ($hasilPadaHalaman->isNotEmpty()) {
            $sesiKuis = SesiKuis::query()
                ->where(function ($query) use ($hasilPadaHalaman): void {
                    foreach ($hasilPadaHalaman as $hasil) {
                        $query->orWhere(function ($query) use ($hasil): void {
                            $query->where('id_kuis', $hasil->id_kuis)
                                ->where('id_user', $hasil->id_user)
                                ->where('percobaan_ke', $hasil->percobaan_ke);
                        });
                    }
                })
                ->get(['id_kuis', 'id_user', 'percobaan_ke', 'waktu_mulai', 'waktu_selesai'])
                ->keyBy(fn (SesiKuis $sesi): string => implode('|', [
                    $sesi->id_kuis,
                    $sesi->id_user,
                    $sesi->percobaan_ke,
                ]));

            $hasilPadaHalaman->each(function (Mengerjakan $hasil) use ($sesiKuis): void {
                $key = implode('|', [
                    $hasil->id_kuis,
                    $hasil->id_user,
                    $hasil->percobaan_ke,
                ]);
                $sesi = $sesiKuis->get($key);
                $durasi = $sesi?->waktu_mulai && $sesi->waktu_selesai
                    ? max(
                        0,
                        $sesi->waktu_selesai->getTimestamp()
                            - $sesi->waktu_mulai->getTimestamp()
                    )
                    : null;

                $hasil->setAttribute('durasi_detik', $durasi);
            });
        }

        return response()->json($nilai);
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

        $mengerjakan->loadMissing('kuis:id_kuis,kkm');
        if (
            $mengerjakan->nilai !== null
            && $mengerjakan->kuis?->kkm !== null
            && (float) $mengerjakan->nilai >= (float) $mengerjakan->kuis->kkm
        ) {
            abort(409, 'Nilai sudah mencapai KKM dan otomatis dinyatakan lulus.');
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
