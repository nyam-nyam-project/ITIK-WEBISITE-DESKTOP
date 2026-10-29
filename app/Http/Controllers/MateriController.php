<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MateriController extends Controller
{
    public function index()
    {
        $materi = Materi::latest()->get();

        return view('materi.index', compact('materi'));
    }

    public function create()
    {
        return view('materi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
                Rule::unique('materis', 'judul'),
            ],
            'bab' => 'nullable|string|max:50',
            'kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'judul.required' => 'Judul materi wajib diisi.',
            'judul.max' => 'Judul materi maksimal 150 karakter.',
            'judul.unique' => 'Judul materi sudah digunakan.',
            'link_youtube.url' => 'Link YouTube harus berupa URL yang valid.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ]);

        // Sementara
        $validated['id_materi'] = 'M'.str_pad(
            (Materi::count() + 1),
            3,
            '0',
            STR_PAD_LEFT
        );

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

        return view('materi.edit', compact('materi'));
    }

    public function update(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);

        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
                Rule::unique('materis', 'judul')
                    ->ignore($materi->id_materi, 'id_materi'),
            ],
            'bab' => 'nullable|string|max:50',
            'kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'judul.unique' => 'Judul materi sudah digunakan.',
        ]);

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
