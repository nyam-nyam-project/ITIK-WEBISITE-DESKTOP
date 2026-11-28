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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function konfirmasiHapusKuis(string $id)
    {
        $kuis = Kuis::with('mapel')->findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak menghapus kuis milik guru lain.');
        }

        $jumlahRiwayat = $kuis->mengerjakan()->count();
        $jumlahSiswaTerdampak = $kuis->mengerjakan()->distinct('id_user')->count('id_user');

        return view('guru.arsip.konfirmasi-hapus-kuis', compact(
            'kuis',
            'jumlahRiwayat',
            'jumlahSiswaTerdampak'
        ));
    }

    public function hapusPermanenKuis(Request $request, string $id)
    {
        $kuis = Kuis::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->role === 'guru' && $kuis->id_user !== $user->id_user) {
            abort(403, 'Anda tidak berhak menghapus kuis milik guru lain.');
        }

        if ($kuis->mengerjakan()->exists()) {
            $validator = Validator::make($request->all(), [
                'konfirmasi_hapus_nilai' => ['accepted'],
            ], [
                'konfirmasi_hapus_nilai.accepted' => 'Anda harus mencentang persetujuan penghapusan nilai siswa terlebih dahulu.',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('guru.arsip.kuis.konfirmasi-hapus', $kuis->id_kuis)
                    ->withErrors($validator)
                    ->with('error', "Kuis \"{$kuis->judul}\" memiliki riwayat pengerjaan siswa. Harap konfirmasi penghapusan nilai terlebih dahulu.");
            }
        }

        DB::transaction(function () use ($kuis): void {
            $mengerjakanIds = $kuis->mengerjakan()->pluck('id_mengerjakan');
            $soalIds = $kuis->soal()->pluck('id_soal');

            if (Schema::hasTable('tr_nilai_essay')) {
                DB::table('tr_nilai_essay')
                    ->where(function ($query) use ($mengerjakanIds, $soalIds): void {
                        if ($mengerjakanIds->isNotEmpty()) {
                            $query->whereIn('id_mengerjakan', $mengerjakanIds);
                        }
                        if ($soalIds->isNotEmpty()) {
                            $query->orWhereIn('id_soal', $soalIds);
                        }
                    })
                    ->when($mengerjakanIds->isEmpty() && $soalIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
                    ->delete();
            }

            if (Schema::hasTable('tr_remedial_siswa')) {
                DB::table('tr_remedial_siswa')
                    ->where('id_kuis_remedial', $kuis->id_kuis)
                    ->when($mengerjakanIds->isNotEmpty(), fn ($q) => $q->orWhereIn('id_mengerjakan_asal', $mengerjakanIds))
                    ->delete();
            }

            if (Schema::hasTable('tr_remedial')) {
                DB::table('tr_remedial')
                    ->where('id_kuis_remedial', $kuis->id_kuis)
                    ->orWhere('id_kuis_asal', $kuis->id_kuis)
                    ->delete();
            }

            if (Schema::hasTable('tr_draft_jawaban')) {
                DB::table('tr_draft_jawaban')
                    ->where('id_kuis', $kuis->id_kuis)
                    ->when($soalIds->isNotEmpty(), fn ($q) => $q->orWhereIn('id_soal', $soalIds))
                    ->delete();
            }

            if (Schema::hasTable('tr_sesi_kuis')) {
                DB::table('tr_sesi_kuis')
                    ->where('id_kuis', $kuis->id_kuis)
                    ->delete();
            }

            $kuis->mengerjakan()->delete();
            $kuis->materi()->detach();

            if (Schema::hasTable('tp_kuis_kelas') && method_exists($kuis, 'kelas')) {
                $kuis->kelas()->detach();
            }

            $kuis->soal()->delete();
            $kuis->delete();
        });

        return redirect()
            ->route('guru.arsip.index', ['tab' => 'kuis'])
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
