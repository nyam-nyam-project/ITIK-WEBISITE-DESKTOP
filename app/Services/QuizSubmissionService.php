<?php

namespace App\Services;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuizSubmissionService
{
    public function submit(Kuis $kuis, User $student): ?Mengerjakan
    {
        return DB::transaction(function () use ($kuis, $student): ?Mengerjakan {
            $sesi = SesiKuis::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $student->id_user)
                ->whereNull('waktu_selesai')
                ->lockForUpdate()
                ->first();

            if (! $sesi) {
                return null;
            }

            $batasWaktu = $sesi->waktu_mulai
                ->copy()
                ->addMinutes($kuis->alokasi_waktu);

            if (
                $kuis->kategori === 'ulangan' &&
                $kuis->waktu_selesai &&
                $kuis->waktu_selesai->lessThan($batasWaktu)
            ) {
                $batasWaktu = $kuis->waktu_selesai->copy();
            }

            $soal = $kuis->soal()
                ->orderBy('id_soal')
                ->get();

            if ($soal->isEmpty()) {
                abort(422, 'Kuis belum memiliki soal.');
            }

            $draft = DraftJawaban::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $student->id_user)
                ->get()
                ->keyBy('id_soal');

            $soalPilihanGanda = $soal->where('tipe_soal', 'pilihan_ganda');
            $totalBobot = $soalPilihanGanda->sum(
                fn ($item): float => (float) $item->bobot
            );
            $bobotBenar = 0;

            foreach ($soalPilihanGanda as $item) {
                $jawabanSiswa = $draft->get($item->id_soal);

                if (
                    $jawabanSiswa?->jawaban_dipilih &&
                    $item->jawaban &&
                    strtoupper($jawabanSiswa->jawaban_dipilih) === strtoupper($item->jawaban)
                ) {
                    $bobotBenar += (float) $item->bobot;
                }
            }

            $nilai = $totalBobot > 0
                ? round(($bobotBenar / $totalBobot) * 100, 2)
                : null;

            $mengerjakan = Mengerjakan::create([
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $student->id_user,
                'nilai' => $nilai,
                'tanggal' => now()->toDateString(),
                'percobaan_ke' => $sesi->percobaan_ke,
                'status_pengerjaan' => 'final',
                'status_validasi' => $nilai === null
                    ? 'tidak dinilai otomatis'
                    : ($kuis->kkm !== null && $nilai >= $kuis->kkm
                        ? 'lulus kkm otomatis'
                        : 'belum divalidasi'),
                'id_validator' => null,
            ]);

            foreach ($soal as $item) {
                $jawabanSiswa = $draft->get($item->id_soal);

                $mengerjakan->jawabanSiswa()->create([
                    'id_soal' => $item->id_soal,
                    'jawaban_dipilih' => $jawabanSiswa?->jawaban_dipilih,
                    'jawaban_text' => $jawabanSiswa?->jawaban_text,
                ]);
            }

            if ($kuis->kategori === 'remedial') {
                RemedialSiswa::where('id_kuis_remedial', $kuis->id_kuis)
                    ->whereHas('mengerjakanAsal', function ($query) use ($student): void {
                        $query->where('id_user', $student->id_user);
                    })
                    ->where('status', 'ditugaskan')
                    ->update(['status' => 'selesai']);
            }

            $waktuSelesai = now();
            $waktuBerhenti = $waktuSelesai->greaterThan($batasWaktu)
                ? $batasWaktu
                : $waktuSelesai;

            $sesi->update([
                'waktu_selesai' => $waktuSelesai,
                'durasi_detik' => max(
                    0,
                    $waktuBerhenti->getTimestamp() - $sesi->waktu_mulai->getTimestamp()
                ),
            ]);

            DraftJawaban::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $student->id_user)
                ->delete();

            return $mengerjakan;
        });
    }
}
