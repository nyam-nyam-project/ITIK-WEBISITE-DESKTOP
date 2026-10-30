<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Materi;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MateriController extends Controller
{
    use MenghasilkanId;

    public function index()
    {
        $materi = Materi::latest()->get();

        return view('materi.index', compact('materi'));
    }

    public function create()
    {
        $mapel = Mapel::aktif()->orderBy('nama_mapel')->get();

        return view('materi.create', compact('mapel'));
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

        $pdfUrl = $materi->pdfUrl();

        return view('materi.show', compact('materi', 'pdfUrl'));
    }

    public function edit($id)
    {
        $materi = Materi::findOrFail($id);
        $mapel = Mapel::where(fn ($q) => $q->where('is_aktif', 1)
                ->orWhere('id_mapel', $materi->id_mapel))
            ->orderBy('nama_mapel')
            ->get();

        return view('materi.edit', compact('materi', 'mapel'));
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

        if ($materi->isi_materi && Storage::disk('public')->exists($materi->isi_materi)) {
            Storage::disk('public')->delete($materi->isi_materi);
        }

        $materi->delete();

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil dihapus.');
    }
}
