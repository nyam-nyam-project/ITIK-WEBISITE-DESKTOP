<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use App\Models\Remedial;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GuruKuisController extends Controller
{
    public function index(): JsonResponse
    {
        $kuis = Kuis::query()
            ->with('materi')
            ->where('is_aktif', true)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => $kuis->items(),
            'meta' => [
                'current_page' => $kuis->currentPage(),
                'last_page' => $kuis->lastPage(),
                'per_page' => $kuis->perPage(),
                'total' => $kuis->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedKuis($request);
        $idKuisAsal = $validated['id_kuis_asal'] ?? null;
        unset($validated['materi'], $validated['id_kuis_asal']);

        $kuis = DB::transaction(function () use ($validated, $request, $idKuisAsal) {
            $kuis = Kuis::create([
                ...$validated,
                'id_kuis' => $this->newKuisId(),
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
            } elseif ($request->filled('materi')) {
                $kuis->materi()->sync($request->input('materi'));
            }

            return $kuis;
        });

        return response()->json([
            'data' => $kuis->load(['materi', 'soal']),
        ], 201);
    }

    public function show(string $kuis): JsonResponse
    {
        $kuis = Kuis::with(['materi', 'soal'])->findOrFail($kuis);

        return response()->json(['data' => $kuis]);
    }

    public function update(Request $request, string $kuis): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuis);

        if ($kuis->status_publikasi === 'terbit') {
            abort(409, 'Kuis yang sudah terbit tidak dapat diedit.');
        }

        $validated = $this->validatedKuis($request, $kuis);
        $idKuisAsal = $validated['id_kuis_asal'] ?? null;
        unset($validated['materi'], $validated['id_kuis_asal']);

        DB::transaction(function () use ($request, $validated, $kuis, $idKuisAsal): void {
            if (
                $kuis->status_publikasi === 'terjadwal' &&
                empty($validated['waktu_publish'])
            ) {
                $validated['status_publikasi'] = 'draft';
            }

            $kuis->update($validated);

            if ($kuis->kategori === 'remedial') {
                if ($idKuisAsal !== null) {
                    Remedial::updateOrCreate(
                        ['id_kuis_remedial' => $kuis->id_kuis],
                        ['id_kuis_asal' => $idKuisAsal],
                    );
                }
            } else {
                Remedial::where('id_kuis_remedial', $kuis->id_kuis)->delete();
                $kuis->materi()->sync($request->input('materi', []));
            }
        });

        return response()->json([
            'data' => $kuis->refresh()->load(['materi', 'soal']),
        ]);
    }

    public function destroy(string $kuis): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuis);
        $kuis->update(['is_aktif' => false]);

        return response()->json([
            'message' => 'Kuis berhasil diarsipkan.',
        ]);
    }

    public function publish(Request $request, string $kuis): JsonResponse
    {
        $kuis = Kuis::with('soal')->findOrFail($kuis);
        $errors = [];

        if (! in_array($kuis->status_publikasi, ['draft', 'terjadwal'], true)) {
            abort(409, 'Kuis ini sudah terbit dan tidak dapat dipublikasikan ulang.');
        }

        if (! $kuis->is_aktif) {
            abort(409, 'Kuis yang sudah diarsipkan tidak dapat dipublikasikan.');
        }

        if (blank($kuis->judul)) {
            $errors[] = 'Judul kuis wajib diisi.';
        }
        if (blank($kuis->kategori)) {
            $errors[] = 'Kategori kuis wajib diisi.';
        }
        if (! $kuis->alokasi_waktu || $kuis->alokasi_waktu <= 0) {
            $errors[] = 'Alokasi waktu kuis wajib diisi dan harus lebih dari 0 menit.';
        }
        if ($kuis->kkm === null || $kuis->kkm < 0 || $kuis->kkm > 100) {
            $errors[] = 'KKM wajib diisi dengan nilai antara 0 sampai 100.';
        }

        if ($kuis->kategori === 'ulangan') {
            if ($kuis->waktu_mulai === null) {
                $errors[] = 'Waktu mulai wajib diisi untuk kategori ulangan.';
            }
            if ($kuis->waktu_selesai === null) {
                $errors[] = 'Waktu selesai wajib diisi untuk kategori ulangan.';
            }
            if (
                $kuis->waktu_mulai !== null &&
                $kuis->waktu_selesai !== null &&
                $kuis->waktu_selesai->lessThanOrEqualTo($kuis->waktu_mulai)
            ) {
                $errors[] = 'Waktu selesai harus lebih besar dari waktu mulai.';
            }
        }

        if ($kuis->soal->isEmpty()) {
            $errors[] = 'Kuis belum memiliki soal. Tambahkan minimal 1 soal.';
        }

        $totalBobot = 0.0;
        foreach ($kuis->soal as $index => $soal) {
            $nomor = $index + 1;
            if (blank(trim($soal->pertanyaan))) {
                $errors[] = "Soal nomor {$nomor}: pertanyaan wajib diisi.";
            }
            if ($soal->tipe_soal === 'essay') {
                continue;
            }

            foreach (['a', 'b', 'c', 'd'] as $option) {
                $field = 'opsi_'.$option;
                if (blank(trim($soal->{$field} ?? '')) && blank($soal->{'gambar_'.$field})) {
                    $errors[] = "Soal nomor {$nomor}: opsi ".strtoupper($option).' wajib diisi.';
                }
            }

            $answer = strtoupper((string) $soal->jawaban);
            if (! in_array($answer, ['A', 'B', 'C', 'D', 'E'], true)) {
                $errors[] = "Soal nomor {$nomor}: jawaban benar wajib ditentukan.";
            } else {
                $field = 'opsi_'.strtolower($answer);
                if (blank(trim($soal->{$field} ?? '')) && blank($soal->{'gambar_'.$field})) {
                    $errors[] = "Soal nomor {$nomor}: jawaban {$answer} harus memiliki teks atau gambar.";
                }
            }

            if ($soal->bobot === null || $soal->bobot <= 0) {
                $errors[] = "Soal nomor {$nomor}: bobot harus lebih dari 0.";
            } else {
                $totalBobot += (float) $soal->bobot;
            }
        }

        if (
            $kuis->soal->where('tipe_soal', 'pilihan_ganda')->isNotEmpty() &&
            round($totalBobot, 2) !== 100.0
        ) {
            $errors[] = 'Total bobot semua soal harus tepat 100. Total saat ini: '.
                number_format($totalBobot, 2).'.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['publish' => $errors]);
        }

        $waktuPublish = $kuis->waktu_publish;
        $scheduled = $waktuPublish !== null && $waktuPublish->isFuture();

        $kuis->update([
            'status_publikasi' => $scheduled ? 'terjadwal' : 'terbit',
            'id_publisher' => $request->user()->id_user,
        ]);

        return response()->json([
            'data' => $kuis->refresh(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedKuis(Request $request, ?Kuis $kuis = null): array
    {
        $judulRule = Rule::unique('kuis', 'judul');
        if ($kuis !== null) {
            $judulRule->ignore($kuis->id_kuis, 'id_kuis');
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150', $judulRule],
            'deskripsi' => ['nullable', 'string'],
            'kategori' => ['required', Rule::in([
                'pre-test', 'post-test', 'kuis harian', 'ulangan', 'remedial',
            ])],
            'tingkat_ujian' => ['sometimes', 'required', Rule::in(['mudah', 'sedang', 'sulit'])],
            'alokasi_waktu' => ['nullable', 'integer', 'min:1', 'max:300'],
            'kkm' => ['nullable', 'integer', 'min:0', 'max:100'],
            'maks_percobaan' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'waktu_publish' => ['nullable', 'date', 'after_or_equal:now'],
            'waktu_mulai' => ['nullable', 'date'],
            'waktu_selesai' => ['nullable', 'date'],
            'materi' => ['nullable', 'array'],
            'materi.*' => ['exists:materis,id_materi'],
            'id_kuis_asal' => ['nullable', 'required_if:kategori,remedial', 'exists:kuis,id_kuis'],
        ], [
            'judul.unique' => 'Judul kuis sudah digunakan.',
            'waktu_publish.after_or_equal' => 'Jadwal publikasi tidak boleh sebelum tanggal dan waktu sekarang.',
        ]);

        $validated['tingkat_ujian'] ??= $kuis?->tingkat_ujian ?? 'mudah';

        if (
            $validated['kategori'] !== 'remedial' &&
            in_array($validated['tingkat_ujian'], ['sedang', 'sulit'], true) &&
            empty($validated['materi'])
        ) {
            throw ValidationException::withMessages([
                'materi' => 'Tingkat Sedang dan Sulit wajib dikaitkan dengan materi.',
            ]);
        }

        if ($validated['kategori'] === 'remedial' && ! empty($validated['id_kuis_asal'])) {
            $kuisAsal = Kuis::find($validated['id_kuis_asal']);
            if (
                $kuisAsal === null ||
                ! $kuisAsal->is_aktif ||
                $kuisAsal->kategori === 'remedial'
            ) {
                throw ValidationException::withMessages([
                    'id_kuis_asal' => 'Pilih kuis asal yang aktif dan bukan kuis remedial.',
                ]);
            }
        }

        if ($validated['kategori'] === 'ulangan') {
            if (empty($validated['waktu_mulai']) || empty($validated['waktu_selesai'])) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai dan waktu selesai wajib diisi untuk ulangan.',
                ]);
            }
            $start = Carbon::parse($validated['waktu_mulai']);
            $end = Carbon::parse($validated['waktu_selesai']);
            if ($start->isPast()) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai ulangan tidak boleh sebelum waktu sekarang.',
                ]);
            }
            if ($end->lessThanOrEqualTo($start)) {
                throw ValidationException::withMessages([
                    'waktu_selesai' => 'Waktu selesai harus setelah waktu mulai.',
                ]);
            }
        } else {
            $validated['waktu_mulai'] = null;
            $validated['waktu_selesai'] = null;
        }

        return $validated;
    }

    private function newKuisId(): string
    {
        do {
            $idKuis = 'K'.Str::upper(Str::random(10));
        } while (Kuis::whereKey($idKuis)->exists());

        return $idKuis;
    }
}
