<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pengajaran;
use App\Models\Mengerjakan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RekapNilaiGuruService
{
    /**
     * Mengembalikan koleksi rekap nilai terbaik per siswa per kuis,
     * hanya dari mata pelajaran yang diajarkan oleh guru tersebut.
     *
     * Setiap item adalah objek dengan properti:
     *   - siswa      : User (id_user, nama_lengkap, nomor_induk)
     *   - kuis       : Kuis (id_kuis, judul, kategori, kkm)
     *   - nilai_terbaik : float|null
     *   - percobaan  : int (jumlah percobaan final)
     *   - tanggal    : string|null (tanggal percobaan terbaik)
     *
     * @param  User         $guru
     * @param  string|null  $idKelas  ID kelas untuk filter; null = semua kelas
     * @return Collection
     */
    public function rekapUntukGuru(User $guru, ?string $idKelas): Collection
    {
        // 1. Ambil id_mapel dari pengajaran guru
        $mapelIds = Pengajaran::where('id_user', $guru->id_user)->pluck('id_mapel');

        if ($mapelIds->isEmpty()) {
            return collect();
        }

        // 2. Ambil id_siswa dari kelas (jika filter kelas diberikan)
        $siswaIds = null;
        if ($idKelas) {
            $siswaIds = DB::table('anggota_kelas')
                ->where('id_kelas', $idKelas)
                ->pluck('id_user');

            if ($siswaIds->isEmpty()) {
                return collect();
            }
        }

        // 3. Query mengerjakan: hanya status final, hanya mapel milik guru
        $query = Mengerjakan::with(['siswa', 'kuis'])
            ->where('status_pengerjaan', 'final')
            ->whereHas('kuis', function ($q) use ($mapelIds) {
                $q->whereIn('id_mapel', $mapelIds);
            });

        if ($siswaIds !== null) {
            $query->whereIn('id_user', $siswaIds);
        }

        $semua = $query->get();

        // 4. Kelompokkan per siswa + kuis, ambil percobaan dengan nilai tertinggi
        return $semua
            ->groupBy(fn($m) => $m->id_user . '___' . $m->id_kuis)
            ->map(function (Collection $rows) {
                $terbaik = $rows->sortByDesc('nilai')->first();

                return (object) [
                    'siswa'         => $terbaik->siswa,
                    'kuis'          => $terbaik->kuis,
                    'nilai_terbaik' => $terbaik->nilai,
                    'percobaan'     => $rows->count(),
                    'tanggal'       => $terbaik->tanggal,
                ];
            })
            ->values()
            ->sortBy([
                fn($a, $b) => strcmp(
                    $a->siswa?->nama_lengkap ?? '',
                    $b->siswa?->nama_lengkap ?? ''
                ),
                fn($a, $b) => strcmp(
                    $a->kuis?->judul ?? '',
                    $b->kuis?->judul ?? ''
                ),
            ])
            ->values();
    }
}
