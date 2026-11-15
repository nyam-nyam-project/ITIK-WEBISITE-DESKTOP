<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class DashboardKelasService
{
    private const KATEGORI_KELULUSAN = ['post-test', 'pre-test', 'kuis harian', 'ulangan'];

    /**
     * @return Collection<int, stdClass>
     */
    public function kelasUntukGuru(User $guru): Collection
    {
        return DB::table('tr_pengajaran as pengajaran')
            ->join('tm_kelas as kelas', 'kelas.id_kelas', '=', 'pengajaran.id_kelas')
            ->join('tm_tahun_ajaran as tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran', '=', 'kelas.id_tahun_ajaran')
            ->join('tm_mapel as mapel', 'mapel.id_mapel', '=', 'pengajaran.id_mapel')
            ->where('pengajaran.id_user', $guru->id_user)
            ->where('kelas.is_aktif', true)
            ->where('tahun_ajaran.is_aktif', true)
            ->select([
                'kelas.id_kelas',
                'kelas.nama_kelas',
                'kelas.tingkat',
                'mapel.id_mapel',
                'mapel.nama_mapel',
            ])
            ->distinct()
            ->orderBy('kelas.nama_kelas')
            ->orderBy('mapel.nama_mapel')
            ->get();
    }

    /**
     * @return array{jumlah_siswa: int, rata_rata_nilai: float|null, jumlah_perlu_dibantu: int}
     */
    public function ringkasanKelas(User $guru, string $idKelas, string $idMapel): array
    {
        $context = $this->contextKelas($guru, $idKelas, $idMapel);

        if ($context === null) {
            return $this->ringkasanKosong();
        }

        $data = $this->dataSiswaKelas($context);
        $scores = collect($data['nilai_terbaik'])
            ->flatMap(fn (array $studentScores): array => array_values($studentScores))
            ->pluck('nilai')
            ->filter(fn (?float $score): bool => $score !== null);

        return [
            'jumlah_siswa' => $data['siswa']->count(),
            'rata_rata_nilai' => $scores->isEmpty() ? null : round((float) $scores->avg(), 2),
            'jumlah_perlu_dibantu' => collect($data['daftar_siswa'])
                ->where('status', 'Perlu dibantu')
                ->count(),
        ];
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function daftarSiswa(User $guru, string $idKelas, string $idMapel, ?string $cari = null): Collection
    {
        $context = $this->contextKelas($guru, $idKelas, $idMapel);

        if ($context === null) {
            return collect();
        }

        $students = $this->dataSiswaKelas($context, $cari)['daftar_siswa'];
        $priority = [
            'Perlu dibantu' => 0,
            'Belum mulai' => 1,
            'Lancar' => 2,
        ];

        return collect($students)
            ->sort(function (array $left, array $right) use ($priority): int {
                $statusOrder = $priority[$left['status']] <=> $priority[$right['status']];

                if ($statusOrder !== 0) {
                    return $statusOrder;
                }

                $nameOrder = strcasecmp($left['nama_lengkap'], $right['nama_lengkap']);

                return $nameOrder !== 0
                    ? $nameOrder
                    : strcmp($left['id_user'], $right['id_user']);
            })
            ->values()
            ->map(static fn (array $student): stdClass => (object) $student);
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function kelulusanPerKategori(User $guru, string $idKelas, string $idMapel): Collection
    {
        $context = $this->contextKelas($guru, $idKelas, $idMapel);
        $result = collect(self::KATEGORI_KELULUSAN)->map(static fn (string $category): array => [
            'kategori' => $category,
            'persentase_lulus' => 0.0,
            'jumlah_lulus' => 0,
            'jumlah_dinilai' => 0,
            'jumlah_dikerjakan' => 0,
            'jumlah_peluang' => 0,
        ]);

        if ($context === null) {
            return $result->map(static fn (array $row): stdClass => (object) $row);
        }

        $data = $this->dataSiswaKelas($context);
        $categoryStats = [];

        foreach (self::KATEGORI_KELULUSAN as $category) {
            $categoryStats[$category] = [
                'jumlah_lulus' => 0,
                'jumlah_dinilai' => 0,
                'jumlah_dikerjakan' => 0,
                'jumlah_peluang' => 0,
            ];
        }

        foreach ($data['kuis'] as $quiz) {
            $category = mb_strtolower(trim($quiz->kategori));

            if (array_key_exists($category, $categoryStats)) {
                $categoryStats[$category]['jumlah_peluang'] += $data['siswa']->count();
            }
        }

        foreach ($data['attempts'] as $attempt) {
            $quiz = $data['kuis']->get($attempt->id_kuis);

            if ($quiz === null) {
                continue;
            }

            $category = mb_strtolower(trim($quiz->kategori));

            if (! array_key_exists($category, $categoryStats)) {
                continue;
            }

            $categoryStats[$category]['jumlah_dikerjakan']++;

            if ($attempt->nilai === null || $quiz->kkm === null) {
                continue;
            }

            $categoryStats[$category]['jumlah_dinilai']++;

            if ((float) $attempt->nilai >= (float) $quiz->kkm) {
                $categoryStats[$category]['jumlah_lulus']++;
            }
        }

        return $result
            ->map(function (array $row) use ($categoryStats): stdClass {
                $stats = $categoryStats[$row['kategori']];
                $row['jumlah_lulus'] = $stats['jumlah_lulus'];
                $row['jumlah_dinilai'] = $stats['jumlah_dinilai'];
                $row['jumlah_dikerjakan'] = $stats['jumlah_dikerjakan'];
                $row['jumlah_peluang'] = $stats['jumlah_peluang'];
                $row['persentase_lulus'] = $stats['jumlah_peluang'] === 0
                    ? 0.0
                    : round(($stats['jumlah_lulus'] / $stats['jumlah_peluang']) * 100, 2);

                return (object) $row;
            });
    }

    /**
     * @return array{ditugaskan: int, selesai: int, belum: int}
     */
    public function remedialKelas(User $guru, string $idKelas, string $idMapel): array
    {
        $context = $this->contextKelas($guru, $idKelas, $idMapel);

        if ($context === null) {
            return ['ditugaskan' => 0, 'selesai' => 0, 'belum' => 0];
        }

        $studentIds = $this->siswaAktifUntukKelas($idKelas)->pluck('id_user');

        if ($studentIds->isEmpty()) {
            return ['ditugaskan' => 0, 'selesai' => 0, 'belum' => 0];
        }

        $studentStatuses = DB::table('tr_remedial_siswa as remedial_siswa')
            ->join('tr_mengerjakan as mengerjakan', 'mengerjakan.id_mengerjakan', '=', 'remedial_siswa.id_mengerjakan_asal')
            ->join('tm_kuis as kuis_asal', 'kuis_asal.id_kuis', '=', 'mengerjakan.id_kuis')
            ->whereIn('mengerjakan.id_user', $studentIds)
            ->where('kuis_asal.id_mapel', $idMapel)
            ->where('kuis_asal.id_tahun_ajaran', $context->id_tahun_ajaran)
            ->whereIn('remedial_siswa.status', ['ditugaskan', 'selesai'])
            ->select('mengerjakan.id_user')
            ->selectRaw(
                'MAX(CASE WHEN remedial_siswa.status = ? THEN 1 ELSE 0 END) as ada_belum',
                ['ditugaskan']
            )
            ->selectRaw(
                'MIN(CASE WHEN remedial_siswa.status = ? THEN 1 ELSE 0 END) as semua_selesai',
                ['selesai']
            )
            ->groupBy('mengerjakan.id_user');

        $counts = DB::query()
            ->fromSub($studentStatuses, 'remedial_per_siswa')
            ->selectRaw('COUNT(*) as ditugaskan')
            ->selectRaw('COALESCE(SUM(semua_selesai), 0) as selesai')
            ->selectRaw('COALESCE(SUM(ada_belum), 0) as belum')
            ->first();

        return [
            'ditugaskan' => (int) $counts->ditugaskan,
            'selesai' => (int) $counts->selesai,
            'belum' => (int) $counts->belum,
        ];
    }

    /**
     * @return array{
     *     siswa: stdClass,
     *     jumlah_materi: int,
     *     jumlah_materi_dibaca: int,
     *     kuis: Collection<int, stdClass>,
     *     hasil_kuis: Collection<string, stdClass>
     * }|null
     */
    public function detailSiswa(User $guru, string $idKelas, string $idMapel, string $idSiswa): ?array
    {
        $context = $this->contextKelas($guru, $idKelas, $idMapel);

        if ($context === null) {
            return null;
        }

        $student = DB::table('tp_anggota_kelas as anggota')
            ->join('tm_user as siswa', 'siswa.id_user', '=', 'anggota.id_user')
            ->where('anggota.id_kelas', $idKelas)
            ->where('anggota.id_user', $idSiswa)
            ->where('siswa.role', 'siswa')
            ->select('siswa.id_user', 'siswa.nama_lengkap', 'siswa.nomor_induk')
            ->first();

        if ($student === null) {
            return null;
        }

        $materials = DB::table('tm_materi')
            ->where('id_mapel', $idMapel)
            ->select('id_materi')
            ->get();

        $readMaterialsCount = $materials->isEmpty()
            ? 0
            : DB::table('tr_progress')
                ->where('id_user', $idSiswa)
                ->whereIn('id_materi', $materials->pluck('id_materi'))
                ->distinct()
                ->count('id_materi');

        $quizzes = DB::table('tm_kuis as kuis')
            ->join('tp_kuis_kelas as kk', 'kk.id_kuis', '=', 'kuis.id_kuis')
            ->where('kk.id_kelas', $context->id_kelas)
            ->where('kuis.id_mapel', $idMapel)
            ->where('kuis.id_tahun_ajaran', $context->id_tahun_ajaran)
            ->where('kuis.is_aktif', true)
            ->select(['kuis.id_kuis', 'kuis.judul', 'kuis.kategori', 'kuis.kkm'])
            ->orderBy('kuis.judul')
            ->get();

        $attempts = $quizzes->isEmpty()
            ? collect()
            : DB::table('tr_mengerjakan')
                ->where('id_user', $idSiswa)
                ->whereIn('id_kuis', $quizzes->pluck('id_kuis'))
                ->where('status_pengerjaan', 'final')
                ->groupBy('id_kuis')
                ->select('id_kuis')
                ->selectRaw('MAX(nilai) as nilai')
                ->get()
                ->keyBy('id_kuis');

        return [
            'siswa' => $student,
            'jumlah_materi' => $materials->count(),
            'jumlah_materi_dibaca' => $readMaterialsCount,
            'kuis' => $quizzes,
            'hasil_kuis' => $attempts,
        ];
    }

    private function contextKelas(User $guru, string $idKelas, string $idMapel): ?stdClass
    {
        return DB::table('tr_pengajaran as pengajaran')
            ->join('tm_kelas as kelas', 'kelas.id_kelas', '=', 'pengajaran.id_kelas')
            ->join('tm_tahun_ajaran as tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran', '=', 'kelas.id_tahun_ajaran')
            ->where('pengajaran.id_user', $guru->id_user)
            ->where('pengajaran.id_kelas', $idKelas)
            ->where('pengajaran.id_mapel', $idMapel)
            ->where('kelas.is_aktif', true)
            ->where('tahun_ajaran.is_aktif', true)
            ->select([
                'kelas.id_kelas',
                'kelas.nama_kelas',
                'kelas.tingkat',
                'kelas.id_tahun_ajaran',
                'pengajaran.id_mapel',
            ])
            ->first();
    }

    /**
     * @return array{
     *     siswa: Collection<int, stdClass>,
     *     kuis: Collection<string, stdClass>,
     *     attempts: Collection<int, stdClass>,
     *     nilai_terbaik: array<string, array<string, array{nilai: float|null, id_kuis: string}>>,
     *     daftar_siswa: array<int, array{id_user: string, nama_lengkap: string, nomor_induk: string|null, persentase_materi_dibaca: float, jumlah_kuis_dikerjakan: int, jumlah_kuis_total: int, rata_rata_nilai: float|null, status: string}>
     * }
     */
    private function dataSiswaKelas(stdClass $context, ?string $cari = null): array
    {
        $students = $this->siswaAktifUntukKelas($context->id_kelas, $cari);
        $studentIds = $students->pluck('id_user');
        $quizzes = DB::table('tm_kuis as kuis')
            ->join('tp_kuis_kelas as kk', 'kk.id_kuis', '=', 'kuis.id_kuis')
            ->where('kk.id_kelas', $context->id_kelas)
            ->where('kuis.id_mapel', $context->id_mapel)
            ->where('kuis.id_tahun_ajaran', $context->id_tahun_ajaran)
            ->where('kuis.is_aktif', true)
            ->select(['kuis.id_kuis', 'kuis.kategori', 'kuis.kkm'])
            ->get()
            ->keyBy('id_kuis');

        $attempts = $quizzes->isEmpty() || $studentIds->isEmpty()
            ? collect()
            : DB::table('tr_mengerjakan as mengerjakan')
                ->whereIn('mengerjakan.id_kuis', $quizzes->keys())
                ->whereIn('mengerjakan.id_user', $studentIds)
                ->where('mengerjakan.status_pengerjaan', 'final')
                ->groupBy('mengerjakan.id_kuis', 'mengerjakan.id_user')
                ->select('mengerjakan.id_kuis', 'mengerjakan.id_user')
                ->selectRaw('MAX(mengerjakan.nilai) as nilai')
                ->get();

        $materials = DB::table('tm_materi')
            ->where('id_mapel', $context->id_mapel)
            ->select('id_materi')
            ->get();

        $progress = $materials->isEmpty() || $studentIds->isEmpty()
            ? collect()
            : DB::table('tr_progress')
                ->whereIn('id_materi', $materials->pluck('id_materi'))
                ->whereIn('id_user', $studentIds)
                ->select('id_materi', 'id_user')
                ->get()
                ->groupBy('id_user')
                ->map(static fn (Collection $rows): int => $rows->pluck('id_materi')->unique()->count());

        $attemptsByStudent = $attempts->groupBy('id_user');
        $bestScores = [];
        $rows = [];

        foreach ($students as $student) {
            $studentAttempts = $attemptsByStudent->get($student->id_user, collect());
            $numericScores = [];
            $hasFailedQuiz = false;

            foreach ($studentAttempts as $attempt) {
                $quiz = $quizzes->get($attempt->id_kuis);
                $bestScores[$student->id_user][$attempt->id_kuis] = [
                    'nilai' => $attempt->nilai === null ? null : (float) $attempt->nilai,
                    'id_kuis' => $attempt->id_kuis,
                ];

                if ($attempt->nilai === null) {
                    continue;
                }

                $score = (float) $attempt->nilai;
                $numericScores[] = $score;

                if ($quiz->kkm !== null && $score < (float) $quiz->kkm) {
                    $hasFailedQuiz = true;
                }
            }

            $readCount = (int) ($progress->get($student->id_user) ?? 0);
            $readPercentage = $materials->isEmpty()
                ? 0.0
                : round(($readCount / $materials->count()) * 100, 2);
            $attemptedCount = $studentAttempts->count();
            $status = $attemptedCount === 0
                ? 'Belum mulai'
                : ($hasFailedQuiz || $readPercentage < 50 ? 'Perlu dibantu' : 'Lancar');

            $rows[] = [
                'id_user' => $student->id_user,
                'nama_lengkap' => $student->nama_lengkap,
                'nomor_induk' => $student->nomor_induk,
                'persentase_materi_dibaca' => $readPercentage,
                'jumlah_kuis_dikerjakan' => $attemptedCount,
                'jumlah_kuis_total' => $quizzes->count(),
                'rata_rata_nilai' => $numericScores === [] ? null : round(array_sum($numericScores) / count($numericScores), 2),
                'status' => $status,
            ];
        }

        return [
            'siswa' => $students,
            'kuis' => $quizzes,
            'attempts' => $attempts,
            'nilai_terbaik' => $bestScores,
            'daftar_siswa' => $rows,
        ];
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function siswaAktifUntukKelas(string $idKelas, ?string $cari = null): Collection
    {
        $query = DB::table('tp_anggota_kelas as anggota')
            ->join('tm_user as siswa', 'siswa.id_user', '=', 'anggota.id_user')
            ->where('anggota.id_kelas', $idKelas)
            ->where('siswa.role', 'siswa')
            ->where('siswa.status', 'aktif')
            ->select('siswa.id_user', 'siswa.nama_lengkap', 'siswa.nomor_induk')
            ->orderBy('siswa.nama_lengkap')
            ->orderBy('siswa.id_user');

        if ($cari !== null && $cari !== '') {
            $query->where(function ($query) use ($cari): void {
                $query->where('siswa.nama_lengkap', 'like', '%'.$cari.'%')
                    ->orWhere('siswa.nomor_induk', 'like', '%'.$cari.'%');
            });
        }

        return $query->get();
    }

    /**
     * @return array{jumlah_siswa: int, rata_rata_nilai: null, jumlah_perlu_dibantu: int}
     */
    private function ringkasanKosong(): array
    {
        return [
            'jumlah_siswa' => 0,
            'rata_rata_nilai' => null,
            'jumlah_perlu_dibantu' => 0,
        ];
    }
}
