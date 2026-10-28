<?php

namespace App\Http\Controllers;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Services\QuizAvailabilityService;
use App\Services\QuizSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaKuisController extends Controller
{
    public function __construct(
        private QuizAvailabilityService $quizAvailability,
        private QuizSubmissionService $quizSubmission
    ) {}

    public function index()
    {
        $user = Auth::user();

        $remedial = RemedialSiswa::with('kuisRemedial')
            ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            })
            ->where('status', 'ditugaskan')
            ->get();

        $kuis = Kuis::where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->with('materi')
            ->orderBy('created_at', 'desc')
            ->get();

        $hasilSebelumnya = Mengerjakan::where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->get(['id_kuis', 'nilai', 'percobaan_ke'])
            ->groupBy('id_kuis');

        $sesiSebelumnya = SesiKuis::where('id_user', $user->id_user)
            ->get(['id_kuis', 'percobaan_ke', 'waktu_selesai'])
            ->groupBy('id_kuis');

        $semuaKuisTerbit = $kuis;
        $kuis = $kuis->filter(function (Kuis $item) use (
            $user,
            $hasilSebelumnya,
            $sesiSebelumnya,
            $semuaKuisTerbit
        ) {
            if ($item->kategori !== 'remedial') {
                $tingkatTerbuka = $this->quizAvailability->isUnlocked(
                    $item,
                    $user,
                    $semuaKuisTerbit,
                    $hasilSebelumnya
                );
                $item->setAttribute('tingkat_terbuka', $tingkatTerbuka);

                $hasilSiswa = $hasilSebelumnya->get($item->id_kuis, collect());
                $sesiSiswa = $sesiSebelumnya->get($item->id_kuis, collect());

                $sudahLulus = $item->kkm !== null && $hasilSiswa->contains(
                    fn (Mengerjakan $hasil): bool => $hasil->nilai !== null
                        && $hasil->nilai >= $item->kkm
                );

                if ($sudahLulus) {
                    return false;
                }

                $percobaanTerpakai = max(
                    (int) ($hasilSiswa->max('percobaan_ke') ?? 0),
                    (int) ($sesiSiswa->max('percobaan_ke') ?? 0),
                );

                $adaSesiAktif = $sesiSiswa->contains(
                    fn (SesiKuis $sesi): bool => $sesi->waktu_selesai === null
                );

                if (! $tingkatTerbuka) {
                    return true;
                }

                return $adaSesiAktif
                    || $item->maks_percobaan === null
                    || $percobaanTerpakai < $item->maks_percobaan;
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

        return view('siswa.kuis.index', compact('kuis', 'remedial'));
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
        if (! $kuis->is_aktif) {
            abort(404);
        }

        if (
            $kuis->kategori !== 'remedial' &&
            ! $this->quizAvailability->isUnlocked($kuis, $user)
        ) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with(
                    'error',
                    "Tingkat {$kuis->tingkat_ujian_label} belum terbuka. "
                    ."Lulus KKM pada tingkat {$kuis->tingkat_sebelumnya_label} terlebih dahulu."
                );
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
            if (! $tugasRemedial) {
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

        $sudahLulus = $kuis->kkm !== null && Mengerjakan::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->whereNotNull('nilai')
            ->get(['nilai'])
            ->contains(fn (Mengerjakan $hasil): bool => $hasil->nilai >= $kuis->kkm);

        if ($sudahLulus) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with('error', 'Kamu sudah lulus kuis ini dan tidak perlu mengulanginya.');
        }

        // Alokasi waktu wajib tersedia
        if (! $kuis->alokasi_waktu || $kuis->alokasi_waktu < 1) {
            return back()->with(
                'error',
                'Alokasi waktu kuis belum diatur.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Khusus ulangan
        |--------------------------------------------------------------------------
        | Siswa hanya boleh mulai dalam jendela waktu ulangan.
        */
        if ($kuis->kategori === 'ulangan') {

            $sekarang = now();

            if (
                $kuis->waktu_mulai &&
                $sekarang->lt($kuis->waktu_mulai)
            ) {
                return back()->with(
                    'error',
                    'Kuis belum dapat dikerjakan.'
                );
            }

            if (
                $kuis->waktu_selesai &&
                $sekarang->gte($kuis->waktu_selesai)
            ) {
                return back()->with(
                    'error',
                    'Waktu pengerjaan kuis sudah berakhir.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Cari sesi yang masih aktif
        |--------------------------------------------------------------------------
        | Kalau siswa sudah pernah mulai tetapi belum submit,
        | jangan membuat percobaan baru.
        */
        $sesi = SesiKuis::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->whereNull('waktu_selesai')
            ->latest('id_sesi')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Kalau belum ada sesi aktif, buat sesi baru
        |--------------------------------------------------------------------------
        */
        if (! $sesi) {

            $percobaanSesi = SesiKuis::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->max('percobaan_ke');

            $percobaanNilai = Mengerjakan::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->max('percobaan_ke');

            $percobaanKe = max($percobaanSesi ?? 0, $percobaanNilai ?? 0) + 1;

            if (
                $kuis->maks_percobaan !== null &&
                $percobaanKe > $kuis->maks_percobaan
            ) {
                return redirect()
                    ->route('siswa.kuis.hasil', $kuis->id_kuis)
                    ->with('error', 'Batas maksimal percobaan kuis sudah tercapai.');
            }

            $sesi = SesiKuis::create([
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $user->id_user,
                'percobaan_ke' => $percobaanKe,
                'waktu_mulai' => now(),
                'waktu_selesai' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Hitung deadline sesi
        |--------------------------------------------------------------------------
        */
        $batasWaktu = $sesi->waktu_mulai
            ->copy()
            ->addMinutes($kuis->alokasi_waktu);

        /*
        |--------------------------------------------------------------------------
        | Untuk ulangan, deadline sesi tidak boleh melewati
        | waktu_selesai ulangan.
        |--------------------------------------------------------------------------
        */
        if (
            $kuis->kategori === 'ulangan' &&
            $kuis->waktu_selesai &&
            $kuis->waktu_selesai->lessThan($batasWaktu)
        ) {
            $batasWaktu = $kuis->waktu_selesai->copy();
        }

        /*
        |--------------------------------------------------------------------------
        | Kalau sesi ternyata sudah habis
        |--------------------------------------------------------------------------
        */
        if (now()->greaterThanOrEqualTo($batasWaktu)) {

            return redirect()
                ->route('siswa.kuis.index')
                ->with(
                    'error',
                    'Waktu pengerjaan kuis sudah habis.'
                );
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

        if ($kuis->status_publikasi !== 'terbit' || ! $kuis->is_aktif) {
            abort(404);
        }

        if (
            $kuis->kategori !== 'remedial' &&
            ! $this->quizAvailability->isUnlocked($kuis, $user)
        ) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with(
                    'error',
                    "Tingkat {$kuis->tingkat_ujian_label} belum terbuka. "
                    ."Lulus KKM pada tingkat {$kuis->tingkat_sebelumnya_label} terlebih dahulu."
                );
        }

        if (
            $kuis->kategori !== 'remedial' &&
            $kuis->kkm !== null &&
            Mengerjakan::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->where('status_pengerjaan', 'final')
                ->whereNotNull('nilai')
                ->get(['nilai'])
                ->contains(fn (Mengerjakan $hasil): bool => $hasil->nilai >= $kuis->kkm)
        ) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with('error', 'Kamu sudah lulus kuis ini dan tidak perlu mengulanginya.');
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
            if (! $tugasRemedial) {

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
         * Cari sesi siswa untuk kuis ini.
         */
        $sesi = SesiKuis::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->whereNull('waktu_selesai')
            ->latest('id_sesi')
            ->firstOrFail();

        /*
         * Hitung batas waktu.
         */
        $batasWaktu = $sesi->waktu_mulai
            ->copy()
            ->addMinutes($kuis->alokasi_waktu);

        /*
         * Backend tetap mengecek timer.
         */
        if (now()->greaterThanOrEqualTo($batasWaktu)) {

            return redirect()
                ->route('siswa.kuis.index')
                ->with(
                    'error',
                    'Waktu pengerjaan kuis sudah habis.'
                );
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

        /*
        * Pastikan kuis sudah terbit dan aktif.
        */
        if ($kuis->status_publikasi !== 'terbit' || ! $kuis->is_aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Kuis tidak dapat dikerjakan.',
            ], 403);
        }

        /*
         * Pastikan siswa mempunyai sesi.
         */
        $sesi = SesiKuis::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->whereNull('waktu_selesai')
            ->latest('id_sesi')
            ->first();

        if (! $sesi) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi kuis tidak ditemukan.',
            ], 404);
        }

        $waktuDeadline = $sesi->waktu_mulai
            ->copy()
            ->addMinutes($kuis->alokasi_waktu);

        if (
            $kuis->kategori === 'ulangan' &&
            $kuis->waktu_selesai &&
            $kuis->waktu_selesai->lessThan($waktuDeadline)
        ) {
            $waktuDeadline = $kuis->waktu_selesai->copy();
        }

        if (now()->greaterThanOrEqualTo($waktuDeadline)) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu pengerjaan sudah habis.',
            ], 422);
        }

        /*
        * Validasi data jawaban.
        */
        $validated = $request->validate([
            'id_soal' => ['required', 'string'],
            'jawaban' => ['nullable', 'in:A,B,C,D,E'],
            'jawaban_text' => ['nullable', 'string', 'max:10000'],
        ]);

        /*
         * Pastikan soal memang milik kuis tersebut.
         */
        $soal = Soal::where('id_soal', $request->id_soal)
            ->where('id_kuis', $kuis->id_kuis)
            ->first();

        if (! $soal) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak ditemukan dalam kuis ini.',
            ], 404);
        }

        /*
        * Simpan / update jawaban.
        */
        if (
            ($soal->tipe_soal === 'essay' && $request->filled('jawaban')) ||
            ($soal->tipe_soal === 'pilihan_ganda' && $request->filled('jawaban_text'))
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Format jawaban tidak sesuai dengan tipe soal.',
            ], 422);
        }

        $draft = DraftJawaban::updateOrCreate(
            [
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $user->id_user,
                'id_soal' => $soal->id_soal,
            ],
            [
                'jawaban_dipilih' => $validated['jawaban'] ?? null,
                'jawaban_text' => $validated['jawaban_text'] ?? null,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Jawaban tersimpan.',
            'id_draft' => $draft->id_draft,
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

        $hasil = $this->quizSubmission->submit($kuis, $user);

        /*
        |--------------------------------------------------------------------------
        | Kalau sudah pernah submit
        |--------------------------------------------------------------------------
        */

        if (! $hasil) {
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

        if (! $hasil) {
            return redirect()
                ->route('siswa.kuis.index')
                ->with('error', 'Hasil kuis belum tersedia.');
        }

        $remedial = null;
        $nilaiAsal = null;
        $adaSoalEssay = $kuis->soal()
            ->where('tipe_soal', 'essay')
            ->exists();
        $semuaHasil = Mengerjakan::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->get(['nilai', 'percobaan_ke']);
        $sudahLulus = $kuis->kkm !== null && $semuaHasil->contains(
            fn (Mengerjakan $attempt): bool => $attempt->nilai !== null
                && $attempt->nilai >= $kuis->kkm
        );
        $percobaanTerpakai = max(
            (int) ($semuaHasil->max('percobaan_ke') ?? 0),
            (int) (SesiKuis::where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->max('percobaan_ke') ?? 0),
        );
        $adaSesiAktif = SesiKuis::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->whereNull('waktu_selesai')
            ->exists();
        $canRetry = ! $sudahLulus
            && ! $adaSesiAktif
            && (
                $kuis->maks_percobaan === null
                || $percobaanTerpakai < $kuis->maks_percobaan
            );
        $sesiHasil = SesiKuis::where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->where('percobaan_ke', $hasil->percobaan_ke)
            ->first();
        $durasiPengerjaan = $sesiHasil?->durasi_detik;

        if ($durasiPengerjaan === null && $sesiHasil?->waktu_selesai) {
            $durasiPengerjaan = max(
                0,
                $sesiHasil->waktu_selesai->getTimestamp()
                    - $sesiHasil->waktu_mulai->getTimestamp()
            );
        }

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

        return view('siswa.kuis.hasil', compact(
            'kuis',
            'hasil',
            'remedial',
            'nilaiAsal',
            'adaSoalEssay',
            'canRetry',
            'percobaanTerpakai',
            'durasiPengerjaan'
        ));
    }

    /*
     * Daftar Seluruh Hasil Nilai Siswa
     */
    public function daftarNilai()
    {
        $user = Auth::user();

        $hasil = Mengerjakan::with('kuis')
            ->where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->orderByDesc('tanggal')
            ->orderByDesc('percobaan_ke')
            ->get();

        return view('siswa.nilai.index', compact('hasil'));
    }
}
