<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Remedial;
use App\Models\Soal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KuisController extends Controller
{
    public function index()
    {
        $kuis = Kuis::where('is_aktif', true)
            ->with('pembuat')
            ->latest('created_at')
            ->get();

        return view('kuis.index', compact('kuis'));
    }

    public function create()
    {
        $materi = Materi::orderBy('bab')
            ->orderBy('judul')
            ->get();

        $kuisAsal = Kuis::where('is_aktif', true)
            ->where('kategori', '!=', 'remedial')
            ->orderBy('judul')
            ->get();

        return view('kuis.create', compact('materi', 'kuisAsal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150', Rule::unique('kuis', 'judul')],
            'deskripsi' => 'nullable|string',

            'kategori' => [
                'required',
                Rule::in([
                    'pre-test',
                    'post-test',
                    'kuis harian',
                    'ulangan',
                    'remedial',
                ]),
            ],

            'tingkat_ujian' => [
                'sometimes',
                'required',
                Rule::in(['mudah', 'sedang', 'sulit']),
            ],
            'alokasi_waktu' => 'nullable|integer|min:1|max:300',
            'kkm' => 'nullable|integer|min:0|max:100',
            'maks_percobaan' => 'nullable|integer|min:1|max:65535',
            'waktu_publish' => 'nullable|date|after_or_equal:now',

            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date',

            'materi' => 'nullable|array',
            'materi.*' => 'exists:materis,id_materi',

            'id_kuis_asal' => 'nullable|exists:kuis,id_kuis',
        ], [
            'judul.unique' => 'Judul kuis sudah digunakan.',
            'waktu_publish.after_or_equal' => 'Jadwal publikasi tidak boleh sebelum tanggal dan waktu sekarang.',
        ]);

        $validated['tingkat_ujian'] ??= 'mudah';

        if (
            $validated['kategori'] !== 'remedial' &&
            in_array($validated['tingkat_ujian'], ['sedang', 'sulit'], true) &&
            empty($validated['materi'])
        ) {
            throw ValidationException::withMessages([
                'materi' => 'Tingkat Sedang dan Sulit wajib dikaitkan dengan materi agar alur ujian dapat dicocokkan.',
            ]);
        }

        /*
        * Simpan ID kuis asal jika kategori remedial.
        */
        if ($validated['kategori'] === 'remedial') {

            if (empty($validated['id_kuis_asal'])) {
                return back()
                    ->withErrors([
                        'id_kuis_asal' => 'Kuis remedial wajib memiliki kuis asal.',
                    ])
                    ->withInput();
            }

        } else {

            $validated['id_kuis_asal'] = null;
        }

        /*
        * Simpan ID kuis asal sebelum
        * dikeluarkan dari data Kuis.
        */
        $idKuisAsal = $validated['id_kuis_asal'] ?? null;

        /*
        * Validasi khusus kategori ulangan.
        */
        if ($validated['kategori'] === 'ulangan') {

            if (! $request->waktu_mulai || ! $request->waktu_selesai) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai dan waktu selesai wajib diisi untuk ulangan.',
                ]);
            }

            $waktuMulai = Carbon::parse($request->waktu_mulai);
            $waktuSelesai = Carbon::parse($request->waktu_selesai);

            if ($waktuMulai->lt(now())) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai ulangan tidak boleh sebelum waktu sekarang.',
                ]);
            }

            if ($waktuSelesai->lte($waktuMulai)) {
                throw ValidationException::withMessages([
                    'waktu_selesai' => 'Waktu selesai harus setelah waktu mulai.',
                ]);
            }

        } else {

            /*
            * Selain ulangan tidak menggunakan
            * jendela waktu khusus.
            */
            $validated['waktu_mulai'] = null;
            $validated['waktu_selesai'] = null;
        }

        /*
        * Generate ID kuis.
        */
        $validated['id_kuis'] = 'K'.str_pad(
            Kuis::count() + 1,
            3,
            '0',
            STR_PAD_LEFT
        );

        /*
        * Guru yang membuat kuis.
        */
        $validated['id_user'] = Auth::user()->id_user;

        /*
        * Semua kuis baru dibuat sebagai draft.
        */
        $validated['status_publikasi'] = 'draft';

        /*
        * id_kuis_asal bukan kolom tabel kuis,
        * jadi jangan ikut disimpan ke tabel kuis.
        */
        unset($validated['materi']);
        unset($validated['id_kuis_asal']);

        /*
        * Simpan kuis.
        */
        $kuis = Kuis::create($validated);

        /*
        * Jika kategori remedial,
        * buat relasi di tabel remedials.
        */
        if ($kuis->kategori === 'remedial') {

            Remedial::create([
                'id_kuis_remedial' => $kuis->id_kuis,
                'id_kuis_asal' => $idKuisAsal,
            ]);
        }

        /*
        * Hubungkan materi untuk kategori yang terkait materi.
        */
        if (
            $request->filled('materi') &&
            $kuis->kategori !== 'remedial'
        ) {
            $kuis->materi()->sync($request->materi);
        }

        return redirect()
            ->route('kuis.index')
            ->with('success', 'Kuis berhasil dibuat sebagai draft.');
    }

    public function show($id)
    {
        $kuis = Kuis::with([
            'pembuat',
            'materi',
            'soal',
        ])->findOrFail($id);

        return view('kuis.show', compact('kuis'));
    }

    public function edit($id)
    {
        $kuis = Kuis::findOrFail($id);

        /*
         * Kuis yang sudah terbit tidak boleh diedit
         * sembarangan.
         */
        if ($kuis->status_publikasi === 'terbit') {
            return back()->withErrors([
                'kuis' => 'Kuis yang sudah terbit tidak dapat diedit.',
            ]);
        }

        $materi = Materi::orderBy('bab')
            ->orderBy('judul')
            ->get();

        $materiTerpilih = $kuis->materi
            ->pluck('id_materi')
            ->toArray();

        return view(
            'kuis.edit',
            compact('kuis', 'materi', 'materiTerpilih')
        );
    }

    public function update(Request $request, $id)
    {
        $kuis = Kuis::findOrFail($id);

        if ($kuis->status_publikasi === 'terbit') {
            return back()->withErrors([
                'kuis' => 'Kuis yang sudah terbit tidak dapat diedit.',
            ]);
        }

        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
                Rule::unique('kuis', 'judul')->ignore($kuis->id_kuis, 'id_kuis'),
            ],
            'deskripsi' => 'nullable|string',
            'kategori' => [
                'required',
                Rule::in([
                    'pre-test',
                    'post-test',
                    'kuis harian',
                    'ulangan',
                    'remedial',
                ]),
            ],
            'tingkat_ujian' => [
                'sometimes',
                'required',
                Rule::in(['mudah', 'sedang', 'sulit']),
            ],
            'alokasi_waktu' => 'nullable|integer|min:1|max:300',
            'kkm' => 'nullable|integer|min:0|max:100',
            'maks_percobaan' => 'nullable|integer|min:1|max:65535',
            'waktu_publish' => 'nullable|date|after_or_equal:now',
            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date',
            'materi' => 'nullable|array',
            'materi.*' => 'exists:materis,id_materi',
        ], [
            'judul.unique' => 'Judul kuis sudah digunakan.',
            'waktu_publish.after_or_equal' => 'Jadwal publikasi tidak boleh sebelum tanggal dan waktu sekarang.',
        ]);

        $validated['tingkat_ujian'] ??= $kuis->tingkat_ujian;

        if (
            $validated['kategori'] !== 'remedial' &&
            in_array($validated['tingkat_ujian'], ['sedang', 'sulit'], true) &&
            empty($validated['materi'])
        ) {
            throw ValidationException::withMessages([
                'materi' => 'Tingkat Sedang dan Sulit wajib dikaitkan dengan materi agar alur ujian dapat dicocokkan.',
            ]);
        }

        if ($validated['kategori'] === 'ulangan') {

            if (! $request->waktu_mulai || ! $request->waktu_selesai) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai dan waktu selesai wajib diisi untuk ulangan.',
                ]);
            }

            $waktuMulai = Carbon::parse($request->waktu_mulai);
            $waktuSelesai = Carbon::parse($request->waktu_selesai);

            if ($waktuMulai->lt(now())) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai ulangan tidak boleh sebelum waktu sekarang.',
                ]);
            }

            if ($waktuSelesai->lte($waktuMulai)) {
                throw ValidationException::withMessages([
                    'waktu_selesai' => 'Waktu selesai harus setelah waktu mulai.',
                ]);
            }

        } else {
            $validated['waktu_mulai'] = null;
            $validated['waktu_selesai'] = null;
        }

        if (
            $kuis->status_publikasi === 'terjadwal' &&
            ! empty($validated['waktu_publish'])
        ) {
            if (empty($validated['alokasi_waktu'])) {
                throw ValidationException::withMessages([
                    'alokasi_waktu' => 'Alokasi waktu wajib diisi untuk kuis terjadwal.',
                ]);
            }

            if (! array_key_exists('kkm', $validated) || $validated['kkm'] === null) {
                throw ValidationException::withMessages([
                    'kkm' => 'KKM wajib diisi untuk kuis terjadwal.',
                ]);
            }
        }

        unset($validated['materi']);

        if (
            $kuis->status_publikasi === 'terjadwal' &&
            empty($validated['waktu_publish'])
        ) {
            $validated['status_publikasi'] = 'draft';
        }

        $kuis->update($validated);

        if ($validated['kategori'] !== 'remedial') {
            $kuis->materi()->sync($request->materi ?? []);
        }

        return redirect()
            ->route('kuis.index')
            ->with('success', 'Kuis berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kuis = Kuis::findOrFail($id);

        /*
         * Soft delete / arsip.
         * Kita tidak menggunakan delete()
         * karena kuis bisa memiliki riwayat nilai.
         */
        $kuis->update([
            'is_aktif' => false,
        ]);

        return redirect()
            ->route('kuis.index')
            ->with('success', 'Kuis berhasil diarsipkan.');
    }

    public function publish($id)
    {
        $kuis = Kuis::with('soal')->findOrFail($id);

        /*
        * 1. Kkuis masih draft
        */
        if (! in_array($kuis->status_publikasi, ['draft', 'terjadwal'], true)) {
            return back()->withErrors([
                'kuis' => 'Kuis ini sudah terbit dan tidak dapat dipublikasikan ulang.',
            ]);
        }

        /*
        * 2. Kuis masih aktif
        */
        if (! $kuis->is_aktif) {
            return back()->withErrors([
                'kuis' => 'Kuis yang sudah diarsipkan tidak dapat dipublikasikan.',
            ]);
        }

        /*
        * 3. Validasi data utama kuis
        */
        $errors = [];

        if (empty($kuis->judul)) {
            $errors[] = 'Judul kuis wajib diisi.';
        }

        if (empty($kuis->kategori)) {
            $errors[] = 'Kategori kuis wajib diisi.';
        }

        if (empty($kuis->alokasi_waktu) || $kuis->alokasi_waktu <= 0) {
            $errors[] = 'Alokasi waktu kuis wajib diisi dan harus lebih dari 0 menit.';
        }

        if ($kuis->kkm === null || $kuis->kkm < 0 || $kuis->kkm > 100) {
            $errors[] = 'KKM wajib diisi dengan nilai antara 0 sampai 100.';
        }

        /*
        * 4. Validasi khusus ulangan
        */
        if ($kuis->kategori === 'ulangan') {

            if (empty($kuis->waktu_mulai)) {
                $errors[] = 'Waktu mulai wajib diisi untuk kategori ulangan.';
            }

            if (empty($kuis->waktu_selesai)) {
                $errors[] = 'Waktu selesai wajib diisi untuk kategori ulangan.';
            }

            if (
                $kuis->waktu_mulai &&
                $kuis->waktu_selesai &&
                $kuis->waktu_selesai <= $kuis->waktu_mulai
            ) {
                $errors[] = 'Waktu selesai harus lebih besar dari waktu mulai.';
            }
        }

        /*
        * 5. Pastikan kuis memiliki soal
        */
        if ($kuis->soal->count() === 0) {
            $errors[] = 'Kuis belum memiliki soal. Tambahkan minimal 1 soal.';
        }

        /*
        * 6. Validasi setiap soal
        */
        $totalBobot = 0;

        foreach ($kuis->soal as $index => $soal) {

            $nomor = $index + 1;

            if (empty(trim($soal->pertanyaan))) {
                $errors[] = "Soal nomor {$nomor}: pertanyaan wajib diisi.";
            }

            if ($soal->tipe_soal === 'essay') {
                continue;
            }

            /*
            * Opsi A-D wajib
            */
            if (empty(trim($soal->opsi_a ?? '')) && empty($soal->gambar_opsi_a)) {
                $errors[] = "Soal nomor {$nomor}: opsi A wajib diisi.";
            }

            if (empty(trim($soal->opsi_b ?? '')) && empty($soal->gambar_opsi_b)) {
                $errors[] = "Soal nomor {$nomor}: opsi B wajib diisi.";
            }

            if (empty(trim($soal->opsi_c ?? '')) && empty($soal->gambar_opsi_c)) {
                $errors[] = "Soal nomor {$nomor}: opsi C wajib diisi.";
            }

            if (empty(trim($soal->opsi_d ?? '')) && empty($soal->gambar_opsi_d)) {
                $errors[] = "Soal nomor {$nomor}: opsi D wajib diisi.";
            }

            /*
            * Jawaban benar wajib
            */
            if (empty($soal->jawaban)) {

                $errors[] = "Soal nomor {$nomor}: jawaban benar wajib ditentukan.";

            } else {

                $jawaban = strtoupper($soal->jawaban);

                if (! in_array($jawaban, ['A', 'B', 'C', 'D', 'E'])) {

                    $errors[] =
                        "Soal nomor {$nomor}: jawaban harus A, B, C, D, atau E.";

                } else {

                    /*
                    * Jika jawaban E dipilih,
                    * opsi E harus tersedia.
                    */
                    $opsi = 'opsi_'.strtolower($jawaban);

                    if (
                        empty(trim($soal->$opsi ?? '')) &&
                        empty($soal->{'gambar_'.$opsi})
                    ) {

                        $errors[] =
                            "Soal nomor {$nomor}: jawaban {$jawaban} harus memiliki teks atau gambar.";
                    }
                }
            }

            /*
            * Validasi bobot
            */
            if ($soal->bobot === null || $soal->bobot <= 0) {

                $errors[] =
                    "Soal nomor {$nomor}: bobot harus lebih dari 0.";

            } else {

                $totalBobot += (float) $soal->bobot;
            }
        }

        /*
        * 7. Total bobot harus tepat 100
        */
        $jumlahPilihanGanda = $kuis->soal
            ->where('tipe_soal', 'pilihan_ganda')
            ->count();

        if ($jumlahPilihanGanda > 0 && round($totalBobot, 2) != 100) {

            $errors[] =
                'Total bobot semua soal harus tepat 100. Total saat ini: '
                .number_format($totalBobot, 2)
                .'.';
        }

        /*
        * 8. Kalau ada kesalahan, jangan publish
        */
        if (! empty($errors)) {
            return back()
                ->withErrors([
                    'publish' => $errors,
                ])
                ->withInput();
        }

        /*
        * 9. Semua valid → publish
        */
        $waktuPublish = $kuis->waktu_publish;
        $jadwalkan = $waktuPublish && $waktuPublish->isFuture();

        $kuis->update([
            'status_publikasi' => $jadwalkan ? 'terjadwal' : 'terbit',
            'id_publisher' => Auth::user()->id_user,
        ]);

        if ($jadwalkan) {
            return redirect()
                ->route('kuis.show', $kuis->id_kuis)
                ->with('success', 'Kuis dijadwalkan untuk dipublikasikan pada '.$waktuPublish->format('d-m-Y H:i').'.');
        }

        return redirect()
            ->route('kuis.show', $kuis->id_kuis)
            ->with(
                'success',
                'Kuis berhasil divalidasi dan dipublikasikan.'
            );
    }
}
