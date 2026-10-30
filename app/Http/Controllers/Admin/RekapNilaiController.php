<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapNilaiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => ['nullable', 'string', 'exists:tahun_ajaran,id_tahun_ajaran'],
            'mapel' => ['nullable', 'string', 'exists:mapel,id_mapel'],
            'kuis' => ['nullable', 'string', 'exists:kuis,id_kuis'],
        ]);

        $daftarTahun = TahunAjaran::orderByDesc('nama')->get();
        $idTahun = $request->input('tahun_ajaran')
            ?: optional(TahunAjaran::aktif())->id_tahun_ajaran;

        $daftarMapel = Mapel::orderBy('nama_mapel')->get();
        $idMapel = $request->input('mapel');

        $daftarKuis = Kuis::query()
            ->when($idTahun, fn ($q) => $q->where('id_tahun_ajaran', $idTahun))
            ->when($idMapel, fn ($q) => $q->where('id_mapel', $idMapel))
            ->orderBy('judul')
            ->get();

        $idKuis = $request->input('kuis');
        $kuisTerpilih = $idKuis ? $daftarKuis->firstWhere('id_kuis', $idKuis) : null;
        if ($idKuis && ! $kuisTerpilih) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'kuis' => 'Kuis tidak sesuai dengan filter tahun ajaran atau mata pelajaran.',
            ]);
        }

        $daftarKelas = Kelas::with('tahunAjaran')
            ->when($idTahun, fn ($q) => $q->where('id_tahun_ajaran', $idTahun))
            ->orderBy('nama_kelas')
            ->get();

        $rekap = $daftarKelas->map(
            fn (Kelas $kelas) => $this->rekapKelas($kelas, $kuisTerpilih, $idMapel, $idTahun)
        );

        return view('admin.rekap.index', compact(
            'rekap',
            'daftarTahun',
            'daftarMapel',
            'daftarKuis',
            'idTahun',
            'idMapel',
            'idKuis',
            'kuisTerpilih'
        ));
    }

    /**
     * Satu siswa dihitung satu nilai per kuis (nilai tertinggi dari semua
     * percobaan), supaya siswa yang mengulang kuis tidak terhitung berkali-kali.
     */
    private function rekapKelas(
        Kelas $kelas,
        ?Kuis $kuis,
        ?string $idMapel,
        ?string $idTahun
    ): array {
        $kkm = $kuis?->kkm ?? 75;

        $nilaiTerbaik = DB::table('mengerjakan')
            ->join('kuis', 'kuis.id_kuis', '=', 'mengerjakan.id_kuis')
            ->whereNotNull('mengerjakan.nilai')
            ->where('mengerjakan.status_pengerjaan', 'final')
            ->when($kuis, fn ($q) => $q->where('mengerjakan.id_kuis', $kuis->id_kuis))
            ->when($idMapel, fn ($q) => $q->where('kuis.id_mapel', $idMapel))
            ->when($idTahun, fn ($q) => $q->where('kuis.id_tahun_ajaran', $idTahun))
            ->groupBy('mengerjakan.id_user', 'mengerjakan.id_kuis')
            ->select('mengerjakan.id_user', 'mengerjakan.id_kuis', DB::raw('MAX(mengerjakan.nilai) AS nilai'));

        $nilaiPerSiswa = DB::table('anggota_kelas')
            ->join('user', 'user.id_user', '=', 'anggota_kelas.id_user')
            ->joinSub($nilaiTerbaik, 'nilai_terbaik', function ($join) {
                $join->on('nilai_terbaik.id_user', '=', 'user.id_user');
            })
            ->where('anggota_kelas.id_kelas', $kelas->id_kelas)
            ->where('user.role', 'siswa')
            ->groupBy('user.id_user', 'user.nama_lengkap', 'user.nomor_induk')
            ->select(
                'user.id_user',
                'user.nama_lengkap',
                'user.nomor_induk',
                DB::raw('AVG(nilai_terbaik.nilai) AS nilai_akhir'),
                DB::raw('COUNT(nilai_terbaik.id_kuis) AS jumlah_kuis')
            )
            ->get();

        $jumlahAnggota = DB::table('anggota_kelas')
            ->where('id_kelas', $kelas->id_kelas)
            ->count();

        if ($nilaiPerSiswa->isEmpty()) {
            return [
                'kelas' => $kelas,
                'kkm' => $kkm,
                'jumlah_anggota' => $jumlahAnggota,
                'jumlah_dinilai' => 0,
                'belum_dinilai' => $jumlahAnggota,
                'rata_rata' => null,
                'tertinggi' => null,
                'terendah' => null,
                'siswa_tertinggi' => collect(),
                'siswa_terendah' => collect(),
                'jumlah_kuat' => 0,
                'jumlah_lemah' => 0,
                'siswa' => collect(),
            ];
        }

        $nilai = $nilaiPerSiswa->map(fn ($baris) => round((float) $baris->nilai_akhir, 2));
        $tertinggi = $nilai->max();
        $terendah = $nilai->min();

        $nilaiPerSiswa = $nilaiPerSiswa->map(function ($baris) {
            $baris->nilai_akhir = round((float) $baris->nilai_akhir, 2);

            return $baris;
        })->sortByDesc('nilai_akhir')->values();

        return [
            'kelas' => $kelas,
            'kkm' => (float) $kkm,
            'jumlah_anggota' => $jumlahAnggota,
            'jumlah_dinilai' => $nilaiPerSiswa->count(),
            'belum_dinilai' => max(0, $jumlahAnggota - $nilaiPerSiswa->count()),
            'rata_rata' => round($nilai->avg(), 2),
            'tertinggi' => $tertinggi,
            'terendah' => $terendah,
            'siswa_tertinggi' => $nilaiPerSiswa->where('nilai_akhir', $tertinggi)->values(),
            'siswa_terendah' => $nilaiPerSiswa->where('nilai_akhir', $terendah)->values(),
            'jumlah_kuat' => $nilaiPerSiswa->where('nilai_akhir', '>=', (float) $kkm)->count(),
            'jumlah_lemah' => $nilaiPerSiswa->where('nilai_akhir', '<', (float) $kkm)->count(),
            'siswa' => $nilaiPerSiswa,
        ];
    }
}
