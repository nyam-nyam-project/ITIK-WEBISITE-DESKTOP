<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if (! Schema::hasTable('anggota_kelas') || ! Schema::hasTable('mapel_kelas')) {
            return [Mapel::pluck('id_mapel'), 'DEFAULT', false];
        }

        $idKelas = \App\Models\Kelas::kelasAktifSiswa($user);

        if (!$idKelas) {
            return [collect(), null, true];
        }

        $mapelIds = \App\Models\Kelas::mapelUntukSiswa($user);

        return [$mapelIds, $idKelas, false];
    }

    public function index(Request $request)
    {
        $user    = Auth::user();
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
            ->when(!$tanpaKelas, fn($q) => $q->whereIn('id_mapel', $mapelIds))
            ->when($tanpaKelas, fn($q) => $q->whereRaw('1 = 0'))  // kosongkan jika belum punya kelas
            ->orderBy('nama_mapel')
            ->get();

        $mapelTerpilih = null;
        $materi        = collect();

        if ($idMapel) {
            $mapelTerpilih = Mapel::find($idMapel);

            if ($mapelTerpilih) {
                $query = Materi::where('id_mapel', $idMapel)
                    ->withCount(['kuis' => function ($q) {
                        $q->where('is_aktif', true)
                            ->where('status_publikasi', 'terbit');
                    }]);

                if (Schema::hasTable('progress')) {
                    $query->with([
                        'progress' => function ($q) use ($user) {
                            $q->where('id_user', $user->id_user);
                        }
                    ]);
                }

                $materi = $query->orderBy('bab')
                    ->orderBy('judul')
                    ->get();
            }
        }

        return view('siswa.materi.index', compact(
            'mapelAktif',
            'mapelTerpilih',
            'materi',
            'idMapel',
            'tanpaKelas',
        ));
    }
    /**
     * Menampilkan materi untuk siswa.
     */
    public function show($idMateri)
    {
        $user   = Auth::user();
        $materi = Materi::findOrFail($idMateri);

        // Tolak jika mapel materi di luar mapel kelas siswa
        [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
        if ($tanpaKelas || !$mapelIds->contains($materi->id_mapel)) {
            abort(403, 'Materi ini tidak termasuk dalam mata pelajaran kelasmu.');
        }

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user'   => $user->id_user,
            ],
            [
                'id_progress' => 'P' . Str::upper(Str::random(10)),
                'status'      => 'sedang belajar',
                'tanggal'     => now()->toDateString(),
            ]
        );

        $pdfUrl = $materi->pdfUrl();

        $kuisTerkait = $materi->kuis()
            ->where('is_aktif', true)
            ->where('status_publikasi', 'terbit')
            ->get();

        return view('siswa.materi.show', compact(
            'materi',
            'progress',
            'pdfUrl',
            'kuisTerkait'
        ));
    }

    /**
     * Menandai materi selesai.
     */
    public function selesai($idMateri)
    {
        $user   = Auth::user();
        $materi = Materi::findOrFail($idMateri);

        // Tolak jika mapel materi di luar mapel kelas siswa
        [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
        if ($tanpaKelas || !$mapelIds->contains($materi->id_mapel)) {
            abort(403, 'Materi ini tidak termasuk dalam mata pelajaran kelasmu.');
        }

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user'   => $user->id_user,
            ],
            [
                'id_progress' => 'P' . Str::upper(Str::random(10)),
                'tanggal'     => now()->toDateString(),
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
