@extends('layouts.guru')
@section('title', 'Edit Materi - ' . $materi->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/materi/kelas.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/guru/materi/kelas.js') }}" defer></script>
@endpush

@section('content')

<div class="page-heading">
    <div>
        <x-tombol-kembali :fallback="route('materi.index', ['mapel' => $materi->id_mapel])" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px" />
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

    <form
        action="{{ route('materi.update', $materi->id_materi) }}"
        method="POST"
        enctype="multipart/form-data"
        data-materi-kelas-form
        data-kelas-options-url="{{ route('materi.kelas') }}"
    >
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
                    <option value="{{ $itemMapel->id_mapel }}" @selected(old('id_mapel', $idMapel) === $itemMapel->id_mapel)>
                        {{ $itemMapel->kode_mapel }} - {{ $itemMapel->nama_mapel }}{{ $itemMapel->is_aktif ? '' : ' (nonaktif)' }}
                    </option>
                @endforeach
            </select>
            <p class="help">Mata pelajaran nonaktif yang sudah terhubung boleh dipertahankan.</p>
            @error('id_mapel') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="bab">Bab</label>
            <input
                type="text"
                id="bab"
                name="bab"
                value="{{ old('bab', $materi->bab) }}"
                placeholder="Contoh: Bab 1"
            >
            <p class="help">Bab harus unik untuk mata pelajaran dan setiap kelas yang dipilih.</p>
            @error('bab') <div class="error">{{ $message }}</div> @enderror
        </div>

        <fieldset class="materi-kelas-fieldset" aria-describedby="materi-kelas-help">
            <legend>Kelas <span class="u-color-a52a3b">*</span></legend>
            <p class="help" id="materi-kelas-help">Pilih minimal satu kelas. Daftar hanya memuat kelas yang Anda ajar untuk mata pelajaran ini pada tahun ajaran aktif.</p>
            <div class="error" data-kelas-error role="alert" @if(! $errors->has('id_kelas') && ! $errors->has('id_kelas.*')) hidden @endif>
                @if($errors->has('id_kelas'))
                    {{ $errors->first('id_kelas') }}
                @else
                    {{ $errors->first('id_kelas.*') }}
                @endif
            </div>
            <div class="materi-kelas-groups" data-kelas-groups>
                @foreach (['X', 'XI', 'XII'] as $tingkat)
                    <section class="materi-kelas-group" aria-labelledby="materi-kelas-{{ $tingkat }}">
                        <h3 id="materi-kelas-{{ $tingkat }}">{{ $tingkat }}</h3>
                        <button type="button" class="btn btn-secondary btn-small" data-pilih-semua="{{ $tingkat }}">
                            Pilih semua kelas {{ $tingkat }}
                        </button>
                        <div class="materi-kelas-options" data-kelas-options="{{ $tingkat }}">
                            @foreach ($kelasOptions->where('tingkat', $tingkat) as $kelas)
                                <label class="materi-kelas-option" for="id_kelas_{{ $kelas->id_kelas }}">
                                    <input
                                        type="checkbox"
                                        id="id_kelas_{{ $kelas->id_kelas }}"
                                        name="id_kelas[]"
                                        value="{{ $kelas->id_kelas }}"
                                        @checked(in_array($kelas->id_kelas, $kelasTerpilih, true))
                                    >
                                    <span>{{ $kelas->nama_kelas }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
            <p class="help" data-kelas-status role="status" aria-live="polite">
                @if($kelasOptions->isEmpty())
                    Tidak ada kelas yang Anda ajar untuk mata pelajaran ini pada tahun ajaran aktif.
                @endif
            </p>
            @foreach ($errors->get('id_kelas.*') as $messages)
                @foreach ($messages as $message)
                    <div class="error" role="alert">{{ $message }}</div>
                @endforeach
            @endforeach
        </fieldset>

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
