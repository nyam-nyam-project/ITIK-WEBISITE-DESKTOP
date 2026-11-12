<?php

namespace App\Http\Controllers;

use App\Models\DraftJawaban;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\Pengajaran;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\Soal;
use App\Services\KuisEssayGradingService;
use App\Services\RekapNilaiGuruService;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ValidasiNilaiController extends Controller
{
    use MenghasilkanId;

    /**
     * Mengunduh rekap nilai guru dalam format CSV (UTF-8 BOM).
     * NISN ditulis sebagai ="..." agar nol di depannya tetap terbaca di Excel.
     */
    public function unduh(Request $request, RekapNilaiGuruService $service)
    {
        $idKelas  = $request->query('kelas');
        $guru     = Auth::user();

        $rows     = $service->rekapUntukGuru($guru, $idKelas);
        $filename = 'rekap-nilai-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel mengenali encoding dengan benar
            fputs($handle, "\xEF\xBB\xBF");

            // Baris header
            fputcsv($handle, [
                'NISN',
                'Nama Siswa',
                'Judul Kuis',
                'Kategori',
                'KKM',
                'Nilai Terbaik',
                'Lulus',
                'Percobaan',
                'Tanggal',
            ]);

            foreach ($rows as $row) {
                $nisn   = $row->siswa?->nomor_induk ?? '';
                $nilai  = $row->nilai_terbaik;
                $kkm    = $row->kuis?->kkm;
                $lulus  = ($nilai !== null && $kkm !== null)
                    ? ($nilai >= $kkm ? 'Ya' : 'Tidak')
                    : '-';

                fputcsv($handle, [
                    '="' . $nisn . '"',                          // format Excel agar nol depan terjaga
                    $row->siswa?->nama_lengkap ?? '-',
                    $row->kuis?->judul ?? '-',
                    $row->kuis?->kategori ?? '-',
                    $kkm !== null ? number_format($kkm, 1) : '-',
                    $nilai !== null ? number_format($nilai, 1) : '-',
                    $lulus,
                    $row->percobaan,
                    $row->tanggal ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menampilkan daftar nilai siswa
     */
    public function index(Request $request)
    {
        Mengerjakan::query()
            ->where('status_pengerjaan', 'final')
            ->where(function ($query) {
                $query->whereNull('status_validasi')
                    ->orWhere('status_validasi', '!=', 'sudah divalidasi');
            })
            ->whereNotNull('nilai')
            ->whereHas('kuis', function ($query) {
                $query->whereNotNull('kkm')
                    ->whereColumn('kuis.kkm', '<=', 'mengerjakans.nilai');
            })
            ->update([
                'status_validasi' => 'sudah divalidasi',
                'id_validator' => null,
            ]);

        $guru = Auth::user();
        $kategori = $request->query('kategori');
        $percobaan = $request->query('percobaan');
        $kelulusan = $request->query('kelulusan');
        $tanggal = $request->query('tanggal');
        $idKelas = $request->query('kelas');

        $kelasList = collect();
        $kelasTerpilih = null;

        if (Schema::hasTable('kelas')) {
            $daftarKelas = Kelas::aktif()
        if (\Illuminate\Support\Facades\Schema::hasTable('kelas') && \Illuminate\Support\Facades\Schema::hasTable('pengajaran')) {
            $kelasQuery = \App\Models\Kelas::aktif();
            $penugasanGlobal = Pengajaran::where('id_user', $guru->id_user)
                ->whereNull('id_kelas')
                ->exists();

            if (! $penugasanGlobal) {
                $kelasIds = Pengajaran::where('id_user', $guru->id_user)
                    ->whereNotNull('id_kelas')
                    ->pluck('id_kelas');
                $kelasQuery->whereIn('id_kelas', $kelasIds);
            }

            $daftarKelas = $kelasQuery
                ->with(['mapel', 'siswa'])
                ->get();

            $semuaMapelIds = $daftarKelas->flatMap(fn ($kelas) => $kelas->mapel->pluck('id_mapel'))->unique()->values();
            $semuaSiswaIds = $daftarKelas->flatMap(fn ($kelas) => $kelas->siswa->pluck('id_user'))->unique()->values();
            $materiPerMapel = \App\Models\Materi::query()
                ->selectRaw('id_mapel, COUNT(*) AS total')
                ->whereIn('id_mapel', $semuaMapelIds)
                ->groupBy('id_mapel')
                ->get()
                ->keyBy('id_mapel');
            $namaKelasMateri = $daftarKelas->flatMap(fn ($kelas) => [$kelas->tingkat, $kelas->nama_kelas])
                ->filter()
                ->unique()
                ->values();
            $materiPerKelas = \App\Models\Materi::query()
                ->selectRaw('kelas, COUNT(*) AS total')
                ->whereIn('kelas', $namaKelasMateri)
                ->groupBy('kelas')
                ->get()
                ->keyBy('kelas');
            $kuisPerMapel = \App\Models\Kuis::query()
                ->selectRaw('id_mapel, COUNT(*) AS total')
                ->where('is_aktif', true)
                ->where('status_publikasi', 'terbit')
                ->whereIn('id_mapel', $semuaMapelIds)
                ->groupBy('id_mapel')
                ->get()
                ->keyBy('id_mapel');
            $kuisAktifTanpaMapel = $daftarKelas->contains(fn ($kelas) => $kelas->mapel->isEmpty())
                ? \App\Models\Kuis::where('is_aktif', true)->where('status_publikasi', 'terbit')->count()
                : 0;
            $nilaiPerSiswa = Mengerjakan::query()
                ->dikelolaOlehGuru($guru)
                ->whereIn('id_user', $semuaSiswaIds)
                ->where('status_pengerjaan', 'final')
                ->whereNotNull('nilai')
                ->selectRaw('id_user, SUM(nilai) AS total_nilai, COUNT(*) AS jumlah_nilai')
                ->groupBy('id_user')
                ->get()
                ->keyBy('id_user');

            $kelasList = $daftarKelas->map(function ($k) use ($materiPerMapel, $materiPerKelas, $kuisPerMapel, $kuisAktifTanpaMapel, $nilaiPerSiswa) {
                $siswaIds = $k->siswa->pluck('id_user')->toArray();
                $mapelIds = $k->mapel->pluck('id_mapel')->toArray();

                $totalSiswa = count($siswaIds);

                $totalMateri = Materi::when(! empty($mapelIds), function ($q) use ($mapelIds) {
                    $q->whereIn('id_mapel', $mapelIds);
                }, function ($q) use ($k) {
                    $q->where('kelas', $k->tingkat)->orWhere('kelas', $k->nama_kelas);
                })->count();

                $kuisAktif = Kuis::where('is_aktif', true)
                    ->where('status_publikasi', 'terbit')
                    ->when(! empty($mapelIds), function ($q) use ($mapelIds) {
                        $q->whereIn('id_mapel', $mapelIds);
                    })->count();

                $rataNilai = ! empty($siswaIds)
                    ? Mengerjakan::whereIn('id_user', $siswaIds)
                        ->where('status_pengerjaan', 'final')
                        ->whereNotNull('nilai')
                        ->avg('nilai')
                    : null;
                $totalMateri = ! empty($mapelIds)
                    ? collect($mapelIds)->sum(fn ($idMapel) => (int) ($materiPerMapel->get($idMapel)?->total ?? 0))
                    : collect([$k->tingkat, $k->nama_kelas])
                        ->filter()
                        ->unique()
                        ->sum(fn ($namaKelas) => (int) ($materiPerKelas->get($namaKelas)?->total ?? 0));
                $kuisAktif = ! empty($mapelIds)
                    ? collect($mapelIds)->sum(fn ($idMapel) => (int) ($kuisPerMapel->get($idMapel)?->total ?? 0))
                    : $kuisAktifTanpaMapel;

                $totalNilai = 0.0;
                $jumlahNilai = 0;
                foreach ($siswaIds as $idSiswa) {
                    $rekapSiswa = $nilaiPerSiswa->get($idSiswa);
                    $totalNilai += (float) ($rekapSiswa?->total_nilai ?? 0);
                    $jumlahNilai += (int) ($rekapSiswa?->jumlah_nilai ?? 0);
                }
                $rataNilai = $jumlahNilai > 0 ? $totalNilai / $jumlahNilai : null;

                return (object) [
                    'id_kelas' => $k->id_kelas,
                    'nama_kelas' => $k->nama_kelas,
                    'tingkat' => $k->tingkat,
                    'total_siswa' => $totalSiswa,
                    'total_materi' => $totalMateri,
                    'kuis_aktif' => $kuisAktif,
                    'rata_nilai' => $rataNilai !== null ? round($rataNilai, 1) : null,
                    'siswa_ids' => $siswaIds,
                ];
            });

            if ($idKelas) {
                $kelasTerpilih = $kelasList->firstWhere('id_kelas', $idKelas);
                if ($kelasTerpilih === null) {
                    abort(403);
                }
            }
        }

        $query = Mengerjakan::dikelolaOlehGuru($guru)
            ->with(['kuis', 'siswa', 'validator'])
            ->where('status_pengerjaan', 'final');

        if ($kelasTerpilih) {
            $query->whereIn('id_user', $kelasTerpilih->siswa_ids);
        }

        if ($kategori) {
            $query->whereHas('kuis', function ($q) use ($kategori) {
                $q->whereRaw('LOWER(TRIM(kategori)) = ?', [strtolower(trim($kategori))]);
            });
        }

        if ($percobaan) {
            $query->where('percobaan_ke', (int) $percobaan);
        }

        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $hasil = $query->orderBy('status_validasi')
            ->orderByDesc('tanggal')
            ->get();

        if ($kelulusan) {
            $hasil = $hasil->filter(function ($item) use ($kelulusan) {
                $lulus = $item->nilai !== null && $item->kuis && $item->kuis->kkm !== null && $item->nilai >= $item->kuis->kkm;
                if ($kelulusan === 'lulus') {
                    return $lulus;
                } elseif ($kelulusan === 'tidak_lulus') {
                    return ! $lulus;
                }

                return true;
            })->values();
        }

        $idHasil = $hasil->pluck('id_mengerjakan');
        $sudahDitugaskanRemedial = $idHasil->isEmpty()
            ? []
            : RemedialSiswa::whereIn('id_mengerjakan_asal', $idHasil)->pluck('id_mengerjakan_asal')->toArray();

        // Daftar kategori kuis unik untuk filter
        $daftarKategori = Kuis::distinct()->pluck('kategori')->filter()->values();

        return view('guru.nilai.index', compact(
            'hasil',
            'sudahDitugaskanRemedial',
            'daftarKategori',
            'kelasList',
            'kelasTerpilih',
            'idKelas',
            'kategori',
            'percobaan',
            'kelulusan',
            'tanggal'
        ));
    }

    /**
     * Menampilkan detail hasil nilai siswa
     */
    public function show($id)
    {
        $mengerjakan = Mengerjakan::with(['siswa', 'kuis', 'validator'])
            ->findOrFail($id);
        abort_unless($mengerjakan->dikelolaOleh(Auth::user()), 403);

        $remedial = null;

        if ($mengerjakan->kuis->kategori === 'remedial') {

            $remedial = RemedialSiswa::with([
                'mengerjakanAsal.kuis',
            ])
                ->where(
                    'id_kuis_remedial',
                    $mengerjakan->id_kuis
                )
                ->whereHas('mengerjakanAsal', function ($query) use ($mengerjakan) {

                    $query->where(
                        'id_user',
                        $mengerjakan->id_user
                    );

                })
                ->first();
        }

        $soal = $mengerjakan->kuis->soal()->orderBy('id_soal')->get();
        $draftJawaban = DraftJawaban::where('id_kuis', $mengerjakan->id_kuis)
            ->where('id_user', $mengerjakan->id_user)
            ->get()
            ->keyBy('id_soal');

        $nilaiEssay = $soal->contains('tipe', 'essay')
            ? NilaiEssay::where('id_mengerjakan', $mengerjakan->id_mengerjakan)->get()->keyBy('id_soal')
            : collect();

        $totalBenar = 0;
        $totalSalah = 0;
        foreach ($soal as $s) {
            if ($s->tipe === 'essay') {
                continue;
            }

            $jw = $draftJawaban->get($s->id_soal)?->jawaban_dipilih;
            if ($jw && $s->jawaban && strtoupper($jw) === strtoupper($s->jawaban)) {
                $totalBenar++;
            } else {
                $totalSalah++;
            }
        }

        return view(
            'guru.nilai.show',
            compact(
                'mengerjakan',
                'remedial',
                'soal',
                'draftJawaban',
                'nilaiEssay',
                'totalBenar',
                'totalSalah'
            )
        );
    }

    /**
     * Validasi nilai siswa
     */
    public function validasi(Mengerjakan $mengerjakan)
    {
        abort_unless($mengerjakan->dikelolaOleh(Auth::user()), 403);

        if ($mengerjakan->status_pengerjaan !== 'final') {
            abort(409, 'Pengerjaan ini belum final.');
        }

        if ($mengerjakan->status_validasi === 'menunggu penilaian') {
            return redirect()
                ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
                ->withErrors(['nilai' => 'Semua essay harus dinilai sebelum nilai kuis divalidasi.']);
        }

        if ($mengerjakan->status_validasi === 'sudah divalidasi') {
            return redirect()
                ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
                ->with('error', 'Nilai ini sudah divalidasi.');
        }

        if (
            $mengerjakan->nilai !== null
            && $mengerjakan->kuis?->kkm !== null
            && (float) $mengerjakan->nilai >= (float) $mengerjakan->kuis->kkm
        ) {
            return redirect()
                ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
                ->with('success', 'Nilai sudah mencapai KKM dan siswa otomatis dinyatakan lulus.');
        }

        $mengerjakan->update([
            'status_validasi' => 'sudah divalidasi',
            'id_validator' => Auth::user()->id_user,
        ]);

        return redirect()
            ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
            ->with('success', 'Nilai siswa berhasil divalidasi.');
    }

    public function nilaiEssay(Request $request, Mengerjakan $mengerjakan, Soal $soal, KuisEssayGradingService $penilaian)
    {
        $penilaian->nilaiEssay($mengerjakan, $soal, $request->user(), $request->all());

        return redirect()
            ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
            ->with('success', 'Penilaian essay berhasil disimpan.');
    }

    /**
     * Berikan tugas remedial langsung dari menu Nilai
     */
    public function tugaskanRemedial($id_mengerjakan)
    {
        return DB::transaction(function () use ($id_mengerjakan) {
            $mengerjakan = Mengerjakan::with(['kuis', 'siswa'])
                ->whereKey($id_mengerjakan)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($mengerjakan->dikelolaOleh(Auth::user()), 403);

            $kuisAsal = Kuis::whereKey($mengerjakan->id_kuis)->lockForUpdate()->firstOrFail();

            if (strtolower(trim($kuisAsal->kategori ?? '')) !== 'ulangan') {
                return back()->with('error', 'Tugas remedial hanya dapat diberikan untuk kuis berkategori Ulangan.');
            }

        // Cari kuis remedial untuk kuis ulangan ini
        $remedial = Remedial::where('id_kuis_asal', $mengerjakan->id_kuis)->first();

        // Jika belum ada kuis remedial untuk ulangan ini, buatkan otomatis
        if (! $remedial) {
            $newId = $this->idBerikutnya('kuis', 'id_kuis', 'K');

            $kuisRemedial = Kuis::create([
                'id_kuis' => $newId,
                'judul' => 'Remedial - '.$mengerjakan->kuis->judul,
                'deskripsi' => 'Kuis remedial untuk perbaikan nilai '.$mengerjakan->kuis->judul,
                'kategori' => 'remedial',
                'tingkat_kesulitan' => $mengerjakan->kuis->tingkat_kesulitan ?? 'sedang',
                'id_mapel' => $mengerjakan->kuis->id_mapel,
                'id_tahun_ajaran' => $mengerjakan->kuis->id_tahun_ajaran,
                'alokasi_waktu' => $mengerjakan->kuis->alokasi_waktu ?? 20,
                'kkm' => $mengerjakan->kuis->kkm ?? 75.00,
                'id_user' => Auth::user()->id_user,
                'status_publikasi' => 'terbit',
                'id_publisher' => Auth::user()->id_user,
                'is_aktif' => true,
            ]);
            if ($mengerjakan->nilai === null || ($kuisAsal->kkm !== null && $mengerjakan->nilai >= $kuisAsal->kkm)) {
                return back()->with('error', 'Siswa ini sudah mencapai KKM.');
            }

            $remedial = Remedial::where('id_kuis_asal', $kuisAsal->id_kuis)
                ->lockForUpdate()
                ->first();

            if (! $remedial) {
                $newId = $this->idBerikutnya('kuis', 'id_kuis', 'K');

                $kuisRemedial = Kuis::create([
                    'id_kuis' => $newId,
                    'judul' => 'Remedial - ' . $kuisAsal->judul,
                    'deskripsi' => 'Kuis remedial untuk perbaikan nilai ' . $kuisAsal->judul,
                    'kategori' => 'remedial',
                    'tingkat_kesulitan' => $kuisAsal->tingkat_kesulitan ?? 'sedang',
                    'id_mapel' => $kuisAsal->id_mapel,
                    'id_tahun_ajaran' => $kuisAsal->id_tahun_ajaran,
                    'alokasi_waktu' => $kuisAsal->alokasi_waktu ?? 20,
                    'kkm' => $kuisAsal->kkm ?? 75.00,
                    'id_user' => Auth::user()->id_user,
                    'status_publikasi' => 'terbit',
                    'id_publisher' => Auth::user()->id_user,
                    'is_aktif' => true,
                ]);

                $remedial = Remedial::create([
                    'id_kuis_remedial' => $kuisRemedial->id_kuis,
                    'id_kuis_asal' => $kuisAsal->id_kuis,
                ]);
            }

            RemedialSiswa::firstOrCreate(
                [
                    'id_kuis_remedial' => $remedial->id_kuis_remedial,
                    'id_mengerjakan_asal' => $mengerjakan->id_mengerjakan,
                ],
                [
                    'id_guru' => Auth::user()->id_user,
                    'tanggal_ditugaskan' => now()->toDateString(),
                    'status' => 'ditugaskan',
                ]
            );

            $namaSiswa = $mengerjakan->siswa->nama_lengkap ?? 'siswa';

            return back()->with('success', "Tugas remedial berhasil diberikan kepada {$namaSiswa}!");
        });
    }
}
