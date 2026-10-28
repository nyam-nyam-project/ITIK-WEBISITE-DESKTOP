<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use App\Models\Soal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GuruSoalController extends Controller
{
    public function index(string $kuis): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuis);
        $soal = $kuis->soal()->orderBy('id_soal')->get();

        return response()->json([
            'data' => $soal->map(fn (Soal $item): array => $this->soalData($item)),
        ]);
    }

    public function store(Request $request, string $kuis): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuis);
        $this->ensureDraft($kuis);

        $validated = $request->validate($this->questionRules());
        $isMultipleChoice = $validated['tipe_soal'] === 'pilihan_ganda';
        $this->validateOptions($request, $validated, $isMultipleChoice);
        $this->validateDuplicateQuestion($kuis, $validated['pertanyaan']);
        $this->validateAnswerOption($request, $validated, $isMultipleChoice);

        $weight = $isMultipleChoice ? (float) $validated['bobot'] : 0;
        $this->validateTotalWeight($kuis, $weight);

        $question = $kuis->soal()->create([
            'id_soal' => $this->newQuestionId(),
            ...$this->questionAttributes($validated, $isMultipleChoice, $request, $kuis->id_kuis),
        ]);

        return response()->json([
            'data' => $this->soalData($question),
        ], 201);
    }

    public function show(string $kuis, string $soal): JsonResponse
    {
        $question = $this->findQuestion($kuis, $soal);

        return response()->json([
            'data' => $this->soalData($question),
        ]);
    }

    public function update(Request $request, string $kuis, string $soal): JsonResponse
    {
        $quiz = Kuis::findOrFail($kuis);
        $question = $this->findQuestion($kuis, $soal);
        $this->ensureDraft($quiz);

        $validated = $request->validate($this->questionRules());
        $isMultipleChoice = $validated['tipe_soal'] === 'pilihan_ganda';
        $this->validateOptions($request, $validated, $isMultipleChoice, $question);
        $this->validateDuplicateQuestion($quiz, $validated['pertanyaan'], $question);
        $this->validateAnswerOption($request, $validated, $isMultipleChoice, $question);

        $weight = $isMultipleChoice ? (float) $validated['bobot'] : 0;
        $this->validateTotalWeight($quiz, $weight, $question);

        $attributes = $this->questionAttributes($validated, $isMultipleChoice, $request, $quiz->id_kuis, $question);
        $oldImages = [];
        foreach ($this->imageFields() as $field) {
            if (
                array_key_exists($field, $attributes) &&
                $question->{$field} !== null &&
                $question->{$field} !== $attributes[$field]
            ) {
                $oldImages[] = $question->{$field};
            }
        }

        $question->update($attributes);

        if ($oldImages !== [] && ! Storage::disk('public')->delete($oldImages)) {
            throw new RuntimeException('Gagal menghapus gambar soal lama.');
        }

        return response()->json([
            'data' => $this->soalData($question->refresh()),
        ]);
    }

    public function destroy(string $kuis, string $soal): Response
    {
        $quiz = Kuis::findOrFail($kuis);
        $question = $this->findQuestion($kuis, $soal);
        $this->ensureDraft($quiz);

        $images = array_filter(array_map(
            fn (string $field): ?string => $question->{$field},
            $this->imageFields(),
        ));

        $question->delete();

        if ($images !== [] && ! Storage::disk('public')->delete($images)) {
            throw new RuntimeException('Gagal menghapus gambar soal.');
        }

        return response()->noContent();
    }

    private function ensureDraft(Kuis $kuis): void
    {
        if ($kuis->status_publikasi !== 'draft') {
            abort(409, 'Soal hanya dapat dikelola ketika kuis masih berstatus draft.');
        }
    }

    private function findQuestion(string $kuis, string $soal): Soal
    {
        return Soal::query()
            ->where('id_kuis', $kuis)
            ->findOrFail($soal);
    }

    private function questionRules(): array
    {
        $rules = [
            'tipe_soal' => ['required', Rule::in(['pilihan_ganda', 'essay'])],
            'pertanyaan' => ['required', 'string'],
            'opsi_a' => ['nullable', 'string', 'max:255'],
            'opsi_b' => ['nullable', 'string', 'max:255'],
            'opsi_c' => ['nullable', 'string', 'max:255'],
            'opsi_d' => ['nullable', 'string', 'max:255'],
            'opsi_e' => ['nullable', 'string', 'max:255'],
            'jawaban' => ['nullable', 'required_if:tipe_soal,pilihan_ganda', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'bobot' => ['nullable', 'required_if:tipe_soal,pilihan_ganda', 'numeric', 'min:0.01', 'max:100'],
            'tingkat_kesulitan' => ['nullable', Rule::in(['mudah', 'sedang', 'sulit'])],
        ];

        foreach ($this->imageFields() as $field) {
            $rules[$field] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
            $rules['hapus_'.$field] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateOptions(
        Request $request,
        array $validated,
        bool $isMultipleChoice,
        ?Soal $question = null,
    ): void {
        if (! $isMultipleChoice) {
            return;
        }

        $errors = [];
        foreach (['a', 'b', 'c', 'd'] as $option) {
            $field = 'opsi_'.$option;
            $imageField = 'gambar_'.$field;
            $hasExistingImage = $question?->{$imageField} !== null &&
                ! $request->boolean('hapus_'.$imageField);

            if (
                blank(trim($validated[$field] ?? '')) &&
                ! $request->hasFile($imageField) &&
                ! $hasExistingImage
            ) {
                $errors[$field] = 'Isi teks opsi atau unggah gambar opsi.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function validateDuplicateQuestion(Kuis $kuis, string $questionText, ?Soal $question = null): void
    {
        $query = $kuis->soal()->where('pertanyaan', $questionText);
        if ($question !== null) {
            $query->where('id_soal', '!=', $question->id_soal);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'pertanyaan' => 'Pertanyaan tersebut sudah digunakan dalam kuis ini.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateAnswerOption(
        Request $request,
        array $validated,
        bool $isMultipleChoice,
        ?Soal $question = null,
    ): void {
        if (! $isMultipleChoice) {
            return;
        }

        $option = 'opsi_'.strtolower($validated['jawaban']);
        $image = 'gambar_'.$option;
        $hasExistingImage = $question?->{$image} !== null &&
            ! $request->boolean('hapus_'.$image);

        if (
            blank(trim($validated[$option] ?? '')) &&
            ! $request->hasFile($image) &&
            ! $hasExistingImage
        ) {
            throw ValidationException::withMessages([
                $option => "Jawaban {$validated['jawaban']} harus memiliki teks atau gambar.",
            ]);
        }
    }

    private function validateTotalWeight(Kuis $kuis, float $weight, ?Soal $question = null): void
    {
        $query = $kuis->soal()->where('tipe_soal', 'pilihan_ganda');
        if ($question !== null) {
            $query->where('id_soal', '!=', $question->id_soal);
        }

        $total = (float) $query->sum('bobot') + $weight;
        if ($total > 100) {
            throw ValidationException::withMessages([
                'bobot' => 'Total bobot soal tidak boleh lebih dari 100.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function questionAttributes(
        array $validated,
        bool $isMultipleChoice,
        Request $request,
        string $quizId,
        ?Soal $question = null,
    ): array {
        $attributes = [
            'tipe_soal' => $validated['tipe_soal'],
            'pertanyaan' => $validated['pertanyaan'],
            'opsi_a' => $isMultipleChoice ? ($validated['opsi_a'] ?? null) : null,
            'opsi_b' => $isMultipleChoice ? ($validated['opsi_b'] ?? null) : null,
            'opsi_c' => $isMultipleChoice ? ($validated['opsi_c'] ?? null) : null,
            'opsi_d' => $isMultipleChoice ? ($validated['opsi_d'] ?? null) : null,
            'opsi_e' => $isMultipleChoice ? ($validated['opsi_e'] ?? null) : null,
            'jawaban' => $isMultipleChoice ? $validated['jawaban'] : null,
            'bobot' => $isMultipleChoice ? (float) $validated['bobot'] : 0,
            'tingkat_kesulitan' => $validated['tingkat_kesulitan'] ?? null,
        ];

        foreach ($this->imageFields() as $field) {
            if (! $isMultipleChoice && $field !== 'gambar_pertanyaan') {
                $attributes[$field] = null;

                continue;
            }

            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('soal-images/'.$quizId, 'public');
                if ($path === false) {
                    throw new RuntimeException('Gagal menyimpan gambar soal.');
                }
                $attributes[$field] = $path;
            } elseif ($request->boolean('hapus_'.$field)) {
                $attributes[$field] = null;
            } elseif ($question !== null) {
                $attributes[$field] = $question->{$field};
            } else {
                $attributes[$field] = null;
            }
        }

        return $attributes;
    }

    /**
     * @return list<string>
     */
    private function imageFields(): array
    {
        return [
            'gambar_pertanyaan',
            'gambar_opsi_a',
            'gambar_opsi_b',
            'gambar_opsi_c',
            'gambar_opsi_d',
            'gambar_opsi_e',
        ];
    }

    private function newQuestionId(): string
    {
        do {
            $idSoal = 'S'.Str::upper(Str::random(10));
        } while (Soal::whereKey($idSoal)->exists());

        return $idSoal;
    }

    /**
     * @return array<string, mixed>
     */
    private function soalData(Soal $soal): array
    {
        $data = $soal->toArray();
        foreach ($this->imageFields() as $field) {
            $data[$field.'_url'] = $soal->{$field} === null
                ? null
                : Storage::disk('public')->url($soal->{$field});
        }

        return $data;
    }
}
