<?php

namespace App\Services;

use App\Models\DraftJawaban;
use App\Models\Mengerjakan;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KuisSubmissionService
{
    public function sesiAktif(string $idKuis, string $idUser): ?SesiKuis
    {
        return SesiKuis::with('kuis')
            ->where('id_kuis', $idKuis)
            ->where('id_user', $idUser)
            ->whereNull('waktu_selesai')
            ->latest('id_sesi')
            ->first();
    }

    public function batasWaktu(SesiKuis $sesi): Carbon
    {
        $kuis = $sesi->kuis;
        $batas = $sesi->waktu_mulai->copy()->addMinutes($kuis->alokasi_waktu);

        if ($kuis->kategori === 'ulangan' && $kuis->waktu_selesai && $kuis->waktu_selesai->lt($batas)) {
            $batas = $kuis->waktu_selesai->copy();
        }

        return $batas;
    }

    /*
     | Tutup semua sesi milik siswa yang waktunya sudah lewat.
     | Dipanggil saat siswa membuka halaman, supaya nilai tetap masuk
     | walaupun ia menutup tab tanpa menekan Kumpulkan.
     */
    public function selesaikanMilikSiswa(string $idUser): int
    {
        $jumlah = 0;

        SesiKuis::with('kuis')->where('id_user', $idUser)
            ->whereNull('waktu_selesai')
            ->each(function (SesiKuis $sesi) use (&$jumlah) {
                if (now()->gte($this->batasWaktu($sesi)) && $this->submit($sesi, true)) {
                    $jumlah++;
                }
            });

        return $jumlah;
    }

    public function submit(SesiKuis $sesi, bool $hanyaKedaluwarsa = false): ?Mengerjakan
    {
        return DB::transaction(function () use ($sesi, $hanyaKedaluwarsa) {
            $sesi = SesiKuis::whereKey($sesi->id_sesi)->lockForUpdate()->firstOrFail();

            if ($sesi->waktu_selesai) {
                return null;
            }

            $sekarang = now();
            $batasWaktu = $this->batasWaktu($sesi);

            if ($hanyaKedaluwarsa && $sekarang->lt($batasWaktu)) {
                return null;
            }

            $waktuSelesai = $sekarang->min($batasWaktu);
            $kuis = $sesi->kuis;
            $soal = $kuis->soal()->get();
            $draft = DraftJawaban::where('id_kuis', $sesi->id_kuis)
                ->where('id_user', $sesi->id_user)
                ->get()->keyBy('id_soal');
            $totalBobot = $soal->sum(fn ($item) => (float) $item->bobot);
            $bobotBenar = 0;

            foreach ($soal as $item) {
                $jawaban = $draft->get($item->id_soal)?->jawaban_dipilih;
                if ($jawaban && $item->jawaban && strtoupper($jawaban) === strtoupper($item->jawaban)) {
                    $bobotBenar += (float) $item->bobot;
                }
            }

            $hasil = Mengerjakan::create([
                'id_kuis' => $sesi->id_kuis,
                'id_user' => $sesi->id_user,
                'nilai' => $totalBobot > 0 ? round(($bobotBenar / $totalBobot) * 100, 2) : 0,
                'tanggal' => $waktuSelesai->toDateString(),
                'percobaan_ke' => $sesi->percobaan_ke,
                'status_pengerjaan' => 'final',
                'status_validasi' => 'belum divalidasi',
                'id_validator' => null,
            ]);

            if ($kuis->kategori === 'remedial') {
                RemedialSiswa::where('id_kuis_remedial', $sesi->id_kuis)
                    ->whereHas('mengerjakanAsal', fn ($query) => $query->where('id_user', $sesi->id_user))
                    ->where('status', 'ditugaskan')
                    ->update(['status' => 'selesai']);
            }

            $sesi->update(['waktu_selesai' => $waktuSelesai]);
            DraftJawaban::where('id_kuis', $sesi->id_kuis)
                ->where('id_user', $sesi->id_user)->delete();

            return $hasil;
        });
    }

    public function selesaikanKedaluwarsa(): int
    {
        $jumlah = 0;

        // Sesi pada kuis yang sudah dinonaktifkan guru dibiarkan terbuka:
        // hasilnya tidak muncul di daftar kuis maupun rekap.
        SesiKuis::with('kuis')->whereNull('waktu_selesai')
            ->whereHas('kuis', function ($query) {
                $query->where('is_aktif', true);
            })
            ->chunkById(100, function ($sesiAktif) use (&$jumlah) {
                foreach ($sesiAktif as $sesi) {
                    if (now()->gte($this->batasWaktu($sesi)) && $this->submit($sesi, true)) {
                        $jumlah++;
                    }
                }
            }, 'id_sesi');

        return $jumlah;
    }
}
