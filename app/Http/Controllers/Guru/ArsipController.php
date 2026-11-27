<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Pengajaran;
use App\Support\MengaturPaginasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArsipController extends Controller
{
    use MengaturPaginasi;

    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'kuis');
        if (! in_array($tab, ['kuis', 'materi'], true)) {
            $tab = 'kuis';
        }

        $idMapel = $request->query('mapel');

        $mapelPengajaranIds = Pengajaran::mapelUntukGuru($user);

        $mapelList = Mapel::whereIn('id_mapel', $mapelPengajaranIds)
            ->orderBy('nama_mapel')
            ->get();

        $perPage = $this->batasPerHalaman($request);

        $kuisArsipQuery = Kuis::where('is_aktif', false)
            ->when($mapelPengajaranIds->isNotEmpty(), fn ($q) => $q->whereIn('id_mapel', $mapelPengajaranIds))
            ->when($idMapel, fn ($q) => $q->where('id_mapel', $idMapel))
            ->when($user->role === 'guru', fn ($q) => $q->where('id_user', $user->id_user));

        $materiArsipQuery = Materi::where('is_aktif', false)
            ->when($mapelPengajaranIds->isNotEmpty(), fn ($q) => $q->whereIn('id_mapel', $mapelPengajaranIds))
            ->when($idMapel, fn ($q) => $q->where('id_mapel', $idMapel))
            ->when($user->role === 'guru', fn ($q) => $q->where('id_user', $user->id_user));

        $totalKuisArsip = (clone $kuisArsipQuery)->count();
        $totalMateriArsip = (clone $materiArsipQuery)->count();

        $kuisArsip = $kuisArsipQuery
            ->with(['mapel', 'soal', 'materi'])
            ->withCount(['soal', 'mengerjakan'])
            ->orderByDesc('created_at')
            ->orderBy('id_kuis')
            ->paginate($perPage, ['*'], 'kuis_page')
            ->withQueryString();

        $materiArsip = $materiArsipQuery
            ->with(['mapel', 'kelasTerkait'])
            ->orderByDesc('created_at')
            ->orderBy('id_materi')
            ->paginate($perPage, ['*'], 'materi_page')
            ->withQueryString();

        return view('guru.arsip.index', compact(
            'tab',
            'idMapel',
            'mapelList',
            'kuisArsip',
            'materiArsip',
            'totalKuisArsip',
            'totalMateriArsip'
        ));
    }

    public function pulihkanKuis(string $id)
    {
        $kuis = Kuis::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak memulihkan kuis milik guru lain.');
        }

        DB::transaction(function () use ($kuis): void {
            $kuis->update([
                'is_aktif' => true,
                'diarsipkan_bersama' => null,
            ]);
        });

        return redirect()
            ->back()
            ->with('success', "Kuis \"{$kuis->judul}\" berhasil dipulihkan ke daftar aktif.");
    }

    public function hapusPermanenKuis(string $id)
    {
        $kuis = Kuis::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak menghapus kuis milik guru lain.');
        }

        if ($kuis->mengerjakan()->exists()) {
            return redirect()
                ->back()
                ->with('error', "Kuis \"{$kuis->judul}\" tidak dapat dihapus permanen karena memiliki data riwayat pengerjaan siswa. Kuis tetap tersimpan di dalam arsip.");
        }

        DB::transaction(function () use ($kuis) {
            $kuis->materi()->detach();
            if (method_exists($kuis, 'kelas')) {
                $kuis->kelas()->detach();
            }
            $kuis->soal()->delete();
            $kuis->delete();
        });

        return redirect()
            ->back()
            ->with('success', "Kuis \"{$kuis->judul}\" berhasil dihapus secara permanen.");
    }

    public function pulihkanMateri(string $id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $materi->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak memulihkan materi milik guru lain.');
        }

        $jumlahKuisDipulihkan = DB::transaction(function () use ($materi): int {
            $materi->update([
                'is_aktif' => true,
            ]);

            $kuisIkut = Kuis::where('diarsipkan_bersama', $materi->id_materi)->get();
            foreach ($kuisIkut as $kuis) {
                $kuis->update([
                    'is_aktif' => true,
                    'diarsipkan_bersama' => null,
                ]);
            }

            return $kuisIkut->count();
        });

        $pesan = "Materi \"{$materi->judul}\" berhasil dipulihkan ke daftar aktif.";
        if ($jumlahKuisDipulihkan > 0) {
            $pesan = "Materi \"{$materi->judul}\" beserta {$jumlahKuisDipulihkan} kuis terkait berhasil dipulihkan ke daftar aktif.";
        }

        return redirect()
            ->back()
            ->with('success', $pesan);
    }

    public function hapusPermanenMateri(string $id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $materi->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak menghapus materi milik guru lain.');
        }

        $pathPdf = $materi->isi_materi;
        $judul = $materi->judul;

        DB::transaction(function () use ($materi): void {
            Kuis::where('diarsipkan_bersama', $materi->id_materi)
                ->update(['diarsipkan_bersama' => null]);
            $materi->kuis()->detach();
            $materi->kelasTerkait()->detach();
            $materi->delete();
        });

        if ($pathPdf && Storage::disk('public')->exists($pathPdf)) {
            Storage::disk('public')->delete($pathPdf);
        }

        return redirect()
            ->back()
            ->with('success', "Materi \"{$judul}\" beserta filenya berhasil dihapus secara permanen.");
    }
}
