<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SoalController extends Controller
{
    public function index(Kuis $kuis)
    {
        $soal = $kuis->soal()
            ->orderBy('id_soal')
            ->get();

        return view('soal.index', compact('kuis', 'soal'));
    }

    public function create(Kuis $kuis)
    {
        if ($kuis->status_publikasi !== 'draft') {
            return redirect()
                ->route('kuis.soal.index', $kuis->id_kuis)
                ->with(
                    'error',
                    'Soal tidak dapat ditambahkan karena kuis sudah diterbitkan.'
                );
        }

        return view('soal.create', compact('kuis'));
    }

    public function store(Request $request, Kuis $kuis)
    {
        /*
         * Soal hanya boleh dibuat ketika kuis masih draft.
         */
        if ($kuis->status_publikasi !== 'draft') {
            return redirect()
                ->route('kuis.show', $kuis->id_kuis)
                ->with(
                    'error',
                    'Soal tidak dapat ditambahkan karena kuis sudah diterbitkan.'
                );
        }

        /*
         * Validasi data soal.
         */
        $validated = $request->validate($this->questionRules());
        $isPilihanGanda = $validated['tipe_soal'] === 'pilihan_ganda';
        $this->validateOptions($request, $validated, $isPilihanGanda);

        /*
         * Pertanyaan tidak boleh sama
         * dalam satu kuis.
         */
        $duplikat = $kuis->soal()
            ->where('pertanyaan', $validated['pertanyaan'])
            ->exists();

        if ($duplikat) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pertanyaan tersebut sudah ada di kuis ini.'
                );
        }

        /*
         * Pastikan jawaban yang dipilih
         * mempunyai opsi.
         */
        $opsiJawaban = 'opsi_'.strtolower($validated['jawaban'] ?? 'a');

        if (
            $isPilihanGanda &&
            empty(trim($validated[$opsiJawaban] ?? '')) &&
            ! $request->hasFile('gambar_'.$opsiJawaban)
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    "Jawaban {$validated['jawaban']} harus memiliki teks atau gambar."
                );
        }

        $bobot = $isPilihanGanda ? (float) $validated['bobot'] : 0;

        if ($isPilihanGanda) {
            $totalBobot = (float) $kuis->soal()
                ->where('tipe_soal', 'pilihan_ganda')
                ->sum('bobot');
            $totalSetelahTambah = $totalBobot + $bobot;

            if ($totalSetelahTambah > 100) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Total bobot soal tidak boleh lebih dari 100. '.
                        'Total saat ini: '.
                        number_format($totalBobot, 2).
                        ', bobot soal baru: '.
                        number_format($bobot, 2).
                        ', sehingga menjadi '.
                        number_format($totalSetelahTambah, 2).
                        '.'
                    );
            }
        }

        /*
         * Generate ID soal.
         */
        $jumlahSoal = $kuis->soal()->count();

        $idSoal = 'S'.str_pad(
            $jumlahSoal + 1,
            3,
            '0',
            STR_PAD_LEFT
        );

        /*
         * Pastikan ID tidak bentrok.
         */
        while (Soal::where('id_soal', $idSoal)->exists()) {
            $jumlahSoal++;

            $idSoal = 'S'.str_pad(
                $jumlahSoal + 1,
                3,
                '0',
                STR_PAD_LEFT
            );
        }

        /*
         * Simpan soal.
         */
        $attributes = [
            'id_soal' => $idSoal,
            'tipe_soal' => $validated['tipe_soal'],
            'pertanyaan' => $validated['pertanyaan'],
            'opsi_a' => $isPilihanGanda ? ($validated['opsi_a'] ?? null) : null,
            'opsi_b' => $isPilihanGanda ? ($validated['opsi_b'] ?? null) : null,
            'opsi_c' => $isPilihanGanda ? ($validated['opsi_c'] ?? null) : null,
            'opsi_d' => $isPilihanGanda ? ($validated['opsi_d'] ?? null) : null,
            'opsi_e' => $isPilihanGanda ? ($validated['opsi_e'] ?? null) : null,
            'jawaban' => $isPilihanGanda ? $validated['jawaban'] : null,
            'bobot' => $bobot,
            'tingkat_kesulitan' => $validated['tingkat_kesulitan'] ?? null,
        ];
        $attributes += $this->storeImages(
            $request,
            $kuis->id_kuis,
            $isPilihanGanda
        );

        $kuis->soal()->create($attributes);

        return redirect()
            ->route('kuis.soal.index', $kuis->id_kuis)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function show(Kuis $kuis, Soal $soal)
    {
        $this->checkSoalBelongsToKuis($kuis, $soal);

        return view('soal.show', compact('kuis', 'soal'));
    }

    public function edit(Kuis $kuis, Soal $soal)
    {
        $this->checkSoalBelongsToKuis($kuis, $soal);

        if ($kuis->status_publikasi !== 'draft') {
            return redirect()
                ->route('kuis.soal.index', $kuis->id_kuis)
                ->with(
                    'error',
                    'Soal tidak dapat diedit karena kuis sudah diterbitkan.'
                );
        }

        return view('soal.edit', compact('kuis', 'soal'));
    }

    public function update(
        Request $request,
        Kuis $kuis,
        Soal $soal
    ) {
        $this->checkSoalBelongsToKuis($kuis, $soal);

        if ($kuis->status_publikasi !== 'draft') {
            return redirect()
                ->route('kuis.soal.index', $kuis->id_kuis)
                ->with(
                    'error',
                    'Soal tidak dapat diedit karena kuis sudah diterbitkan.'
                );
        }

        /*
         * Validasi data soal.
         */
        $validated = $request->validate($this->questionRules());
        $isPilihanGanda = $validated['tipe_soal'] === 'pilihan_ganda';
        $this->validateOptions($request, $validated, $isPilihanGanda, $soal);

        /*
         * Pertanyaan tidak boleh sama dengan
         * soal lain dalam kuis yang sama.
         */
        $duplikat = $kuis->soal()
            ->where('pertanyaan', $validated['pertanyaan'])
            ->where('id_soal', '!=', $soal->id_soal)
            ->exists();

        if ($duplikat) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pertanyaan tersebut sudah digunakan oleh soal lain dalam kuis ini.'
                );
        }

        /*
         * Pastikan jawaban yang dipilih mempunyai opsi.
         */
        $opsiJawaban = 'opsi_'.strtolower($validated['jawaban'] ?? 'a');

        if (
            $isPilihanGanda &&
            empty(trim($validated[$opsiJawaban] ?? '')) &&
            ! $request->hasFile('gambar_'.$opsiJawaban) &&
            (
                empty($soal->{'gambar_'.$opsiJawaban}) ||
                $request->boolean('hapus_gambar_'.$opsiJawaban)
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    "Jawaban {$validated['jawaban']} harus memiliki teks atau gambar."
                );
        }

        /*
         * Hitung total bobot.
         *
         * Bobot soal yang sedang diedit
         * dikeluarkan terlebih dahulu.
         */
        $bobot = $isPilihanGanda ? (float) $validated['bobot'] : 0;

        if ($isPilihanGanda) {
            $totalBobotLain = (float) $kuis->soal()
                ->where('tipe_soal', 'pilihan_ganda')
                ->where('id_soal', '!=', $soal->id_soal)
                ->sum('bobot');
            $totalSetelahUpdate = $totalBobotLain + $bobot;

            if ($totalSetelahUpdate > 100) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Total bobot soal tidak boleh lebih dari 100. '.
                        'Total bobot soal lain: '.
                        number_format($totalBobotLain, 2).
                        ', bobot baru: '.
                        number_format($bobot, 2).
                        ', sehingga menjadi '.
                        number_format($totalSetelahUpdate, 2).
                        '.'
                    );
            }
        }

        $imageUpdates = $this->storeImages(
            $request,
            $kuis->id_kuis,
            $isPilihanGanda
        );

        if (! $isPilihanGanda) {
            foreach (array_slice($this->imageFields(), 1) as $field) {
                $imageUpdates[$field] = null;
            }
        }

        $oldImages = [];

        foreach ($this->imageFields() as $field) {
            if (
                array_key_exists($field, $imageUpdates) &&
                $soal->{$field} &&
                $soal->{$field} !== $imageUpdates[$field]
            ) {
                $oldImages[] = $soal->{$field};
            }
        }

        $attributes = [
            'tipe_soal' => $validated['tipe_soal'],
            'pertanyaan' => $validated['pertanyaan'],
            'opsi_a' => $isPilihanGanda ? ($validated['opsi_a'] ?? null) : null,
            'opsi_b' => $isPilihanGanda ? ($validated['opsi_b'] ?? null) : null,
            'opsi_c' => $isPilihanGanda ? ($validated['opsi_c'] ?? null) : null,
            'opsi_d' => $isPilihanGanda ? ($validated['opsi_d'] ?? null) : null,
            'opsi_e' => $isPilihanGanda ? ($validated['opsi_e'] ?? null) : null,
            'jawaban' => $isPilihanGanda ? $validated['jawaban'] : null,
            'bobot' => $bobot,
            'tingkat_kesulitan' => $validated['tingkat_kesulitan'] ?? null,
        ] + $imageUpdates;

        $soal->update($attributes);

        if ($oldImages !== [] && ! Storage::disk('public')->delete($oldImages)) {
            throw new RuntimeException('Gagal menghapus gambar soal lama.');
        }

        return redirect()
            ->route('kuis.soal.index', $kuis->id_kuis)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Kuis $kuis, Soal $soal)
    {
        $this->checkSoalBelongsToKuis($kuis, $soal);

        if ($kuis->status_publikasi !== 'draft') {
            return redirect()
                ->route('kuis.soal.index', $kuis->id_kuis)
                ->with(
                    'error',
                    'Soal tidak dapat dihapus karena kuis sudah diterbitkan.'
                );
        }

        $images = array_filter(array_map(
            fn (string $field): ?string => $soal->{$field},
            $this->imageFields()
        ));

        $soal->delete();

        if ($images !== [] && ! Storage::disk('public')->delete($images)) {
            throw new RuntimeException('Gagal menghapus gambar soal.');
        }

        return redirect()
            ->route('kuis.soal.index', $kuis->id_kuis)
            ->with('success', 'Soal berhasil dihapus.');
    }

    private function checkSoalBelongsToKuis(
        Kuis $kuis,
        Soal $soal
    ): void {
        if ($soal->id_kuis !== $kuis->id_kuis) {
            abort(404);
        }
    }

    private function questionRules(): array
    {
        $rules = [
            'tipe_soal' => [
                'required',
                Rule::in(['pilihan_ganda', 'essay']),
            ],
            'pertanyaan' => ['required', 'string'],
            'opsi_a' => ['nullable', 'string', 'max:255'],
            'opsi_b' => ['nullable', 'string', 'max:255'],
            'opsi_c' => ['nullable', 'string', 'max:255'],
            'opsi_d' => ['nullable', 'string', 'max:255'],
            'opsi_e' => ['nullable', 'string', 'max:255'],
            'jawaban' => [
                'nullable',
                'required_if:tipe_soal,pilihan_ganda',
                Rule::in(['A', 'B', 'C', 'D', 'E']),
            ],
            'bobot' => [
                'nullable',
                'required_if:tipe_soal,pilihan_ganda',
                'numeric',
                'min:0.01',
                'max:100',
            ],
            'tingkat_kesulitan' => [
                'nullable',
                Rule::in(['mudah', 'sedang', 'sulit']),
            ],
        ];

        foreach ($this->imageFields() as $field) {
            $rules[$field] = [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ];
            $rules['hapus_'.$field] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    private function validateOptions(
        Request $request,
        array $validated,
        bool $isPilihanGanda,
        ?Soal $soal = null
    ): void {
        if (! $isPilihanGanda) {
            return;
        }

        $errors = [];

        foreach (['a', 'b', 'c', 'd'] as $option) {
            $textField = 'opsi_'.$option;
            $imageField = 'gambar_'.$textField;
            $hasExistingImage = $soal?->{$imageField} &&
                ! $request->boolean('hapus_'.$imageField);

            if (
                empty(trim($validated[$textField] ?? '')) &&
                ! $request->hasFile($imageField) &&
                ! $hasExistingImage
            ) {
                $errors[$textField] = 'Isi teks opsi atau unggah gambar opsi.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

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

    private function storeImages(
        Request $request,
        string $quizId,
        bool $includeOptions
    ): array {
        $images = [];

        foreach ($this->imageFields() as $field) {
            if (! $includeOptions && $field !== 'gambar_pertanyaan') {
                continue;
            }

            if ($request->hasFile($field)) {
                $path = $request->file($field)->store(
                    'soal-images/'.$quizId,
                    'public'
                );

                if ($path === false) {
                    throw new RuntimeException('Gagal menyimpan gambar soal.');
                }

                $images[$field] = $path;
            } elseif ($request->boolean('hapus_'.$field)) {
                $images[$field] = null;
            }
        }

        return $images;
    }
}
