<?php

namespace App\Http\Controllers;

use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use Illuminate\Support\Facades\Auth;

class ValidasiNilaiController extends Controller
{
    /**
     * Menampilkan daftar nilai siswa
     */
    public function index()
    {
        $hasil = Mengerjakan::with(['kuis', 'siswa', 'validator'])
            ->where('status_pengerjaan', 'final')
            ->orderBy('status_validasi')
            ->orderByDesc('tanggal')
            ->get();

        return view('guru.nilai.index', compact('hasil'));
    }

    /**
     * Menampilkan detail hasil nilai siswa
     */
    public function show($id)
    {
        $mengerjakan = Mengerjakan::with(['siswa','kuis','validator',])
            ->findOrFail($id);

        $remedial = null;

        if ($mengerjakan->kuis->kategori === 'remedial') {

            $remedial = RemedialSiswa::with([
                'mengerjakanAsal.kuis',
            ])
                ->where(
                    'id_kuis_remedial',
                    $mengerjakan->id_kuis
                )
                ->whereHas('mengerjakanAsal', function ($query) use ($mengerjakan) {

                    $query->where(
                        'id_user',
                        $mengerjakan->id_user
                    );

                })
                ->first();
        }

        return view(
            'guru.nilai.show',
            compact(
                'mengerjakan',
                'remedial'
            )
        );
    }

    /**
     * Validasi nilai siswa
     */
    public function validasi(Mengerjakan $mengerjakan)
    {
        if ($mengerjakan->status_validasi === 'sudah divalidasi') {
            return redirect()
                ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
                ->with('error', 'Nilai ini sudah divalidasi.');
        }

        $mengerjakan->update([
            'status_validasi' => 'sudah divalidasi',
            'id_validator' => Auth::user()->id_user,
        ]);

        return redirect()
            ->route('guru.nilai.show', $mengerjakan->id_mengerjakan)
            ->with('success', 'Nilai siswa berhasil divalidasi.');
    }
}
