<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tahunAktif = TahunAjaran::aktif();

        $jumlahSiswaAktif = User::siswa()->aktif()->count();
        $jumlahSiswaNonaktif = User::siswa()->where('status', 'nonaktif')->count();
        $jumlahGuruAktif = User::guru()->aktif()->count();
        $jumlahGuruNonaktif = User::guru()->where('status', 'nonaktif')->count();
        $jumlahMapel = Mapel::aktif()->count();

        $kelasTahunAktif = $tahunAktif
            ? Kelas::where('id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
                ->withCount('anggota')
                ->orderBy('nama_kelas')
                ->get()
            : collect();

        $siswaTanpaKelas = $tahunAktif
            ? User::siswa()
                ->aktif()
                ->whereNotExists(function ($query) use ($tahunAktif) {
                    $query->select(DB::raw(1))
                        ->from('anggota_kelas')
                        ->join('kelas', 'kelas.id_kelas', '=', 'anggota_kelas.id_kelas')
                        ->whereColumn('anggota_kelas.id_user', 'user.id_user')
                        ->where('kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran);
                })
                ->count()
            : 0;

        return view('admin.dashboard', compact(
            'tahunAktif',
            'jumlahSiswaAktif',
            'jumlahSiswaNonaktif',
            'jumlahGuruAktif',
            'jumlahGuruNonaktif',
            'jumlahMapel',
            'kelasTahunAktif',
            'siswaTanpaKelas'
        ));
    }
}
