<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\Mengerjakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RemedialController extends Controller
{
    /**
     * Menampilkan daftar kuis remedial.
     */
    public function index()
    {
        $remedials = Remedial::with([
            'kuisRemedial',
            'kuisAsal',
        ])
            ->latest('id_remedial')
            ->get();

        return view(
            'remedial.index',
            compact('remedials')
        );
    }

    /**
     * Form membuat kuis remedial.
     */
    public function create()
    {
        /*
         * Kuis yang dapat dijadikan asal remedial.
         *
         * Kuis remedial tidak boleh menjadi
         * kuis asal remedial lagi.
         */
        $kuisAsal = Kuis::where('is_aktif', true)
            ->where('status_publikasi', 'terbit')
            ->where('kategori', '!=', 'remedial')
            ->whereNotNull('kkm')
            ->orderByDesc('created_at')
            ->get();

        return view(
            'remedial.create',
            compact('kuisAsal')
        );
    }

    /**
     * Menyimpan kuis remedial + relasinya.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:150',

            'deskripsi' => 'nullable|string',

            'id_kuis_asal' => [
                'required',
                'string',
                'exists:kuis,id_kuis',
            ],

            'alokasi_waktu' => [
                'nullable',
                'integer',
                'min:1',
                'max:300',
            ],

            'kkm' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ]);

        /*
         * Pastikan kuis asal benar-benar aktif
         * dan bukan kuis remedial.
         */
        $kuisAsal = Kuis::where('id_kuis', $validated['id_kuis_asal'])
            ->where('is_aktif', true)
            ->first();

        if (!$kuisAsal) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_kuis_asal' =>
                        'Kuis asal tidak ditemukan atau sudah tidak aktif.',
                ]);
        }

        if ($kuisAsal->kategori === 'remedial') {
            return back()
                ->withInput()
                ->withErrors([
                    'id_kuis_asal' =>
                        'Kuis remedial tidak dapat dijadikan kuis asal.',
                ]);
        }

        /*
         * Guru yang membuat remedial.
         */
        $guru = Auth::user();

        /*
         * Buat ID kuis.
         */
        $idKuis = $this->generateKuisId();

        DB::transaction(function () use (
            $validated,
            $guru,
            $idKuis
        ) {

            /*
             * 1. Buat kuis baru.
             */
            $kuis = Kuis::create([
                'id_kuis' => $idKuis,

                'judul' => $validated['judul'],

                'deskripsi' => $validated['deskripsi'] ?? null,

                'kategori' => 'remedial',

                'alokasi_waktu' =>
                    $validated['alokasi_waktu'] ?? null,

                'kkm' =>
                    $validated['kkm'] ?? null,

                /*
                 * Remedial tidak menggunakan
                 * jendela waktu khusus ulangan.
                 */
                'waktu_mulai' => null,

                'waktu_selesai' => null,

                'id_user' => $guru->id_user,

                /*
                 * Kuis remedial tetap draft.
                 */
                'status_publikasi' => 'draft',

                'id_publisher' => null,

                'is_aktif' => true,
            ]);

            /*
             * 2. Hubungkan kuis remedial
             *    dengan kuis asal.
             */
            Remedial::create([
                'id_kuis_remedial' => $kuis->id_kuis,

                'id_kuis_asal' =>
                    $validated['id_kuis_asal'],
            ]);
        });

        return redirect()
            ->route('guru.remedial.index')
            ->with(
                'success',
                'Kuis remedial berhasil dibuat sebagai draft.'
            );
    }

    /**
     * Menampilkan detail kuis remedial.
     */
    public function show($id)
    {
        $remedial = Remedial::with([
            'kuisRemedial.soal',
            'kuisAsal',
            'kuisRemedial.remedialSiswa.mengerjakanAsal.siswa',
        ])->findOrFail($id);

        $remedial->remedialSiswa->each(function ($item) {

            $siswa = $item->mengerjakanAsal?->id_user;

            if (!$siswa) {
                $item->hasilRemedial = null;
                return;
            }

            $item->hasilRemedial = Mengerjakan::where(
                'id_kuis',
                $item->id_kuis_remedial
            )
                ->where('id_user', $siswa)
                ->where('status_pengerjaan', 'final')
                ->orderByDesc('percobaan_ke')
                ->first();
        });

        return view('remedial.show', compact('remedial'));
    }

    /**
     * Generate ID Kuis berikutnya.
     *
     * Contoh:
     * K001
     * K002
     * K003
     */
    private function generateKuisId(): string
    {
        $nomor = Kuis::query()
            ->pluck('id_kuis')
            ->map(function (string $idKuis): int {
                return (int) preg_replace('/^K/', '', $idKuis);
            })
            ->max() ?? 0;

        return 'K' . str_pad(
            $nomor + 1,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Menampilkan siswa yang nilainya di bawah KKM berdasarkan kuis asal.
     */
    public function siswa($idRemedial)
    {
        $remedial = Remedial::with([
            'kuisRemedial',
            'kuisAsal',
        ])->findOrFail($idRemedial);

        $kuisAsal = $remedial->kuisAsal;

        if (!$kuisAsal) {
            return back()->with('error', 'Kuis asal tidak ditemukan.');
        }

        $hasilSiswa = Mengerjakan::with('siswa')
            ->where('id_kuis', $kuisAsal->id_kuis)
            ->where('status_pengerjaan', 'final')
            ->where('status_validasi', 'sudah divalidasi')
            ->whereNotNull('nilai')
            ->where('nilai', '<', $kuisAsal->kkm)
            ->orderBy('nilai')
            ->get();

        $sudahDitugaskan = RemedialSiswa::where(
            'id_kuis_remedial',
            $remedial->id_kuis_remedial
        )
            ->pluck('id_mengerjakan_asal')
            ->toArray();

        return view(
            'remedial.siswa',
            compact(
                'remedial',
                'kuisAsal',
                'hasilSiswa',
                'sudahDitugaskan'
            )
        );
    }

    /**
     * Memberikan tugas remedial kepada siswa.
     */
    public function tugaskan(Request $request, $idRemedial)
    {
        $remedial = Remedial::with('kuisAsal')
            ->findOrFail($idRemedial);

        $request->validate([
            'id_mengerjakan' => 'required|array|min:1',
            'id_mengerjakan.*' => 'required|integer|exists:mengerjakan,id_mengerjakan',
        ]);

        DB::transaction(function () use ($request, $remedial) {
            foreach ($request->id_mengerjakan as $idMengerjakan) {
                $hasil = Mengerjakan::with('kuis')
                    ->findOrFail($idMengerjakan);

                if ($hasil->id_kuis !== $remedial->id_kuis_asal) {
                    abort(403);
                }

                if (
                    $hasil->nilai === null ||
                    $hasil->nilai >= $hasil->kuis->kkm
                ) {
                    continue;
                }

                RemedialSiswa::firstOrCreate(
                    [
                        'id_kuis_remedial' => $remedial->id_kuis_remedial,
                        'id_mengerjakan_asal' => $hasil->id_mengerjakan,
                    ],
                    [
                        'id_guru' => Auth::user()->id_user,
                        'tanggal_ditugaskan' => now()->toDateString(),
                        'status' => 'ditugaskan',
                    ]
                );
            }
        });

        return redirect()
            ->route('guru.remedial.siswa', $remedial->id_remedial)
            ->with('success', 'Siswa berhasil diberikan tugas remedial.');
    }
}
