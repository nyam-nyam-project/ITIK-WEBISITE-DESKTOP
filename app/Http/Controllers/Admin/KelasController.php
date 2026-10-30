<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KelasController extends Controller
{
    use MenghasilkanId;

    public function index(Request $request)
    {
        $request->validate(['tahun_ajaran' => ['nullable', 'string', 'exists:tahun_ajaran,id_tahun_ajaran']]);
        $daftarTahun = TahunAjaran::orderByDesc('nama')->get();

        $idTahun = $request->input('tahun_ajaran')
            ?: optional(TahunAjaran::aktif())->id_tahun_ajaran;

        $kelas = Kelas::with('tahunAjaran', 'mapel')
            ->withCount('anggota')
            ->when($idTahun, fn ($query) => $query->where('id_tahun_ajaran', $idTahun))
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.kelas.index', compact('kelas', 'daftarTahun', 'idTahun'));
    }

    public function create()
    {
        $daftarTahun = TahunAjaran::orderByDesc('nama')->get();
        $daftarMapel = Mapel::aktif()->orderBy('nama_mapel')->get();

        return view('admin.kelas.create', compact('daftarTahun', 'daftarMapel'));
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);
        $idMapel = $validated['mapel'] ?? [];
        unset($validated['mapel']);

        $validated['id_kelas'] = $this->idBerikutnya('kelas', 'id_kelas', 'KLS');
        $validated['is_aktif'] = $request->boolean('is_aktif');

        $kelas = DB::transaction(function () use ($validated, $idMapel) {
            $kelas = Kelas::create($validated);
            $kelas->mapel()->sync($idMapel);

            return $kelas;
        });

        return redirect()
            ->route('admin.kelas.show', $kelas)
            ->with('success', "Kelas {$kelas->nama_kelas} berhasil ditambahkan.");
    }

    public function show(Kelas $kelas)
    {
        $kelas->load('tahunAjaran', 'mapel');

        $anggota = $kelas->siswa()->orderBy('nama_lengkap')->get();

        $siswaTersedia = User::siswa()
            ->aktif()
            ->whereDoesntHave('kelasDiikuti', fn ($q) => $q->where('id_tahun_ajaran', $kelas->id_tahun_ajaran))
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.kelas.show', compact('kelas', 'anggota', 'siswaTersedia'));
    }

    public function edit(Kelas $kelas)
    {
        $daftarTahun = TahunAjaran::orderByDesc('nama')->get();
        $mapelTerpilih = $kelas->mapel->pluck('id_mapel')->all();
        $daftarMapel = Mapel::where(fn ($q) => $q->where('is_aktif', 1)->orWhereIn('id_mapel', $mapelTerpilih))
            ->orderBy('nama_mapel')->get();

        return view('admin.kelas.edit', compact('kelas', 'daftarTahun', 'daftarMapel', 'mapelTerpilih'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $this->validasi($request, $kelas);
        $idMapel = $validated['mapel'] ?? [];
        unset($validated['mapel']);

        $validated['is_aktif'] = $request->boolean('is_aktif');

        DB::transaction(function () use ($kelas, $validated, $idMapel) {
            $kelas->update($validated);
            $kelas->mapel()->sync($idMapel);
        });

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', "Kelas {$kelas->nama_kelas} berhasil diperbarui.");
    }

    public function tambahAnggota(Request $request, Kelas $kelas)
    {
        abort_unless($kelas->is_aktif, 422, 'Kelas tidak aktif.');

        $validated = $request->validate([
            'id_user' => ['required', 'array', 'min:1'],
            'id_user.*' => ['required', 'string', 'distinct', Rule::exists('user', 'id_user')->where('role', 'siswa')->where('status', 'aktif')],
        ]);

        $jumlahBaru = DB::transaction(function () use ($validated, $kelas) {
            $siswa = User::whereIn('id_user', $validated['id_user'])->orderBy('id_user')->lockForUpdate()->get();
            $bentrok = $siswa->first(fn ($user) => $user->kelasDiikuti()
                ->where('id_tahun_ajaran', $kelas->id_tahun_ajaran)
                ->where('kelas.id_kelas', '!=', $kelas->id_kelas)->exists());

            if ($bentrok) {
                throw ValidationException::withMessages([
                    'id_user' => "{$bentrok->nama_lengkap} sudah terdaftar di kelas lain pada tahun ajaran ini.",
                ]);
            }

            $hasil = $kelas->siswa()->syncWithoutDetaching($siswa->modelKeys());

            return count($hasil['attached']);
        });

        return redirect()
            ->route('admin.kelas.show', $kelas)
            ->with('success', $jumlahBaru.' siswa berhasil dimasukkan ke '.$kelas->nama_kelas.'.');
    }

    private function validasi(Request $request, ?Kelas $kelas = null): array
    {
        $idTahun = $request->input('id_tahun_ajaran');

        return $request->validate([
            'nama_kelas' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelas', 'nama_kelas')
                    ->where(fn ($query) => $query->where('id_tahun_ajaran', $idTahun))
                    ->ignore($kelas?->id_kelas, 'id_kelas'),
            ],
            'tingkat' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'id_tahun_ajaran' => [
                'required', 'string', 'exists:tahun_ajaran,id_tahun_ajaran',
                ...($kelas ? [Rule::in([$kelas->id_tahun_ajaran])] : []),
            ],
            'is_aktif' => ['nullable', 'boolean'],
            'mapel' => ['nullable', 'array'],
            'mapel.*' => ['required', 'string', 'distinct', 'exists:mapel,id_mapel'],
        ], [
            'nama_kelas.unique' => 'Nama kelas itu sudah ada di tahun ajaran yang sama.',
        ]);
    }
}