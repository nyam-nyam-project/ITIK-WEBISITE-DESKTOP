<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuruRemedialController extends Controller
{
    public function index(): JsonResponse
    {
        $remedials = Remedial::query()
            ->with(['kuisRemedial', 'kuisAsal'])
            ->latest('id_remedial')
            ->paginate(15);

        return response()->json([
            'data' => $remedials->items(),
            'meta' => [
                'current_page' => $remedials->currentPage(),
                'last_page' => $remedials->lastPage(),
                'per_page' => $remedials->perPage(),
                'total' => $remedials->total(),
            ],
        ]);
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
            ->whereKey($validated['id_kuis_asal'])
            ->where('is_aktif', true)
            ->where('status_publikasi', 'terbit')
            ->where('kategori', '!=', 'remedial')
            ->whereNotNull('kkm')
            ->first();

        if ($kuisAsal === null) {
            throw ValidationException::withMessages([
                'id_kuis_asal' => 'Kuis asal harus aktif, sudah terbit, dan bukan kuis remedial.',
            ]);
        }

        $kuis = DB::transaction(function () use ($validated, $request): Kuis {
            $kuis = Kuis::create([
                'id_kuis' => $this->newKuisId(),
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'kategori' => 'remedial',
                'tingkat_ujian' => 'mudah',
                'alokasi_waktu' => $validated['alokasi_waktu'] ?? null,
                'kkm' => $validated['kkm'] ?? null,
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'waktu_publish' => null,
                'maks_percobaan' => null,
                'id_user' => $request->user()->id_user,
                'status_publikasi' => 'draft',
                'id_publisher' => null,
                'is_aktif' => true,
            ]);

            Remedial::create([
                'id_kuis_remedial' => $kuis->id_kuis,
                'id_kuis_asal' => $validated['id_kuis_asal'],
            ]);

            return $kuis;
        });

        return response()->json([
            'data' => $kuis->load(['remedial.kuisAsal', 'soal']),
        ], 201);
    }

    public function show(string $remedial): JsonResponse
    {
        $remedial = Remedial::with([
            'kuisRemedial.soal',
            'kuisAsal',
            'remedialSiswa.mengerjakanAsal.siswa',
        ])->findOrFail($remedial);

        $studentIds = $remedial->remedialSiswa
            ->map(fn (RemedialSiswa $assignment): ?string => $assignment->mengerjakanAsal?->id_user)
            ->filter()
            ->unique()
            ->values();
        $results = Mengerjakan::query()
            ->where('id_kuis', $remedial->id_kuis_remedial)
            ->whereIn('id_user', $studentIds)
            ->where('status_pengerjaan', 'final')
            ->orderByDesc('percobaan_ke')
            ->get()
            ->unique('id_user')
            ->keyBy('id_user');

        $assignments = $remedial->remedialSiswa->map(function (RemedialSiswa $assignment) use ($results): array {
            $sourceResult = $assignment->mengerjakanAsal;

            return [
                'penugasan' => $assignment,
                'hasil_remedial' => $sourceResult === null
                    ? null
                    : $results->get($sourceResult->id_user),
            ];
        });

        return response()->json([
            'data' => $remedial,
            'siswa' => $assignments,
        ]);
    }

    public function students(string $remedial): JsonResponse
    {
        $remedial = Remedial::with(['kuisRemedial', 'kuisAsal'])->findOrFail($remedial);
        $kuisAsal = $remedial->kuisAsal;

        if ($kuisAsal === null || $kuisAsal->kkm === null) {
            throw ValidationException::withMessages([
                'id_kuis_asal' => 'Kuis asal atau KKM tidak tersedia.',
            ]);
        }

        $assignedIds = RemedialSiswa::query()
            ->where('id_kuis_remedial', $remedial->id_kuis_remedial)
            ->pluck('id_mengerjakan_asal')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $hasilSiswa = Mengerjakan::query()
            ->with('siswa')
            ->where('id_kuis', $kuisAsal->id_kuis)
            ->where('status_pengerjaan', 'final')
            ->where('status_validasi', 'sudah divalidasi')
            ->whereNotNull('nilai')
            ->where('nilai', '<', $kuisAsal->kkm)
            ->orderBy('nilai')
            ->get()
            ->map(function (Mengerjakan $hasil) use ($assignedIds): array {
                return [
                    'hasil' => $hasil,
                    'sudah_ditugaskan' => in_array((int) $hasil->id_mengerjakan, $assignedIds, true),
                ];
            });

        return response()->json([
            'data' => $hasilSiswa,
        ]);
    }

    public function assign(Request $request, string $remedial): JsonResponse
    {
        $remedial = Remedial::with('kuisAsal')->findOrFail($remedial);
        $validated = $request->validate([
            'id_mengerjakan' => ['required', 'array', 'min:1'],
            'id_mengerjakan.*' => ['required', 'integer', 'exists:mengerjakans,id_mengerjakan'],
        ]);

        $assigned = DB::transaction(function () use ($validated, $remedial, $request): int {
            $count = 0;
            foreach ($validated['id_mengerjakan'] as $idMengerjakan) {
                $hasil = Mengerjakan::findOrFail($idMengerjakan);

                if (
                    $hasil->id_kuis !== $remedial->id_kuis_asal ||
                    $hasil->status_pengerjaan !== 'final' ||
                    $hasil->status_validasi !== 'sudah divalidasi' ||
                    $hasil->nilai === null ||
                    $remedial->kuisAsal?->kkm === null ||
                    $hasil->nilai >= $remedial->kuisAsal->kkm
                ) {
                    throw ValidationException::withMessages([
                        'id_mengerjakan' => 'Hanya nilai tervalidasi di bawah KKM kuis asal yang dapat ditugaskan.',
                    ]);
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
                    ],
                );

                if ($assignment->wasRecentlyCreated) {
                    $count++;
                }
            }

            return $count;
        });

        return response()->json([
            'message' => 'Siswa berhasil diberikan tugas remedial.',
            'data' => [
                'jumlah_ditugaskan' => $assigned,
            ],
        ]);
    }

    private function newKuisId(): string
    {
        do {
            $idKuis = 'K'.Str::upper(Str::random(10));
        } while (Kuis::whereKey($idKuis)->exists());

        return $idKuis;
    }
}
