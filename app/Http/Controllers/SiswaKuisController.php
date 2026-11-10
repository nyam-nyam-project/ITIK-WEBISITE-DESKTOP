<?php

namespace App\Http\Controllers;

use App\Services\KuisSubmissionService;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\SesiKuis;
use App\Models\DraftJawaban;
use App\Models\Mengerjakan;
use App\Models\RemedialSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiswaKuisController extends Controller
{
    public const BATAS_PELANGGARAN = 3;

    public function __construct(private KuisSubmissionService $submission)
    {
    }

    /**
     * Ambil id_mapel yang boleh diakses siswa berdasarkan kelas di tahun aktif.
     * Mengembalikan [Collection $mapelIds, ?string $idKelas, bool $tanpaKelas].
     */
    private function mapelSiswa(): array
    {
        $user = Auth::user();

        if (! Schema::hasTable('anggota_kelas') || ! Schema::hasTable('mapel_kelas')) {
            return [Mapel::pluck('id_mapel'), 'DEFAULT', false];
        }

        $idKelas = \App\Models\Kelas::kelasAktifSiswa($user);

        if (!$idKelas) {
            return [collect(), null, true];
        }

        $mapelIds = \App\Models\Kelas::mapelUntukSiswa($user);

        return [$mapelIds, $idKelas, false];
    }

    public function index(Request $request)
    {
        Kuis::sinkronisasiStatus();

        $user = Auth::user();

        $this->submission->selesaikanMilikSiswa($user->id_user);

        $idMapel = $request->query('mapel');
        $idMateri = $request->query('materi');

        [$mapelIds, $idKelas, $tanpaKelas] = $this->mapelSiswa();

        if ($idMapel) {
            if ($tanpaKelas || ! $mapelIds->contains($idMapel)) {
                abort(403, 'Anda tidak berhak mengakses mata pelajaran di luar kelas Anda.');
            }
        }

        $mapelAktif = Mapel::aktif()
            ->when(! $tanpaKelas, fn ($q) => $q->whereIn('id_mapel', $mapelIds))
            ->when($tanpaKelas, fn ($q) => $q->whereRaw('1 = 0'))
            ->withCount(['kuis' => function ($q) {
                $q->where('status_publikasi', 'terbit')
                  ->where('is_aktif', true);
            }])
            ->orderBy('nama_mapel')
            ->get();

        $mapelTerpilih = null;
        $daftarMateri = collect();
        $materiTerpilih = null;

        if ($idMapel) {
            $mapelTerpilih = Mapel::where('id_mapel', $idMapel)->first();
            $daftarMateri = Materi::where('id_mapel', $idMapel)
                ->orderBy('bab')
                ->orderBy('judul')
                ->with(['kuis' => function ($q) {
                    $q->where('status_publikasi', 'terbit')
                      ->where('is_aktif', true);
                }])
                ->get();

            if ($idMateri) {
                $materiTerpilih = $daftarMateri->firstWhere('id_materi', $idMateri);
            }
        }

        $remedial = RemedialSiswa::with('kuisRemedial')
            ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            })
            ->where('status', 'ditugaskan')
            ->when($idMapel, function ($q) use ($idMapel) {
                $q->whereHas('kuisRemedial', fn ($sub) => $sub->where('id_mapel', $idMapel));
            })
            ->get();

        $kuisQuery = Kuis::where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->where(function ($q) use ($mapelIds, $user, $tanpaKelas) {
                $q->when(! $tanpaKelas, fn ($subQ) => $subQ->whereIn('id_mapel', $mapelIds))
                  ->when($tanpaKelas, fn ($subQ) => $subQ->whereRaw('1 = 0'))
                  ->orWhere(function ($sub) use ($user) {
                      $sub->whereRaw('LOWER(TRIM(kategori)) = ?', ['remedial'])
                          ->whereHas('remedialSiswa', function ($r) use ($user) {
                              $r->where('status', 'ditugaskan')
                                ->whereHas('mengerjakanAsal', fn ($m) => $m->where('id_user', $user->id_user));
                          });
                  });
            })
            ->with(['soal', 'mapel', 'materi'])
            ->orderBy('created_at', 'desc');

        if ($idMapel) {
            $kuisQuery->where('id_mapel', $idMapel);
        }

        if ($idMateri) {
            $kuisQuery->whereHas('materi', function ($q) use ($idMateri) {
                $q->where('materi.id_materi', $idMateri);
            });
        }

        $kuis = $kuisQuery->get();

        $kuis = $kuis->filter(function ($item) use ($user) {
            if (strtolower(trim($item->kategori)) !== 'remedial') {
                return true;
            }

            return RemedialSiswa::where(
                'id_kuis_remedial',
                $item->id_kuis
            )
                ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                    $query->where('id_user', $user->id_user);
                })
                ->where('status', 'ditugaskan')
                ->exists();
        })->values();

        $statusKuis = $this->hitungStatusDanProgresiKuis($kuis, $user->id_user);

        return view('siswa.kuis.index', compact(
            'kuis',
            'remedial',
            'mapelAktif',
            'mapelTerpilih',
            'daftarMateri',
            'materiTerpilih',
            'statusKuis',
            'tanpaKelas'
        ));
    }

    public function mulai(Kuis $kuis)
    {
        $user = Auth::user();

        // Pastikan yang mengakses adalah siswa
        if ($user->role !== 'siswa') {
            abort(403);
        }

        // Kuis harus sudah terbit
        if ($kuis->status_publikasi !== 'terbit') {
            abort(404);
        }

        // Kuis harus aktif
        if (!$kuis->is_aktif) {
            abort(404);
        }

        // Pastikan kuis termasuk mapel kelas siswa, kecuali remedial yang sesuai penugasan
        if (strtolower(trim($kuis->kategori)) !== 'remedial') {
            [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
            if ($tanpaKelas || ! $mapelIds->contains($kuis->id_mapel)) {
                abort(403, 'Kuis ini tidak termasuk dalam mata pelajaran kelas Anda.');
            }
        }

        // Jika kuis remedial, pastikan siswa memang mendapat tugas
        if ($kuis->kategori === 'remedial') {

            $tugasRemedial = RemedialSiswa::where(
                'id_kuis_remedial',
                $kuis->id_kuis
            )
                ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                    $query->where('id_user', $user->id_user);
                })
                ->first();

            // Siswa tidak mendapatkan tugas remedial ini
            if (!$tugasRemedial) {
                abort(
                    403,
                    'Kamu tidak memiliki tugas remedial untuk kuis ini.'
                );
            }

            // Tugas remedial sudah selesai
            if ($tugasRemedial->status === 'selesai') {

                return redirect()
                    ->route('siswa.remedial.index')
                    ->with(
                        'error',
                        'Tugas remedial ini sudah selesai dan tidak dapat dikerjakan ulang.'
                    );
            }

            // Status selain ditugaskan juga tidak boleh dikerjakan
            if ($tugasRemedial->status !== 'ditugaskan') {

                abort(
                    403,
                    'Tugas remedial tidak dapat dikerjakan.'
                );
            }
        }

        // Progresi tingkat kesulitan kuis harian (Point 1)
        $isKuisHarian = strtolower(trim($kuis->kategori)) === 'kuis harian';
        $tingkatKuis = strtolower(trim($kuis->tingkat_kesulitan ?? 'mudah'));
        if ($isKuisHarian && in_array($tingkatKuis, ['sedang', 'sulit'])) {
            $tingkatSebelumnya = $tingkatKuis === 'sulit' ? 'sedang' : 'mudah';
            $idMat = $kuis->materi()->first()?->id_materi;

            $lulusSebelumnya = Mengerjakan::where('id_user', $user->id_user)
                ->where('status_pengerjaan', 'final')
                ->whereHas('kuis', function ($q) use ($kuis, $tingkatSebelumnya, $idMat) {
                    $q->where('id_mapel', $kuis->id_mapel)
                      ->whereRaw('LOWER(TRIM(kategori)) = ?', ['kuis harian'])
                      ->whereRaw('LOWER(TRIM(tingkat_kesulitan)) = ?', [$tingkatSebelumnya])
                      ->when($idMat, fn ($sub) => $sub->whereHas('materi', fn ($m) => $m->where('materi.id_materi', $idMat)))
                      ->whereRaw('mengerjakan.nilai >= kuis.kkm');
                })
                ->exists();

            if (!$lulusSebelumnya) {
                return back()->with(
                    'error',
                    "Kuis ini terkunci. Anda harus lulus kuis harian tingkat " . ucfirst($tingkatSebelumnya) . " terlebih dahulu."
                );
            }
        }

        // Alokasi waktu wajib tersedia
        if (!$kuis->alokasi_waktu || $kuis->alokasi_waktu < 1) {
            return back()->with(
                'error',
                'Alokasi waktu kuis belum diatur.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pengecekan jadwal waktu mulai & selesai
        |--------------------------------------------------------------------------
        */
        $sekarang = now();

        if ($kuis->waktu_mulai && $sekarang->lt($kuis->waktu_mulai)) {
            return back()->with(
                'error',
                'Kuis belum dapat dikerjakan. Dijadwalkan mulai: ' . $kuis->waktu_mulai->format('d/m/Y H:i')
            );
        }

        if ($kuis->waktu_selesai && $sekarang->gte($kuis->waktu_selesai)) {
            return back()->with(
                'error',
                'Waktu pengerjaan kuis sudah berakhir.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cari sesi yang masih aktif (terbaru, belum selesai)
        |--------------------------------------------------------------------------
        | Kalau siswa sudah pernah mulai tetapi belum submit,
        | jangan membuat percobaan baru.
        */
        $sesi = $this->submission->sesiAktif($kuis->id_kuis, $user->id_user);

        /*
        |--------------------------------------------------------------------------
        | Kalau sesi aktif sudah kedaluwarsa, auto-submit dan redirect ke hasil
        |--------------------------------------------------------------------------
        */
        if ($sesi && now()->gte($this->submission->batasWaktu($sesi))) {
            $this->submission->submit($sesi, true);

            return redirect()
                ->route('siswa.kuis.hasil', $kuis->id_kuis)
                ->with('info', 'Waktu habis, jawaban otomatis dikumpulkan.');
        }

        /*
        |--------------------------------------------------------------------------
        | Kalau belum ada sesi aktif, buat sesi baru
        |--------------------------------------------------------------------------
        */
        if (!$sesi) {

            $percobaanSesi = SesiKuis::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->max('percobaan_ke');

            $percobaanNilai = Mengerjakan::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->max('percobaan_ke');

            $percobaanKe = max($percobaanSesi ?? 0, $percobaanNilai ?? 0) + 1;

            // Bersihkan draft jawaban lama untuk percobaan baru
            DraftJawaban::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->delete();

            $sesi = SesiKuis::create([
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $user->id_user,
                'percobaan_ke' => $percobaanKe,
                'waktu_mulai' => now(),
                'waktu_selesai' => null,
            ]);
        }

        return redirect()->route(
            'siswa.kuis.kerjakan',
            $kuis->id_kuis
        );
    }

    public function kerjakan(Kuis $kuis)
    {
        $user = Auth::user();

        // Pastikan yang mengakses adalah siswa
        if ($user->role !== 'siswa') {
            abort(403);
        }

        // Pastikan kuis termasuk mapel kelas siswa, kecuali remedial yang sesuai penugasan
        if (strtolower(trim($kuis->kategori)) !== 'remedial') {
            [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
            if ($tanpaKelas || ! $mapelIds->contains($kuis->id_mapel)) {
                abort(403, 'Kuis ini tidak termasuk dalam mata pelajaran kelas Anda.');
            }
        }

        /*
         * KHUSUS KUIS REMEDIAL
         *
         * Pastikan siswa memang mendapatkan
         * tugas remedial dari guru.
         */
        if ($kuis->kategori === 'remedial') {

            $tugasRemedial = RemedialSiswa::where(
                'id_kuis_remedial',
                $kuis->id_kuis
            )
                ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                    $query->where('id_user', $user->id_user);
                })
                ->first();

            // Siswa tidak mendapatkan tugas remedial ini
            if (!$tugasRemedial) {

                abort(
                    403,
                    'Kamu tidak memiliki tugas remedial untuk kuis ini.'
                );
            }

            // Tugas remedial sudah selesai
            if ($tugasRemedial->status === 'selesai') {

                return redirect()
                    ->route('siswa.remedial.index')
                    ->with(
                        'error',
                        'Tugas remedial ini sudah selesai dan tidak dapat dikerjakan ulang.'
                    );
            }

            // Selain status ditugaskan tidak boleh dikerjakan
            if ($tugasRemedial->status !== 'ditugaskan') {

                abort(
                    403,
                    'Tugas remedial tidak dapat dikerjakan.'
                );
            }
        }

        /*
         * Cari sesi aktif siswa untuk kuis ini (terbaru, belum selesai).
         * Kalau tidak ada sesi aktif, arahkan ke daftar kuis.
         */
        $sesi = $this->submission->sesiAktif($kuis->id_kuis, $user->id_user);

        if (!$sesi) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with('error', 'Sesi kuis tidak ditemukan. Silakan mulai kuis dari daftar.');
        }

        /*
         * Kalau waktu sesi sudah habis, auto-submit lalu redirect ke hasil.
         */
        $batasWaktu = $this->submission->batasWaktu($sesi);

        if (now()->greaterThanOrEqualTo($batasWaktu)) {
            $this->submission->submit($sesi, true);

            return redirect()
                ->route('siswa.kuis.hasil', $kuis->id_kuis)
                ->with('info', 'Waktu habis, jawaban otomatis dikumpulkan.');
        }

        /*
         * Ambil soal.
         */
        $soal = $kuis->soal()
            ->orderBy('id_soal')
            ->get();

        /*
         * Ambil draft jawaban siswa.
         */
        $draftJawaban = DraftJawaban::where(
            'id_kuis',
            $kuis->id_kuis
        )
            ->where('id_user', $user->id_user)
            ->get()
            ->keyBy('id_soal');

        return view(
            'siswa.kuis.kerjakan',
            compact(
                'kuis',
                'soal',
                'draftJawaban',
                'batasWaktu'
            )
        );
    }

    public function simpanJawaban(Request $request, Kuis $kuis)
    {
        $user = Auth::user();

        if (strtolower(trim($kuis->kategori)) !== 'remedial') {
            [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
            if ($tanpaKelas || ! $mapelIds->contains($kuis->id_mapel)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuis ini tidak termasuk dalam mata pelajaran kelas Anda.'
                ], 403);
            }
        }

        /*
         * Pastikan kuis sudah terbit dan aktif.
         */
        if ($kuis->status_publikasi !== 'terbit' || !$kuis->is_aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Kuis tidak dapat dikerjakan.'
            ], 403);
        }

        /*
         * Pastikan siswa mempunyai sesi aktif.
         */
        $sesi = $this->submission->sesiAktif($kuis->id_kuis, $user->id_user);

        if (!$sesi) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi kuis tidak ditemukan.'
            ], 404);
        }

        /*
         * Tolak autosave jika batas waktu sudah lewat.
         */
        if (now()->greaterThanOrEqualTo($this->submission->batasWaktu($sesi))) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu pengerjaan sudah habis.'
            ], 422);
        }

        /*
         * Validasi data jawaban.
         */
        $request->validate([
            'id_soal' => ['required', 'string'],
            'jawaban' => ['nullable', 'in:A,B,C,D,E'],
        ]);

        /*
         * Pastikan soal memang milik kuis tersebut.
         */
        $soal = Soal::where('id_soal', $request->id_soal)
            ->where('id_kuis', $kuis->id_kuis)
            ->first();

        if (!$soal) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak ditemukan dalam kuis ini.'
            ], 404);
        }

        /*
         * Simpan / update jawaban.
         */
        $draft = DraftJawaban::updateOrCreate(
            [
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $user->id_user,
                'id_soal' => $soal->id_soal,
            ],
            [
                'jawaban_dipilih' => $request->jawaban,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Jawaban tersimpan.',
            'id_draft' => $draft->id_draft,
        ]);
    }

    /**
     * Catat pelanggaran CBT (keluar fullscreen / pindah tab / blur).
     * Jika pelanggaran mencapai batas (3), kuis otomatis disubmit.
     */
    public function catatPelanggaran(Request $request, Kuis $kuis)
    {
        $user = Auth::user();

        $sesi = $this->submission->sesiAktif($kuis->id_kuis, $user->id_user);

        if (!$sesi) {
            abort(404, 'Sesi kuis aktif tidak ditemukan.');
        }

        // Naikkan penghitung secara atomik di database
        DB::update(
            'UPDATE sesi_kuis SET jumlah_pelanggaran = jumlah_pelanggaran + 1 WHERE id_sesi = ? AND waktu_selesai IS NULL',
            [$sesi->id_sesi]
        );

        $jumlah = (int) DB::table('sesi_kuis')
            ->where('id_sesi', $sesi->id_sesi)
            ->value('jumlah_pelanggaran');

        $autoSubmitted = false;

        if ($jumlah >= self::BATAS_PELANGGARAN) {
            $sesi->refresh();
            $this->submission->submit($sesi);
            $autoSubmitted = true;
        }

        return response()->json([
            'jumlah' => $jumlah,
            'batas' => self::BATAS_PELANGGARAN,
            'autoSubmitted' => $autoSubmitted,
        ]);
    }

    /*
     * Detail Hasil Satu Kuis
     */
    public function submit(Request $request, Kuis $kuis)
    {
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            abort(403);
        }

        // Pastikan kuis termasuk mapel kelas siswa, kecuali remedial yang sesuai penugasan
        if (strtolower(trim($kuis->kategori)) !== 'remedial') {
            [$mapelIds, , $tanpaKelas] = $this->mapelSiswa();
            if ($tanpaKelas || ! $mapelIds->contains($kuis->id_mapel)) {
                abort(403, 'Kuis ini tidak termasuk dalam mata pelajaran kelas Anda.');
            }
        }

        // Cari sesi aktif siswa untuk kuis ini.
        $sesi = $this->submission->sesiAktif($kuis->id_kuis, $user->id_user);

        // Kalau tidak ada sesi aktif, pengerjaan sudah selesai sebelumnya.
        if (!$sesi) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with('error', 'Pengerjaan kuis ini sudah selesai.');
        }

        // Delegasikan submit ke service (mencakup transaksi, nilai, tutup sesi, hapus draft).
        $hasil = $this->submission->submit($sesi);

        if (!$hasil) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with('error', 'Pengerjaan kuis ini sudah selesai.');
        }

        return redirect()
            ->route('siswa.kuis.hasil', $kuis->id_kuis)
            ->with('success', 'Kuis berhasil dikumpulkan.');
    }

    public function hasil(Kuis $kuis)
    {
        $user = Auth::user();

        $hasil = Mengerjakan::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->orderByDesc('percobaan_ke')
            ->first();

        if (!$hasil) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with('error', 'Hasil kuis belum tersedia.');
        }

        // Ambil sesi yang cocok untuk menghitung durasi.
        // Kolom durasi_detik tidak ada di DB live, jadi hitung dari selisih waktu.
        $sesiHasil = SesiKuis::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->where('percobaan_ke', $hasil->percobaan_ke)
            ->whereNotNull('waktu_selesai')
            ->first();

        $durasiTampil = null;
        if ($sesiHasil) {
            $detik = (int) $sesiHasil->waktu_mulai->diffInSeconds($sesiHasil->waktu_selesai);
            $menit  = intdiv($detik, 60);
            $sisa   = $detik % 60;
            $durasiTampil = $menit . ' menit ' . $sisa . ' detik';
        }

        $remedial = null;
        $nilaiAsal = null;

        // Kalau kuis ini adalah remedial
        if ($kuis->kategori === 'remedial') {

            $remedial = RemedialSiswa::with('mengerjakanAsal.kuis')
                ->where('id_kuis_remedial', $kuis->id_kuis)
                ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                    $query->where('id_user', $user->id_user);
                })
                ->first();

            if ($remedial) {
                $nilaiAsal = $remedial->mengerjakanAsal->nilai;
            }
        }

        // Ambil daftar butir soal & jawaban siswa untuk evaluasi hasil
        $soal = $kuis->soal()->orderBy('id_soal')->get();
        $draftJawaban = DraftJawaban::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->get()
            ->keyBy('id_soal');

        $totalBenar = 0;
        $totalSalah = 0;
        foreach ($soal as $s) {
            $jw = $draftJawaban->get($s->id_soal)?->jawaban_dipilih;
            if ($jw && $s->jawaban && strtoupper($jw) === strtoupper($s->jawaban)) {
                $totalBenar++;
            } else {
                $totalSalah++;
            }
        }

        $lulus = $hasil->nilai !== null && $kuis->kkm !== null && (float) $hasil->nilai >= (float) $kuis->kkm;

        // Tentukan apakah detail evaluasi soal (benar/salah) boleh ditampilkan ke siswa.
        // Keputusan dibuat di controller; Blade hanya menerima data yang sudah difilter.
        $tampilJawaban = $kuis->tampil_jawaban ?? 'setelah_submit';
        $tampilDetail  = match ($tampilJawaban) {
            'tidak'       => false,
            'setelah_kkm' => $lulus,
            default       => true, // 'setelah_submit'
        };

        // Jika tidak boleh tampil, kosongkan data evaluasi agar tidak bocor ke view
        if (! $tampilDetail) {
            $soal         = collect();
            $draftJawaban = collect();
            $totalBenar   = null;
            $totalSalah   = null;
        }

        return view('siswa.kuis.hasil', compact(
            'kuis',
            'hasil',
            'lulus',
            'remedial',
            'nilaiAsal',
            'durasiTampil',
            'soal',
            'draftJawaban',
            'totalBenar',
            'totalSalah',
            'tampilDetail'
        ));
    }

    /*
     * Daftar Seluruh Hasil Nilai Siswa (Berhirarki: Mapel -> Bab Materi -> Detail Kuis)
     */
    public function daftarNilai(Request $request)
    {
        $user = Auth::user();

        // Tutup sesi kedaluwarsa supaya nilai langsung muncul.
        $this->submission->selesaikanMilikSiswa($user->id_user);

        $idMapel = $request->query('mapel');
        $idMateri = $request->query('materi');

        $mapelAktif = Mapel::aktif()
            ->withCount(['kuis' => function ($q) {
                $q->where('status_publikasi', 'terbit')
                  ->where('is_aktif', true);
            }])
            ->orderBy('nama_mapel')
            ->get();

        $mapelTerpilih = null;
        $daftarMateri = collect();
        $materiTerpilih = null;
        $ringkasanBab = [];

        // Semua riwayat nilai final milik siswa ini
        $semuaHasil = Mengerjakan::with(['kuis.mapel', 'kuis.materi'])
            ->where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->orderByDesc('tanggal')
            ->orderByDesc('percobaan_ke')
            ->get();

        if ($idMapel) {
            $mapelTerpilih = Mapel::where('id_mapel', $idMapel)->first();

            $daftarMateri = Materi::where('id_mapel', $idMapel)
                ->orderBy('bab')
                ->orderBy('judul')
                ->with(['kuis' => function ($q) {
                    $q->where('status_publikasi', 'terbit')
                      ->where('is_aktif', true);
                }])
                ->get();

            // Hitung statistik per bab untuk siswa ini
            foreach ($daftarMateri as $mat) {
                $kuisIds = $mat->kuis->pluck('id_kuis')->toArray();
                $hasilBab = $semuaHasil->whereIn('id_kuis', $kuisIds);

                $nilaiTertinggi = $hasilBab->isNotEmpty() ? $hasilBab->max('nilai') : null;
                $rataRata = $hasilBab->isNotEmpty() ? $hasilBab->avg('nilai') : null;
                $totalKuisSelesai = $hasilBab->pluck('id_kuis')->unique()->count();
                $totalKuisTersedia = $mat->kuis->count();

                $ringkasanBab[$mat->id_materi] = [
                    'total_kuis' => $totalKuisTersedia,
                    'kuis_selesai' => $totalKuisSelesai,
                    'nilai_tertinggi' => $nilaiTertinggi,
                    'rata_rata' => $rataRata,
                    'total_attempts' => $hasilBab->count(),
                ];
            }

            if ($idMateri) {
                $materiTerpilih = $daftarMateri->firstWhere('id_materi', $idMateri);
            }
        }

        // Filter hasil sesuai mapel dan bab terpilih
        $hasilQuery = Mengerjakan::with(['kuis.mapel', 'kuis.materi'])
            ->where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final');

        if ($idMapel) {
            $hasilQuery->whereHas('kuis', function ($q) use ($idMapel) {
                $q->where('id_mapel', $idMapel);
            });
        }

        if ($idMateri) {
            $hasilQuery->whereHas('kuis.materi', function ($q) use ($idMateri) {
                $q->where('materi.id_materi', $idMateri);
            });
        }

        $hasil = $hasilQuery->orderByDesc('tanggal')
            ->orderByDesc('percobaan_ke')
            ->get();

        // Kuis umum / ulangan untuk mapel terpilih (di level 2)
        $hasilUlanganUmum = collect();
        if ($mapelTerpilih && !$materiTerpilih) {
            $hasilUlanganUmum = $semuaHasil->filter(function ($item) use ($idMapel) {
                return $item->kuis &&
                    $item->kuis->id_mapel === $idMapel &&
                    (strtolower(trim($item->kuis->kategori ?? '')) === 'ulangan' || $item->kuis->materi->isEmpty());
            });
        }

        return view('siswa.nilai.index', compact(
            'mapelAktif',
            'mapelTerpilih',
            'daftarMateri',
            'materiTerpilih',
            'ringkasanBab',
            'hasil',
            'hasilUlanganUmum',
            'semuaHasil'
        ));
    }
    /**
     * Hitung status riwayat pengerjaan dan progresi KKM kuis harian.
     */
    private function hitungStatusDanProgresiKuis($kuis, string $idUser): array
    {
        $nilaiSiswa = Mengerjakan::where('id_user', $idUser)
            ->where('status_pengerjaan', 'final')
            ->get()
            ->groupBy('id_kuis');

        $lulusMudahPerMateri = [];
        $lulusSedangPerMateri = [];
        $lulusMudahPerMapel = [];
        $lulusSedangPerMapel = [];

        $statusKuis = [];
        foreach ($kuis as $item) {
            $riwayat = $nilaiSiswa->get($item->id_kuis);
            $nilaiTertinggi = $riwayat ? $riwayat->max('nilai') : null;
            $sudahLulus = $nilaiTertinggi !== null && $item->kkm !== null && $nilaiTertinggi >= $item->kkm;

            $isKuisHarian = strtolower(trim($item->kategori)) === 'kuis harian';
            $tingkat = strtolower(trim($item->tingkat_kesulitan ?? 'mudah'));

            if ($isKuisHarian && $sudahLulus) {
                if ($tingkat === 'mudah') {
                    $lulusMudahPerMapel[$item->id_mapel] = true;
                    foreach ($item->materi as $m) {
                        $lulusMudahPerMateri[$m->id_materi] = true;
                    }
                } elseif ($tingkat === 'sedang') {
                    $lulusSedangPerMapel[$item->id_mapel] = true;
                    foreach ($item->materi as $m) {
                        $lulusSedangPerMateri[$m->id_materi] = true;
                    }
                }
            }

            $statusKuis[$item->id_kuis] = [
                'nilai_tertinggi' => $nilaiTertinggi,
                'sudah_pernah' => $riwayat && $riwayat->isNotEmpty(),
                'lulus' => $sudahLulus,
                'terkunci' => false,
                'alasan_kunci' => null,
            ];
        }

        foreach ($kuis as $item) {
            $isKuisHarian = strtolower(trim($item->kategori)) === 'kuis harian';
            $tingkat = strtolower(trim($item->tingkat_kesulitan ?? 'mudah'));

            if ($isKuisHarian) {
                $idMat = $item->materi->first()?->id_materi;

                if ($tingkat === 'sedang') {
                    $lulus = $idMat ? !empty($lulusMudahPerMateri[$idMat]) : !empty($lulusMudahPerMapel[$item->id_mapel]);
                    if (!$lulus) {
                        $statusKuis[$item->id_kuis]['terkunci'] = true;
                        $statusKuis[$item->id_kuis]['alasan_kunci'] = 'Selesaikan kuis harian tingkat Mudah dengan nilai >= KKM terlebih dahulu.';
                    }
                } elseif ($tingkat === 'sulit') {
                    $lulus = $idMat ? !empty($lulusSedangPerMateri[$idMat]) : !empty($lulusSedangPerMapel[$item->id_mapel]);
                    if (!$lulus) {
                        $statusKuis[$item->id_kuis]['terkunci'] = true;
                        $statusKuis[$item->id_kuis]['alasan_kunci'] = 'Selesaikan kuis harian tingkat Sedang dengan nilai >= KKM terlebih dahulu.';
                    }
                }
            }
        }

        return $statusKuis;
    }
}