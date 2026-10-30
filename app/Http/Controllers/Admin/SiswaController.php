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
            'tahun_ajaran' => ['nullable', 'string', 'exists:tahun_ajaran,id_tahun_ajaran'],
            'kelas' => ['nullable', 'string', 'exists:kelas,id_kelas'],
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
                        ->from('anggota_kelas')
                        ->whereColumn('anggota_kelas.id_user', 'user.id_user')
                        ->where('anggota_kelas.id_kelas', $request->input('kelas'));
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
            'nomor_induk' => ['required', 'string', 'max:50', 'regex:/^\d+$/', 'unique:user,nomor_induk'],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['required', 'string', 'min:6'],
            'id_kelas' => ['nullable', 'string', Rule::exists('kelas', 'id_kelas')->where('is_aktif', 1)],
        ], [
            'nomor_induk.regex' => 'NISN hanya boleh berisi angka.',
            'nomor_induk.unique' => 'NISN itu sudah terpakai.',
        ]);

        $siswa = DB::transaction(function () use ($validated, $request) {
            $siswa = User::create([
                'id_user' => $this->idBerikutnya('user', 'id_user', 'U'),
                'nomor_induk' => $validated['nomor_induk'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'role' => 'siswa',
                'status' => 'aktif',
                'email' => $validated['email'] ?? null,
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

        $daftarKelas = Kelas::with('tahunAjaran')->aktif()->orderBy('nama_kelas')->get();
        $kelasTerpilih = $siswa->kelasDiikuti->pluck('id_kelas')->all();

        return view('admin.siswa.edit', compact('siswa', 'daftarKelas', 'kelasTerpilih'));
    }

    public function update(Request $request, User $siswa)
    {
        abort_unless($siswa->role === 'siswa', 404);

        $validated = $request->validate([
            'nomor_induk' => [
                'required', 'string', 'max:50', 'regex:/^\d+$/',
                Rule::unique('user', 'nomor_induk')->ignore($siswa->id_user, 'id_user'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'nomor_induk.regex' => 'NISN hanya boleh berisi angka.',
        ]);

        $perubahan = [
            'nomor_induk' => $validated['nomor_induk'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'] ?? null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $perubahan['password'] = $validated['password'];
            $perubahan['wajib_ganti_password'] = true;
        }

        $siswa->update($perubahan);

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
