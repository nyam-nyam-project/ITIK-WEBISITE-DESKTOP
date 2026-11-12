<?php

namespace App\Services;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Progress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MateriProgressService
{
    public function findMateriBelumDibacaUntukPostTest(Kuis $kuis, string $idUser): ?Materi
    {
        return $kuis->materi()
            ->whereDoesntHave('progress', function (Builder $query) use ($idUser): void {
                $query->where('id_user', $idUser);
            })
            ->first();
    }

    public function hasPostTestBelumDikerjakan(Materi $materi, string $idUser): bool
    {
        return $materi->kuis()
            ->where('is_aktif', true)
            ->where('status_publikasi', 'terbit')
            ->whereRaw('LOWER(TRIM(kategori)) = ?', ['post-test'])
            ->whereDoesntHave('mengerjakan', function (Builder $query) use ($idUser): void {
                $query->where('id_user', $idUser)
                    ->where('status_pengerjaan', 'final');
            })
            ->exists();
    }

    public function tandaiSelesaiSetelahPostTest(Kuis $kuis, string $idUser): void
    {
        if (strtolower(trim((string) $kuis->kategori)) !== 'post-test') {
            return;
        }

        foreach ($kuis->materi as $materi) {
            $status = $this->hasPostTestBelumDikerjakan($materi, $idUser)
                ? 'sedang belajar'
                : 'selesai';
            $progress = Progress::firstOrCreate(
                [
                    'id_materi' => $materi->id_materi,
                    'id_user' => $idUser,
                ],
                [
                    'id_progress' => 'P'.Str::upper(Str::random(10)),
                    'status' => $status,
                    'tanggal' => now()->toDateString(),
                ]
            );

            $progress->update([
                'status' => $status,
                'tanggal' => now()->toDateString(),
            ]);
        }
    }
}
