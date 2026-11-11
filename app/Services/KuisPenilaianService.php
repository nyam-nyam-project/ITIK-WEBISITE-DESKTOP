<?php

namespace App\Services;

use App\Models\Mengerjakan;
use Illuminate\Support\Collection;

class KuisPenilaianService
{
    public function hitung(Collection $soal, Collection $draft, Collection $nilaiEssay): ?float
    {
        $draft = $draft->keyBy('id_soal');
        $nilaiEssay = $nilaiEssay->keyBy('id_soal');
        $totalBobot = $soal->sum(fn ($item) => (float) $item->bobot);
        $skor = 0.0;

        foreach ($soal as $item) {
            if ($item->tipe === 'essay') {
                $nilai = $nilaiEssay->get($item->id_soal)?->nilai;

                if ($nilai === null) {
                    return null;
                }

                $skor += (float) $nilai;
            } else {
                $jawaban = $draft->get($item->id_soal)?->jawaban_dipilih;

                if ($jawaban && $item->jawaban && strtoupper($jawaban) === strtoupper($item->jawaban)) {
                    $skor += (float) $item->bobot;
                }
            }
        }

        return $totalBobot > 0 ? round(($skor / $totalBobot) * 100, 2) : 0.0;
    }

    public function adaPenilaianTertunda(string $idKuis, ?string $idUser = null): bool
    {
        return Mengerjakan::where('id_kuis', $idKuis)
            ->where('status_pengerjaan', 'final')
            ->where('status_validasi', 'menunggu penilaian')
            ->when($idUser !== null, fn ($query) => $query->where('id_user', $idUser))
            ->exists();
    }
}
