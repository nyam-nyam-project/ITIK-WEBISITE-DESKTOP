<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class GrafikGuruService
{
    private const TINGKAT_KESULITAN = ['mudah', 'sedang', 'sulit'];

    /**
     * @return array{
     *     nilai_per_tingkat: array<string, array<int, string|null|float>>,
     *     ketuntasan_per_kuis: array<string, array<int, string|int>>,
     *     progres_materi: array<string, array<int, string|float>>,
     *     pre_post_test: array<int, array{judul_materi: string, nama_siswa: string, nilai_pre: float|null, nilai_post: float|null}>,
     *     statistik: array{jumlahSiswa: int, jumlahMateri: int, jumlahKuis: int, rataNilai: float|null, kuisTerbaru: Collection, materiTerbaru: Collection}
     * }
     */
    public function ambilUntuk(User $guru): array
    {
        $konteks = $this->konteksGuru($guru);

        if ($konteks['penugasan']->isEmpty()) {
            return [
                'nilai_per_tingkat' => $this->grafikNilaiKosong(),
                'ketuntasan_per_kuis' => $this->grafikKetuntasanKosong(),
                'progres_materi' => $this->grafikProgresKosong(),
                'pre_post_test' => [],
                'statistik' => $this->statistikKosong(),
            ];
        }

        $kuis = $this->kuisUntuk($konteks);
        $materi = $this->materiUntuk($konteks);
        [$nilaiTerbaik, $kuisDikerjakan] = $this->pengerjaanUntuk($kuis, $konteks);

        return [
            'nilai_per_tingkat' => $this->grafikNilaiPerTingkat($kuis, $nilaiTerbaik),
            'ketuntasan_per_kuis' => $this->grafikKetuntasanPerKuis($kuis, $nilaiTerbaik, $kuisDikerjakan, $konteks),
            'progres_materi' => $this->grafikProgresMateri($materi, $konteks),
            'pre_post_test' => $this->grafikPrePostTest($materi, $kuis, $nilaiTerbaik, $konteks),
            'statistik' => $this->statistikDashboard($kuis, $materi, $nilaiTerbaik, $konteks),
        ];
    }

    /**
     * @return array{
     *     penugasan: Collection<int, stdClass>,
     *     kelas_ids: Collection<int, string>,
     *     mapel_ids: Collection<int, string>,
     *     tahun_ajaran_ids: Collection<int, string>,
     *     siswa_per_kelas: Collection<string, Collection<int, stdClass>>,
     *     siswa_per_mapel: Collection<string, Collection<int, stdClass>>,
     *     siswa_ids: Collection<int, string>
     * }
     */
    private function konteksGuru(User $guru): array
    {
        $penugasan = DB::table('tr_pengajaran as pengajaran')
            ->join('tm_kelas as kelas', 'kelas.id_kelas', '=', 'pengajaran.id_kelas')
            ->join('tm_tahun_ajaran as tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran', '=', 'kelas.id_tahun_ajaran')
            ->where('pengajaran.id_user', $guru->id_user)
            ->where('kelas.is_aktif', true)
            ->where('tahun_ajaran.is_aktif', true)
            ->select([
                'pengajaran.id_kelas',
                'pengajaran.id_mapel',
                'kelas.nama_kelas',
                'kelas.tingkat',
                'kelas.id_tahun_ajaran',
            ])
            ->get();

        $kelasIds = $penugasan->pluck('id_kelas')->unique()->values();
        $mapelIds = $penugasan->pluck('id_mapel')->unique()->values();
        $tahunAjaranIds = $penugasan->pluck('id_tahun_ajaran')->unique()->values();

        $siswa = $kelasIds->isEmpty()
            ? collect()
            : DB::table('tp_anggota_kelas as anggota')
                ->join('tm_user as siswa', 'siswa.id_user', '=', 'anggota.id_user')
                ->whereIn('anggota.id_kelas', $kelasIds)
                ->where('siswa.role', 'siswa')
                ->select('anggota.id_kelas', 'siswa.id_user', 'siswa.nama_lengkap')
                ->orderBy('siswa.nama_lengkap')
                ->orderBy('siswa.id_user')
                ->get();

        $siswaPerKelas = $siswa->groupBy('id_kelas');
        $siswaPerMapel = $penugasan
            ->groupBy('id_mapel')
            ->map(function (Collection $penugasanMapel) use ($siswa): Collection {
                $kelasMapel = $penugasanMapel->pluck('id_kelas')->unique();

                return $siswa
                    ->filter(fn (stdClass $anggota): bool => $kelasMapel->contains($anggota->id_kelas))
                    ->unique('id_user')
                    ->values();
            });

        return [
            'penugasan' => $penugasan,
            'kelas_ids' => $kelasIds,
            'mapel_ids' => $mapelIds,
            'tahun_ajaran_ids' => $tahunAjaranIds,
            'siswa_per_kelas' => $siswaPerKelas,
            'siswa_per_mapel' => $siswaPerMapel,
            'siswa_ids' => $siswa->pluck('id_user')->unique()->values(),
        ];
    }

    /**
     * @param  array{mapel_ids: Collection<int, string>, tahun_ajaran_ids: Collection<int, string>}  $konteks
     * @return Collection<int, stdClass>
     */
    private function kuisUntuk(array $konteks): Collection
    {
        if ($konteks['mapel_ids']->isEmpty() || $konteks['tahun_ajaran_ids']->isEmpty()) {
            return collect();
        }

        return DB::table('tm_kuis')
            ->whereIn('id_mapel', $konteks['mapel_ids'])
            ->whereIn('id_tahun_ajaran', $konteks['tahun_ajaran_ids'])
            ->select([
                'id_kuis',
                'judul',
                'kategori',
                'tingkat_kesulitan',
                'id_mapel',
                'id_tahun_ajaran',
                'kkm',
                'is_aktif',
                'status_publikasi',
                'alokasi_waktu',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->orderBy('id_kuis')
            ->get();
    }

    /**
     * @param  array{mapel_ids: Collection<int, string>, penugasan: Collection<int, stdClass>}  $konteks
     * @return Collection<int, stdClass>
     */
    private function materiUntuk(array $konteks): Collection
    {
        if ($konteks['mapel_ids']->isEmpty()) {
            return collect();
        }

        return DB::table('tm_materi')
            ->whereIn('id_mapel', $konteks['mapel_ids'])
            ->select(['id_materi', 'judul', 'id_mapel', 'kelas', 'created_at', 'bab'])
            ->orderByDesc('created_at')
            ->orderBy('id_materi')
            ->get()
            ->filter(fn (stdClass $materi): bool => $this->penugasanMateri($materi, $konteks['penugasan'])->isNotEmpty())
            ->values();
    }

    /**
     * @param  Collection<int, stdClass>  $kuis
     * @param  array{siswa_ids: Collection<int, string>, siswa_per_mapel: Collection<string, Collection<int, stdClass>>}  $konteks
     * @return array{0: array<string, array<string, array{nilai: float, nama_siswa: string}>>, 1: array<string, array<string, true>>}
     */
    private function pengerjaanUntuk(Collection $kuis, array $konteks): array
    {
        if ($kuis->isEmpty() || $konteks['siswa_ids']->isEmpty()) {
            return [[], []];
        }

        $kuisById = $kuis->keyBy('id_kuis');
        $siswaDiMapel = $konteks['siswa_per_mapel']->map(
            fn (Collection $siswa): Collection => $siswa->pluck('id_user')->flip()
        );

        $pengerjaan = DB::table('tr_mengerjakan as pengerjaan')
            ->join('tm_user as siswa', 'siswa.id_user', '=', 'pengerjaan.id_user')
            ->whereIn('pengerjaan.id_kuis', $kuis->pluck('id_kuis'))
            ->whereIn('pengerjaan.id_user', $konteks['siswa_ids'])
            ->where('pengerjaan.status_pengerjaan', 'final')
            ->select([
                'pengerjaan.id_mengerjakan',
                'pengerjaan.id_kuis',
                'pengerjaan.id_user',
                'pengerjaan.nilai',
                'siswa.nama_lengkap',
            ])
            ->orderBy('siswa.nama_lengkap')
            ->orderBy('pengerjaan.id_user')
            ->orderBy('pengerjaan.id_mengerjakan')
            ->get();

        $nilaiTerbaik = [];
        $kuisDikerjakan = [];

        foreach ($pengerjaan as $baris) {
            $kuisTerkait = $kuisById->get($baris->id_kuis);
            $siswaMapel = $kuisTerkait ? $siswaDiMapel->get($kuisTerkait->id_mapel) : null;

            if (! $siswaMapel || ! $siswaMapel->has($baris->id_user)) {
                continue;
            }

            $kuisDikerjakan[$baris->id_kuis][$baris->id_user] = true;

            if ($baris->nilai === null) {
                continue;
            }

            $nilai = (float) $baris->nilai;
            $nilaiSebelumnya = $nilaiTerbaik[$baris->id_kuis][$baris->id_user]['nilai'] ?? null;

            if ($nilaiSebelumnya === null || $nilai > $nilaiSebelumnya) {
                $nilaiTerbaik[$baris->id_kuis][$baris->id_user] = [
                    'nilai' => $nilai,
                    'nama_siswa' => $baris->nama_lengkap,
                ];
            }
        }

        return [$nilaiTerbaik, $kuisDikerjakan];
    }

    /**
     * @param  Collection<int, stdClass>  $kuis
     * @param  array<string, array<string, array{nilai: float, nama_siswa: string}>>  $nilaiTerbaik
     * @return array<string, array<int, string|null|float>>
     */
    private function grafikNilaiPerTingkat(Collection $kuis, array $nilaiTerbaik): array
    {
        $nilaiPerTingkat = array_fill_keys(self::TINGKAT_KESULITAN, []);

        foreach ($kuis as $barisKuis) {
            $tingkat = mb_strtolower($barisKuis->tingkat_kesulitan);

            if (! array_key_exists($tingkat, $nilaiPerTingkat)) {
                continue;
            }

            foreach ($nilaiTerbaik[$barisKuis->id_kuis] ?? [] as $nilaiSiswa) {
                $nilaiPerTingkat[$tingkat][] = $nilaiSiswa;
            }
        }

        $rataRata = [];
        $nilaiTertinggi = [];
        $namaSiswaTertinggi = [];
        $nilaiTerendah = [];
        $namaSiswaTerendah = [];

        foreach (self::TINGKAT_KESULITAN as $tingkat) {
            $nilaiSiswa = $nilaiPerTingkat[$tingkat];

            if ($nilaiSiswa === []) {
                $rataRata[] = null;
                $nilaiTertinggi[] = null;
                $namaSiswaTertinggi[] = null;
                $nilaiTerendah[] = null;
                $namaSiswaTerendah[] = null;

                continue;
            }

            $tertinggi = $nilaiSiswa[0];
            $terendah = $nilaiSiswa[0];
            $jumlahNilai = 0;

            foreach ($nilaiSiswa as $barisNilai) {
                $jumlahNilai += $barisNilai['nilai'];

                if ($barisNilai['nilai'] > $tertinggi['nilai']) {
                    $tertinggi = $barisNilai;
                }

                if ($barisNilai['nilai'] < $terendah['nilai']) {
                    $terendah = $barisNilai;
                }
            }

            $rataRata[] = round($jumlahNilai / count($nilaiSiswa), 2);
            $nilaiTertinggi[] = $tertinggi['nilai'];
            $namaSiswaTertinggi[] = $tertinggi['nama_siswa'];
            $nilaiTerendah[] = $terendah['nilai'];
            $namaSiswaTerendah[] = $terendah['nama_siswa'];
        }

        return [
            'labels' => self::TINGKAT_KESULITAN,
            'rata_rata' => $rataRata,
            'nilai_tertinggi' => $nilaiTertinggi,
            'nama_siswa_tertinggi' => $namaSiswaTertinggi,
            'nilai_terendah' => $nilaiTerendah,
            'nama_siswa_terendah' => $namaSiswaTerendah,
        ];
    }

    /**
     * @param  Collection<int, stdClass>  $kuis
     * @param  array<string, array<string, array{nilai: float, nama_siswa: string}>>  $nilaiTerbaik
     * @param  array<string, array<string, true>>  $kuisDikerjakan
     * @param  array{siswa_per_mapel: Collection<string, Collection<int, stdClass>>}  $konteks
     * @return array<string, array<int, string|int>>
     */
    private function grafikKetuntasanPerKuis(
        Collection $kuis,
        array $nilaiTerbaik,
        array $kuisDikerjakan,
        array $konteks
    ): array {
        $labels = [];
        $lulus = [];
        $belumLulus = [];
        $belumMengerjakan = [];

        foreach ($kuis as $barisKuis) {
            if ($barisKuis->kkm === null) {
                continue;
            }

            $labels[] = $barisKuis->judul;
            $jumlahLulus = 0;
            $jumlahBelumLulus = 0;
            $jumlahBelumMengerjakan = 0;
            $siswaMapel = $konteks['siswa_per_mapel']->get($barisKuis->id_mapel, collect());

            foreach ($siswaMapel as $siswa) {
                $nilai = $nilaiTerbaik[$barisKuis->id_kuis][$siswa->id_user]['nilai'] ?? null;

                if ($nilai !== null) {
                    if ($nilai >= (float) $barisKuis->kkm) {
                        $jumlahLulus++;
                    } else {
                        $jumlahBelumLulus++;
                    }

                    continue;
                }

                if (! isset($kuisDikerjakan[$barisKuis->id_kuis][$siswa->id_user])) {
                    $jumlahBelumMengerjakan++;
                }
            }

            $lulus[] = $jumlahLulus;
            $belumLulus[] = $jumlahBelumLulus;
            $belumMengerjakan[] = $jumlahBelumMengerjakan;
        }

        return [
            'labels' => $labels,
            'lulus' => $lulus,
            'belum_lulus' => $belumLulus,
            'belum_mengerjakan' => $belumMengerjakan,
        ];
    }

    /**
     * @param  Collection<int, stdClass>  $materi
     * @param  array{siswa_per_kelas: Collection<string, Collection<int, stdClass>>, penugasan: Collection<int, stdClass>}  $konteks
     * @return array<string, array<int, string|float>>
     */
    private function grafikProgresMateri(Collection $materi, array $konteks): array
    {
        $siswaPerMateri = [];
        $siswaIds = collect();

        foreach ($materi as $barisMateri) {
            $siswaPerMateri[$barisMateri->id_materi] = $this->siswaUntukMateri($barisMateri, $konteks);
            $siswaIds = $siswaIds->merge($siswaPerMateri[$barisMateri->id_materi]->pluck('id_user'));
        }

        $progressSelesai = $materi->isEmpty() || $siswaIds->isEmpty()
            ? collect()
            : DB::table('tr_progress')
                ->whereIn('id_materi', $materi->pluck('id_materi'))
                ->whereIn('id_user', $siswaIds->unique())
                ->where('status', 'selesai')
                ->select('id_materi', 'id_user')
                ->get()
                ->groupBy('id_materi');

        $labels = [];
        $persentase = [];

        foreach ($materi as $barisMateri) {
            $siswa = $siswaPerMateri[$barisMateri->id_materi];
            $selesai = ($progressSelesai->get($barisMateri->id_materi, collect()))
                ->pluck('id_user')
                ->unique()
                ->count();

            $labels[] = $barisMateri->judul;
            $persentase[] = $siswa->isEmpty()
                ? 0.0
                : round(($selesai / $siswa->count()) * 100, 2);
        }

        return [
            'labels' => $labels,
            'persentase' => $persentase,
        ];
    }

    /**
     * @param  Collection<int, stdClass>  $materi
     * @param  Collection<int, stdClass>  $kuis
     * @param  array<string, array<string, array{nilai: float, nama_siswa: string}>>  $nilaiTerbaik
     * @param  array{siswa_per_kelas: Collection<string, Collection<int, stdClass>>, penugasan: Collection<int, stdClass>}  $konteks
     * @return array<int, array{judul_materi: string, nama_siswa: string, nilai_pre: float|null, nilai_post: float|null}>
     */
    private function grafikPrePostTest(
        Collection $materi,
        Collection $kuis,
        array $nilaiTerbaik,
        array $konteks
    ): array {
        if ($materi->isEmpty() || $kuis->isEmpty()) {
            return [];
        }

        $kuisTertaut = DB::table('tp_materi_kuis as relasi')
            ->join('tm_kuis as kuis', 'kuis.id_kuis', '=', 'relasi.id_kuis')
            ->whereIn('relasi.id_materi', $materi->pluck('id_materi'))
            ->whereIn('relasi.id_kuis', $kuis->pluck('id_kuis'))
            ->select('relasi.id_materi', 'kuis.id_kuis', 'kuis.id_mapel', 'kuis.kategori')
            ->get();

        $materiById = $materi->keyBy('id_materi');
        $kuisById = $kuis->keyBy('id_kuis');
        $nilaiPerMateriSiswa = [];

        foreach ($kuisTertaut as $relasi) {
            $barisMateri = $materiById->get($relasi->id_materi);
            $barisKuis = $kuisById->get($relasi->id_kuis);
            $kategori = mb_strtolower(trim($relasi->kategori));

            if (
                ! $barisMateri ||
                ! $barisKuis ||
                $barisMateri->id_mapel !== $relasi->id_mapel ||
                ! in_array($kategori, ['pre-test', 'post-test'], true)
            ) {
                continue;
            }

            $jenisNilai = $kategori === 'pre-test' ? 'nilai_pre' : 'nilai_post';
            $siswaMateri = $this->siswaUntukMateri($barisMateri, $konteks)->pluck('id_user')->flip();

            foreach ($nilaiTerbaik[$relasi->id_kuis] ?? [] as $idSiswa => $nilaiSiswa) {
                if (! $siswaMateri->has($idSiswa)) {
                    continue;
                }

                $nilaiSaatIni = $nilaiPerMateriSiswa[$relasi->id_materi][$idSiswa][$jenisNilai] ?? null;

                if ($nilaiSaatIni === null || $nilaiSiswa['nilai'] > $nilaiSaatIni) {
                    $nilaiPerMateriSiswa[$relasi->id_materi][$idSiswa]['nama_siswa'] = $nilaiSiswa['nama_siswa'];
                    $nilaiPerMateriSiswa[$relasi->id_materi][$idSiswa][$jenisNilai] = $nilaiSiswa['nilai'];
                }
            }
        }

        $hasil = [];

        foreach ($materi as $barisMateri) {
            foreach ($nilaiPerMateriSiswa[$barisMateri->id_materi] ?? [] as $nilaiSiswa) {
                $hasil[] = [
                    'judul_materi' => $barisMateri->judul,
                    'nama_siswa' => $nilaiSiswa['nama_siswa'],
                    'nilai_pre' => $nilaiSiswa['nilai_pre'] ?? null,
                    'nilai_post' => $nilaiSiswa['nilai_post'] ?? null,
                ];
            }
        }

        return $hasil;
    }

    /**
     * @param  Collection<int, stdClass>  $kuis
     * @param  Collection<int, stdClass>  $materi
     * @param  array<string, array<string, array{nilai: float, nama_siswa: string}>>  $nilaiTerbaik
     * @param  array{siswa_ids: Collection<int, string>}  $konteks
     * @return array{jumlahSiswa: int, jumlahMateri: int, jumlahKuis: int, rataNilai: float|null, kuisTerbaru: Collection, materiTerbaru: Collection}
     */
    private function statistikDashboard(Collection $kuis, Collection $materi, array $nilaiTerbaik, array $konteks): array
    {
        $nilai = collect($nilaiTerbaik)
            ->flatMap(fn (array $nilaiKuis): array => array_values($nilaiKuis))
            ->pluck('nilai');

        return [
            'jumlahSiswa' => $konteks['siswa_ids']->count(),
            'jumlahMateri' => $materi->count(),
            'jumlahKuis' => $kuis->where('is_aktif', true)->count(),
            'rataNilai' => $nilai->isEmpty() ? null : round($nilai->avg(), 2),
            'kuisTerbaru' => $kuis
                ->where('is_aktif', true)
                ->sortByDesc('created_at')
                ->take(5)
                ->values(),
            'materiTerbaru' => $materi->take(5)->values(),
        ];
    }

    /**
     * @param  Collection<int, stdClass>  $penugasan
     * @return Collection<int, stdClass>
     */
    private function penugasanMateri(stdClass $materi, Collection $penugasan): Collection
    {
        $penugasanMapel = $penugasan->where('id_mapel', $materi->id_mapel);

        if ($materi->kelas === null || $materi->kelas === '') {
            return $penugasanMapel;
        }

        return $penugasanMapel->filter(
            fn (stdClass $baris): bool => $materi->kelas === $baris->nama_kelas || $materi->kelas === $baris->tingkat
        );
    }

    /**
     * @param  array{siswa_per_kelas: Collection<string, Collection<int, stdClass>>, penugasan: Collection<int, stdClass>}  $konteks
     * @return Collection<int, stdClass>
     */
    private function siswaUntukMateri(stdClass $materi, array $konteks): Collection
    {
        $kelasIds = $this->penugasanMateri($materi, $konteks['penugasan'])
            ->pluck('id_kelas')
            ->unique();

        return $konteks['siswa_per_kelas']
            ->filter(fn (Collection $siswa, string $idKelas): bool => $kelasIds->contains($idKelas))
            ->flatten(1)
            ->unique('id_user')
            ->values();
    }

    /**
     * @return array<string, array<int, string|null|float>>
     */
    private function grafikNilaiKosong(): array
    {
        return [
            'labels' => [],
            'rata_rata' => [],
            'nilai_tertinggi' => [],
            'nama_siswa_tertinggi' => [],
            'nilai_terendah' => [],
            'nama_siswa_terendah' => [],
        ];
    }

    /**
     * @return array<string, array<int, string|int>>
     */
    private function grafikKetuntasanKosong(): array
    {
        return [
            'labels' => [],
            'lulus' => [],
            'belum_lulus' => [],
            'belum_mengerjakan' => [],
        ];
    }

    /**
     * @return array<string, array<int, string|float>>
     */
    private function grafikProgresKosong(): array
    {
        return [
            'labels' => [],
            'persentase' => [],
        ];
    }

    /**
     * @return array{jumlahSiswa: int, jumlahMateri: int, jumlahKuis: int, rataNilai: null, kuisTerbaru: Collection, materiTerbaru: Collection}
     */
    private function statistikKosong(): array
    {
        return [
            'jumlahSiswa' => 0,
            'jumlahMateri' => 0,
            'jumlahKuis' => 0,
            'rataNilai' => null,
            'kuisTerbaru' => collect(),
            'materiTerbaru' => collect(),
        ];
    }
}
