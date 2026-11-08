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

        return view('admin.pengajaran.index', compact(
            'pengajaran',
            'daftarTahun',
            'idTahun',
            'daftarGuru',
            'mapelKelas'
        ));
    }

    public function store(Request $request)
    {
        if ($request->filled('mapel_kelas') && (! $request->filled('id_kelas') || ! $request->filled('id_mapel'))) {
            $parts = explode(':', $request->input('mapel_kelas'));
            if (count($parts) === 2) {
                $request->merge([
                    'id_kelas' => $parts[0],
                    'id_mapel' => $parts[1],
                ]);
            }
        }

        $validated = $request->validate([
            'id_user' => ['required', 'string', Rule::exists('user', 'id_user')->where('role', 'guru')->where('status', 'aktif')],
            'id_kelas' => ['required', 'string', 'exists:kelas,id_kelas'],
            'id_mapel' => ['required', 'string', 'exists:mapel,id_mapel'],
        ], [
            'id_user.required' => 'Guru wajib dipilih.',
            'id_user.exists' => 'Guru yang dipilih tidak valid atau tidak aktif.',
            'id_kelas.required' => 'Kelas wajib dipilih.',
            'id_kelas.exists' => 'Kelas yang dipilih tidak valid.',
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'id_mapel.exists' => 'Mata pelajaran yang dipilih tidak valid.',
        ]);

        $adaDiMapelKelas = DB::table('mapel_kelas')
            ->where('id_kelas', $validated['id_kelas'])
            ->where('id_mapel', $validated['id_mapel'])
            ->exists();

        if (! $adaDiMapelKelas) {
            throw ValidationException::withMessages([
                'id_mapel' => 'Mata pelajaran tersebut tidak terdaftar pada kelas yang dipilih.',
            ]);
        }

        $duplikat = Pengajaran::where('id_user', $validated['id_user'])
            ->where('id_kelas', $validated['id_kelas'])
            ->where('id_mapel', $validated['id_mapel'])
            ->exists();

        if ($duplikat) {
            throw ValidationException::withMessages([
                'id_user' => 'Guru tersebut sudah ditugaskan mengajar mata pelajaran ini pada kelas yang dipilih.',
            ]);
        }

        Pengajaran::create($validated);

        return redirect()
            ->route('admin.pengajaran.index')
            ->with('success', 'Penugasan pengajaran berhasil ditambahkan.');
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
