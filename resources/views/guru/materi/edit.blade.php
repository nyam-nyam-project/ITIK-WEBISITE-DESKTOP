@extends('layouts.guru')
@section('title', 'Edit Materi - ' . $materi->judul)

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('materi.index', ['mapel' => $materi->id_mapel]) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>Edit Materi</h1>
        <p>Perbarui informasi dan dokumen materi pembelajaran.</p>
    </div>
</div>

<section class="card form-card">
    <div class="card-header">
        <div>
            <h2>Edit: {{ $materi->judul }}</h2>
            <p class="muted">Tanda bintang (<span class="u-color-a52a3b">*</span>) menandakan kolom wajib diisi.</p>
        </div>
    </div>

    <form action="{{ route('materi.update', $materi->id_materi) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="judul">Judul Materi <span class="u-color-a52a3b">*</span></label>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul', $materi->judul) }}"
                placeholder="Masukkan judul materi"
                required
            >
            @error('judul') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="id_mapel">Mata Pelajaran <span class="u-color-a52a3b">*</span></label>
            <select id="id_mapel" name="id_mapel" required>
                @foreach ($mapel as $itemMapel)
                    <option value="{{ $itemMapel->id_mapel }}" @selected(old('id_mapel', $materi->id_mapel) === $itemMapel->id_mapel)>
                        {{ $itemMapel->kode_mapel }} - {{ $itemMapel->nama_mapel }}{{ $itemMapel->is_aktif ? '' : ' (nonaktif)' }}
                    </option>
                @endforeach
            </select>
            <p class="help">Mata pelajaran nonaktif yang sudah terhubung boleh dipertahankan.</p>
            @error('id_mapel') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="bab">Bab</label>
                <input
                    type="text"
                    id="bab"
                    name="bab"
                    value="{{ old('bab', $materi->bab) }}"
                    placeholder="Contoh: Bab 1"
                >
                <p class="help">Bab harus unik untuk mata pelajaran dan kelas yang dipilih.</p>
                @error('bab') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelasOptions as $kelas)
                        <option value="{{ $kelas }}" @selected(old('kelas', $materi->kelas) === $kelas)>
                            {{ $kelas }}
                        </option>
                    @endforeach
                </select>
                @if ($kelasOptions->isEmpty())
                    <p class="help">Belum ada kelas aktif pada tahun ajaran aktif.</p>
                @endif
                @error('kelas') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="field">
            <label for="deskripsi">Deskripsi</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                placeholder="Masukkan deskripsi materi..."
            >{{ old('deskripsi', $materi->deskripsi) }}</textarea>
            @error('deskripsi') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="file_pdf">File PDF Materi</label>
            @if($materi->isi_materi)
                <div class="alert alert-info u-margin-bottom-10px u-padding-10px-14px">
                    File saat ini: <strong>{{ basename($materi->isi_materi) }}</strong> &bull; Kosongkan jika tidak ingin mengganti file.
                </div>
            @endif
            <input
                type="file"
                id="file_pdf"
                name="file_pdf"
                accept=".pdf,application/pdf"
            >
            <p class="help">Unggah file PDF materi baru jika ingin mengganti file lama (maksimal 10 MB).</p>
            @error('file_pdf') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="link_youtube">Link YouTube Video Pembelajaran</label>
            <input
                type="url"
                id="link_youtube"
                name="link_youtube"
                value="{{ old('link_youtube', $materi->link_youtube) }}"
                placeholder="https://www.youtube.com/watch?v=..."
            >
            @error('link_youtube') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions actions">
            <button type="submit" class="btn">
                Simpan Perubahan
            </button>
            <a href="{{ route('materi.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</section>

@endsection
