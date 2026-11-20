<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Progress;
use App\Models\RemedialSiswa;
use App\Services\KuisSubmissionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SiswaDashboardController extends Controller
{
    public function __construct(private KuisSubmissionService $submission) {}

    /**
     * Ambil id_mapel yang boleh diakses siswa berdasarkan kelas di tahun aktif.
     * Mengembalikan Collection jika tabel kelas ada, atau null jika tabel kelas belum ada (misal di beberapa tes unit).
     */
    private function mapelSiswa(): ?Collection
    {
        $user = Auth::user();

        if (! Schema::hasTable('tp_anggota_kelas') || ! Schema::hasTable('tp_mapel_kelas')) {
            return null;
        }

        $idKelas = Kelas::kelasAktifSiswa($user);

        if (! $idKelas) {
            return collect();
        }

        return Kelas::mapelUntukSiswa($user);
    }

    public function index()
    {
        Kuis::sinkronisasiStatus();

        $user = Auth::user();

        // Tutup sesi kedaluwarsa supaya nilai hasil auto-submit langsung muncul di dashboard.
        $this->submission->selesaikanMilikSiswa($user->id_user);

        $mapelIds = $this->mapelSiswa();

        /*
         * Tugas remedial yang belum dikerjakan.
         * Query sama dengan SiswaKuisController@index.
         */
        $remedialAktif = RemedialSiswa::with('kuisRemedial')
            ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            })
            ->where('status', 'ditugaskan')
            ->get();

        /*
         * Materi pembelajaran yang aktif untuk kelas siswa.
         */
        $materiBaseQuery = Materi::where('is_aktif', true)
            ->when($mapelIds !== null, fn ($q) => $q->whereIn('id_mapel', $mapelIds));

        $totalMateri = (clone $materiBaseQuery)->count();

        $materiSelesai = Progress::where('id_user', $user->id_user)
            ->where('status', 'selesai')
            ->whereHas('materi', function ($q) use ($mapelIds) {
                $q->where('is_aktif', true)
                    ->when($mapelIds !== null, fn ($sub) => $sub->whereIn('id_mapel', $mapelIds));
            })
            ->count();

        /*
         * Materi terbaru: hanya yang statusnya masih Belum Mulai atau Sedang Belajar
         * (kecualikan materi yang sudah berstatus 'selesai').
         */
        $materiTerbaru = (clone $materiBaseQuery)
            ->whereDoesntHave('progress', function ($q) use ($user) {
                $q->where('id_user', $user->id_user)
                    ->where('status', 'selesai');
            })
            ->with([
                'progress' => function ($query) use ($user) {
                    $query->where('id_user', $user->id_user);
                },
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
         * Kuis terbaru yang terbit, aktif, dan belum dikerjakan oleh siswa ini.
         * Kuis remedial tidak ikut, karena sudah tampil di kartu remedial.
         */
        $idKelas = (Schema::hasTable('tp_anggota_kelas') && Schema::hasTable('tp_kuis_kelas'))
            ? Kelas::kelasAktifSiswa($user)
            : null;

        $kuisQuery = Kuis::where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->where('kategori', '!=', 'remedial')
            ->when($mapelIds !== null, fn ($q) => $q->whereIn('id_mapel', $mapelIds))
            ->when(Schema::hasTable('tp_kuis_kelas'), function ($q) use ($idKelas) {
                if ($idKelas) {
                    $q->whereHas('kelas', fn ($k) => $k->where('tp_kuis_kelas.id_kelas', $idKelas));
                } else {
                    $q->whereRaw('1 = 0');
                }
            });

        $kuisTersedia = (clone $kuisQuery)->count();

        // Kuis yang belum dikerjakan oleh siswa yang sedang login (kecualikan yang sudah dikerjakan/submit)
        $kuisBelumDikerjakanQuery = (clone $kuisQuery)
            ->whereDoesntHave('mengerjakan', function ($q) use ($user) {
                $q->where('id_user', $user->id_user);
            });

        $kuisBelumDikerjakan = (clone $kuisBelumDikerjakanQuery)->count();

        $kuisTerbaru = (clone $kuisBelumDikerjakanQuery)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
         * Rata-rata nilai kuis siswa.
         */
        $rataNilai = Mengerjakan::where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->whereNotNull('nilai')
            ->avg('nilai');

        $remedialBelum = $remedialAktif->count();

        return view('siswa.dashboard', compact(
            'remedialAktif',
            'materiTerbaru',
            'kuisTerbaru',
            'kuisTersedia',
            'kuisBelumDikerjakan',
            'totalMateri',
            'materiSelesai',
            'rataNilai',
            'remedialBelum'
        ));
    }
}
