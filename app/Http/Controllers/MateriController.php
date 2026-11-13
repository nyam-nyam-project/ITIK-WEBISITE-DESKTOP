<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Pengajaran;
use App\Models\TahunAjaran;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MateriController extends Controller
{
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
                ->withCount('materi')
                ->orderBy('nama_mapel')
                ->get();

            $mapelTerpilih = null;
            $materi = collect();

            if ($idMapel) {
                $mapelTerpilih = Mapel::find($idMapel);
                if ($mapelTerpilih) {
                    $materi = Materi::where('id_mapel', $idMapel)
                        ->orderBy('bab')
                        ->orderBy('judul')
                        ->get();
                }
            }
        } else {
            // Admin tidak berubah
            $mapelAktif = Mapel::aktif()
                ->withCount('materi')
                ->orderBy('nama_mapel')
                ->get();

            $mapelTerpilih = null;
            $materi = collect();

            if ($idMapel) {
                $mapelTerpilih = Mapel::find($idMapel);
                if ($mapelTerpilih) {
                    $materi = Materi::where('id_mapel', $idMapel)
                        ->orderBy('bab')
                        ->orderBy('judul')
                        ->get();
                }
            }
        }

        return view('guru.materi.index', compact('materi', 'mapelAktif', 'mapelTerpilih', 'idMapel', 'tanpaPengajaran'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user && $user->role === 'guru') {
            $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

            $mapel = Mapel::aktif()
                ->when($mapelPengajaranIds->isNotEmpty(), fn ($q) => $q->whereIn('id_mapel', $mapelPengajaranIds), fn ($q) => $q->whereRaw('1 = 0'))
                ->orderBy('nama_mapel')
                ->get();
        } else {
            $mapel = Mapel::aktif()->orderBy('nama_mapel')->get();
        }

        $kelasOptions = $this->kelasOptions();

        return view('guru.materi.create', compact('mapel', 'kelasOptions'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $kelasOptions = $this->kelasOptions();

        $validated = $request->validate([
            'id_mapel' => [
                'required',
                'string',
                Rule::exists('tm_mapel', 'id_mapel')->where(function ($q) use ($user) {
                    $q->where('is_aktif', 1);
                    if ($user && $user->role === 'guru') {
                        $q->whereIn('id_mapel', Pengajaran::mapelUntukGuru($user));
                    }
                }),
            ],
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'kelas' => $this->kelasRules($kelasOptions),
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'judul.required' => 'Judul materi wajib diisi.',
            'judul.max' => 'Judul materi maksimal 150 karakter.',
            'kelas.in' => 'Kelas yang dipilih tidak tersedia.',
            'link_youtube.url' => 'Link YouTube harus berupa URL yang valid.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ]);

        if (isset($validated['bab']) && $validated['bab'] !== '') {
            $duplikatBab = Materi::query()
                ->where('id_mapel', $validated['id_mapel'])
                ->where('kelas', $validated['kelas'] ?? null)
                ->where('bab', $validated['bab'])
                ->exists();

            if ($duplikatBab) {
                return back()
                    ->withInput()
                    ->with('error', 'Bab tersebut sudah digunakan untuk mata pelajaran dan kelas ini.');
            }
        }

        $duplikat = Materi::where('kelas', $validated['kelas'] ?? null)
            ->where('bab', $validated['bab'] ?? null)
            ->where('judul', $validated['judul'])
            ->exists();

        if ($duplikat) {
            return back()
                ->withInput()
                ->with('error', 'Materi dengan kelas, bab, dan judul tersebut sudah ada.');
        }

        $validated['id_materi'] = $this->idBerikutnya('tm_materi', 'id_materi', 'M');

        $validated['id_user'] = Auth::user()->id_user;

        if ($request->hasFile('file_pdf')) {
            $validated['isi_materi'] = $request->file('file_pdf')->store('materi', 'public');
        }

        unset($validated['file_pdf']);

        Materi::create($validated);

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil ditambahkan.');
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

        $pdfUrl = $materi->pdfUrl();

        return view('guru.materi.show', compact('materi', 'pdfUrl'));
    }

    public function edit($id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru') {
            $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

            if (! $mapelPengajaranIds->contains($materi->id_mapel)) {
                abort(403, 'Anda tidak berhak mengubah materi di luar penugasan pengajaran Anda.');
            }
        }

        $mapel = Mapel::where(fn ($q) => $q->where('is_aktif', 1)
            ->orWhere('id_mapel', $materi->id_mapel))
            ->when($user && $user->role === 'guru', function ($q) use ($user) {
                $q->whereIn('id_mapel', Pengajaran::mapelUntukGuru($user));
            })
            ->orderBy('nama_mapel')
            ->get();

        $kelasOptions = $this->kelasOptions($materi->kelas);

        return view('guru.materi.edit', compact('materi', 'mapel', 'kelasOptions'));
    }

    public function update(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru') {
            $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

            if (! $mapelPengajaranIds->contains($materi->id_mapel)) {
                abort(403, 'Anda tidak berhak mengubah materi di luar penugasan pengajaran Anda.');
            }
        }

        $kelasOptions = $this->kelasOptions($materi->kelas);

        $validated = $request->validate([
            'id_mapel' => [
                'required',
                'string',
                Rule::exists('tm_mapel', 'id_mapel')->where(function ($q) use ($user, $materi) {
                    if ($user && $user->role === 'guru') {
                        $q->whereIn('id_mapel', Pengajaran::mapelUntukGuru($user));
                    }
                    $q->where(fn ($sub) => $sub->where('is_aktif', 1)->orWhere('id_mapel', $materi->id_mapel));
                }),
            ],
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'kelas' => $this->kelasRules($kelasOptions),
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'kelas.in' => 'Kelas yang dipilih tidak tersedia.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ]);

        if (isset($validated['bab']) && $validated['bab'] !== '') {
            $duplikatBab = Materi::query()
                ->where('id_mapel', $validated['id_mapel'])
                ->where('kelas', $validated['kelas'] ?? null)
                ->where('bab', $validated['bab'])
                ->where('id_materi', '!=', $materi->id_materi)
                ->exists();

            if ($duplikatBab) {
                return back()
                    ->withInput()
                    ->with('error', 'Bab tersebut sudah digunakan untuk mata pelajaran dan kelas ini.');
            }
        }

        $duplikat = Materi::where('kelas', $validated['kelas'] ?? null)
            ->where('bab', $validated['bab'] ?? null)
            ->where('judul', $validated['judul'])
            ->where('id_materi', '!=', $materi->id_materi)
            ->exists();

        if ($duplikat) {
            return back()
                ->withInput()
                ->with('error', 'Materi dengan kelas, bab, dan judul tersebut sudah digunakan oleh materi lain.');
        }

        if ($request->hasFile('file_pdf')) {
            if ($materi->isi_materi && Storage::disk('public')->exists($materi->isi_materi)) {
                Storage::disk('public')->delete($materi->isi_materi);
            }

            $validated['isi_materi'] = $request->file('file_pdf')->store('materi', 'public');
        }

        unset($validated['file_pdf']);

        $materi->update($validated);

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru') {
            $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

            if (! $mapelPengajaranIds->contains($materi->id_mapel)) {
                abort(403, 'Anda tidak berhak menghapus materi di luar penugasan pengajaran Anda.');
            }
        }

        $idMapel = $materi->id_mapel;

        if ($materi->isi_materi && Storage::disk('public')->exists($materi->isi_materi)) {
            Storage::disk('public')->delete($materi->isi_materi);
        }

        $materi->delete();

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * @return Collection<int, string>
     */
    private function kelasOptions(?string $currentKelas = null): Collection
    {
        if (! Schema::hasTable('tm_kelas')) {
            return $currentKelas === null ? collect() : collect([$currentKelas]);
        }

        $tahunAjaranAktif = Schema::hasTable('tm_tahun_ajaran')
            ? TahunAjaran::aktif()
            : null;

        $kelasOptions = Kelas::query()
            ->aktif()
            ->when(
                $tahunAjaranAktif !== null,
                fn ($query) => $query->where('id_tahun_ajaran', $tahunAjaranAktif->id_tahun_ajaran)
            )
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas');

        if ($currentKelas !== null && ! $kelasOptions->contains($currentKelas)) {
            $kelasOptions->push($currentKelas);
        }

        return $kelasOptions->values();
    }

    /**
     * @param  Collection<int, string>  $kelasOptions
     * @return array<int, mixed>
     */
    private function kelasRules(Collection $kelasOptions): array
    {
        $rules = ['nullable', 'string', 'max:50'];

        if (Schema::hasTable('tm_kelas')) {
            $rules[] = Rule::in($kelasOptions->all());
        }

        return $rules;
    }
}
