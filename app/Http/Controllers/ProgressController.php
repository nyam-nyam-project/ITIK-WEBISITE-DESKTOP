<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $idMapel = $request->query('mapel');

        $mapelAktif = Mapel::aktif()
            ->withCount('materi')
            ->orderBy('nama_mapel')
            ->get();

        $mapelTerpilih = null;
        $materi = collect();

        if ($idMapel) {
            $mapelTerpilih = Mapel::find($idMapel);
            if ($mapelTerpilih) {
                $query = Materi::where('id_mapel', $idMapel)
                    ->withCount(['kuis' => function ($q) {
                        $q->where('is_aktif', true)
                            ->where('status_publikasi', 'terbit');
                    }]);

                if (\Illuminate\Support\Facades\Schema::hasTable('progress')) {
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

        return view('siswa.materi.index', compact('mapelAktif', 'mapelTerpilih', 'materi', 'idMapel'));
    }
    /**
     * Menampilkan materi untuk siswa.
     */
    public function show($idMateri)
    {
        $user = Auth::user();

        $materi = Materi::findOrFail($idMateri);

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $user->id_user,
            ],
            [
                'id_progress' => 'P' . Str::upper(Str::random(10)),
                'status' => 'sedang belajar',
                'tanggal' => now()->toDateString(),
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
        $user = Auth::user();

        $materi = Materi::findOrFail($idMateri);

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $user->id_user,
            ],
            [
                'id_progress' => 'P' . Str::upper(Str::random(10)),
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
