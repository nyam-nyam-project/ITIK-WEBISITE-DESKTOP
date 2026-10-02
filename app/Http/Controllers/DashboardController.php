<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahSiswa = DB::table('user')
            ->where('role', 'siswa')
            ->count();

        $jumlahMateri = DB::table('materi')
            ->count();

        $jumlahKuis = DB::table('kuis')
            ->where('is_aktif', 1)
            ->count();

        $jumlahSoal = DB::table('soal')
            ->count();

        $rataNilai = DB::table('mengerjakan')
            ->whereNotNull('nilai')
            ->avg('nilai');

        $kuisTerbaru = DB::table('kuis')
            ->where('is_aktif', 1)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $materiTerbaru = DB::table('materi')
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