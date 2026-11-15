<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\User;
use App\Services\DashboardKelasService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use stdClass;

class DashboardController extends Controller
{
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
        $kategoriKelulusan = $this->dashboardKelasService
            ->kelulusanPerKategori($guru, $idKelas, $idMapel)
            ->each(static function (stdClass $kategori): void {
                $kategori->bar_width = max(0, min(100, $kategori->persentase_lulus));
            });

        return view('guru.dashboard', [
            'kelasUntukGuru' => $kelasUntukGuru,
            'kelasDipilih' => $kelasDipilih,
            'mapelUntukKelas' => $kelasUntukGuru->where('id_kelas', $idKelas)->unique('id_mapel'),
            'cari' => $cari,
            'ringkasan' => $this->dashboardKelasService->ringkasanKelas($guru, $idKelas, $idMapel),
            'daftarSiswa' => $this->dashboardKelasService->daftarSiswa($guru, $idKelas, $idMapel, $cari),
            'kategoriKelulusan' => $kategoriKelulusan,
            'remedial' => $this->dashboardKelasService->remedialKelas($guru, $idKelas, $idMapel),
        ]);
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
