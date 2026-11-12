<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        Kuis::sinkronisasiStatus();

        $jumlahSiswa = DB::table('tm_user')
            ->where('role', 'siswa')
            ->count();

        $jumlahMateri = DB::table('tm_materi')
            ->count();

        $jumlahKuis = DB::table('tm_kuis')
            ->where('is_aktif', 1)
            ->count();

        $rataNilai = DB::table('tr_mengerjakan')
            ->whereNotNull('nilai')
            ->avg('nilai');

        $kuisTerbaru = DB::table('tm_kuis')
            ->where('is_aktif', 1)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $materiTerbaru = DB::table('tm_materi')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('guru.dashboard', compact(
            'jumlahSiswa',
            'jumlahMateri',
            'jumlahKuis',
            'rataNilai',
            'kuisTerbaru',
            'materiTerbaru'
        ));
    }
}
