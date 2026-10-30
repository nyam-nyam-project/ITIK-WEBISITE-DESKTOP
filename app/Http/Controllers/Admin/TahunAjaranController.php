<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TahunAjaranController extends Controller
{
    use MenghasilkanId;

    public function index()
    {
        $tahunAjaran = TahunAjaran::withCount('kelas')
            ->orderByDesc('nama')
            ->get();

        return view('admin.tahun-ajaran.index', compact('tahunAjaran'));
    }

    public function create()
    {
        return view('admin.tahun-ajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        $validated['id_tahun_ajaran'] = $this->idBerikutnya('tahun_ajaran', 'id_tahun_ajaran', 'TA');
        $validated['is_aktif'] = 0;

        TahunAjaran::create($validated);

        return redirect()
            ->route('admin.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$validated['nama']} berhasil ditambahkan.");
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $validated = $this->validasi($request, $tahunAjaran->id_tahun_ajaran);

        $tahunAjaran->update($validated);

        return redirect()
            ->route('admin.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$tahunAjaran->nama} berhasil diperbarui.");
    }

    public function aktifkan(TahunAjaran $tahunAjaran)
    {
        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::orderBy('id_tahun_ajaran')->lockForUpdate()->get();
            TahunAjaran::where('is_aktif', 1)->update(['is_aktif' => 0]);
            TahunAjaran::whereKey($tahunAjaran->getKey())->update(['is_aktif' => 1]);
        });

        return redirect()
            ->route('admin.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$tahunAjaran->nama} sekarang aktif.");
    }

    private function validasi(Request $request, ?string $kecuali = null): array
    {
        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('tahun_ajaran', 'nama')->ignore($kecuali, 'id_tahun_ajaran'),
            ],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after:tanggal_mulai'],
        ], [
            'nama.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);
    }
}
