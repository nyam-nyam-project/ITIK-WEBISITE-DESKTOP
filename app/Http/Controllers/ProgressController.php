<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProgressController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $materi = Materi::with([
            'progress' => function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            }
        ])
        ->orderBy('id_materi')
        ->get();

        return view('siswa.materi.index', compact('materi'));
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

        return view('siswa.materi.show', compact(
            'materi',
            'progress'
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
