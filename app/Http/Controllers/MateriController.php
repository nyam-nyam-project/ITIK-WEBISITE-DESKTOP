<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'isi_materi' => 'nullable|string|max:255',
            'link_youtube' => 'nullable|url|max:255',
        ], [
            'judul.required' => 'Judul materi wajib diisi.',
            'judul.max' => 'Judul materi maksimal 150 karakter.',
            'link_youtube.url' => 'Link YouTube harus berupa URL yang valid.',
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

        //Sementara
        $validated['id_materi'] = 'M' . str_pad(
            (Materi::count() + 1),
            3,
            '0',
            STR_PAD_LEFT
        );

        $validated['id_user'] = Auth::user()->id_user;

        Materi::create($validated);

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show($id)
    {
        $materi = Materi::findOrFail($id);

        return view('materi.show', compact('materi'));
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
            'judul' => 'required|string|max:150',
            'bab' => 'nullable|string|max:50',
            'kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'isi_materi' => 'nullable|string|max:255',
            'link_youtube' => 'nullable|url|max:255',
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

        $materi->update($validated);

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $materi = Materi::findOrFail($id);

        $materi->delete();

        return redirect()
            ->route('materi.index')
            ->with('success', 'Materi berhasil dihapus.');
    }
}
