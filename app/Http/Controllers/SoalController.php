<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Soal;
use App\Services\KuisPenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SoalController extends Controller
{
    public function index(Kuis $kuis)
    {
        $soal = $kuis->soal()
            ->orderBy('id_soal')
            ->get();

        return view('guru.soal.index', compact('kuis', 'soal'));
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

        return view('guru.soal.create', compact('kuis'));
    }

    public function store(Request $request, Kuis $kuis)
    {
        return DB::transaction(function () use ($request, $kuis) {
            $kuis = Kuis::whereKey($kuis->getKey())->lockForUpdate()->firstOrFail();
            $this->pastikanTidakAdaPenilaianTertunda($kuis);

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
             * Bobot dihitung otomatis oleh sistem agar guru tidak kerepotan.
             */
            $validated = $this->validasiSoal($request, $kuis, 'pilihan_ganda');

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
            if ($validated['tipe'] === 'pilihan_ganda') {
                $opsiJawaban = 'opsi_' . strtolower($validated['jawaban']);

                if (empty(trim($validated[$opsiJawaban] ?? ''))) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            "Jawaban {$validated['jawaban']} dipilih, tetapi opsi {$validated['jawaban']} belum diisi."
                        );
                }
            }

            /*
             * Generate ID soal.
             */
            $jumlahSoal = $kuis->soal()->count();

            $idSoal = 'S' . str_pad(
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

                $idSoal = 'S' . str_pad(
                    $jumlahSoal + 1,
                    3,
                    '0',
                    STR_PAD_LEFT
                );
            }

            /*
             * Simpan soal. Tingkat kesulitan default mengikuti tingkat kesulitan kuis.
             */
            $kuis->soal()->create([
                'id_soal' => $idSoal,
                'tipe' => $validated['tipe'],
                'pertanyaan' => $validated['pertanyaan'],
                'opsi_a' => $validated['opsi_a'],
                'opsi_b' => $validated['opsi_b'],
                'opsi_c' => $validated['opsi_c'],
                'opsi_d' => $validated['opsi_d'],
                'opsi_e' => $validated['opsi_e'] ?? null,
                'jawaban' => $validated['jawaban'],
                'bobot' => 1,
                'tingkat_kesulitan' => $validated['tingkat_kesulitan'] ?? $kuis->tingkat_kesulitan ?? 'mudah',
            ]);

            // Seimbangkan bobot seluruh soal secara otomatis (skala 100 dibagi rata)
            $this->seimbangkanBobot($kuis);

            return redirect()
                ->route('kuis.soal.index', $kuis->id_kuis)
                ->with('success', 'Soal berhasil ditambahkan. Bobot nilai telah dihitung otomatis.');
        });
    }

    public function show(Kuis $kuis, Soal $soal)
    {
        $this->checkSoalBelongsToKuis($kuis, $soal);

        return view('guru.soal.show', compact('kuis', 'soal'));
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

        return view('guru.soal.edit', compact('kuis', 'soal'));
    }

    public function update(
        Request $request,
        Kuis $kuis,
        Soal $soal
    ) {
        return DB::transaction(function () use ($request, $kuis, $soal) {
            $kuis = Kuis::whereKey($kuis->getKey())->lockForUpdate()->firstOrFail();
            $soal = Soal::whereKey($soal->getKey())->firstOrFail();
            $this->checkSoalBelongsToKuis($kuis, $soal);
            $this->pastikanTidakAdaPenilaianTertunda($kuis);

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
            $validated = $this->validasiSoal($request, $kuis, $soal->tipe ?? 'pilihan_ganda');

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
            if ($validated['tipe'] === 'pilihan_ganda') {
                $opsiJawaban = 'opsi_' . strtolower($validated['jawaban']);

                if (empty(trim($validated[$opsiJawaban] ?? ''))) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            "Jawaban {$validated['jawaban']} dipilih, tetapi opsi {$validated['jawaban']} belum diisi."
                        );
                }
            }

            /*
             * Update soal. Bobot diseimbangkan otomatis.
             */
            $soal->update([
                'tipe' => $validated['tipe'],
                'pertanyaan' => $validated['pertanyaan'],
                'opsi_a' => $validated['opsi_a'],
                'opsi_b' => $validated['opsi_b'],
                'opsi_c' => $validated['opsi_c'],
                'opsi_d' => $validated['opsi_d'],
                'opsi_e' => $validated['opsi_e'] ?? null,
                'jawaban' => $validated['jawaban'],
                'tingkat_kesulitan' =>
                    $validated['tingkat_kesulitan'] ?? $soal->tingkat_kesulitan ?? $kuis->tingkat_kesulitan ?? 'mudah',
            ]);

            $this->seimbangkanBobot($kuis);

            return redirect()
                ->route('kuis.soal.index', $kuis->id_kuis)
                ->with('success', 'Soal berhasil diperbarui.');
        });
    }

    public function destroy(Kuis $kuis, Soal $soal)
    {
        return DB::transaction(function () use ($kuis, $soal) {
            $kuis = Kuis::whereKey($kuis->getKey())->lockForUpdate()->firstOrFail();
            $soal = Soal::whereKey($soal->getKey())->firstOrFail();
            $this->checkSoalBelongsToKuis($kuis, $soal);
            $this->pastikanTidakAdaPenilaianTertunda($kuis);

            if ($kuis->status_publikasi !== 'draft') {
                return redirect()
                    ->route('kuis.soal.index', $kuis->id_kuis)
                    ->with(
                        'error',
                        'Soal tidak dapat dihapus karena kuis sudah diterbitkan.'
                    );
            }

            $soal->delete();

            $this->seimbangkanBobot($kuis);

            return redirect()
                ->route('kuis.soal.index', $kuis->id_kuis)
                ->with('success', 'Soal berhasil dihapus.');
        });
    }

    private function validasiSoal(Request $request, Kuis $kuis, string $tipeDefault): array
    {
        $essay = $request->input('tipe', $tipeDefault) === 'essay';

        $validated = $request->validate([
            'tipe' => ['sometimes', 'required', Rule::in(['pilihan_ganda', 'essay'])],
            'pertanyaan' => 'required|string',
            'opsi_a' => $essay ? 'exclude' : 'required|string|max:255',
            'opsi_b' => $essay ? 'exclude' : 'required|string|max:255',
            'opsi_c' => $essay ? 'exclude' : 'required|string|max:255',
            'opsi_d' => $essay ? 'exclude' : 'required|string|max:255',
            'opsi_e' => $essay ? 'exclude' : 'nullable|string|max:255',
            'jawaban' => [
                Rule::excludeIf($essay),
                'required',
                Rule::in(['A', 'B', 'C', 'D', 'E']),
            ],
            'bobot' => 'nullable|numeric|min:0.01|max:100',
            'tingkat_kesulitan' => [
                'nullable',
                Rule::in(['mudah', 'sedang', 'sulit']),
            ],
        ]);

        $validated['tipe'] = $validated['tipe'] ?? $tipeDefault;

        if ($essay) {
            if (in_array(strtolower(trim((string) $kuis->kategori)), ['pre-test', 'post-test'], true)) {
                throw ValidationException::withMessages([
                    'tipe' => 'Soal essay tidak diperbolehkan untuk pre-test atau post-test.',
                ]);
            }

            foreach (['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban'] as $field) {
                $validated[$field] = null;
            }
        }

        return $validated;
    }

    private function pastikanTidakAdaPenilaianTertunda(Kuis $kuis): void
    {
        if (app(KuisPenilaianService::class)->adaPenilaianTertunda($kuis->id_kuis)) {
            throw ValidationException::withMessages([
                'kuis' => 'Kuis tidak dapat diubah selama masih ada jawaban yang menunggu penilaian.',
            ]);
        }
    }

    private function seimbangkanBobot(Kuis $kuis): void
    {
        $soalList = $kuis->soal()->orderBy('id_soal')->get();
        $total = $soalList->count();

        if ($total === 0) {
            return;
        }

        $bobotPerSoal = round(100 / $total, 2);
        $totalTerhitung = $bobotPerSoal * $total;
        $selisih = round(100 - $totalTerhitung, 2);

        foreach ($soalList as $index => $item) {
            $bobotIni = $bobotPerSoal;
            if ($index === $total - 1 && $selisih != 0) {
                $bobotIni = round($bobotIni + $selisih, 2);
            }
            $item->update(['bobot' => $bobotIni]);
        }
    }

    private function checkSoalBelongsToKuis(
        Kuis $kuis,
        Soal $soal
    ): void {
        if ($soal->id_kuis !== $kuis->id_kuis) {
            abort(404);
        }
    }
}
