@extends('layouts.guru')
@section('title', 'Edit Kuis - ' . $kuis->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/kuis/edit.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('kuis.index', ['mapel' => $kuis->id_mapel]) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-6px">
            ← Kembali
        </a>
        <h1>Edit Kuis</h1>
        <p>Perbarui informasi kuis, alokasi waktu, kriteria kelulusan, dan jadwal pelaksanaan.</p>
    </div>
</div>

<section class="card form-card">
    <div class="card-header">
        <div>
            <h2>Formulir Edit Kuis</h2>
            <p class="muted">Tanda bintang (<span class="u-color-a52a3b">*</span>) menandakan kolom wajib diisi.</p>
        </div>
    </div>

    <form action="{{ route('kuis.update', $kuis->id_kuis) }}" method="POST" data-kuis-form="edit">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="judul">Judul Kuis <span class="u-color-a52a3b">*</span></label>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul', $kuis->judul) }}"
                placeholder="Masukkan judul kuis"
                required
            >
            @error('judul') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="deskripsi">Deskripsi Kuis</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="4"
                placeholder="Masukkan petunjuk atau deskripsi kuis..."
            >{{ old('deskripsi', $kuis->deskripsi) }}</textarea>
            @error('deskripsi') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="id_mapel">Mata Pelajaran <span class="u-color-a52a3b">*</span></label>
                <select id="id_mapel" name="id_mapel" required>
                    @foreach ($mapel as $itemMapel)
                        <option value="{{ $itemMapel->id_mapel }}" @selected(old('id_mapel', $kuis->id_mapel) === $itemMapel->id_mapel)>
                            {{ $itemMapel->kode_mapel }} - {{ $itemMapel->nama_mapel }}{{ $itemMapel->is_aktif ? '' : ' (nonaktif)' }}
                        </option>
                    @endforeach
                </select>
                <p class="help">Diisi otomatis, tidak dapat diubah.</p>
                @error('id_mapel') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="tahun_ajaran">Tahun Ajaran</label>
                <input
                    id="tahun_ajaran"
                    type="text"
                    value="{{ $kuis->tahunAjaran?->nama ?? '-' }}"
                    readonly
                >
                <p class="help">Tahun ajaran kuis dikunci sejak kuis dibuat.</p>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="tingkat_kesulitan">Tingkat Kesulitan <span class="u-color-a52a3b">*</span></label>
                <select id="tingkat_kesulitan" name="tingkat_kesulitan" required>
                    @foreach (['mudah' => 'Mudah', 'sedang' => 'Sedang', 'sulit' => 'Sulit'] as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('tingkat_kesulitan', $kuis->tingkat_kesulitan) === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('tingkat_kesulitan') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="kategori">Kategori <span class="u-color-a52a3b">*</span></label>
                <select name="kategori" id="kategori" required>
                    <option value="post-test" @selected(old('kategori', $kuis->kategori) === 'post-test')>Post-test</option>
                    <option value="pre-test" @selected(old('kategori', $kuis->kategori) === 'pre-test')>Pre-test</option>
                    <option value="kuis harian" @selected(old('kategori', $kuis->kategori) === 'kuis harian')>Kuis Harian</option>
                    <option value="ulangan" @selected(old('kategori', $kuis->kategori) === 'ulangan')>Ulangan</option>
                    <option value="remedial" @selected(old('kategori', $kuis->kategori) === 'remedial')>Remedial</option>
                </select>
                @error('kategori') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- PILIH MATERI TERKAIT -->
        <div id="materi-container" class="special-card">
            <label class="u-font-weight-700 u-display-block u-margin-bottom-6px" for="materi-select">Materi Terkait</label>
            <p class="muted small u-margin-bottom-10px">Pilih materi yang berkaitan dengan kuis. Tahan tombol Ctrl (Windows) untuk memilih beberapa bab.</p>
            <select class="u-height-140px u-width-100pct u-border-1px-solid-d0c8ed u-border-radius-10px u-padding-10px"
                id="materi-select"
                name="materi[]"
                multiple

            >
                @foreach ($materi as $itemMateri)
                    <option
                        value="{{ $itemMateri->id_materi }}"
                        data-mapel="{{ $itemMateri->id_mapel }}"
                        @selected(in_array($itemMateri->id_materi, old('materi', $materiTerpilih ?? [])))
                    >
                        @if ($itemMateri->bab) [{{ $itemMateri->bab }}] - @endif {{ $itemMateri->judul }}
                    </option>
                @endforeach
            </select>
            <p id="materi-empty-hint" class="help u-display-none u-color-c98200 u-font-weight-700 u-margin-top-8px">
                ⚠️ Belum ada materi untuk mata pelajaran ini.
            </p>
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="alokasi_waktu">Alokasi Waktu (menit)</label>
                <input
                    type="number"
                    id="alokasi_waktu"
                    name="alokasi_waktu"
                    value="{{ old('alokasi_waktu', $kuis->alokasi_waktu) }}"
                    min="1"
                    placeholder="Contoh: 60"
                >
                <p class="help">Kosongkan jika kuis tidak memiliki batas pengerjaan.</p>
                @error('alokasi_waktu') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="kkm">KKM (Kriteria Ketuntasan Minimal)</label>
                <input
                    type="number"
                    id="kkm"
                    name="kkm"
                    value="{{ old('kkm', $kuis->kkm) }}"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="Contoh: 75"
                >
                <p class="help">Batas nilai minimal untuk dinyatakan lulus.</p>
                @error('kkm') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- JADWAL TERBIT & DRAFT OTOMATIS -->
        <div id="waktu-container" class="special-card">
            <label class="u-font-weight-700 u-display-block u-margin-bottom-6px">📅 Jadwal Terbit &amp; Selesai Otomatis</label>
            <p class="muted small u-margin-bottom-12px" id="waktu-keterangan">Tentukan tanggal dan jam untuk menerbitkan kuis secara otomatis, dan/atau mengembalikannya ke draft saat waktu berakhir. (Wajib untuk kategori Ulangan, opsional untuk kategori lainnya).</p>
            <div class="grid grid-2">
                <div class="field">
                    <label for="waktu_mulai">Tanggal &amp; Waktu Terbit Otomatis</label>
                    <input
                        type="datetime-local"
                        id="waktu_mulai"
                        name="waktu_mulai"
                        value="{{ old('waktu_mulai', $kuis->waktu_mulai?->format('Y-m-d\TH:i')) }}"
                    >
                    <p class="help">Kuis otomatis berstatus "Terbit" saat waktu ini tiba.</p>
                    @error('waktu_mulai') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="waktu_selesai">Tanggal &amp; Waktu Selesai (Otomatis Ditutup / Draft)</label>
                    <input
                        type="datetime-local"
                        id="waktu_selesai"
                        name="waktu_selesai"
                        value="{{ old('waktu_selesai', $kuis->waktu_selesai?->format('Y-m-d\TH:i')) }}"
                    >
                    <p class="help">Kuis otomatis kembali ke "Draft" saat waktu ini lewat.</p>
                    @error('waktu_selesai') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('kuis.index', ['mapel' => $kuis->id_mapel]) }}" class="btn btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>

@push('scripts')
<script src="{{ asset('js/guru/kuis/form.js') }}" defer></script>
@endpush

@endsection
