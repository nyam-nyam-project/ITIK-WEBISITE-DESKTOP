<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MenghasilkanId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    use MenghasilkanId;

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'cari' => ['nullable', 'string', 'max:100'],
        ]);

        $guru = User::guru()
            ->withCount(['kuis', 'materi'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
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

        return view('admin.guru.index', compact('guru'));
    }

    public function create()
    {
        return view('admin.guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_induk' => ['required', 'string', 'max:50', 'regex:/^\d+$/', 'unique:user,nomor_induk'],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'nomor_induk.regex' => 'NIP hanya boleh berisi angka.',
            'nomor_induk.unique' => 'NIP itu sudah terpakai.',
        ]);

        $guru = User::create([
            'id_user' => $this->idBerikutnya('user', 'id_user', 'U'),
            'nomor_induk' => $validated['nomor_induk'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'role' => 'guru',
            'status' => 'aktif',
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'wajib_ganti_password' => true,
            'dibuat_oleh' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.guru.index')
            ->with('success', "Guru {$guru->nama_lengkap} berhasil ditambahkan.");
    }

    public function edit(User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        return view('admin.guru.edit', compact('guru'));
    }

    public function update(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $validated = $request->validate([
            'nomor_induk' => [
                'required', 'string', 'max:50', 'regex:/^\d+$/',
                Rule::unique('user', 'nomor_induk')->ignore($guru->id_user, 'id_user'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'nomor_induk.regex' => 'NIP hanya boleh berisi angka.',
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

        $guru->update($perubahan);

        return redirect()
            ->route('admin.guru.index')
            ->with('success', "Data {$guru->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Guru keluar ditandai nonaktif. FK fk_kuis_user dan fk_materi_user
     * memakai RESTRICT, jadi guru yang punya kuis atau materi memang tidak
     * bisa dihapus tanpa memindahkan kepemilikan kontennya lebih dulu.
     */
    public function nonaktifkan(User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $guru->update(['status' => 'nonaktif']);

        return redirect()
            ->route('admin.guru.index')
            ->with('success', "{$guru->nama_lengkap} ditandai keluar. Materi dan kuisnya tetap tersimpan.");
    }

    public function aktifkan(User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $guru->update(['status' => 'aktif']);

        return redirect()
            ->route('admin.guru.index')
            ->with('success', "{$guru->nama_lengkap} diaktifkan kembali.");
    }
}
