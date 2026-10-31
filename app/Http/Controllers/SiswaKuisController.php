<?php

namespace App\Http\Controllers;

use App\Services\KuisSubmissionService;
use App\Models\Kuis;
use App\Models\Soal;
use App\Models\SesiKuis;
use App\Models\DraftJawaban;
use App\Models\Mengerjakan;
use App\Models\RemedialSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaKuisController extends Controller
{
    public function __construct(private KuisSubmissionService $submission)
    {
    }

    public function index()
    {
        $user = Auth::user();

        // Tutup sesi yang sudah kedaluwarsa milik siswa ini,
        // supaya nilai otomatis muncul saat halaman dibuka.
        $this->submission->selesaikanMilikSiswa($user->id_user);

        $remedial = RemedialSiswa::with('kuisRemedial')
            ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            })
            ->where('status', 'ditugaskan')
            ->get();

        $kuis = Kuis::where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $kuis = $kuis->filter(function (Kuis $item) use ($user) {
            if ($item->kategori !== 'remedial') {
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
        if (!$kuis->is_aktif) {
            abort(404);
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

        // Alokasi waktu wajib tersedia
        if (!$kuis->alokasi_waktu || $kuis->alokasi_waktu < 1) {
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

    /*
     * Detail Hasil Satu Kuis
     */
    public function submit(Request $request, Kuis $kuis)
    {
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            abort(403);
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

        return view('siswa.kuis.hasil', compact('kuis', 'hasil', 'remedial', 'nilaiAsal', 'durasiTampil'));
    }

    /*
     * Daftar Seluruh Hasil Nilai Siswa
     */
    public function daftarNilai()
    {
        $user = Auth::user();

        // Tutup sesi kedaluwarsa supaya nilai langsung muncul.
        $this->submission->selesaikanMilikSiswa($user->id_user);

        $hasil = Mengerjakan::with('kuis')
            ->where('id_user', $user->id_user)
            ->where('status_pengerjaan', 'final')
            ->orderByDesc('tanggal')
            ->orderByDesc('percobaan_ke')
            ->get();

        return view('siswa.nilai.index', compact('hasil'));
    }
}

