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
                        ->from('tp_anggota_kelas')
                        ->join('tm_kelas', 'tm_kelas.id_kelas', '=', 'tp_anggota_kelas.id_kelas')
                        ->whereColumn('tp_anggota_kelas.id_user', 'tm_user.id_user')
                        ->where('tm_kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran);
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
