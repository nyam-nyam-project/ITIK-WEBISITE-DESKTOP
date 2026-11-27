<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Pengajaran;
use App\Models\User;
use App\Services\MateriKelasService;
use App\Support\MengaturPaginasi;
use App\Support\MenghasilkanId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class MateriController extends Controller
{
    use MengaturPaginasi;
    use MenghasilkanId;

    public function index(Request $request)
    {
        $idMapel = $request->query('mapel');
        $user = Auth::user();
        $tanpaPengajaran = false;

        if ($user && $user->role === 'guru') {
            $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

            if ($mapelPengajaranIds->isEmpty()) {
                $tanpaPengajaran = true;
                $mapelAktif = collect();
                $mapelTerpilih = null;
                $materi = collect();

                return view('guru.materi.index', compact('materi', 'mapelAktif', 'mapelTerpilih', 'idMapel', 'tanpaPengajaran'));
            }

            if ($idMapel && ! $mapelPengajaranIds->contains($idMapel)) {
                abort(403, 'Anda tidak berhak mengakses mata pelajaran di luar penugasan pengajaran Anda.');
            }

            $mapelAktif = Mapel::aktif()
                ->whereIn('id_mapel', $mapelPengajaranIds)
                ->withCount(['materi' => fn ($q) => $q->where('is_aktif', true)])
                ->orderBy('nama_mapel')
                ->get();

            $mapelTerpilih = null;
            $materi = collect();

            if ($idMapel) {
                $mapelTerpilih = Mapel::find($idMapel);
                if ($mapelTerpilih) {
                    $materi = Materi::where('is_aktif', true)
                        ->where('id_mapel', $idMapel)
                        ->with([
                            'kelasTerkait' => fn ($query) => $query->orderBy('tingkat')->orderBy('nama_kelas'),
                            'kuis' => fn ($query) => $query
                                ->where('is_aktif', true)
                                ->select('tm_kuis.id_kuis', 'tm_kuis.kategori'),
                        ])
                        ->orderBy('bab')
                        ->orderBy('judul')
                        ->orderBy('id_materi')
                        ->paginate($this->batasPerHalaman($request))
                        ->withQueryString();
                }
            }
        } else {
            // Admin tidak berubah
            $mapelAktif = Mapel::aktif()
                ->withCount(['materi' => fn ($q) => $q->where('is_aktif', true)])
                ->orderBy('nama_mapel')
                ->get();

            $mapelTerpilih = null;
            $materi = collect();

            if ($idMapel) {
                $mapelTerpilih = Mapel::find($idMapel);
                if ($mapelTerpilih) {
                    $materi = Materi::where('is_aktif', true)
                        ->where('id_mapel', $idMapel)
                        ->with('kelasTerkait')
                        ->orderBy('bab')
                        ->orderBy('judul')
                        ->orderBy('id_materi')
                        ->paginate($this->batasPerHalaman($request))
                        ->withQueryString();
                }
            }
        }

        return view('guru.materi.index', compact('materi', 'mapelAktif', 'mapelTerpilih', 'idMapel', 'tanpaPengajaran'));
    }

    public function create(Request $request, MateriKelasService $materiKelasService)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $mapelPengajaranIds = $materiKelasService->mapelIdsUntukGuru($user);
        $mapel = Mapel::aktif()
            ->whereIn('id_mapel', $mapelPengajaranIds)
            ->orderBy('nama_mapel')
            ->get();
        $idMapel = $request->query('mapel', $request->old('id_mapel'));
        $kelasOptions = $materiKelasService->kelasUntukGuru($user, is_string($idMapel) ? $idMapel : null);
        $kelasTerpilih = $request->old('id_kelas', []);
        $kelasTerpilih = is_array($kelasTerpilih) ? $kelasTerpilih : [];

        return view('guru.materi.create', compact('mapel', 'kelasOptions', 'idMapel', 'kelasTerpilih'));
    }

    public function kelasUntukMapel(Request $request, MateriKelasService $materiKelasService): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $idMapel = $request->string('id_mapel')->toString();
        abort_unless(
            $materiKelasService->mapelIdsUntukGuru($user)->contains($idMapel),
            403,
            'Anda tidak mengajar mata pelajaran tersebut pada tahun ajaran aktif.',
        );

        $kelas = $materiKelasService->kelasUntukGuru($user, $idMapel)
            ->map(fn (Kelas $item): array => [
                'id_kelas' => $item->id_kelas,
                'nama_kelas' => $item->nama_kelas,
                'tingkat' => $item->tingkat,
            ])
            ->values();

        return response()->json(['data' => $kelas]);
    }

    public function store(Request $request, MateriKelasService $materiKelasService)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $mapelPengajaranIds = $materiKelasService->mapelIdsUntukGuru($user);
        $idMapel = $request->input('id_mapel');
        $kelasOptions = $materiKelasService->kelasUntukGuru($user, is_string($idMapel) ? $idMapel : null);
        $idKelasDiizinkan = $kelasOptions->pluck('id_kelas')->all();

        $validated = $request->validate([
            'id_mapel' => [
                'required',
                'string',
                Rule::exists('tm_mapel', 'id_mapel')
                    ->where(fn ($query) => $query
                        ->where('is_aktif', true)
                        ->whereIn('id_mapel', $mapelPengajaranIds)),
            ],
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'id_kelas' => ['required', 'array', 'min:1'],
            'id_kelas.*' => ['required', 'string', 'distinct:strict', Rule::in($idKelasDiizinkan)],
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'judul.required' => 'Judul materi wajib diisi.',
            'judul.max' => 'Judul materi maksimal 150 karakter.',
            'id_kelas.required' => 'Pilih minimal satu kelas.',
            'id_kelas.min' => 'Pilih minimal satu kelas.',
            'id_kelas.*.in' => 'Kelas yang dipilih tidak Anda ajar untuk mata pelajaran ini pada tahun ajaran aktif.',
            'link_youtube.url' => 'Link YouTube harus berupa URL yang valid.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ]);

        $idKelasDipilih = array_values($validated['id_kelas']);
        $materiKelasService->pastikanBabUnik(
            $validated['bab'] ?? null,
            $validated['id_mapel'],
            $idKelasDipilih,
        );

        $validated['id_materi'] = $this->idBerikutnya('tm_materi', 'id_materi', 'M');
        $validated['id_user'] = $user->id_user;

        $path = null;
        if ($request->hasFile('file_pdf')) {
            $path = $request->file('file_pdf')->store('materi', 'public');
            if ($path === false) {
                throw new RuntimeException('File PDF materi gagal disimpan.');
            }
            $validated['isi_materi'] = $path;
        }

        unset($validated['file_pdf'], $validated['id_kelas']);

        try {
            DB::transaction(function () use ($validated, $idKelasDipilih): void {
                $materi = Materi::create($validated);
                $materi->kelasTerkait()->sync($idKelasDipilih);
            });
        } catch (Throwable $exception) {
            if ($path !== null) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('materi.index', ['mapel' => $validated['id_mapel']])
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function streamPdf(string $id)
    {
        $materi = Materi::findOrFail($id);

        if (! $materi->isi_materi || ! Storage::disk('public')->exists($materi->isi_materi)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        $path = storage_path('app/public/'.$materi->isi_materi);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    public function show($id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru') {
            $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

            if (! $mapelPengajaranIds->contains($materi->id_mapel)) {
                abort(403, 'Anda tidak berhak mengakses materi di luar penugasan pengajaran Anda.');
            }
        }

        $materi->load([
            'mapel',
            'kelasTerkait' => fn ($query) => $query->orderBy('tingkat')->orderBy('nama_kelas'),
            'kuis' => fn ($query) => $query
                ->where('is_aktif', true)
                ->select('tm_kuis.id_kuis', 'tm_kuis.kategori'),
        ]);

        $pdfUrl = $materi->pdfUrl();

        return view('guru.materi.show', compact('materi', 'pdfUrl'));
    }

    public function edit(Request $request, $id, MateriKelasService $materiKelasService)
    {
        $materi = Materi::findOrFail($id);
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $mapelPengajaranIds = $materiKelasService->mapelIdsUntukGuru($user);
        if (! $mapelPengajaranIds->contains($materi->id_mapel)) {
            abort(403, 'Anda tidak berhak mengubah materi di luar penugasan pengajaran Anda.');
        }

        $mapel = Mapel::where(fn ($query) => $query
            ->where('is_aktif', true)
            ->orWhere('id_mapel', $materi->id_mapel))
            ->whereIn('id_mapel', $mapelPengajaranIds)
            ->orderBy('nama_mapel')
            ->get();

        $idMapel = $request->old('id_mapel', $materi->id_mapel);
        $kelasOptions = $materiKelasService->kelasUntukGuru(
            $user,
            is_string($idMapel) ? $idMapel : null,
        );
        $materi->load('kelasTerkait');
        $kelasTerpilih = $request->old(
            'id_kelas',
            $materi->kelasTerkait->pluck('id_kelas')->all(),
        );
        $kelasTerpilih = is_array($kelasTerpilih) ? $kelasTerpilih : [];

        return view('guru.materi.edit', compact('materi', 'mapel', 'kelasOptions', 'idMapel', 'kelasTerpilih'));
    }

    public function update(Request $request, $id, MateriKelasService $materiKelasService)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        $mapelPengajaranIds = $materiKelasService->mapelIdsUntukGuru($user);

        if (! $mapelPengajaranIds->contains($materi->id_mapel)) {
            abort(403, 'Anda tidak berhak mengubah materi di luar penugasan pengajaran Anda.');
        }

        $idMapel = $request->input('id_mapel', $materi->id_mapel);
        $kelasOptions = $materiKelasService->kelasUntukGuru(
            $user,
            is_string($idMapel) ? $idMapel : null,
        );
        $idKelasDiizinkan = $kelasOptions->pluck('id_kelas')->all();

        $validated = $request->validate([
            'id_mapel' => [
                'required',
                'string',
                Rule::exists('tm_mapel', 'id_mapel')
                    ->where(fn ($query) => $query
                        ->whereIn('id_mapel', $mapelPengajaranIds)
                        ->where(fn ($subquery) => $subquery
                            ->where('is_aktif', true)
                            ->orWhere('id_mapel', $materi->id_mapel))),
            ],
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'id_kelas' => ['required', 'array', 'min:1'],
            'id_kelas.*' => ['required', 'string', 'distinct:strict', Rule::in($idKelasDiizinkan)],
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'judul.required' => 'Judul materi wajib diisi.',
            'judul.max' => 'Judul materi maksimal 150 karakter.',
            'id_kelas.required' => 'Pilih minimal satu kelas.',
            'id_kelas.min' => 'Pilih minimal satu kelas.',
            'id_kelas.*.in' => 'Kelas yang dipilih tidak Anda ajar untuk mata pelajaran ini pada tahun ajaran aktif.',
            'link_youtube.url' => 'Link YouTube harus berupa URL yang valid.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ]);

        $idKelasDipilih = array_values($validated['id_kelas']);
        $materiKelasService->pastikanBabUnik(
            $validated['bab'] ?? null,
            $validated['id_mapel'],
            $idKelasDipilih,
            $materi,
        );

        $oldPath = $materi->isi_materi;
        $newPath = null;
        if ($request->hasFile('file_pdf')) {
            $newPath = $request->file('file_pdf')->store('materi', 'public');
            if ($newPath === false) {
                throw new RuntimeException('File PDF materi gagal disimpan.');
            }
            $validated['isi_materi'] = $newPath;
        }

        unset($validated['file_pdf'], $validated['id_kelas']);

        try {
            DB::transaction(function () use ($materi, $validated, $idKelasDipilih): void {
                $materi->update($validated);
                $materi->kelasTerkait()->sync($idKelasDipilih);
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath !== null && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('materi.index', ['mapel' => $materi->id_mapel])
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function konfirmasiArsip(string $id)
    {
        $materi = Materi::with(['mapel', 'kelasTerkait'])->findOrFail($id);
        $this->pastikanGuruBerhak($materi);

        $kuisIkut = $this->kuisIkutDiarsipkan($materi);
        $jumlahSiswaTerdampak = $this->jumlahSiswaTerdampak($materi);

        return view('guru.materi.konfirmasi-arsip', compact(
            'materi',
            'kuisIkut',
            'jumlahSiswaTerdampak'
        ));
    }

    public function destroy($id)
    {
        $materi = Materi::findOrFail($id);
        $this->pastikanGuruBerhak($materi);

        $idMapel = $materi->id_mapel;

        /*
         * Soft delete / arsip dalam transaksi.
         * File PDF dan data riwayat/nilai tetap disimpan agar riwayat belajar siswa terjaga.
         * Pre-test, post-test, dan kuis harian yang tidak dipakai materi aktif lain ikut diarsipkan.
         */
        $jumlahKuisIkut = DB::transaction(function () use ($materi): int {
            $kuisIkut = $this->kuisIkutDiarsipkan($materi);

            $materi->update([
                'is_aktif' => false,
            ]);

            foreach ($kuisIkut as $kuis) {
                $kuis->update([
                    'is_aktif' => false,
                    'diarsipkan_bersama' => $materi->id_materi,
                ]);
            }

            return $kuisIkut->count();
        });

        $pesan = $jumlahKuisIkut > 0
            ? "Materi dan {$jumlahKuisIkut} kuis terkait berhasil diarsipkan."
            : 'Materi berhasil diarsipkan.';

        return redirect()
            ->route('materi.index', ['mapel' => $idMapel])
            ->with('success', $pesan);
    }

    private function pastikanGuruBerhak(Materi $materi): void
    {
        $user = Auth::user();

        if ($user && $user->role === 'guru') {
            $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

            if (! $mapelPengajaranIds->contains($materi->id_mapel)) {
                abort(403, 'Anda tidak berhak menghapus materi di luar penugasan pengajaran Anda.');
            }
        }
    }

    /**
     * @return Collection<int, Kuis>
     */
    private function kuisIkutDiarsipkan(Materi $materi)
    {
        $kuisTerkait = $materi->kuis()
            ->where('tm_kuis.is_aktif', true)
            ->orderBy('tm_kuis.judul')
            ->get();

        return $kuisTerkait->filter(function (Kuis $kuis) use ($materi): bool {
            $kategori = mb_strtolower(trim((string) $kuis->kategori));

            if (in_array($kategori, ['pre-test', 'post-test'], true)) {
                return true;
            }

            if ($kategori === 'kuis harian') {
                $dipakaiMateriAktifLain = $kuis->materi()
                    ->where('tm_materi.id_materi', '!=', $materi->id_materi)
                    ->where('tm_materi.is_aktif', true)
                    ->exists();

                return ! $dipakaiMateriAktifLain;
            }

            return false;
        })->values();
    }

    private function jumlahSiswaTerdampak(Materi $materi): int
    {
        return DB::table('tp_materi_kelas')
            ->join('tp_anggota_kelas', 'tp_anggota_kelas.id_kelas', '=', 'tp_materi_kelas.id_kelas')
            ->join('tm_user', 'tm_user.id_user', '=', 'tp_anggota_kelas.id_user')
            ->where('tp_materi_kelas.id_materi', $materi->id_materi)
            ->where('tm_user.role', 'siswa')
            ->where('tm_user.status', 'aktif')
            ->distinct()
            ->count('tm_user.id_user');
    }
}
