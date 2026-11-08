@extends('layouts.guru')
@section('title', 'Tambah Materi')

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ request('mapel') ? route('materi.index', ['mapel' => request('mapel')]) : route('materi.index') }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>Tambah Materi</h1>
        <p>Lengkapi formulir di bawah ini untuk menambahkan materi pembelajaran baru.</p>
    </div>
</div>

<section class="card form-card">
    <div class="card-header">
        <div>
            <h2>Formulir Materi Baru</h2>
            <p class="muted">Tanda bintang (<span class="u-color-a52a3b">*</span>) menandakan kolom wajib diisi.</p>
        </div>
    </div>

    <form action="{{ route('materi.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="field">
            <label for="judul">Judul Materi <span class="u-color-a52a3b">*</span></label>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul') }}"
                placeholder="Masukkan judul materi"
                required
            >
            @error('judul') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="id_mapel">Mata Pelajaran <span class="u-color-a52a3b">*</span></label>
            <select id="id_mapel" name="id_mapel" required>
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach ($mapel as $itemMapel)
                    <option value="{{ $itemMapel->id_mapel }}" @selected(old('id_mapel', request('mapel')) === $itemMapel->id_mapel)>
                        {{ $itemMapel->kode_mapel }} - {{ $itemMapel->nama_mapel }}
                    </option>
                @endforeach
            </select>
            @if ($mapel->isEmpty())
                <p class="help">Belum ada mata pelajaran aktif. Hubungi admin sekolah.</p>
            @endif
            @error('id_mapel') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="bab">Bab</label>
                <input
                    type="text"
                    id="bab"
                    name="bab"
                    value="{{ old('bab') }}"
                    placeholder="Contoh: Bab 1"
                >
                @error('bab') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="kelas">Kelas</label>
                <input
                    type="text"
                    id="kelas"
                    name="kelas"
                    value="{{ old('kelas') }}"
                    placeholder="Contoh: X RPL 1"
                >
                @error('kelas') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="field">
            <label for="deskripsi">Deskripsi</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                placeholder="Masukkan ringkasan atau deskripsi materi..."
            >{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="file_pdf">File PDF Materi</label>
            <input
                type="file"
                id="file_pdf"
                name="file_pdf"
                accept=".pdf,application/pdf"
            >
            <p class="help">Unggah file PDF materi (maksimal 10 MB). Siswa dapat langsung membaca PDF ini di portal pembelajaran.</p>
            @error('file_pdf') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="link_youtube">Link YouTube Video Pembelajaran</label>
            <input
                type="url"
                id="link_youtube"
                name="link_youtube"
                value="{{ old('link_youtube') }}"
                placeholder="https://www.youtube.com/watch?v=..."
            >
            <p class="help">Opsional: tautkan video YouTube untuk membantu penjelasan materi.</p>
            @error('link_youtube') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions actions">
            <button type="submit" class="btn">
                Simpan Materi
            </button>
            <a href="{{ route('materi.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</section>

@endsection
