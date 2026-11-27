<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MateriKelasService
{
    /**
     * @return Collection<int, string>
     */
    public function mapelIdsUntukGuru(User $guru): Collection
    {
        $tahunAjaranAktif = TahunAjaran::aktif();

        if (! $tahunAjaranAktif || ! Schema::hasTable('tr_pengajaran') || ! Schema::hasTable('tm_kelas')) {
            return collect();
        }

        return DB::table('tr_pengajaran')
            ->join('tm_kelas', 'tr_pengajaran.id_kelas', '=', 'tm_kelas.id_kelas')
            ->where('tr_pengajaran.id_user', $guru->id_user)
            ->where('tm_kelas.id_tahun_ajaran', $tahunAjaranAktif->id_tahun_ajaran)
            ->where('tm_kelas.is_aktif', true)
            ->distinct()
            ->pluck('tr_pengajaran.id_mapel');
    }

    /**
     * @return Collection<int, Kelas>
     */
    public function kelasUntukGuru(User $guru, ?string $idMapel): Collection
    {
        $tahunAjaranAktif = TahunAjaran::aktif();

        if (! $idMapel || ! $tahunAjaranAktif || ! Schema::hasTable('tr_pengajaran')) {
            return collect();
        }

        return Kelas::query()
            ->where('is_aktif', true)
            ->where('id_tahun_ajaran', $tahunAjaranAktif->id_tahun_ajaran)
            ->whereHas('pengajaran', fn (Builder $query) => $query
                ->where('id_user', $guru->id_user)
                ->where('id_mapel', $idMapel))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get(['id_kelas', 'nama_kelas', 'tingkat']);
    }

    /**
     * @param  array<int, string>  $idKelas
     */
    public function pastikanBabUnik(
        ?string $bab,
        string $idMapel,
        array $idKelas,
        ?Materi $materi = null,
    ): void {
        if ($bab === null || $bab === '') {
            return;
        }

        $duplikat = Materi::query()
            ->where('id_mapel', $idMapel)
            ->where('bab', $bab)
            ->whereHas('kelasTerkait', fn (Builder $query) => $query
                ->whereIn('tm_kelas.id_kelas', $idKelas))
            ->when($materi, fn (Builder $query) => $query
                ->where('id_materi', '!=', $materi->id_materi))
            ->exists();

        if ($duplikat) {
            throw ValidationException::withMessages([
                'bab' => ['Bab tersebut sudah digunakan untuk mata pelajaran dan salah satu kelas yang dipilih.'],
            ]);
        }
    }
}
