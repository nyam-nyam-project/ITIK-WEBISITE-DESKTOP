@extends('layouts.guru')
@section('title', 'Buat Kuis Remedial')

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('guru.remedial.index') }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>Buat Kuis Remedial</h1>
        <p>Siapkan kuis remedial untuk siswa yang nilainya di bawah KKM pada Ulangan.</p>
    </div>
</div>

<div class="card u-background-eef2ff u-border-left-4px-solid-6366f1 u-padding-14px-18px u-margin-bottom-22px">
    <div class="u-font-size-14px u-color-3730a3">
        💡 <strong>Ketentuan Remedial:</strong> Sesuai kebijakan sekolah, kuis remedial hanya dapat dibuat untuk kuis dengan kategori <strong>Ulangan</strong>. Siswa yang nilainya &lt; KKM pada kuis ulangan yang dipilih akan dapat diberikan tugas kuis remedial ini.
    </div>
</div>

<section class="card form-card">
    <div class="card-header">
        <div>
            <h2>Formulir Kuis Remedial Baru</h2>
            <p class="muted">Tanda bintang (<span class="u-color-a52a3b">*</span>) menandakan kolom wajib diisi.</p>
        </div>
    </div>

    <form action="{{ route('guru.remedial.store') }}" method="POST">
        @csrf

        <div class="field">
            <label for="id_kuis_asal">Pilih Kuis Ulangan (Kuis Asal) <span class="u-color-a52a3b">*</span></label>
            <select name="id_kuis_asal" id="id_kuis_asal" required>
                <option value="">-- Pilih Kuis Ulangan --</option>
                @foreach($kuisAsal as $ka)
                    <option value="{{ $ka->id_kuis }}" {{ old('id_kuis_asal') == $ka->id_kuis ? 'selected' : '' }}>
                        [{{ $ka->id_kuis }}] {{ $ka->judul }} &nbsp;—&nbsp; KKM: {{ $ka->kkm }} &nbsp;(Mapel: {{ $ka->mapel->nama_mapel ?? '-' }})
                    </option>
                @endforeach
            </select>
            <p class="help">Hanya menampilkan kuis aktif berstatus terbit dengan kategori Ulangan.</p>
            @error('id_kuis_asal') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="judul">Judul Kuis Remedial <span class="u-color-a52a3b">*</span></label>
            <input
                type="text"
                name="judul"
                id="judul"
                value="{{ old('judul') }}"
                placeholder="Contoh: Remedial Ulangan Harian 1 - Dasar Jaringan"
                required
                maxlength="150"
            >
            @error('judul') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="kkm">Nilai KKM <span class="u-color-a52a3b">*</span></label>
                <input
                    type="number"
                    step="0.01"
                    name="kkm"
                    id="kkm"
                    value="{{ old('kkm', '75.00') }}"
                    required
                    min="0"
                    max="100"
                >
                <p class="help">Batas kelulusan remedial.</p>
                @error('kkm') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="alokasi_waktu">Alokasi Waktu (Menit) <span class="u-color-a52a3b">*</span></label>
                <input
                    type="number"
                    name="alokasi_waktu"
                    id="alokasi_waktu"
                    value="{{ old('alokasi_waktu', 20) }}"
                    required
                    min="1"
                    max="300"
                >
                <p class="help">Durasi pengerjaan siswa.</p>
                @error('alokasi_waktu') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="field">
            <label for="deskripsi">Deskripsi / Petunjuk Remedial</label>
            <textarea
                name="deskripsi"
                id="deskripsi"
                rows="3"
                placeholder="Petunjuk pengerjaan remedial untuk siswa..."
            >{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('guru.remedial.index') }}" class="btn btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn btn-primary">
                Simpan Kuis Remedial
            </button>
        </div>
    </form>
</section>

@endsection
