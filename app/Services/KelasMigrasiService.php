<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KelasMigrasiService
{
    /**
     * Pindahkan sejumlah siswa ke kelas tujuan dalam tahun ajaran yang sama secara massal.
     *
     * @param  array<int, string>  $idSiswa
     * @return array{berhasil: int, dilewati: int}
     *
     * @throws ValidationException
     */
    public static function pindahMassal(array $idSiswa, string $idKelasTujuan, ?string $idKelasAsal = null): array
    {
        return (new static)->pindah($idSiswa, $idKelasTujuan, $idKelasAsal);
    }

    /**
     * Eksekusi pemindahan siswa dengan transaksi database dan row locking.
     *
     * @param  array<int, string>  $idSiswa
     * @return array{berhasil: int, dilewati: int}
     *
     * @throws ValidationException
     */
    public function pindah(array $idSiswa, string $idKelasTujuan, ?string $idKelasAsal = null): array
    {
        $idSiswa = array_values(array_unique(array_filter($idSiswa)));
        if (empty($idSiswa)) {
            return ['berhasil' => 0, 'dilewati' => 0];
        }

        return DB::transaction(function () use ($idSiswa, $idKelasTujuan, $idKelasAsal) {
            $kelasTujuan = Kelas::where('id_kelas', $idKelasTujuan)->lockForUpdate()->first();
            if (! $kelasTujuan) {
                throw ValidationException::withMessages([
                    'id_kelas_tujuan' => 'Kelas tujuan tidak ditemukan.',
                ]);
            }

            if ($idKelasAsal === null) {
                $idKelasAsal = DB::table('tp_anggota_kelas')
                    ->join('tm_kelas', 'tp_anggota_kelas.id_kelas', '=', 'tm_kelas.id_kelas')
                    ->where('tm_kelas.id_tahun_ajaran', $kelasTujuan->id_tahun_ajaran)
                    ->whereIn('tp_anggota_kelas.id_user', $idSiswa)
                    ->where('tp_anggota_kelas.id_kelas', '!=', $idKelasTujuan)
                    ->value('tp_anggota_kelas.id_kelas');
            }

            if (! $idKelasAsal) {
                throw ValidationException::withMessages([
                    'id_kelas_asal' => 'Kelas asal tidak ditemukan atau tidak valid.',
                ]);
            }

            $kelasAsal = Kelas::where('id_kelas', $idKelasAsal)->lockForUpdate()->first();
            if (! $kelasAsal) {
                throw ValidationException::withMessages([
                    'id_kelas_asal' => 'Kelas asal tidak ditemukan.',
                ]);
            }

            // Validasi: kelas tujuan berbeda dari kelas asal
            if ($kelasTujuan->id_kelas === $kelasAsal->id_kelas) {
                throw ValidationException::withMessages([
                    'id_kelas_tujuan' => 'Kelas tujuan harus berbeda dari kelas asal.',
                ]);
            }

            // Validasi: kelas tujuan berada di tahun ajaran yang sama
            if ($kelasTujuan->id_tahun_ajaran !== $kelasAsal->id_tahun_ajaran) {
                throw ValidationException::withMessages([
                    'id_kelas_tujuan' => 'Kelas tujuan harus berada di tahun ajaran yang sama dengan kelas asal.',
                ]);
            }

            // Kunci baris user siswa
            User::whereIn('id_user', $idSiswa)->lockForUpdate()->get();

            // Identifikasi siswa yang terdaftar di kelas asal
            $anggotaAsal = DB::table('tp_anggota_kelas')
                ->where('id_kelas', $idKelasAsal)
                ->whereIn('id_user', $idSiswa)
                ->lockForUpdate()
                ->pluck('id_user')
                ->all();

            $bukanAnggotaAsal = array_diff($idSiswa, $anggotaAsal);

            // Identifikasi siswa yang sudah terdaftar di kelas tujuan
            $sudahDiTujuan = DB::table('tp_anggota_kelas')
                ->where('id_kelas', $idKelasTujuan)
                ->whereIn('id_user', $anggotaAsal)
                ->lockForUpdate()
                ->pluck('id_user')
                ->all();

            $sisanya = array_values(array_diff($anggotaAsal, $sudahDiTujuan));

            // Untuk siswa yang sudah ada di tujuan, hapus baris di kelas asal
            if (! empty($sudahDiTujuan)) {
                DB::table('tp_anggota_kelas')
                    ->where('id_kelas', $idKelasAsal)
                    ->whereIn('id_user', $sudahDiTujuan)
                    ->delete();
            }

            // Untuk sisanya, UPDATE tp_anggota_kelas SET id_kelas = ? WHERE id_kelas = ? AND id_user IN (...)
            $jumlahDiupdate = 0;
            if (! empty($sisanya)) {
                $jumlahDiupdate = DB::table('tp_anggota_kelas')
                    ->where('id_kelas', $idKelasAsal)
                    ->whereIn('id_user', $sisanya)
                    ->update(['id_kelas' => $idKelasTujuan]);
            }

            // Jika user.kelas berbeda dari tingkat kelas tujuan, update juga
            $siswaTerkait = array_merge($sisanya, $sudahDiTujuan);
            if (! empty($siswaTerkait) && ! empty($kelasTujuan->tingkat)) {
                User::whereIn('id_user', $siswaTerkait)
                    ->where(function ($q) use ($kelasTujuan) {
                        $q->whereNull('kelas')
                            ->orWhere('kelas', '!=', $kelasTujuan->tingkat);
                    })
                    ->update(['kelas' => $kelasTujuan->tingkat]);
            }

            return [
                'berhasil' => $jumlahDiupdate,
                'dilewati' => count($sudahDiTujuan) + count($bukanAnggotaAsal),
            ];
        });
    }
}
