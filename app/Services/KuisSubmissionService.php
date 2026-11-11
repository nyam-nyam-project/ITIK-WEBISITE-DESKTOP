<?php

namespace App\Services;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KuisSubmissionService
{
    public function __construct(private KuisPenilaianService $penilaian)
    {
    }

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

        if ($kuis->waktu_selesai && $kuis->waktu_selesai->lt($batas)) {
            $batas = $kuis->waktu_selesai->copy();
        }

        return $batas;
    }

    /*
     | Tutup semua sesi milik siswa yang waktunya sudah lewat.
     | Dipanggil saat siswa membuka halaman, supaya nilai tetap masuk
     | walaupun ia menutup tab tanpa menekan Kumpulkan.
     | Hanya berlaku untuk kuis yang masih aktif (is_aktif = true).
     */
    public function selesaikanMilikSiswa(string $idUser): int
    {
        $jumlah = 0;

        SesiKuis::with('kuis')->where('id_user', $idUser)
            ->whereNull('waktu_selesai')
            ->whereHas('kuis', function ($query) {
                $query->where('is_aktif', true);
            })
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
            $kuis = Kuis::whereKey($sesi->id_kuis)->lockForUpdate()->firstOrFail();
            $sesi = SesiKuis::whereKey($sesi->id_sesi)->lockForUpdate()->firstOrFail();
            $sesi->setRelation('kuis', $kuis);

            if ($sesi->waktu_selesai) {
                return null;
            }

            $sekarang = now();
            $batasWaktu = $this->batasWaktu($sesi);

            if ($hanyaKedaluwarsa && $sekarang->lt($batasWaktu)) {
                return null;
            }

            $waktuSelesai = $sekarang->min($batasWaktu);

            // Cek apakah data pengerjaan untuk kuis, siswa, dan percobaan ini sudah ada
            // (misal dari sesi lama yang tertinggal atau pernah tersimpan sebelumnya).
            $sudahAda = Mengerjakan::where('id_kuis', $sesi->id_kuis)
                ->where('id_user', $sesi->id_user)
                ->where('percobaan_ke', $sesi->percobaan_ke)
                ->first();

            if ($sudahAda) {
                $sesi->update(['waktu_selesai' => $waktuSelesai]);

                return $sudahAda;
            }

            $soal = $kuis->soal()->get();
            $draft = DraftJawaban::where('id_kuis', $sesi->id_kuis)
                ->where('id_user', $sesi->id_user)
                ->get()->keyBy('id_soal');
            $nilai = $this->penilaian->hitung($soal, $draft, collect());

            $hasil = Mengerjakan::create([
                'id_kuis' => $sesi->id_kuis,
                'id_user' => $sesi->id_user,
                'nilai' => $nilai,
                'tanggal' => $waktuSelesai->toDateString(),
                'percobaan_ke' => $sesi->percobaan_ke,
                'status_pengerjaan' => 'final',
                'status_validasi' => $nilai === null ? 'menunggu penilaian' : 'sudah divalidasi',
                'id_validator' => $nilai === null ? null : ($kuis->id_user ?? null),
            ]);

            foreach ($soal->where('tipe', 'essay') as $item) {
                NilaiEssay::create([
                    'id_mengerjakan' => $hasil->id_mengerjakan,
                    'id_soal' => $item->id_soal,
                    'jawaban_teks' => $draft->get($item->id_soal)?->jawaban_teks,
                    'nilai' => null,
                ]);
            }

            if ($kuis->kategori === 'remedial') {
                RemedialSiswa::where('id_kuis_remedial', $sesi->id_kuis)
                    ->whereHas('mengerjakanAsal', fn ($query) => $query->where('id_user', $sesi->id_user))
                    ->where('status', 'ditugaskan')
                    ->update(['status' => 'selesai']);
            }

            $sesi->update(['waktu_selesai' => $waktuSelesai]);
            // Catatan: DraftJawaban tidak dihapus di sini agar siswa dan guru dapat melihat
            // evaluasi butir soal yang benar dan salah setelah kuis dikumpulkan.
            // Draft akan di-reset saat siswa memulai sesi percobaan baru.

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
