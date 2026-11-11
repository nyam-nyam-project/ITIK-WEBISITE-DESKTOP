<?php

namespace App\Services;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class KuisEssayGradingService
{
    public function __construct(private KuisPenilaianService $penilaian)
    {
    }

    public function nilaiEssay(Mengerjakan $pengerjaan, Soal $soal, User $guru, array $input): Mengerjakan
    {
        return DB::transaction(function () use ($pengerjaan, $soal, $guru, $input) {
            $pengerjaan = Mengerjakan::whereKey($pengerjaan->getKey())->lockForUpdate()->firstOrFail();
            $kuis = Kuis::whereKey($pengerjaan->id_kuis)->lockForUpdate()->firstOrFail();

            abort_unless($pengerjaan->dikelolaOleh($guru), 403);

            if ($pengerjaan->status_pengerjaan !== 'final' || $pengerjaan->status_validasi !== 'menunggu penilaian') {
                throw ValidationException::withMessages([
                    'nilai' => 'Percobaan ini tidak sedang menunggu penilaian essay.',
                ]);
            }

            $soal = $kuis->soal()
                ->whereKey($soal->getKey())
                ->where('tipe', 'essay')
                ->firstOrFail();

            $validated = Validator::make($input, [
                'nilai' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.$soal->bobot],
                'catatan_guru' => ['nullable', 'string', 'max:10000'],
            ])->validate();

            $nilaiEssay = NilaiEssay::where('id_mengerjakan', $pengerjaan->id_mengerjakan)
                ->where('id_soal', $soal->id_soal)
                ->lockForUpdate()
                ->firstOrFail();

            $nilaiEssay->update([
                'nilai' => $validated['nilai'],
                'catatan_guru' => $validated['catatan_guru'] ?? null,
                'id_penilai' => $guru->id_user,
                'dinilai_at' => now(),
            ]);

            $nilai = $this->penilaian->hitung(
                $kuis->soal()->get(),
                DraftJawaban::where('id_kuis', $kuis->id_kuis)
                    ->where('id_user', $pengerjaan->id_user)
                    ->get(),
                NilaiEssay::where('id_mengerjakan', $pengerjaan->id_mengerjakan)->get()
            );

            if ($nilai !== null) {
                $pengerjaan->update([
                    'nilai' => $nilai,
                    'status_validasi' => 'sudah divalidasi',
                    'id_validator' => $guru->id_user,
                ]);
            }

            return $pengerjaan->fresh();
        });
    }
}