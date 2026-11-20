<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    use MenghasilkanId;

    public function index(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => ['nullable', 'string', 'exists:tm_tahun_ajaran,id_tahun_ajaran'],
            'kelas' => ['nullable', 'string', 'exists:tm_kelas,id_kelas'],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'cari' => ['nullable', 'string', 'max:100'],
        ]);

        $daftarTahun = TahunAjaran::orderByDesc('nama')->get();
        $idTahun = $request->input('tahun_ajaran')
            ?: optional(TahunAjaran::aktif())->id_tahun_ajaran;

        $daftarKelas = Kelas::when($idTahun, fn ($q) => $q->where('id_tahun_ajaran', $idTahun))
            ->orderBy('nama_kelas')
            ->get();

        $siswa = User::siswa()
            ->with(['kelasDiikuti' => fn ($q) => $q->where('id_tahun_ajaran', $idTahun)])
            ->when($request->filled('tahun_ajaran'), fn ($q) => $q->whereHas('kelasDiikuti', fn ($sub) => $sub->where('id_tahun_ajaran', $idTahun)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('kelas'), function ($q) use ($request) {
                $q->whereExists(function ($sub) use ($request) {
                    $sub->select(DB::raw(1))
                        ->from('tp_anggota_kelas')
                        ->whereColumn('tp_anggota_kelas.id_user', 'tm_user.id_user')
                        ->where('tp_anggota_kelas.id_kelas', $request->input('kelas'));
                });
            })
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->input('cari');
                $q->where(function ($sub) use ($cari) {
                    $sub->where('nama_lengkap', 'like', "%{$cari}%")
                        ->orWhere('nomor_induk', 'like', "%{$cari}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate(25)
            ->withQueryString();

        return view('admin.siswa.index', compact('siswa', 'daftarKelas', 'daftarTahun', 'idTahun'));
    }

    public function create()
    {
        $daftarKelas = Kelas::with('tahunAjaran')
            ->aktif()
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.siswa.create', compact('daftarKelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_induk' => ['required', 'string', 'max:50', 'regex:/^\d+$/', 'unique:tm_user,nomor_induk'],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['required', 'string', 'min:6'],
            'id_kelas' => ['nullable', 'string', Rule::exists('tm_kelas', 'id_kelas')->where('is_aktif', 1)],
        ], [
            'nomor_induk.regex' => 'NISN hanya boleh berisi angka.',
            'nomor_induk.unique' => 'NISN itu sudah terpakai.',
        ]);

        $siswa = DB::transaction(function () use ($validated, $request) {
            $siswa = User::create([
                'id_user' => $this->idBerikutnya('tm_user', 'id_user', 'U'),
                'nomor_induk' => $validated['nomor_induk'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'role' => 'siswa',
                'status' => 'aktif',
                'email' => null,
                'password' => $validated['password'],
                'wajib_ganti_password' => true,
                'dibuat_oleh' => Auth::id(),
            ]);

            if ($request->filled('id_kelas')) {
                $kelas = Kelas::find($validated['id_kelas']);
                $kelas->siswa()->syncWithoutDetaching([$siswa->id_user]);
                $siswa->update(['kelas' => $kelas->tingkat]);
            }

            return $siswa;
        });

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', "Siswa {$siswa->nama_lengkap} berhasil ditambahkan.");
    }

    public function edit(User $siswa)
    {
        abort_unless($siswa->role === 'siswa', 404);

        $tahunAjaranAktif = TahunAjaran::aktif();
        $daftarKelas = Kelas::with('tahunAjaran')
            ->aktif()
            ->when(
                $tahunAjaranAktif !== null,
                fn ($query) => $query->where('id_tahun_ajaran', $tahunAjaranAktif->id_tahun_ajaran),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->orderBy('nama_kelas')
            ->get();
        $kelasTerpilih = $tahunAjaranAktif
            ? $siswa->kelasDiikuti()
                ->where('tm_kelas.id_tahun_ajaran', $tahunAjaranAktif->id_tahun_ajaran)
                ->value('tm_kelas.id_kelas')
            : null;

        return view('admin.siswa.edit', compact('siswa', 'daftarKelas', 'kelasTerpilih'));
    }

    public function update(Request $request, User $siswa)
    {
        abort_unless($siswa->role === 'siswa', 404);

        $tahunAjaranAktif = TahunAjaran::aktif();
        $validated = $request->validate([
            'nomor_induk' => [
                'required', 'string', 'max:50', 'regex:/^\d+$/',
                Rule::unique('tm_user', 'nomor_induk')->ignore($siswa->id_user, 'id_user'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'id_kelas' => ['sometimes', 'nullable', 'string', Rule::exists('tm_kelas', 'id_kelas')
                ->where(fn ($query) => $query
                    ->where('is_aktif', true)
                    ->where('id_tahun_ajaran', $tahunAjaranAktif?->id_tahun_ajaran ?? ''))],
        ], [
            'nomor_induk.regex' => 'NISN hanya boleh berisi angka.',
            'id_kelas.exists' => 'Kelas harus aktif pada tahun ajaran aktif.',
        ]);

        $perubahan = [
            'nomor_induk' => $validated['nomor_induk'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $perubahan['password'] = $validated['password'];
            $perubahan['wajib_ganti_password'] = true;
        }

        DB::transaction(function () use ($siswa, $perubahan, $validated, $tahunAjaranAktif): void {
            $siswa->update($perubahan);

            if (! array_key_exists('id_kelas', $validated) || $tahunAjaranAktif === null) {
                return;
            }

            $kelasDiTahunAktif = $siswa->kelasDiikuti()
                ->where('tm_kelas.id_tahun_ajaran', $tahunAjaranAktif->id_tahun_ajaran)
                ->pluck('tm_kelas.id_kelas')
                ->all();
            $kelasTujuan = $validated['id_kelas'];
            $kelasYangDilepas = array_values(array_filter(
                $kelasDiTahunAktif,
                fn (string $idKelas): bool => $idKelas !== $kelasTujuan
            ));

            if ($kelasYangDilepas !== []) {
                $siswa->kelasDiikuti()->detach($kelasYangDilepas);
            }

            if ($kelasTujuan !== null && ! in_array($kelasTujuan, $kelasDiTahunAktif, true)) {
                $siswa->kelasDiikuti()->attach($kelasTujuan);
            }
        });

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', "Data {$siswa->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Siswa keluar sekolah ditandai nonaktif, bukan dihapus. Baris `user`
     * dipakai oleh FK ON DELETE CASCADE di mengerjakan, progress, sesi_kuis,
     * dan draft_jawaban, jadi menghapusnya akan ikut membuang seluruh nilai.
     */
    public function nonaktifkan(User $siswa)
    {
        abort_unless($siswa->role === 'siswa', 404);

        $siswa->update(['status' => 'nonaktif']);

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', "{$siswa->nama_lengkap} ditandai keluar. Riwayat nilainya tetap tersimpan.");
    }

    public function aktifkan(User $siswa)
    {
        abort_unless($siswa->role === 'siswa', 404);

        $siswa->update(['status' => 'aktif']);

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', "{$siswa->nama_lengkap} diaktifkan kembali.");
    }
}
