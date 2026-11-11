<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Pengajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PengajaranController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => ['nullable', 'string', 'exists:tahun_ajaran,id_tahun_ajaran'],
        ]);

        $daftarTahun = TahunAjaran::orderByDesc('nama')->get();

        $idTahun = $request->input('tahun_ajaran')
            ?: optional(TahunAjaran::aktif())->id_tahun_ajaran;

        $pengajaran = Pengajaran::with(['guru', 'kelas.tahunAjaran', 'mapel'])
            ->when($idTahun, function ($query) use ($idTahun) {
                $query->whereHas('kelas', fn ($q) => $q->where('id_tahun_ajaran', $idTahun));
            })
            ->get();

        // Guru aktif untuk form penugasan
        $daftarGuru = User::guru()->aktif()->orderBy('nama_lengkap')->get();

        // Pilihan kelas dan mapel diambil dari mapel_kelas sesuai tahun ajaran
        $mapelKelas = DB::table('mapel_kelas')
            ->join('kelas', 'mapel_kelas.id_kelas', '=', 'kelas.id_kelas')
            ->join('mapel', 'mapel_kelas.id_mapel', '=', 'mapel.id_mapel')
            ->when($idTahun, fn ($q) => $q->where('kelas.id_tahun_ajaran', $idTahun))
            ->where('kelas.is_aktif', true)
            ->where('mapel.is_aktif', true)
            ->select(
                'mapel_kelas.id_kelas',
                'mapel_kelas.id_mapel',
                'kelas.nama_kelas',
                'mapel.nama_mapel',
                'mapel.kode_mapel'
            )
            ->orderBy('kelas.nama_kelas')
            ->orderBy('mapel.nama_mapel')
            ->get();

        // Daftar kelas aktif pada tahun ajaran ini
        $daftarKelas = Kelas::when($idTahun, fn ($q) => $q->where('id_tahun_ajaran', $idTahun))
            ->where('is_aktif', true)
            ->orderBy('nama_kelas')
            ->get();

        // Daftar mapel aktif
        $daftarMapel = Mapel::aktif()
            ->orderBy('nama_mapel')
            ->get();

        return view('admin.pengajaran.index', compact(
            'pengajaran',
            'daftarTahun',
            'idTahun',
            'daftarGuru',
            'mapelKelas',
            'daftarKelas',
            'daftarMapel'
        ));
    }

    public function store(Request $request)
    {
        // 1. Validasi guru
        $request->validate([
            'id_user' => ['required', 'string', Rule::exists('user', 'id_user')->where('role', 'guru')->where('status', 'aktif')],
        ], [
            'id_user.required' => 'Guru wajib dipilih.',
            'id_user.exists' => 'Guru yang dipilih tidak valid atau tidak aktif.',
        ]);

        $idUser = $request->input('id_user');

        // 2. Kumpulkan pasangan (kelas, mapel)
        $pasangan = [];

        // Dukungan format mapel_kelas (misal: "KLS001:MP001")
        if ($request->filled('mapel_kelas')) {
            $rawMapelKelas = is_array($request->input('mapel_kelas'))
                ? $request->input('mapel_kelas')
                : [$request->input('mapel_kelas')];
            foreach ($rawMapelKelas as $item) {
                $parts = explode(':', $item);
                if (count($parts) === 2) {
                    $pasangan[] = [
                        'id_kelas' => $parts[0],
                        'id_mapel' => $parts[1],
                    ];
                }
            }
        }

        // Dukungan format id_kelas[] / kelas[] dan id_mapel[] / mapel[]
        $kelasList = (array) ($request->input('id_kelas') ?? $request->input('kelas') ?? []);
        $mapelList = (array) ($request->input('id_mapel') ?? $request->input('mapel') ?? []);

        // Filter null / empty strings
        $kelasList = array_values(array_filter($kelasList));
        $mapelList = array_values(array_filter($mapelList));

        // Kombinasi dibuat dengan perkalian (Cartesian product)
        if (!empty($kelasList) && !empty($mapelList)) {
            foreach ($kelasList as $k) {
                foreach ($mapelList as $m) {
                    $pasangan[] = [
                        'id_kelas' => $k,
                        'id_mapel' => $m,
                    ];
                }
            }
        }

        // Jika tidak ada kelas atau mapel yang dipilih
        if (empty($pasangan)) {
            $errors = [];
            if (empty($kelasList) && empty($request->input('mapel_kelas'))) {
                $errors['id_kelas'] = 'Kelas wajib dipilih.';
            }
            if (empty($mapelList) && empty($request->input('mapel_kelas'))) {
                $errors['id_mapel'] = 'Mata pelajaran wajib dipilih.';
            }
            throw ValidationException::withMessages($errors);
        }

        // Hilangkan duplikat dari pasangan yang dikirim
        $pasanganUnik = collect($pasangan)->unique(fn ($p) => $p['id_kelas'] . ':' . $p['id_mapel'])->values();

        // 3. Pastikan kelas & mapel yang dipilih valid (terdaftar di database)
        foreach ($pasanganUnik as $p) {
            if (! DB::table('kelas')->where('id_kelas', $p['id_kelas'])->exists()) {
                throw ValidationException::withMessages([
                    'id_kelas' => 'Kelas yang dipilih tidak valid.',
                ]);
            }
            if (! DB::table('mapel')->where('id_mapel', $p['id_mapel'])->exists()) {
                throw ValidationException::withMessages([
                    'id_mapel' => 'Mata pelajaran yang dipilih tidak valid.',
                ]);
            }
        }

        // 4. Cek setiap pasangan terhadap mapel_kelas (kurikulum kelas)
        $pasanganValid = $pasanganUnik->filter(function ($p) {
            return DB::table('mapel_kelas')
                ->where('id_kelas', $p['id_kelas'])
                ->where('id_mapel', $p['id_mapel'])
                ->exists();
        });

        if ($pasanganValid->isEmpty()) {
            throw ValidationException::withMessages([
                'id_mapel' => 'Mata pelajaran tersebut tidak terdaftar pada kelas yang dipilih.',
            ]);
        }

        // 5. Buat satu baris pengajaran per pasangan yang valid, lewati kombinasi yang sudah ada
        $dilewati = $pasanganUnik->count() - $pasanganValid->count();
        $dibuat = 0;

        foreach ($pasanganValid as $p) {
            $sudahAda = Pengajaran::where('id_user', $idUser)
                ->where('id_kelas', $p['id_kelas'])
                ->where('id_mapel', $p['id_mapel'])
                ->exists();

            if ($sudahAda) {
                $dilewati++;
                continue;
            }

            Pengajaran::create([
                'id_user' => $idUser,
                'id_kelas' => $p['id_kelas'],
                'id_mapel' => $p['id_mapel'],
            ]);
            $dibuat++;
        }

        $pesan = "Penugasan pengajaran berhasil diproses: {$dibuat} dibuat, {$dilewati} dilewati.";

        return redirect()
            ->route('admin.pengajaran.index')
            ->with('success', $pesan);
    }

    public function destroy($id)
    {
        $pengajaran = Pengajaran::findOrFail($id);
        $pengajaran->delete();

        return redirect()
            ->route('admin.pengajaran.index')
            ->with('success', 'Penugasan pengajaran berhasil dihapus.');
    }
}
