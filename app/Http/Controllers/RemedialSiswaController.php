<?php

namespace App\Http\Controllers;

use App\Models\RemedialSiswa;
use Illuminate\Support\Facades\Auth;

class RemedialSiswaController extends Controller
{
    /**
     * Daftar tugas remedial siswa
     */
    public function index()
    {
        $user = Auth::user();

        $tugas = RemedialSiswa::with([
            'kuisRemedial',
            'mengerjakanAsal.kuis',
        ])
            ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            })
            ->orderByDesc('tanggal_ditugaskan')
            ->get();

        return view('siswa.remedial.index', compact('tugas'));
    }

    /**
     * Siswa mulai mengerjakan remedial
     */
    public function kerjakan($id)
    {
        $user = Auth::user();

        $tugas = RemedialSiswa::with([
            'kuisRemedial',
            'mengerjakanAsal',
        ])
            ->where('id_remedial_siswa', $id)
            ->whereHas('mengerjakanAsal', function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            })
            ->firstOrFail();

        // Kalau sudah selesai, tidak boleh mengerjakan lagi
        if ($tugas->status === 'selesai') {
            return redirect()
                ->route('siswa.remedial.index')
                ->with('error', 'Tugas remedial ini sudah selesai dan tidak dapat dikerjakan lagi.');
        }

        // Pastikan kuis remedial masih ada
        if (!$tugas->kuisRemedial) {
            return redirect()
                ->route('siswa.remedial.index')
                ->with('error', 'Kuis remedial tidak ditemukan.');
        }

        // Arahkan ke halaman mulai kuis
        return redirect()->route(
            'siswa.kuis.mulai',
            $tugas->kuisRemedial->id_kuis
        );
    }
}
