<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuruRemedialController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Remedial::query()
                ->with(['kuisRemedial:id_kuis,judul,status_publikasi,is_aktif', 'kuisAsal:id_kuis,judul,kkm'])
                ->latest('id_remedial')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'id_kuis_asal' => ['required', 'string', 'exists:kuis,id_kuis'],
            'alokasi_waktu' => ['nullable', 'integer', 'min:1', 'max:300'],
            'kkm' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $kuisAsal = Kuis::query()
            ->where('id_kuis', $validated['id_kuis_asal'])
            ->where('is_aktif', true)
            ->where('status_publikasi', 'terbit')
            ->where('kategori', '!=', 'remedial')
            ->whereNotNull('kkm')
            ->first();

        if (!$kuisAsal) {
            throw ValidationException::withMessages([
                'id_kuis_asal' => ['Kuis asal tidak aktif, belum terbit, atau tidak memenuhi syarat remedial.'],
            ]);
        }

        $idKuis = $this->nextKuisId();

        $remedial = DB::transaction(function () use ($validated, $request, $idKuis): Remedial {
            $kuisRemedial = Kuis::create([
                'id_kuis' => $idKuis,
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'kategori' => 'remedial',
                'alokasi_waktu' => $validated['alokasi_waktu'] ?? null,
                'kkm' => $validated['kkm'] ?? null,
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'id_user' => $request->user()->id_user,
                'status_publikasi' => 'draft',
                'id_publisher' => null,
                'is_aktif' => true,
            ]);

            return Remedial::create([
                'id_kuis_remedial' => $kuisRemedial->id_kuis,
                'id_kuis_asal' => $validated['id_kuis_asal'],
            ]);
        });

        return response()->json([
            'message' => 'Kuis remedial berhasil dibuat sebagai draft.',
            'data' => $remedial->load(['kuisRemedial', 'kuisAsal']),
        ], 201);
    }

    public function show(Remedial $remedial): JsonResponse
    {
        $remedial->load([
            'kuisRemedial.soal',
            'kuisAsal',
            'remedialSiswa.kuisRemedial',
            'remedialSiswa.mengerjakanAsal.siswa',
        ]);

        return response()->json([
            'data' => $remedial,
        ]);
    }

    public function siswa(Remedial $remedial): JsonResponse
    {
        $kuisAsal = $remedial->kuisAsal;

        abort_if(!$kuisAsal || $kuisAsal->kkm === null, 404);

        $hasilSiswa = Mengerjakan::query()
            ->with('siswa:id_user,nama_lengkap,nomor_induk,kelas')
            ->where('id_kuis', $kuisAsal->id_kuis)
            ->where('status_pengerjaan', 'final')
            ->where('status_validasi', 'sudah divalidasi')
            ->whereNotNull('nilai')
            ->where('nilai', '<', $kuisAsal->kkm)
            ->whereDoesntHave('remedialSiswa', function (Builder $query) use ($remedial): void {
                $query->where('id_kuis_remedial', $remedial->id_kuis_remedial);
            })
            ->orderBy('nilai')
            ->paginate(20);

        return response()->json([
            'data' => $hasilSiswa,
            'kuis_asal' => [
                'id_kuis' => $kuisAsal->id_kuis,
                'judul' => $kuisAsal->judul,
                'kkm' => $kuisAsal->kkm,
            ],
        ]);
    }

    public function tugaskan(Request $request, Remedial $remedial): JsonResponse
    {
        $validated = $request->validate([
            'id_mengerjakan' => ['required', 'array', 'min:1'],
            'id_mengerjakan.*' => ['required', 'integer', 'distinct', 'exists:mengerjakans,id_mengerjakan'],
        ]);

        $ditugaskan = DB::transaction(function () use ($validated, $remedial, $request): int {
            $count = 0;

            foreach ($validated['id_mengerjakan'] as $idMengerjakan) {
                $hasil = Mengerjakan::query()
                    ->with('kuis:id_kuis,kkm')
                    ->where('id_mengerjakan', $idMengerjakan)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($hasil->id_kuis !== $remedial->id_kuis_asal) {
                    abort(404);
                }

                if (
                    $hasil->status_pengerjaan !== 'final' ||
                    $hasil->status_validasi !== 'sudah divalidasi' ||
                    $hasil->nilai === null ||
                    $hasil->kuis->kkm === null ||
                    $hasil->nilai >= $hasil->kuis->kkm
                ) {
                    continue;
                }

                $assignment = RemedialSiswa::firstOrCreate(
                    [
                        'id_kuis_remedial' => $remedial->id_kuis_remedial,
                        'id_mengerjakan_asal' => $hasil->id_mengerjakan,
                    ],
                    [
                        'id_guru' => $request->user()->id_user,
                        'tanggal_ditugaskan' => now()->toDateString(),
                        'status' => 'ditugaskan',
                    ]
                );

                if ($assignment->wasRecentlyCreated) {
                    $count++;
                }
            }

            return $count;
        });

        return response()->json([
            'message' => 'Penugasan remedial selesai.',
            'jumlah_ditugaskan' => $ditugaskan,
        ]);
    }

    private function nextKuisId(): string
    {
        $number = Kuis::query()
            ->pluck('id_kuis')
            ->map(fn (string $id): int => (int) preg_replace('/^K/', '', $id))
            ->max() ?? 0;

        return 'K'.str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
    }
}
