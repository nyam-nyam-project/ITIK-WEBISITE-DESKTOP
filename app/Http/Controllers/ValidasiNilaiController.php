<?php

namespace App\Http\Controllers;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\Soal;
use App\Services\KuisPenilaianService;
use App\Services\RekapNilaiGuruService;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $kategori = $request->query('kategori');
        $percobaan = $request->query('percobaan');
        $kelulusan = $request->query('kelulusan');
        $tanggal = $request->query('tanggal');
        $idKelas = $request->query('kelas');

        $kelasList = collect();
        $kelasTerpilih = null;

        if (\Illuminate\Support\Facades\Schema::hasTable('kelas')) {
            $daftarKelas = \App\Models\Kelas::aktif()
                ->with(['mapel', 'siswa'])
                ->get();

            $kelasList = $daftarKelas->map(function ($k) {
                $siswaIds = $k->siswa->pluck('id_user')->toArray();
                $mapelIds = $k->mapel->pluck('id_mapel')->toArray();

                $totalSiswa = count($siswaIds);

                $totalMateri = \App\Models\Materi::when(!empty($mapelIds), function ($q) use ($mapelIds) {
                    $q->whereIn('id_mapel', $mapelIds);
                }, function ($q) use ($k) {
                    $q->where('kelas', $k->tingkat)->orWhere('kelas', $k->nama_kelas);
                })->count();

                $kuisAktif = \App\Models\Kuis::where('is_aktif', true)
                    ->where('status_publikasi', 'terbit')
                    ->when(!empty($mapelIds), function ($q) use ($mapelIds) {
                        $q->whereIn('id_mapel', $mapelIds);
                    })->count();

                $rataNilai = !empty($siswaIds)
                    ? \App\Models\Mengerjakan::whereIn('id_user', $siswaIds)
                        ->where('status_pengerjaan', 'final')
                        ->whereNotNull('nilai')
                        ->avg('nilai')
                    : null;

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
            }
        }

        $query = Mengerjakan::with(['kuis', 'siswa', 'validator'])
            ->where('status_pengerjaan', 'final');

        if ($kelasTerpilih && !empty($kelasTerpilih->siswa_ids)) {
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
                    return !$lulus;
                }
                return true;
            })->values();
        }

        $sudahDitugaskanRemedial = RemedialSiswa::pluck('id_mengerjakan_asal')->toArray();

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
        $mengerjakan = Mengerjakan::with(['siswa','kuis','validator',])
            ->findOrFail($id);

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

        $mengerjakan->update([
            'status_validasi' => 'sudah divalidasi',
            'id_validator' => Auth::user()->id_user,
        ]);

        return redirect()
            ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
            ->with('success', 'Nilai siswa berhasil divalidasi.');
    }

    public function nilaiEssay(Request $request, Mengerjakan $mengerjakan, Soal $soal, KuisPenilaianService $penilaian)
    {
        DB::transaction(function () use ($request, $mengerjakan, $soal, $penilaian) {
            $kuis = Kuis::whereKey($mengerjakan->id_kuis)->lockForUpdate()->firstOrFail();
            abort_unless($kuis->id_user === $request->user()->id_user, 403);

            $mengerjakan = Mengerjakan::whereKey($mengerjakan->id_mengerjakan)->lockForUpdate()->firstOrFail();
            $soal = $kuis->soal()->whereKey($soal->id_soal)->where('tipe', 'essay')->firstOrFail();
            $nilaiEssay = NilaiEssay::where('id_mengerjakan', $mengerjakan->id_mengerjakan)
                ->where('id_soal', $soal->id_soal)
                ->lockForUpdate()
                ->firstOrFail();

            if ($mengerjakan->status_pengerjaan !== 'final' || $mengerjakan->status_validasi !== 'menunggu penilaian') {
                throw ValidationException::withMessages([
                    'nilai' => 'Percobaan ini tidak sedang menunggu penilaian essay.',
                ]);
            }

            $validated = $request->validate([
                'nilai' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.$soal->bobot],
                'catatan_guru' => ['nullable', 'string'],
            ]);

            $nilaiEssay->update([
                'nilai' => $validated['nilai'],
                'catatan_guru' => $validated['catatan_guru'] ?? null,
                'id_penilai' => $request->user()->id_user,
                'dinilai_at' => now(),
            ]);

            $nilai = $penilaian->hitung(
                $kuis->soal()->get(),
                DraftJawaban::where('id_kuis', $kuis->id_kuis)->where('id_user', $mengerjakan->id_user)->get(),
                NilaiEssay::where('id_mengerjakan', $mengerjakan->id_mengerjakan)->get()
            );

            if ($nilai !== null) {
                $mengerjakan->update([
                    'nilai' => $nilai,
                    'status_validasi' => 'sudah divalidasi',
                    'id_validator' => $request->user()->id_user,
                ]);
            }
        });

        return redirect()
            ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
            ->with('success', 'Penilaian essay berhasil disimpan.');
    }

    /**
     * Berikan tugas remedial langsung dari menu Nilai
     */
    public function tugaskanRemedial($id_mengerjakan)
    {
        $mengerjakan = Mengerjakan::with(['kuis', 'siswa'])->findOrFail($id_mengerjakan);

        if (strtolower(trim($mengerjakan->kuis->kategori ?? '')) !== 'ulangan') {
            return back()->with('error', 'Tugas remedial hanya dapat diberikan untuk kuis berkategori Ulangan.');
        }

        if ($mengerjakan->nilai === null || ($mengerjakan->kuis->kkm !== null && $mengerjakan->nilai >= $mengerjakan->kuis->kkm)) {
            return back()->with('error', 'Siswa ini sudah mencapai KKM.');
        }

        // Cari kuis remedial untuk kuis ulangan ini
        $remedial = Remedial::where('id_kuis_asal', $mengerjakan->id_kuis)->first();

        // Jika belum ada kuis remedial untuk ulangan ini, buatkan otomatis
        if (!$remedial) {
            $newId = $this->idBerikutnya('kuis', 'id_kuis', 'K');

            $kuisRemedial = Kuis::create([
                'id_kuis' => $newId,
                'judul' => 'Remedial - ' . $mengerjakan->kuis->judul,
                'deskripsi' => 'Kuis remedial untuk perbaikan nilai ' . $mengerjakan->kuis->judul,
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

            $remedial = Remedial::create([
                'id_kuis_remedial' => $kuisRemedial->id_kuis,
                'id_kuis_asal' => $mengerjakan->id_kuis,
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
    }
}
