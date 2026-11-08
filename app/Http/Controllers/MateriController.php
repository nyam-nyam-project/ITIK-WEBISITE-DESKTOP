<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Pengajaran;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
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

        if ($user && $user->role === 'guru' && Schema::hasTable('pengajaran')) {
            $mapelPengajaranIds = Pengajaran::where('id_user', $user->id_user)
                ->pluck('id_mapel')
                ->unique()
                ->all();

            if (empty($mapelPengajaranIds)) {
                $tanpaPengajaran = true;
                $mapelAktif = collect();
                $mapelTerpilih = null;
                $materi = collect();

                return view('guru.materi.index', compact('materi', 'mapelAktif', 'mapelTerpilih', 'idMapel', 'tanpaPengajaran'));
            }

            if ($idMapel && ! in_array($idMapel, $mapelPengajaranIds, true)) {
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
        if ($user && $user->role === 'guru' && Schema::hasTable('pengajaran')) {
            $mapelPengajaranIds = Pengajaran::where('id_user', $user->id_user)
                ->pluck('id_mapel')
                ->unique()
                ->all();

            $mapel = Mapel::aktif()
                ->when(! empty($mapelPengajaranIds), fn ($q) => $q->whereIn('id_mapel', $mapelPengajaranIds))
                ->orderBy('nama_mapel')
                ->get();
        } else {
            $mapel = Mapel::aktif()->orderBy('nama_mapel')->get();
        }

        return view('guru.materi.create', compact('mapel'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_mapel' => ['required', 'string', Rule::exists('mapel', 'id_mapel')->where('is_aktif', 1)],
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'judul.required' => 'Judul materi wajib diisi.',
            'judul.max' => 'Judul materi maksimal 150 karakter.',
            'link_youtube.url' => 'Link YouTube harus berupa URL yang valid.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ]);

        $duplikat = Materi::where('kelas', $request->kelas)
            ->where('bab', $request->bab)
            ->where('judul', $request->judul)
            ->exists();

        if ($duplikat) {
            return back()
                ->withInput()
                ->with('error', 'Materi dengan kelas, bab, dan judul tersebut sudah ada.');
        }

        $validated['id_materi'] = $this->idBerikutnya('materi', 'id_materi', 'M');

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

        if ($user && $user->role === 'guru' && Schema::hasTable('pengajaran')) {
            $mapelPengajaranIds = Pengajaran::where('id_user', $user->id_user)
                ->pluck('id_mapel')
                ->unique()
                ->all();

            if (! in_array($materi->id_mapel, $mapelPengajaranIds, true)) {
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

        if ($user && $user->role === 'guru' && Schema::hasTable('pengajaran')) {
            $mapelPengajaranIds = Pengajaran::where('id_user', $user->id_user)
                ->pluck('id_mapel')
                ->unique()
                ->all();

            if (! in_array($materi->id_mapel, $mapelPengajaranIds, true)) {
                abort(403, 'Anda tidak berhak mengubah materi di luar penugasan pengajaran Anda.');
            }
        }

        $mapel = Mapel::where(fn ($q) => $q->where('is_aktif', 1)
                ->orWhere('id_mapel', $materi->id_mapel))
            ->orderBy('nama_mapel')
            ->get();

        return view('guru.materi.edit', compact('materi', 'mapel'));
    }

    public function update(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);

        $validated = $request->validate([
            'id_mapel' => ['required', 'string', Rule::exists('mapel', 'id_mapel')
                ->where(fn ($q) => $q->where('is_aktif', 1)->orWhere('id_mapel', $materi->id_mapel))],
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ]);

        $duplikat = Materi::where('kelas', $request->kelas)
            ->where('bab', $request->bab)
            ->where('judul', $request->judul)
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
        $idMapel = $materi->id_mapel;

        if ($materi->isi_materi && Storage::disk('public')->exists($materi->isi_materi)) {
            Storage::disk('public')->delete($materi->isi_materi);
        }

        $materi->delete();

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil dihapus.');
    }
}
