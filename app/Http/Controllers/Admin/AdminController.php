<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MengaturPaginasi;
use App\Support\MenghasilkanId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    use MengaturPaginasi;
    use MenghasilkanId;

    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'cari' => ['nullable', 'string', 'max:100'],
        ]);

        $admins = User::query()
            ->where('role', 'admin')
            ->with('pembuatAkun:id_user,nama_lengkap')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('cari'), function ($query) use ($request): void {
                $cari = $request->input('cari');
                $query->where(function ($subQuery) use ($cari): void {
                    $subQuery->where('nama_lengkap', 'like', "%{$cari}%")
                        ->orWhere('nomor_induk', 'like', "%{$cari}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->orderBy('id_user')
            ->paginate($this->batasPerHalaman($request))
            ->withQueryString();

        $jumlahAdminAktif = User::query()
            ->where('role', 'admin')
            ->where('status', 'aktif')
            ->count();

        return view('admin.admin.index', compact('admins', 'jumlahAdminAktif'));
    }

    public function create(): View
    {
        return view('admin.admin.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/\A[a-z0-9_]+\z/',
                Rule::unique('tm_user', 'nomor_induk'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('tm_user', 'email')],
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, dan garis bawah.',
            'username.unique' => 'Username tersebut sudah digunakan sebagai username, NISN, atau NIP.',
            'email.unique' => 'Email tersebut sudah digunakan.',
        ]);

        $admin = User::create([
            'id_user' => $this->idBerikutnya('tm_user', 'id_user', 'U'),
            'nomor_induk' => $validated['username'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'role' => 'admin',
            'status' => 'aktif',
            'email' => $validated['email'] ?? null,
            'password' => $validated['username'],
            'wajib_ganti_password' => true,
            'dibuat_oleh' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.admin.index')
            ->with('success', "Admin {$admin->nama_lengkap} berhasil ditambahkan.");
    }

    public function toggleStatus(User $admin): RedirectResponse
    {
        abort_unless($admin->role === 'admin', 404);

        $statusBaru = DB::transaction(function () use ($admin): string {
            $adminsAktif = User::query()
                ->where('role', 'admin')
                ->where('status', 'aktif')
                ->orderBy('id_user')
                ->lockForUpdate()
                ->get(['id_user']);

            $adminTerkunci = User::query()
                ->whereKey($admin->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($adminTerkunci->role === 'admin', 404);

            if ($adminTerkunci->status === 'aktif' && $adminsAktif->count() <= 1) {
                throw ValidationException::withMessages([
                    'admin' => 'Admin aktif terakhir tidak dapat dinonaktifkan.',
                ]);
            }

            if ($adminTerkunci->status === 'aktif' && $adminTerkunci->id_user === Auth::id()) {
                throw ValidationException::withMessages([
                    'admin' => 'Anda tidak dapat menonaktifkan akun sendiri.',
                ]);
            }

            $adminTerkunci->status = $adminTerkunci->status === 'aktif' ? 'nonaktif' : 'aktif';
            $adminTerkunci->save();

            return $adminTerkunci->status;
        });

        $pesan = $statusBaru === 'aktif'
            ? "{$admin->nama_lengkap} berhasil diaktifkan kembali."
            : "{$admin->nama_lengkap} berhasil dinonaktifkan. Akun tidak dihapus.";

        return redirect()
            ->route('admin.admin.index')
            ->with('success', $pesan);
    }
}
