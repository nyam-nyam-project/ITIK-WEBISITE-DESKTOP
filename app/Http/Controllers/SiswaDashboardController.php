<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Progress;
use App\Models\RemedialSiswa;
use Illuminate\Support\Facades\Auth;

class SiswaDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

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
         * Materi terbaru + progress milik siswa ini.
         * Relasi progress difilter seperti ProgressController@index.
         */
        $materiTerbaru = Materi::with([
            'progress' => function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            }
        ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
         * Kuis terbaru yang terbit dan aktif.
         * Kuis remedial tidak ikut, karena sudah tampil
         * di kartu remedial.
         */
        $kuisQuery = Kuis::where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->where('kategori', '!=', 'remedial');

        $kuisTersedia = (clone $kuisQuery)->count();

        $kuisTerbaru = (clone $kuisQuery)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
         * Statistik ringkas.
         */
        $totalMateri = Materi::count();

        $materiSelesai = Progress::where('id_user', $user->id_user)
            ->where('status', 'selesai')
            ->count();

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
            'totalMateri',
            'materiSelesai',
            'rataNilai',
            'remedialBelum'
        ));
    }
}