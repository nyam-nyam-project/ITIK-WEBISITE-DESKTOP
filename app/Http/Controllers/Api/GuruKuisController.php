<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Remedial;
use App\Models\Soal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GuruKuisController extends Controller
{
    private const KATEGORI = ['post-test', 'pass-test', 'kuis harian', 'ulangan', 'remedial'];

    public function index(): JsonResponse
    {
        return response()->json(
            Kuis::query()
                ->where('is_aktif', true)
                ->with(['pembuat:id_user,nama_lengkap', 'materi:id_materi,judul'])
                ->latest('created_at')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedKuis($request);
        $materi = $validated['materi'] ?? [];
        $idKuisAsal = $validated['id_kuis_asal'] ?? null;
        unset($validated['materi'], $validated['id_kuis_asal']);

        $kuis = DB::transaction(function () use ($validated, $materi, $idKuisAsal, $request): Kuis {
            $kuis = Kuis::create([
                ...$validated,
                'id_kuis' => $this->nextKuisId(),
                'id_user' => $request->user()->id_user,
                'status_publikasi' => 'draft',
                'id_publisher' => null,
                'is_aktif' => true,
            ]);

            if ($kuis->kategori === 'remedial') {
                Remedial::create([
                    'id_kuis_remedial' => $kuis->id_kuis,
                    'id_kuis_asal' => $idKuisAsal,
                ]);
            } elseif ($materi !== []) {
                $kuis->materi()->sync($materi);
            }

            return $kuis;
        });

        return response()->json([
            'message' => 'Kuis berhasil dibuat sebagai draft.',
            'data' => $kuis->load(['materi', 'soal']),
        ], 201);
    }

    public function show(Kuis $kuis): JsonResponse
    {
        return response()->json([
            'data' => $kuis->load(['pembuat:id_user,nama_lengkap', 'materi', 'soal']),
        ]);
    }

    public function update(Request $request, Kuis $kuis): JsonResponse
    {
        abort_if($kuis->status_publikasi !== 'draft', 409, 'Kuis yang sudah terbit tidak dapat diedit.');

        $validated = $this->validatedKuis($request);
        $materi = $validated['materi'] ?? [];
        $idKuisAsal = $validated['id_kuis_asal'] ?? null;
        unset($validated['materi'], $validated['id_kuis_asal']);

        DB::transaction(function () use ($kuis, $validated, $materi, $idKuisAsal): void {
            $kuis->update($validated);

            if ($kuis->kategori === 'remedial') {
                Remedial::updateOrCreate(
                    ['id_kuis_remedial' => $kuis->id_kuis],
                    ['id_kuis_asal' => $idKuisAsal]
                );
            } else {
                $kuis->materi()->sync($materi);
                Remedial::where('id_kuis_remedial', $kuis->id_kuis)->delete();
            }
        });

        return response()->json([
            'message' => 'Kuis berhasil diperbarui.',
            'data' => $kuis->fresh()->load(['materi', 'soal']),
        ]);
    }

    public function destroy(Kuis $kuis): JsonResponse
    {
        $kuis->update(['is_aktif' => false]);

        return response()->json([
            'message' => 'Kuis berhasil diarsipkan.',
        ]);
    }

    public function publish(Request $request, Kuis $kuis): JsonResponse
    {
        abort_if($kuis->status_publikasi !== 'draft', 409, 'Kuis ini sudah terbit.');
        abort_if(!$kuis->is_aktif, 409, 'Kuis yang sudah diarsipkan tidak dapat dipublikasikan.');

        $soal = $kuis->soal()->orderBy('id_soal')->get();
        $errors = [];

        if (!$kuis->judul || !$kuis->kategori) {
            $errors[] = 'Judul dan kategori kuis wajib diisi.';
        }

        if (!$kuis->alokasi_waktu || $kuis->alokasi_waktu < 1) {
            $errors[] = 'Alokasi waktu harus lebih dari 0 menit.';
        }

        if ($kuis->kkm === null || $kuis->kkm < 0 || $kuis->kkm > 100) {
            $errors[] = 'KKM wajib diisi dengan nilai antara 0 sampai 100.';
        }

        if ($kuis->kategori === 'ulangan' && (
            !$kuis->waktu_mulai ||
            !$kuis->waktu_selesai ||
            $kuis->waktu_selesai->lessThanOrEqualTo($kuis->waktu_mulai)
        )) {
            $errors[] = 'Jadwal ulangan tidak valid.';
        }

        if ($soal->isEmpty()) {
            $errors[] = 'Kuis harus memiliki minimal satu soal.';
        }

        foreach ($soal as $index => $item) {
            $number = $index + 1;
            $correctOption = 'opsi_'.strtolower($item->jawaban);

            if (
                !$item->pertanyaan ||
                !$item->opsi_a ||
                !$item->opsi_b ||
                !$item->opsi_c ||
                !$item->opsi_d ||
                !in_array($item->jawaban, ['A', 'B', 'C', 'D', 'E'], true) ||
                !$item->$correctOption ||
                $item->bobot === null ||
                $item->bobot <= 0
            ) {
                $errors[] = "Data soal nomor {$number} belum lengkap.";
            }
        }

        $totalBobot = round((float) $soal->sum('bobot'), 2);

        if ($soal->isNotEmpty() && $totalBobot !== 100.0) {
            $errors[] = 'Total bobot semua soal harus tepat 100.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'publish' => $errors,
            ]);
        }

        $kuis->update([
            'status_publikasi' => 'terbit',
            'id_publisher' => $request->user()->id_user,
        ]);

        return response()->json([
            'message' => 'Kuis berhasil dipublikasikan.',
            'data' => $kuis->fresh(),
        ]);
    }

    public function storeSoal(Request $request, Kuis $kuis): JsonResponse
    {
        $this->ensureDraft($kuis);
        $validated = $this->validatedSoal($request);

        $duplicate = $kuis->soal()->where('pertanyaan', $validated['pertanyaan'])->exists();
        if ($duplicate) {
            throw ValidationException::withMessages([
                'pertanyaan' => ['Pertanyaan tersebut sudah ada di kuis ini.'],
            ]);
        }

        $this->ensureCorrectOption($validated);
        $totalBobot = (float) $kuis->soal()->sum('bobot') + (float) $validated['bobot'];

        if ($totalBobot > 100) {
            throw ValidationException::withMessages([
                'bobot' => ['Total bobot soal tidak boleh lebih dari 100.'],
            ]);
        }

        $soal = $kuis->soal()->create([
            ...$validated,
            'id_soal' => $this->nextSoalId(),
        ]);

        return response()->json([
            'message' => 'Soal berhasil ditambahkan.',
            'data' => $soal,
        ], 201);
    }

    public function updateSoal(Request $request, Kuis $kuis, Soal $soal): JsonResponse
    {
        $this->ensureSoalBelongsToKuis($kuis, $soal);
        $this->ensureDraft($kuis);
        $validated = $this->validatedSoal($request);

        $duplicate = $kuis->soal()
            ->where('pertanyaan', $validated['pertanyaan'])
            ->where('id_soal', '!=', $soal->id_soal)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'pertanyaan' => ['Pertanyaan tersebut sudah ada di kuis ini.'],
            ]);
        }

        $this->ensureCorrectOption($validated);
        $totalBobot = (float) $kuis->soal()->sum('bobot')
            - (float) $soal->bobot
            + (float) $validated['bobot'];

        if ($totalBobot > 100) {
            throw ValidationException::withMessages([
                'bobot' => ['Total bobot soal tidak boleh lebih dari 100.'],
            ]);
        }

        $soal->update($validated);

        return response()->json([
            'message' => 'Soal berhasil diperbarui.',
            'data' => $soal->fresh(),
        ]);
    }

    public function destroySoal(Kuis $kuis, Soal $soal): JsonResponse
    {
        $this->ensureSoalBelongsToKuis($kuis, $soal);
        $this->ensureDraft($kuis);
        $soal->delete();

        return response()->json([
            'message' => 'Soal berhasil dihapus.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedKuis(Request $request): array
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'kategori' => ['required', Rule::in(self::KATEGORI)],
            'alokasi_waktu' => ['nullable', 'integer', 'min:1', 'max:300'],
            'kkm' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'waktu_mulai' => ['required_if:kategori,ulangan', 'nullable', 'date'],
            'waktu_selesai' => ['required_if:kategori,ulangan', 'nullable', 'date'],
            'materi' => ['nullable', 'array'],
            'materi.*' => ['string', 'exists:materis,id_materi'],
            'id_kuis_asal' => ['required_if:kategori,remedial', 'nullable', 'string', 'exists:kuis,id_kuis'],
        ]);

        if ($validated['kategori'] === 'ulangan') {
            if (
                now()->greaterThan($validated['waktu_mulai']) ||
                now()->greaterThanOrEqualTo($validated['waktu_selesai']) ||
                $validated['waktu_selesai'] <= $validated['waktu_mulai']
            ) {
                throw ValidationException::withMessages([
                    'waktu_selesai' => ['Jadwal ulangan tidak valid.'],
                ]);
            }
        } else {
            $validated['waktu_mulai'] = null;
            $validated['waktu_selesai'] = null;
        }

        if ($validated['kategori'] === 'remedial') {
            $kuisAsal = Kuis::query()
                ->where('id_kuis', $validated['id_kuis_asal'])
                ->where('is_aktif', true)
                ->where('status_publikasi', 'terbit')
                ->where('kategori', '!=', 'remedial')
                ->first();

            if (!$kuisAsal) {
                throw ValidationException::withMessages([
                    'id_kuis_asal' => ['Kuis asal tidak valid untuk remedial.'],
                ]);
            }
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSoal(Request $request): array
    {
        return $request->validate([
            'pertanyaan' => ['required', 'string'],
            'opsi_a' => ['required', 'string', 'max:255'],
            'opsi_b' => ['required', 'string', 'max:255'],
            'opsi_c' => ['required', 'string', 'max:255'],
            'opsi_d' => ['required', 'string', 'max:255'],
            'opsi_e' => ['nullable', 'string', 'max:255'],
            'jawaban' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'bobot' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'tingkat_kesulitan' => ['nullable', Rule::in(['mudah', 'sedang', 'sulit'])],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function ensureCorrectOption(array $validated): void
    {
        $correctOption = 'opsi_'.strtolower($validated['jawaban']);

        if (empty(trim($validated[$correctOption] ?? ''))) {
            throw ValidationException::withMessages([
                'jawaban' => ["Opsi {$validated['jawaban']} yang menjadi jawaban benar wajib diisi."],
            ]);
        }
    }

    private function ensureDraft(Kuis $kuis): void
    {
        abort_if($kuis->status_publikasi !== 'draft', 409, 'Kuis yang sudah terbit tidak dapat diubah.');
    }

    private function ensureSoalBelongsToKuis(Kuis $kuis, Soal $soal): void
    {
        abort_unless($soal->id_kuis === $kuis->id_kuis, 404);
    }

    private function nextKuisId(): string
    {
        $number = Kuis::query()
            ->pluck('id_kuis')
            ->map(fn (string $id): int => (int) preg_replace('/^K/', '', $id))
            ->max() ?? 0;

        return 'K'.str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
    }

    private function nextSoalId(): string
    {
        $number = Soal::query()
            ->pluck('id_soal')
            ->map(fn (string $id): int => (int) preg_replace('/^S/', '', $id))
            ->max() ?? 0;

        return 'S'.str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
    }
}
