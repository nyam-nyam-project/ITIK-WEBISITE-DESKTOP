<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\User;
use App\Services\DashboardKelasService;
use App\Support\MengaturPaginasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use stdClass;

class DashboardController extends Controller
{
    use MengaturPaginasi;

    public function __construct(
        private Request $request,
        private DashboardKelasService $dashboardKelasService
    ) {}

    public function index(): View
    {
        Kuis::sinkronisasiStatus();

        $guru = $this->request->user();

        if (! $guru instanceof User) {
            abort(403);
        }

        $filters = $this->request->validate([
            'kelas' => ['sometimes', 'nullable', 'string', 'max:50'],
            'mapel' => ['sometimes', 'nullable', 'string', 'max:50'],
            'cari' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);
        $kelasUntukGuru = $this->dashboardKelasService->kelasUntukGuru($guru);
        $kelasDipilih = $this->pilihPengajaran(
            $kelasUntukGuru,
            $filters['kelas'] ?? null,
            $filters['mapel'] ?? null
        );

        if ($kelasDipilih === null) {
            return view('guru.dashboard', [
                'kelasUntukGuru' => $kelasUntukGuru,
                'kelasDipilih' => null,
            ]);
        }

        $cari = trim($filters['cari'] ?? '');
        $idKelas = $kelasDipilih->id_kelas;
        $idMapel = $kelasDipilih->id_mapel;
        $semuaSiswa = $this->dashboardKelasService->daftarSiswa($guru, $idKelas, $idMapel, $cari);

        $rataRataMateri = $semuaSiswa->isEmpty()
            ? 0.0
            : round((float) $semuaSiswa->avg('persentase_materi_dibaca'), 0);

        $perPage = $this->batasPerHalaman($this->request);
        $halamanSaatIni = LengthAwarePaginator::resolveCurrentPage();
        $daftarSiswa = new LengthAwarePaginator(
            $semuaSiswa->forPage($halamanSaatIni, $perPage)->values(),
            $semuaSiswa->count(),
            $perPage,
            $halamanSaatIni,
            [
                'path' => $this->request->url(),
                'query' => $this->request->query(),
            ]
        );
        $radiusCincin = 42;
        $kelilingCincin = round(2 * M_PI * $radiusCincin, 2);
        $offsetCincin = round($kelilingCincin * (1 - ($rataRataMateri / 100)), 2);

        $progresKelas = [
            'persentase_materi' => $rataRataMateri,
            'keliling_cincin' => $kelilingCincin,
            'offset_cincin' => $offsetCincin,
        ];

        $kategoriKelulusan = $this->dashboardKelasService
            ->kelulusanPerKategori($guru, $idKelas, $idMapel)
            ->each(static function (stdClass $kategori): void {
                $kategori->bar_width = max(0, min(100, $kategori->persentase_lulus));
                $kategori->jumlah_menunggu = max(0, $kategori->jumlah_dikerjakan - $kategori->jumlah_dinilai);
                $kategori->jumlah_belum = max(0, $kategori->jumlah_dinilai - $kategori->jumlah_lulus);
                $kategori->persentase_progres = $kategori->jumlah_peluang === 0
                    ? 0.0
                    : round(($kategori->jumlah_dikerjakan / $kategori->jumlah_peluang) * 100, 1);
            });

        return view('guru.dashboard', [
            'kelasUntukGuru' => $kelasUntukGuru,
            'kelasDipilih' => $kelasDipilih,
            'mapelUntukKelas' => $kelasUntukGuru->where('id_kelas', $idKelas)->unique('id_mapel'),
            'cari' => $cari,
            'ringkasan' => $this->dashboardKelasService->ringkasanKelas($guru, $idKelas, $idMapel),
            'daftarSiswa' => $daftarSiswa,
            'progresKelas' => $progresKelas,
            'kategoriKelulusan' => $kategoriKelulusan,
            'remedial' => $this->dashboardKelasService->remedialKelas($guru, $idKelas, $idMapel),
        ]);
    }

    public function progresSiswa(string $idSiswa): View
    {
        $guru = $this->request->user();

        if (! $guru instanceof User) {
            abort(403);
        }

        $filters = $this->request->validate([
            'kelas' => ['required', 'string', 'max:50'],
            'mapel' => ['required', 'string', 'max:50'],
        ]);

        $idKelas = $filters['kelas'];
        $idMapel = $filters['mapel'];

        $kelasUntukGuru = $this->dashboardKelasService->kelasUntukGuru($guru);
        $kelasDipilih = $kelasUntukGuru->first(
            fn (stdClass $row): bool => $row->id_kelas === $idKelas && $row->id_mapel === $idMapel
        );

        abort_if($kelasDipilih === null, 403);

        $detailSiswaRaw = $this->dashboardKelasService->detailSiswa(
            $guru,
            $idKelas,
            $idMapel,
            $idSiswa
        );

        abort_if($detailSiswaRaw === null, 403);

        $detailSiswa = $this->siapkanDetailSiswa($detailSiswaRaw);

        return view('guru.siswa-progres', [
            'kelasDipilih' => $kelasDipilih,
            'detailSiswa' => $detailSiswa,
        ]);
    }

    /**
     * @param  array{
     *     siswa: stdClass,
     *     jumlah_materi: int,
     *     jumlah_materi_dibaca: int,
     *     kuis: Collection<int, stdClass>,
     *     hasil_kuis: Collection<string, stdClass>
     * }  $detail
     * @return array{
     *     siswa: stdClass,
     *     jumlah_materi: int,
     *     jumlah_materi_dibaca: int,
     *     persentase_materi: float,
     *     keliling_cincin: float,
     *     offset_cincin: float,
     *     progres_kategori: Collection<int, array{kategori: string, jumlah_kuis: int, dikerjakan: int, lulus: int, belum: int, menunggu: int}>,
     *     grafik_nilai: array{lebar: int, tinggi: int, kiri: int, dasar: int, ticks: array<int, array{nilai: int, y: int}>, points: array<int, array<string, mixed>>, pesan: string|null}
     * }
     */
    private function siapkanDetailSiswa(array $detail): array
    {
        $persentaseMateri = $detail['jumlah_materi'] === 0
            ? 0.0
            : round(($detail['jumlah_materi_dibaca'] / $detail['jumlah_materi']) * 100, 1);
        $radiusCincin = 42;
        $kelilingCincin = round(2 * M_PI * $radiusCincin, 2);
        $offsetCincin = round($kelilingCincin * (1 - ($persentaseMateri / 100)), 2);
        $progresKategori = $detail['kuis']
            ->groupBy('kategori')
            ->sortKeys()
            ->map(function (Collection $quizzes, string $category) use ($detail): array {
                $attemptedCount = 0;
                $passedCount = 0;
                $pendingCount = 0;

                foreach ($quizzes as $quiz) {
                    $attempt = $detail['hasil_kuis']->get($quiz->id_kuis);

                    if ($attempt === null) {
                        continue;
                    }

                    $attemptedCount++;

                    if ($attempt->nilai === null) {
                        $pendingCount++;
                    }

                    if (
                        $attempt->nilai !== null
                        && $quiz->kkm !== null
                        && (float) $attempt->nilai >= (float) $quiz->kkm
                    ) {
                        $passedCount++;
                    }
                }

                return [
                    'kategori' => $category,
                    'jumlah_kuis' => $quizzes->count(),
                    'dikerjakan' => $attemptedCount,
                    'lulus' => $passedCount,
                    'menunggu' => $pendingCount,
                    'belum' => $attemptedCount - $passedCount - $pendingCount,
                ];
            })
            ->values();

        $scoredQuizzes = [];

        foreach ($detail['kuis'] as $quiz) {
            $attempt = $detail['hasil_kuis']->get($quiz->id_kuis);

            if ($attempt === null || $attempt->nilai === null) {
                continue;
            }

            $scoredQuizzes[] = [
                'id_kuis' => $quiz->id_kuis,
                'judul' => $quiz->judul,
                'nilai' => (float) $attempt->nilai,
                'kkm' => $quiz->kkm === null ? null : (float) $quiz->kkm,
            ];
        }

        $plotTop = 20;
        $plotHeight = 160;
        $plotBottom = $plotTop + $plotHeight;
        $plotLeft = 58;
        $slotWidth = 140;
        $barWidth = 44;
        $chartWidth = max(580, $plotLeft + (count($scoredQuizzes) * $slotWidth) + 30);
        $chartHeight = 280;
        $points = [];

        foreach ($scoredQuizzes as $index => $quiz) {
            $centerX = $plotLeft + 44 + ($index * $slotWidth);
            $score = max(0, min(100, $quiz['nilai']));
            $barY = round($plotBottom - (($score / 100) * $plotHeight), 2);
            $passed = $quiz['kkm'] === null ? null : $quiz['nilai'] >= $quiz['kkm'];

            $points[] = [
                ...$quiz,
                'kkm_display' => $quiz['kkm'] === null
                    ? null
                    : rtrim(rtrim(number_format($quiz['kkm'], 2, '.', ''), '0'), '.'),
                'lulus' => $passed,
                'warna' => $passed === null ? '#64748b' : ($passed ? '#15803d' : '#dc2626'),
                'x' => $centerX - ($barWidth / 2),
                'tengah_x' => $centerX,
                'bar_y' => $barY,
                'bar_tinggi' => round($plotBottom - $barY, 2),
                'kkm_y' => $quiz['kkm'] === null
                    ? null
                    : round($plotBottom - ((max(0, min(100, $quiz['kkm'])) / 100) * $plotHeight), 2),
                'label_y' => $plotBottom + 44,
            ];
        }

        $ticks = collect([0, 25, 50, 75, 100])
            ->map(fn (int $value): array => [
                'nilai' => $value,
                'y' => (int) round($plotBottom - (($value / 100) * $plotHeight)),
            ])
            ->all();

        return [
            'siswa' => $detail['siswa'],
            'jumlah_materi' => $detail['jumlah_materi'],
            'jumlah_materi_dibaca' => $detail['jumlah_materi_dibaca'],
            'persentase_materi' => $persentaseMateri,
            'keliling_cincin' => $kelilingCincin,
            'offset_cincin' => $offsetCincin,
            'progres_kategori' => $progresKategori,
            'grafik_nilai' => [
                'lebar' => $chartWidth,
                'tinggi' => $chartHeight,
                'kiri' => $plotLeft,
                'dasar' => $plotBottom,
                'ticks' => $ticks,
                'points' => $points,
                'pesan' => $points !== []
                    ? null
                    : ($detail['hasil_kuis']->isEmpty()
                        ? 'Belum ada kuis yang dikerjakan.'
                        : 'Belum ada nilai kuis yang dapat ditampilkan.'),
            ],
        ];
    }

    /**
     * @param  Collection<int, stdClass>  $pengajaran
     */
    private function pilihPengajaran(
        Collection $pengajaran,
        ?string $idKelas,
        ?string $idMapel
    ): ?stdClass {
        if ($pengajaran->isEmpty()) {
            return null;
        }

        return $pengajaran->first(
            fn (stdClass $row): bool => $row->id_kelas === $idKelas && $row->id_mapel === $idMapel
        ) ?? $pengajaran->first(
            fn (stdClass $row): bool => $idKelas !== null && $row->id_kelas === $idKelas
        ) ?? $pengajaran->first(
            fn (stdClass $row): bool => $idMapel !== null && $row->id_mapel === $idMapel
        ) ?? $pengajaran->first();
    }
}
