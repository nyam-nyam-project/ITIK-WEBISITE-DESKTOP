<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Progress;
use App\Services\MateriProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProgressController extends Controller
{
    /**
     * Ambil id_mapel yang boleh diakses siswa berdasarkan kelas di tahun aktif.
     * Mengembalikan [mapelIds, idKelas|null, tanpaKelas].
     */
    private function mapelSiswa(): array
    {
        $user = Auth::user();

        if (! Schema::hasTable('tp_anggota_kelas') || ! Schema::hasTable('tp_mapel_kelas')) {
            return [Mapel::pluck('id_mapel'), 'DEFAULT', false];
        }

        $idKelas = Kelas::kelasAktifSiswa($user);

        if (! $idKelas) {
            return [collect(), null, true];
        }

        $mapelIds = Kelas::mapelUntukSiswa($user);

        return [$mapelIds, $idKelas, false];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $idMapel = $request->query('mapel');

        [$mapelIds, $idKelas, $tanpaKelas] = $this->mapelSiswa();

        if ($idMapel) {
            if ($tanpaKelas || ! $mapelIds->contains($idMapel)) {
                abort(403, 'Mata pelajaran ini tidak termasuk dalam kelas Anda.');
            }
        }

        // Batasi mapelAktif hanya ke mapel kelas siswa
        $mapelAktif = Mapel::aktif()
            ->withCount('materi')
            ->when(! $tanpaKelas, fn ($q) => $q->whereIn('id_mapel', $mapelIds))
            ->when($tanpaKelas, fn ($q) => $q->whereRaw('1 = 0'))  // kosongkan jika belum punya kelas
            ->orderBy('nama_mapel')
            ->get();

        $mapelTerpilih = null;
        $materi = collect();
        $kuisSelesaiIds = collect();

        if ($idMapel) {
            $mapelTerpilih = Mapel::find($idMapel);

            if ($mapelTerpilih) {
                $query = Materi::where('id_mapel', $idMapel)
                    ->with([
                        'kuis' => function ($q) {
                            $q->where('is_aktif', true)
                                ->where('status_publikasi', 'terbit');
                        },
                    ])
                    ->withCount(['kuis' => function ($q) {
                        $q->where('is_aktif', true)
                            ->where('status_publikasi', 'terbit');
                    }]);

                if (Schema::hasTable('tr_progress')) {
                    $query->with([
                        'progress' => function ($q) use ($user) {
                            $q->where('id_user', $user->id_user);
                        },
                    ]);
                }

                $materi = $query->orderBy('bab')
                    ->orderBy('judul')
                    ->get();

                if ($materi->isNotEmpty() && Schema::hasTable('tr_mengerjakan')) {
                    $semuaKuisIds = $materi->pluck('kuis')->flatten()->pluck('id_kuis')->unique();
                    if ($semuaKuisIds->isNotEmpty()) {
                        $kuisSelesaiIds = Mengerjakan::whereIn('id_kuis', $semuaKuisIds)
                            ->where('id_user', $user->id_user)
                            ->where('status_pengerjaan', 'final')
                            ->pluck('id_kuis')
                            ->flip();
                    }
                }
            }
        }

        return view('siswa.materi.index', compact(
            'mapelAktif',
            'mapelTerpilih',
            'materi',
            'idMapel',
            'tanpaKelas',
            'kuisSelesaiIds',
        ));
    }

    /**
     * Menampilkan materi untuk siswa.
     */
    public function show($idMateri)
    {
        $user = Auth::user();
        $materi = Materi::findOrFail($idMateri);

        // Tolak jika mapel materi di luar mapel kelas siswa
        [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
        if ($tanpaKelas || ! $mapelIds->contains($materi->id_mapel)) {
            abort(403, 'Materi ini tidak termasuk dalam mata pelajaran kelasmu.');
        }

        // Cek apakah ada Pre-test aktif & terbit yang belum diselesaikan siswa
        if (Schema::hasTable('tr_mengerjakan')) {
            $preTestBelumSelesai = $materi->kuis()
                ->where('is_aktif', true)
                ->where('status_publikasi', 'terbit')
                ->whereRaw('LOWER(TRIM(kategori)) = ?', ['pre-test'])
                ->whereDoesntHave('mengerjakan', function ($q) use ($user) {
                    $q->where('id_user', $user->id_user)
                        ->where('status_pengerjaan', 'final');
                })
                ->first();

            if ($preTestBelumSelesai) {
                return redirect()
                    ->route('siswa.materi.index', ['mapel' => $materi->id_mapel])
                    ->with('error', 'Materi ini terkunci. Silakan kerjakan Pre-test "'.$preTestBelumSelesai->judul.'" terlebih dahulu.');
            }
        }

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $user->id_user,
            ],
            [
                'id_progress' => 'P'.Str::upper(Str::random(10)),
                'status' => 'sedang belajar',
                'tanggal' => now()->toDateString(),
            ]
        );

        $pdfUrl = $materi->pdfUrl();

        $kuisTerkait = $materi->kuis()
            ->where('is_aktif', true)
            ->where('status_publikasi', 'terbit')
            ->with(['mengerjakan' => function ($query) use ($user) {
                $query->where('id_user', $user->id_user)
                    ->where('status_pengerjaan', 'final');
            }])
            ->get();

        $postTests = $kuisTerkait->filter(
            fn (Kuis $kuis) => strtolower(trim($kuis->kategori ?? '')) === 'post-test'
        );
        $postTest = $postTests->first();
        $postTestSelesai = $postTests->isNotEmpty()
            && $postTests->every(fn (Kuis $kuis) => $kuis->mengerjakan->isNotEmpty());
        $materiSelesai = $postTests->isNotEmpty()
            ? $postTestSelesai
            : $progress->status === 'selesai';
        $materiProgressDibuka = $progress->exists;

        return view('siswa.materi.show', compact(
            'materi',
            'progress',
            'pdfUrl',
            'kuisTerkait',
            'postTest',
            'postTestSelesai',
            'materiSelesai',
            'materiProgressDibuka'
        ));
    }

    /**
     * Menandai materi selesai.
     */
    public function selesai($idMateri, MateriProgressService $materiProgressService)
    {
        $user = Auth::user();
        $materi = Materi::findOrFail($idMateri);

        // Tolak jika mapel materi di luar mapel kelas siswa
        [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
        if ($tanpaKelas || ! $mapelIds->contains($materi->id_mapel)) {
            abort(403, 'Materi ini tidak termasuk dalam mata pelajaran kelasmu.');
        }

        // Cek apakah ada Pre-test aktif & terbit yang belum diselesaikan
        if (Schema::hasTable('tr_mengerjakan')) {
            $preTestBelumSelesai = $materi->kuis()
                ->where('is_aktif', true)
                ->where('status_publikasi', 'terbit')
                ->whereRaw('LOWER(TRIM(kategori)) = ?', ['pre-test'])
                ->whereDoesntHave('mengerjakan', function ($q) use ($user) {
                    $q->where('id_user', $user->id_user)
                        ->where('status_pengerjaan', 'final');
                })
                ->first();

            if ($preTestBelumSelesai) {
                return redirect()
                    ->route('siswa.materi.index', ['mapel' => $materi->id_mapel])
                    ->with('error', 'Materi ini terkunci. Silakan selesaikan Pre-test terlebih dahulu.');
            }
        }

        if ($materiProgressService->hasPostTestBelumDikerjakan($materi, $user->id_user)) {
            return back()->with('error', 'Selesaikan Post-test terlebih dahulu sebelum menandai materi selesai.');
        }

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $user->id_user,
            ],
            [
                'id_progress' => 'P'.Str::upper(Str::random(10)),
                'tanggal' => now()->toDateString(),
            ]
        );

        $progress->update([
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
        ]);

        return redirect()
            ->route('siswa.materi.show', $materi->id_materi)
            ->with('success', 'Materi berhasil ditandai selesai.');
    }
}
