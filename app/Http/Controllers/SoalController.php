<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string|max:255',
            'opsi_b' => 'required|string|max:255',
            'opsi_c' => 'required|string|max:255',
            'opsi_d' => 'required|string|max:255',
            'opsi_e' => 'nullable|string|max:255',
            'jawaban' => [
                'required',
                Rule::in(['A', 'B', 'C', 'D', 'E']),
            ],
            'bobot' => 'required|numeric|min:0.01|max:100',
            'tingkat_kesulitan' => [
                'nullable',
                Rule::in(['mudah', 'sedang', 'sulit']),
            ],
        ]);

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
        $opsiJawaban = 'opsi_' . strtolower($validated['jawaban']);

        if (empty(trim($validated[$opsiJawaban] ?? ''))) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    "Jawaban {$validated['jawaban']} dipilih, tetapi opsi {$validated['jawaban']} belum diisi."
                );
        }

        /*
         * Total bobot soal yang sudah ada.
         */
        $totalBobot = (float) $kuis->soal()->sum('bobot');

        $totalSetelahTambah =
            $totalBobot + (float) $validated['bobot'];

        /*
         * Total bobot tidak boleh lebih dari 100.
         */
        if ($totalSetelahTambah > 100) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Total bobot soal tidak boleh lebih dari 100. ' .
                    'Total saat ini: ' .
                    number_format($totalBobot, 2) .
                    ', bobot soal baru: ' .
                    number_format($validated['bobot'], 2) .
                    ', sehingga menjadi ' .
                    number_format($totalSetelahTambah, 2) .
                    '.'
                );
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
         * Simpan soal.
         */
        $kuis->soal()->create([
            'id_soal' => $idSoal,
            'pertanyaan' => $validated['pertanyaan'],
            'opsi_a' => $validated['opsi_a'],
            'opsi_b' => $validated['opsi_b'],
            'opsi_c' => $validated['opsi_c'],
            'opsi_d' => $validated['opsi_d'],
            'opsi_e' => $validated['opsi_e'] ?? null,
            'jawaban' => $validated['jawaban'],
            'bobot' => $validated['bobot'],
            'tingkat_kesulitan' =>
                $validated['tingkat_kesulitan'] ?? null,
        ]);

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
        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string|max:255',
            'opsi_b' => 'required|string|max:255',
            'opsi_c' => 'required|string|max:255',
            'opsi_d' => 'required|string|max:255',
            'opsi_e' => 'nullable|string|max:255',
            'jawaban' => [
                'required',
                Rule::in(['A', 'B', 'C', 'D', 'E']),
            ],
            'bobot' => 'required|numeric|min:0.01|max:100',
            'tingkat_kesulitan' => [
                'nullable',
                Rule::in(['mudah', 'sedang', 'sulit']),
            ],
        ]);

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
        $opsiJawaban = 'opsi_' . strtolower($validated['jawaban']);

        if (empty(trim($validated[$opsiJawaban] ?? ''))) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    "Jawaban {$validated['jawaban']} dipilih, tetapi opsi {$validated['jawaban']} belum diisi."
                );
        }

        /*
         * Hitung total bobot.
         *
         * Bobot soal yang sedang diedit
         * dikeluarkan terlebih dahulu.
         */
        $totalBobotLain = (float) $kuis->soal()
            ->where('id_soal', '!=', $soal->id_soal)
            ->sum('bobot');

        $totalSetelahUpdate =
            $totalBobotLain + (float) $validated['bobot'];

        /*
         * Total bobot tidak boleh lebih dari 100.
         */
        if ($totalSetelahUpdate > 100) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Total bobot soal tidak boleh lebih dari 100. ' .
                    'Total bobot soal lain: ' .
                    number_format($totalBobotLain, 2) .
                    ', bobot baru: ' .
                    number_format($validated['bobot'], 2) .
                    ', sehingga menjadi ' .
                    number_format($totalSetelahUpdate, 2) .
                    '.'
                );
        }

        /*
         * Update soal.
         */
        $soal->update([
            'pertanyaan' => $validated['pertanyaan'],
            'opsi_a' => $validated['opsi_a'],
            'opsi_b' => $validated['opsi_b'],
            'opsi_c' => $validated['opsi_c'],
            'opsi_d' => $validated['opsi_d'],
            'opsi_e' => $validated['opsi_e'] ?? null,
            'jawaban' => $validated['jawaban'],
            'bobot' => $validated['bobot'],
            'tingkat_kesulitan' =>
                $validated['tingkat_kesulitan'] ?? null,
        ]);

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

        $soal->delete();

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
}
