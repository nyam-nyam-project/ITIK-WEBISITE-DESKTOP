<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahSiswa = DB::table('users')
            ->where('role', 'siswa')
            ->count();

        $jumlahMateri = DB::table('materis')
            ->count();

        $jumlahKuis = DB::table('kuis')
            ->where('is_aktif', 1)
            ->count();

        $jumlahSoal = DB::table('soals')
            ->count();

        $rataNilai = DB::table('mengerjakans')
            ->whereNotNull('nilai')
            ->avg('nilai');

        $kuisTerbaru = DB::table('kuis')
            ->where('is_aktif', 1)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $materiTerbaru = DB::table('materis')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('guru.dashboard', compact(
            'jumlahSiswa',
            'jumlahMateri',
            'jumlahKuis',
            'jumlahSoal',
            'rataNilai',
            'kuisTerbaru',
            'materiTerbaru'
        ));
    }
}
