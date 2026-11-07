@extends('layouts.guru')
@section('title', 'Edit Soal - ' . $kuis->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/soal/edit.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('kuis.soal.index', $kuis->id_kuis) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-6px">
            ← Kembali
        </a>
        <h1>Edit Butir Soal</h1>
        <p>Kuis: <strong>{{ $kuis->judul }}</strong></p>
    </div>
</div>

<section class="card form-card">
    <div class="card-header">
        <div>
            <h2>Formulir Edit Butir Soal</h2>
            <p class="muted">Tanda bintang (<span class="u-color-a52a3b">*</span>) menandakan kolom wajib diisi.</p>
        </div>
    </div>

    <form action="{{ route('kuis.soal.update', [$kuis->id_kuis, $soal->id_soal]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="pertanyaan">Pertanyaan / Butir Soal <span class="u-color-a52a3b">*</span></label>
            <textarea
                id="pertanyaan"
                name="pertanyaan"
                rows="4"
                placeholder="Tuliskan butir soal atau pertanyaan di sini..."
                required
            >{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
            @error('pertanyaan') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="options-entry" data-jawaban="{{ old('jawaban', $soal->jawaban) }}">
            <label class="u-font-weight-700 u-display-block u-margin-bottom-6px">Pilihan Jawaban</label>
            <p class="muted small u-margin-bottom-16px">Tuliskan alternatif jawaban untuk opsi A hingga D (dan opsi E jika diperlukan). <strong>Klik opsi untuk menjadikannya kunci jawaban.</strong></p>

            {{-- Hidden input untuk menyimpan kunci jawaban --}}
            <input type="hidden" name="jawaban" id="hidden-jawaban" value="{{ old('jawaban', $soal->jawaban) }}" required>

            <div class="option-row" data-opsi="A">
                <div class="option-tag">A</div>
                <div class="u-flex-1">
                    <input type="text" name="opsi_a" value="{{ old('opsi_a', $soal->opsi_a) }}" placeholder="Teks pilihan A *" required data-stop-propagation>
                    @error('opsi_a') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="option-row" data-opsi="B">
                <div class="option-tag">B</div>
                <div class="u-flex-1">
                    <input type="text" name="opsi_b" value="{{ old('opsi_b', $soal->opsi_b) }}" placeholder="Teks pilihan B *" required data-stop-propagation>
                    @error('opsi_b') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="option-row" data-opsi="C">
                <div class="option-tag">C</div>
                <div class="u-flex-1">
                    <input type="text" name="opsi_c" value="{{ old('opsi_c', $soal->opsi_c) }}" placeholder="Teks pilihan C *" required data-stop-propagation>
                    @error('opsi_c') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="option-row" data-opsi="D">
                <div class="option-tag">D</div>
                <div class="u-flex-1">
                    <input type="text" name="opsi_d" value="{{ old('opsi_d', $soal->opsi_d) }}" placeholder="Teks pilihan D *" required data-stop-propagation>
                    @error('opsi_d') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="option-row u-margin-bottom-0" data-opsi="E">
                <div class="option-tag">E</div>
                <div class="u-flex-1">
                    <input type="text" name="opsi_e" value="{{ old('opsi_e', $soal->opsi_e) }}" placeholder="Teks pilihan E (opsional)" data-stop-propagation>
                    @error('opsi_e') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <p class="jawaban-hint">👆 Klik pada baris opsi untuk memilihnya sebagai kunci jawaban yang benar.</p>
            <p class="jawaban-selected-label" id="label-jawaban-selected" data-answer-label="&#9989; Kunci Jawaban: Opsi "></p>
            @error('jawaban') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="tingkat_kesulitan">Tingkat Kesulitan</label>
            <select name="tingkat_kesulitan" id="tingkat_kesulitan">
                <option value="">-- Sesuai Kuis / Bebas --</option>
                <option value="mudah" @selected(old('tingkat_kesulitan', $soal->tingkat_kesulitan) === 'mudah')>Mudah</option>
                <option value="sedang" @selected(old('tingkat_kesulitan', $soal->tingkat_kesulitan) === 'sedang')>Sedang</option>
                <option value="sulit" @selected(old('tingkat_kesulitan', $soal->tingkat_kesulitan) === 'sulit')>Sulit</option>
            </select>
            @error('tingkat_kesulitan') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('kuis.soal.index', $kuis->id_kuis) }}" class="btn btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>

@push('scripts')
<script src="{{ asset('js/guru/soal/form.js') }}" defer></script>
@endpush

@endsection
