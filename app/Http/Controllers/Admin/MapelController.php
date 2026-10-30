<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    use MenghasilkanId;

    public function index()
    {
        $mapel = Mapel::withCount(['kelas', 'kuis', 'materi'])
            ->orderBy('kode_mapel')
            ->get();

        return view('admin.mapel.index', compact('mapel'));
    }

    public function create()
    {
        return view('admin.mapel.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        $validated['id_mapel'] = $this->idBerikutnya('mapel', 'id_mapel', 'MP');

        Mapel::create($validated);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', "Mata pelajaran {$validated['nama_mapel']} berhasil ditambahkan.");
    }

    public function edit(Mapel $mapel)
    {
        return view('admin.mapel.edit', compact('mapel'));
    }

    public function update(Request $request, Mapel $mapel)
    {
        $validated = $this->validasi($request, $mapel->id_mapel);

        $mapel->update($validated);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', "Mata pelajaran {$mapel->nama_mapel} berhasil diperbarui.");
    }

    private function validasi(Request $request, ?string $kecuali = null): array
    {
        if (is_string($request->input('kode_mapel'))) {
            $request->merge(['kode_mapel' => strtoupper($request->input('kode_mapel'))]);
        }

        $validated = $request->validate([
            'kode_mapel' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('mapel', 'kode_mapel')->ignore($kecuali, 'id_mapel'),
            ],
            'nama_mapel' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'is_aktif' => ['nullable', 'boolean'],
        ], [
            'kode_mapel.unique' => 'Kode mata pelajaran sudah digunakan.',
            'kode_mapel.alpha_dash' => 'Kode hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.',
        ]);

        $validated['kode_mapel'] = strtoupper($validated['kode_mapel']);
        $validated['is_aktif'] = $request->boolean('is_aktif');

        return $validated;
    }
}
