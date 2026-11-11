<?php

namespace App\Services;

use App\Models\RemedialSiswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class RemedialService
{
    /**
     * Ambil daftar tugas remedial untuk siswa.
     */
    public function untukSiswa(string $idUser): Collection|\Illuminate\Support\Collection
    {
        if (! Schema::hasTable('remedial_siswa')) {
            return collect();
        }

        return RemedialSiswa::with([
            'kuisRemedial',
            'mengerjakanAsal.kuis',
        ])
            ->whereHas('mengerjakanAsal', function ($query) use ($idUser) {
                $query->where('id_user', $idUser);
            })
            ->orderByDesc('tanggal_ditugaskan')
            ->get();
    }
}
