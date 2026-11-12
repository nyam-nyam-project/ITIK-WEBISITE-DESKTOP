<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Pengajaran;
use App\Models\Remedial;
use App\Models\TahunAjaran;
use App\Services\KuisPenilaianService;
use App\Support\MenghasilkanId;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KuisController extends Controller
{
    use MenghasilkanId;

    public const KATEGORI = ['post-test', 'pre-test', 'kuis harian', 'ulangan', 'remedial'];

    private const PILIHAN_TAMPIL_JAWABAN = ['tidak', 'setelah_submit', 'setelah_kkm'];

    private function pilihanMapel(?string $idMapelSaatIni = null)
    {
        $user = Auth::user();

        if ($user && $user->role === 'guru') {
            $mapelIds = Pengajaran::mapelUntukGuru($user);

            return Mapel::whereIn('id_mapel', $mapelIds)
                ->where(fn ($q) => $q->where('is_aktif', 1)
                    ->when($idMapelSaatIni, fn ($q) => $q->orWhere('id_mapel', $idMapelSaatIni)))
                ->orderBy('nama_mapel')
                ->get();
        }

        return Mapel::where(fn ($q) => $q->where('is_aktif', 1)
            ->when($idMapelSaatIni, fn ($q) => $q->orWhere('id_mapel', $idMapelSaatIni)))
            ->orderBy('nama_mapel')
            ->get();
    }

    private function aturanMapel(?string $idMapelSaatIni = null): array
    {
        $user = Auth::user();

        return [
            'required',
            'string',
            Rule::exists('tm_mapel', 'id_mapel')->where(function ($q) use ($idMapelSaatIni, $user) {
                if ($user && $user->role === 'guru') {
                    $mapelIds = Pengajaran::mapelUntukGuru($user);
                    $q->whereIn('id_mapel', $mapelIds);
                }

                $q->where(fn ($sub) => $sub->where('is_aktif', 1)
                    ->when($idMapelSaatIni, fn ($s) => $s->orWhere('id_mapel', $idMapelSaatIni)));
            }),
        ];
    }

    public function index(Request $request)
    {
        Kuis::sinkronisasiStatus();

        $idMapel = $request->query('mapel');
        $user = Auth::user();

        if ($user && $user->role === 'guru') {
            $mapelIds = Pengajaran::mapelUntukGuru($user);

            if ($idMapel && ! $mapelIds->contains($idMapel)) {
                abort(403, 'Anda tidak berhak mengakses mata pelajaran di luar penugasan pengajaran Anda.');
            }

            $mapelAktif = Mapel::aktif()
                ->whereIn('id_mapel', $mapelIds)
                ->withCount(['kuis' => function ($q) {
                    $q->where('is_aktif', true);
                }])
                ->orderBy('nama_mapel')
                ->get();

            $mapelTerpilih = null;
            $daftarMateri = collect();
            if ($idMapel) {
                $mapelTerpilih = Mapel::where('id_mapel', $idMapel)->first();
                $daftarMateri = Materi::where('id_mapel', $idMapel)
                    ->orderBy('bab')
                    ->orderBy('judul')
                    ->get();
            }

            $kuis = Kuis::where('is_aktif', true)
                ->whereIn('id_mapel', $mapelIds)
                ->when($idMapel, fn ($q) => $q->where('id_mapel', $idMapel))
                ->with(['pembuat', 'mapel', 'soal', 'materi'])
                ->latest('created_at')
                ->get();

            return view('guru.kuis.index', compact('kuis', 'mapelAktif', 'mapelTerpilih', 'daftarMateri'));
        }

        $mapelAktif = Mapel::aktif()
            ->withCount(['kuis' => function ($q) {
                $q->where('is_aktif', true);
            }])
            ->orderBy('nama_mapel')
            ->get();

        $mapelTerpilih = null;
        $daftarMateri = collect();
        if ($idMapel) {
            $mapelTerpilih = Mapel::where('id_mapel', $idMapel)->first();
            $daftarMateri = Materi::where('id_mapel', $idMapel)
                ->orderBy('bab')
                ->orderBy('judul')
                ->get();
        }

        $kuis = Kuis::where('is_aktif', true)
            ->when($idMapel, fn ($q) => $q->where('id_mapel', $idMapel))
            ->with(['pembuat', 'mapel', 'soal', 'materi'])
            ->latest('created_at')
            ->get();

        return view('guru.kuis.index', compact('kuis', 'mapelAktif', 'mapelTerpilih', 'daftarMateri'));
    }

    public function create(Request $request)
    {
        // Preselect dari shortcut "Buat Kuis" di halaman Materi
        $materiDipreselect = null;
        if ($request->filled('materi')) {
            $kandidat = Materi::find($request->query('materi'));
            if (! $kandidat) {
                return redirect()
                    ->route('materi.index')
                    ->with('error', 'Materi tidak ditemukan.');
            }

            $user = Auth::user();
            if ($user && $user->role === 'guru') {
                $mapelIds = Pengajaran::mapelUntukGuru($user);
                $berhak = $mapelIds->contains($kandidat->id_mapel);

                if (! $berhak) {
                    return redirect()
                        ->route('materi.index')
                        ->with('error', 'Anda tidak berhak membuat kuis untuk materi di luar penugasan pengajaran Anda.');
                }
            }

            $materiDipreselect = $kandidat;
        }

        // Preselect dan kunci kategori jika dikirim via query
        $kategoriDipreselect = null;
        if ($request->filled('kategori')) {
            $kategoriQuery = strtolower(trim((string) $request->query('kategori')));
            if (! in_array($kategoriQuery, self::KATEGORI, true)) {
                return redirect()
                    ->route('materi.index')
                    ->with('error', 'Kategori kuis tidak valid.');
            }
            $kategoriDipreselect = $kategoriQuery;
        }

        $user = Auth::user();
        $materiQuery = Materi::orderBy('bab')->orderBy('judul');
        if ($user && $user->role === 'guru') {
            $mapelIds = Pengajaran::mapelUntukGuru($user);
            $materiQuery->whereIn('id_mapel', $mapelIds);
        }
        $materi = $materiQuery->get();

        $kuisAsal = Kuis::where('is_aktif', true)
            ->whereRaw('LOWER(TRIM(kategori)) = ?', ['ulangan'])
            ->orderBy('judul')
            ->get();

        // Cek duplikat: satu materi hanya boleh punya 1 pre-test & 1 post-test aktif
        if ($materiDipreselect && $kategoriDipreselect && in_array($kategoriDipreselect, ['pre-test', 'post-test'], true)) {
            $sudahAda = $materiDipreselect->kuis()
                ->where('is_aktif', true)
                ->whereRaw('LOWER(TRIM(kategori)) = ?', [$kategoriDipreselect])
                ->exists();
            if ($sudahAda) {
                $labelKategori = ucfirst($kategoriDipreselect);

                return redirect()
                    ->route('materi.show', $materiDipreselect->id_materi)
                    ->with('error', "Materi ini sudah memiliki {$labelKategori} aktif. Setiap materi hanya boleh memiliki satu {$kategoriDipreselect}.");
            }
        }

        // Jika ada materi dipreselect, gunakan mapelnya; jika tidak, pakai query param
        $mapelTerpilih = $materiDipreselect
            ? $materiDipreselect->id_mapel
            : $request->query('mapel');

        $mapel = $this->pilihanMapel($mapelTerpilih);

        $tahunAjaran = TahunAjaran::aktif();

        return view('guru.kuis.create', compact(
            'materi', 'kuisAsal', 'mapel', 'tahunAjaran',
            'mapelTerpilih', 'materiDipreselect', 'kategoriDipreselect'
        ));
    }

    public function store(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();

        if (! $tahunAjaran) {
            return back()
                ->withInput()
                ->withErrors([
                    'kuis' => 'Belum ada tahun ajaran aktif. Hubungi admin sekolah untuk mengaktifkannya.',
                ]);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',

            'kategori' => ['required', Rule::in(self::KATEGORI)],

            'id_mapel' => $this->aturanMapel(),

            'tingkat_kesulitan' => ['required', Rule::in(['mudah', 'sedang', 'sulit'])],

            'alokasi_waktu' => 'nullable|integer|min:1|max:300',
            'kkm' => 'nullable|numeric|min:0|max:100',

            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date',

            'materi' => 'nullable|array',
            'materi.*' => 'exists:tm_materi,id_materi',

            'id_kuis_asal' => 'nullable|exists:tm_kuis,id_kuis',
            'soal' => 'prohibited',

            'tampil_jawaban' => ['nullable', Rule::in(self::PILIHAN_TAMPIL_JAWABAN)],
        ], [
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'tingkat_kesulitan.required' => 'Tingkat kesulitan wajib dipilih.',
            'soal.prohibited' => 'Gunakan endpoint soal untuk mengelola soal kuis.',
        ]);

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
        * Validasi khusus kategori ulangan & jadwal otomatis.
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

            $validated['waktu_mulai'] = $request->waktu_mulai;
            $validated['waktu_selesai'] = $request->waktu_selesai;

        } elseif (! empty($request->waktu_mulai) || ! empty($request->waktu_selesai)) {
            if (! empty($request->waktu_mulai) && ! empty($request->waktu_selesai)) {
                $waktuMulai = Carbon::parse($request->waktu_mulai);
                $waktuSelesai = Carbon::parse($request->waktu_selesai);

                if ($waktuSelesai->lte($waktuMulai)) {
                    throw ValidationException::withMessages([
                        'waktu_selesai' => 'Waktu selesai harus setelah waktu mulai.',
                    ]);
                }
            }

            $validated['waktu_mulai'] = $request->waktu_mulai ?: null;
            $validated['waktu_selesai'] = $request->waktu_selesai ?: null;

        } else {
            $validated['waktu_mulai'] = null;
            $validated['waktu_selesai'] = null;
        }

        $validated['id_kuis'] = $this->idBerikutnya('tm_kuis', 'id_kuis', 'K');
        $validated['id_tahun_ajaran'] = $tahunAjaran->id_tahun_ajaran;

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
        unset($validated['materi'], $validated['soal']);
        unset($validated['id_kuis_asal']);

        // Default tampil_jawaban jika tidak diisi
        $validated['tampil_jawaban'] = $validated['tampil_jawaban'] ?? 'setelah_submit';

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
        * Validasi: pre-test dan post-test maksimal 1 per materi (aktif).
        */
        if (in_array($kuis->kategori, ['pre-test', 'post-test'], true) && $request->filled('materi')) {
            $labelKategori = ucfirst($kuis->kategori);
            foreach ($request->materi as $idMateri) {
                $materiObj = Materi::find($idMateri);
                if (! $materiObj) {
                    continue;
                }
                $konflik = $materiObj->kuis()
                    ->where('is_aktif', true)
                    ->whereRaw('LOWER(TRIM(kategori)) = ?', [$kuis->kategori])
                    ->exists();
                if ($konflik) {
                    // Hapus kuis yang baru dibuat sebelum lempar error
                    $kuis->delete();
                    throw ValidationException::withMessages([
                        'materi' => 'Materi "'.$materiObj->judul.'" sudah memiliki '.$labelKategori.' aktif. Setiap materi hanya boleh memiliki satu '.$kuis->kategori.'.',
                    ]);
                }
            }
        }

        /*
        * Hubungkan materi untuk:
        * post-test, pre-test, kuis harian, ulangan.
        */
        if (
            $request->filled('materi') &&
            $kuis->kategori !== 'remedial'
        ) {
            $kuis->materi()->sync($request->materi);
        }

        return redirect()
            ->route('kuis.index', ['mapel' => $kuis->id_mapel])
            ->with('success', 'Kuis berhasil dibuat sebagai draft.');
    }

    public function show($id)
    {
        Kuis::sinkronisasiStatus();

        $kuis = Kuis::with([
            'pembuat',
            'materi',
            'soal',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user && $user->role === 'guru') {
            $mapelIds = Pengajaran::mapelUntukGuru($user);
            if (! $mapelIds->contains($kuis->id_mapel)) {
                abort(403, 'Anda tidak berhak mengakses kuis di luar penugasan pengajaran Anda.');
            }
        }

        return view('guru.kuis.show', compact('kuis'));
    }

    public function edit($id)
    {
        $kuis = Kuis::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak mengubah kuis milik guru lain.');
        }

        /*
         * Kuis yang sudah terbit tidak boleh diedit
         * sembarangan.
         */
        if ($kuis->status_publikasi === 'terbit') {
            return back()
                ->withErrors([
                    'kuis' => 'Kuis yang sudah terbit tidak dapat diedit. Ubah ke status draft terlebih dahulu.',
                ])
                ->with('error', 'Kuis yang sudah terbit tidak dapat diedit. Ubah ke status draft terlebih dahulu.');
        }

        $materiDikunci = in_array(strtolower(trim((string) $kuis->kategori)), ['pre-test', 'post-test'], true);

        $materi = Materi::orderBy('bab')
            ->orderBy('judul')
            ->get();
        $materiTerpilih = $kuis->materi
            ->pluck('id_materi')
            ->toArray();

        $mapel = $this->pilihanMapel($kuis->id_mapel);

        return view(
            'guru.kuis.edit',
            compact('kuis', 'materi', 'materiTerpilih', 'mapel', 'materiDikunci')
        );
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $kuis = Kuis::whereKey($id)->lockForUpdate()->firstOrFail();
            $user = Auth::user();

            if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
                abort(403, 'Anda tidak berhak mengubah kuis milik guru lain.');
            }

            $this->pastikanTidakAdaPenilaianTertunda($kuis);

            if ($kuis->status_publikasi === 'terbit') {
                return back()
                    ->withErrors([
                        'kuis' => 'Kuis yang sudah terbit tidak dapat diedit. Ubah ke status draft terlebih dahulu.',
                    ])
                    ->with('error', 'Kuis yang sudah terbit tidak dapat diedit. Ubah ke status draft terlebih dahulu.');
            }

            $validated = $request->validate([
                'judul' => 'required|string|max:150',
                'deskripsi' => 'nullable|string',
                'kategori' => ['required', Rule::in(self::KATEGORI)],
                'id_mapel' => $this->aturanMapel($kuis->id_mapel),
                'tingkat_kesulitan' => ['required', Rule::in(['mudah', 'sedang', 'sulit'])],
                'alokasi_waktu' => 'nullable|integer|min:1|max:300',
                'kkm' => 'nullable|numeric|min:0|max:100',
                'waktu_mulai' => 'nullable|date',
                'waktu_selesai' => 'nullable|date',
                'materi' => 'nullable|array',
                'materi.*' => 'exists:tm_materi,id_materi',
                'soal' => 'prohibited',
                'tampil_jawaban' => ['nullable', Rule::in(self::PILIHAN_TAMPIL_JAWABAN)],
            ], [
                'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
                'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
                'tingkat_kesulitan.required' => 'Tingkat kesulitan wajib dipilih.',
                'soal.prohibited' => 'Gunakan endpoint soal untuk mengelola soal kuis.',
            ]);

            if (in_array($validated['kategori'], ['pre-test', 'post-test'], true)
                && $kuis->soal()->where('tipe', 'essay')->exists()) {
                throw ValidationException::withMessages([
                    'kategori' => 'Kuis yang memiliki essay tidak dapat menjadi pre-test atau post-test.',
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

                $validated['waktu_mulai'] = $request->waktu_mulai;
                $validated['waktu_selesai'] = $request->waktu_selesai;
            } elseif (! empty($request->waktu_mulai) || ! empty($request->waktu_selesai)) {
                if (! empty($request->waktu_mulai) && ! empty($request->waktu_selesai)) {
                    $waktuMulai = Carbon::parse($request->waktu_mulai);
                    $waktuSelesai = Carbon::parse($request->waktu_selesai);

                    if ($waktuSelesai->lte($waktuMulai)) {
                        throw ValidationException::withMessages([
                            'waktu_selesai' => 'Waktu selesai harus setelah waktu mulai.',
                        ]);
                    }
                }

                $validated['waktu_mulai'] = $request->waktu_mulai ?: null;
                $validated['waktu_selesai'] = $request->waktu_selesai ?: null;
            } else {
                $validated['waktu_mulai'] = null;
                $validated['waktu_selesai'] = null;
            }

            unset($validated['materi'], $validated['soal']);
            $kuis->update($validated);

            // Pre-test dan Post-test: materi dikunci, tidak boleh diubah saat edit
            if (! in_array($kuis->kategori, ['pre-test', 'post-test', 'remedial'], true)) {
                $kuis->materi()->sync($request->materi ?? []);
            }

            return redirect()
                ->route('kuis.index', ['mapel' => $kuis->id_mapel])
                ->with('success', 'Kuis berhasil diperbarui.');
        });
    }

    public function destroy($id)
    {
        $kuis = Kuis::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak menghapus kuis milik guru lain.');
        }

        $idMapel = $kuis->id_mapel;

        /*
         * Soft delete / arsip.
         * Kita tidak menggunakan delete()
         * karena kuis bisa memiliki riwayat nilai.
         */
        $kuis->update([
            'is_aktif' => false,
        ]);

        return redirect()
            ->route('kuis.index', ['mapel' => $idMapel])
            ->with('success', 'Kuis berhasil diarsipkan.');
    }

    public function publish($id)
    {
        return DB::transaction(function () use ($id) {
            $kuis = Kuis::whereKey($id)->lockForUpdate()->firstOrFail();
            $kuis->load('soal');
            $user = Auth::user();

            if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
                abort(403, 'Anda tidak berhak mengubah status kuis milik guru lain.');
            }

            $this->pastikanTidakAdaPenilaianTertunda($kuis);

            if ($kuis->status_publikasi !== 'draft') {
                return back()->withErrors([
                    'kuis' => 'Kuis ini sudah terbit dan tidak dapat dipublikasikan ulang.',
                ]);
            }

            if (! $kuis->is_aktif) {
                return back()->withErrors([
                    'kuis' => 'Kuis yang sudah diarsipkan tidak dapat dipublikasikan.',
                ]);
            }

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

            if ($kuis->kategori === 'ulangan') {
                if (empty($kuis->waktu_mulai)) {
                    $errors[] = 'Waktu mulai wajib diisi untuk kategori ulangan.';
                }

                if (empty($kuis->waktu_selesai)) {
                    $errors[] = 'Waktu selesai wajib diisi untuk kategori ulangan.';
                }

                if ($kuis->waktu_mulai && $kuis->waktu_selesai && $kuis->waktu_selesai <= $kuis->waktu_mulai) {
                    $errors[] = 'Waktu selesai harus lebih besar dari waktu mulai.';
                }
            }

            if ($kuis->soal->count() === 0) {
                $errors[] = 'Kuis belum memiliki soal. Tambahkan minimal 1 soal.';
            }

            $totalBobot = 0;

            foreach ($kuis->soal as $index => $soal) {
                $nomor = $index + 1;

                if (empty(trim($soal->pertanyaan))) {
                    $errors[] = "Soal nomor {$nomor}: pertanyaan wajib diisi.";
                }

                if ($soal->tipe === 'essay') {
                    if (in_array(strtolower(trim((string) $kuis->kategori)), ['pre-test', 'post-test'], true)) {
                        $errors[] = "Soal nomor {$nomor}: essay tidak diperbolehkan untuk pre-test atau post-test.";
                    }
                } else {
                    foreach (['a', 'b', 'c', 'd'] as $opsiWajib) {
                        if (empty(trim($soal->{'opsi_'.$opsiWajib} ?? ''))) {
                            $label = strtoupper($opsiWajib);
                            $errors[] = "Soal nomor {$nomor}: opsi {$label} wajib diisi.";
                        }
                    }

                    if (empty($soal->jawaban)) {
                        $errors[] = "Soal nomor {$nomor}: jawaban benar wajib ditentukan.";
                    } else {
                        $jawaban = strtoupper($soal->jawaban);

                        if (! in_array($jawaban, ['A', 'B', 'C', 'D', 'E'])) {
                            $errors[] = "Soal nomor {$nomor}: jawaban harus A, B, C, D, atau E.";
                        } else {
                            $opsi = 'opsi_'.strtolower($jawaban);

                            if (empty(trim($soal->$opsi ?? ''))) {
                                $errors[] = "Soal nomor {$nomor}: jawaban {$jawaban} dipilih tetapi opsi {$jawaban} kosong.";
                            }
                        }
                    }
                }

                if ($soal->bobot === null || $soal->bobot <= 0) {
                    $errors[] = "Soal nomor {$nomor}: bobot harus lebih dari 0.";
                } else {
                    $totalBobot += (float) $soal->bobot;
                }
            }

            if ($kuis->soal()->count() > 0) {
                $this->seimbangkanBobot($kuis);
                $totalBobot = (float) $kuis->soal()->sum('bobot');
            }

            if ($kuis->soal->count() > 0 && round($totalBobot, 2) != 100) {
                $errors[] = 'Total bobot semua soal harus tepat 100. Total saat ini: '
                    .number_format($totalBobot, 2).'.';
            }

            if (! empty($errors)) {
                return back()->withErrors(['publish' => $errors])->withInput();
            }

            $kuis->update([
                'status_publikasi' => 'terbit',
                'id_publisher' => Auth::user()->id_user,
            ]);

            return redirect()
                ->route('kuis.show', $kuis->id_kuis)
                ->with('success', 'Kuis berhasil divalidasi dan dipublikasikan.');
        });
    }

    public function unpublish($id)
    {
        $kuis = Kuis::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak mengubah status kuis milik guru lain.');
        }

        if ($kuis->status_publikasi !== 'terbit') {
            return back()->withErrors([
                'kuis' => 'Kuis belum diterbitkan.',
            ]);
        }

        $kuis->update([
            'status_publikasi' => 'draft',
            'id_publisher' => null,
        ]);

        return redirect()
            ->route('kuis.show', $kuis->id_kuis)
            ->with('success', 'Kuis berhasil ditarik kembali menjadi draft.');
    }

    private function pastikanTidakAdaPenilaianTertunda(Kuis $kuis): void
    {
        if (app(KuisPenilaianService::class)->adaPenilaianTertunda($kuis->id_kuis)) {
            throw ValidationException::withMessages([
                'kuis' => 'Kuis tidak dapat diubah selama masih ada jawaban yang menunggu penilaian.',
            ]);
        }
    }

    private function seimbangkanBobot(Kuis $kuis): void
    {
        $soalList = $kuis->soal()->orderBy('id_soal')->get();
        $total = $soalList->count();

        if ($total === 0) {
            return;
        }

        $bobotPerSoal = round(100 / $total, 2);
        $totalTerhitung = $bobotPerSoal * $total;
        $selisih = round(100 - $totalTerhitung, 2);

        foreach ($soalList as $index => $item) {
            $bobotIni = $bobotPerSoal;
            if ($index === $total - 1 && $selisih != 0) {
                $bobotIni = round($bobotIni + $selisih, 2);
            }
            $item->update(['bobot' => $bobotIni]);
        }
    }
}
