@extends('layouts.guru')
@section('title', 'Tambah Kuis')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/kuis/create.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        @if($materiDipreselect)
            <a href="{{ route('materi.show', $materiDipreselect->id_materi) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-6px">
                ← Kembali
            </a>
        @else
            <a href="{{ route('kuis.index', $mapelTerpilih ? ['mapel' => $mapelTerpilih] : []) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-6px">
                ← Kembali
            </a>
        @endif
        <h1>Tambah Kuis Baru</h1>
        <p>Atur parameter penilaian, tingkat kesulitan, alokasi waktu, dan jadwal kuis.</p>
    </div>
</div>

<section class="card form-card">
    <div class="card-header">
        <div>
            <h2>Formulir Kuis Baru</h2>
            <p class="muted">Tanda bintang (<span class="u-color-a52a3b">*</span>) menandakan kolom wajib diisi.</p>
        </div>
    </div>

    <form action="{{ route('kuis.store') }}" method="POST" data-kuis-form="create">
        @csrf

        <div class="field">
            <label for="judul">Judul Kuis <span class="u-color-a52a3b">*</span></label>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul') }}"
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
                placeholder="Masukkan petunjuk atau deskripsi kuis..."
            >{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="id_mapel">Mata Pelajaran <span class="u-color-a52a3b">*</span></label>
                @if($materiDipreselect)
                    <input
                        type="text"
                        value="{{ $mapel->firstWhere('id_mapel', $materiDipreselect->id_mapel)?->kode_mapel }} - {{ $mapel->firstWhere('id_mapel', $materiDipreselect->id_mapel)?->nama_mapel }}"
                        readonly
                    >
                    <input type="hidden" id="id_mapel" name="id_mapel" value="{{ $materiDipreselect->id_mapel }}">
                    <p class="help">Mata pelajaran mengikuti materi yang dipilih.</p>
                @else
                    <select id="id_mapel" name="id_mapel" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach ($mapel as $itemMapel)
                            <option value="{{ $itemMapel->id_mapel }}" @selected(old('id_mapel', $mapelTerpilih) === $itemMapel->id_mapel)>
                                {{ $itemMapel->kode_mapel }} - {{ $itemMapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    @if ($mapel->isEmpty())
                        <p class="help">Belum ada mata pelajaran aktif. Hubungi admin sekolah.</p>
                    @endif
                @endif
                @error('id_mapel') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="tahun_ajaran">Tahun Ajaran</label>
                <input
                    id="tahun_ajaran"
                    type="text"
                    value="{{ $tahunAjaran?->nama ?? 'Belum ada tahun ajaran aktif' }}"
                    readonly
                >
                <p class="help">Diisi otomatis dari tahun ajaran aktif.</p>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="tingkat_kesulitan">Tingkat Kesulitan <span class="u-color-a52a3b">*</span></label>
                <select id="tingkat_kesulitan" name="tingkat_kesulitan" required>
                    @foreach (['mudah' => 'Mudah', 'sedang' => 'Sedang', 'sulit' => 'Sulit'] as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('tingkat_kesulitan', 'mudah') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('tingkat_kesulitan') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="kategori">Kategori Kuis <span class="u-color-a52a3b">*</span></label>
                <select id="kategori" name="kategori" required>
                    <option value="" {{ old('kategori') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                    <option value="post-test" {{ old('kategori') === 'post-test' ? 'selected' : '' }}>Post-test</option>
                    <option value="pre-test" {{ old('kategori') === 'pre-test' ? 'selected' : '' }}>Pre-test</option>
                    <option value="kuis harian" {{ old('kategori') === 'kuis harian' ? 'selected' : '' }}>Kuis Harian</option>
                    <option value="ulangan" {{ old('kategori') === 'ulangan' ? 'selected' : '' }}>Ulangan</option>
                    <option value="remedial" {{ old('kategori') === 'remedial' ? 'selected' : '' }}>Remedial</option>
                </select>
                @error('kategori') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- MATERI PER MAPEL --}}
        <div id="materi-container" class="{{ $materiDipreselect ? 'special-card u-display-block' : 'special-card u-display-none' }}" @if($materiDipreselect) data-locked="true" @endif>
            <div class="field u-margin-bottom-0">
                @if($materiDipreselect)
                    {{-- Mode terkunci: datang dari shortcut "Buat Kuis" di halaman Materi --}}
                    <label>Hubungkan ke Bab Materi</label>
                    <div class="materi-locked-box">
                        <label class="checkbox-label u-display-flex u-items-center u-gap-8px">
                            <input
                                type="checkbox"
                                checked
                                disabled
                                aria-disabled="true"
                            >
                            <span>
                                @if($materiDipreselect->bab)
                                    <strong>[{{ $materiDipreselect->bab }}]</strong> -
                                @endif
                                {{ $materiDipreselect->judul }}
                                <span class="muted small">(Terkunci dari materi)</span>
                            </span>
                        </label>
                        <input type="hidden" name="materi[]" value="{{ $materiDipreselect->id_materi }}">
                    </div>
                    <p class="help">Materi ini telah dipilih otomatis dan dikunci. <a href="{{ route('kuis.create', ['mapel' => $materiDipreselect->id_mapel]) }}">Pilih materi lain</a></p>
                @else
                    <label for="materi-select">Hubungkan ke Bab Materi</label>
                    <select class="u-min-height-120px" id="materi-select" name="materi[]" multiple>
                        @foreach ($materi as $itemMateri)
                            <option
                                value="{{ $itemMateri->id_materi }}"
                                data-mapel="{{ $itemMateri->id_mapel }}"
                                {{ in_array($itemMateri->id_materi, old('materi', [])) ? 'selected' : '' }}
                            >
                                @if ($itemMateri->bab) [{{ $itemMateri->bab }}] - @endif {{ $itemMateri->judul }}
                            </option>
                        @endforeach
                    </select>
                    <p id="materi-empty-hint" class="help u-display-none u-color-8a4d08 u-font-weight-bold">
                        ⚠️ Belum ada materi untuk mata pelajaran ini. Buat materi terlebih dahulu di menu Materi agar kuis dapat terpetakan ke bab.
                    </p>
                    <p class="help">Gunakan Ctrl + klik untuk memilih lebih dari satu bab materi.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="alokasi_waktu">Alokasi Waktu (menit)</label>
                <input
                    type="number"
                    id="alokasi_waktu"
                    name="alokasi_waktu"
                    value="{{ old('alokasi_waktu') }}"
                    min="1"
                    placeholder="Contoh: 60"
                >
                @error('alokasi_waktu') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="kkm">KKM (Kriteria Ketuntasan Minimal)</label>
                <input
                    type="number"
                    id="kkm"
                    name="kkm"
                    value="{{ old('kkm') }}"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="Contoh: 75"
                >
                @error('kkm') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- JADWAL TERBIT & DRAFT OTOMATIS --}}
        <div id="waktu-container" class="special-card">
            <h3 class="u-font-size-15px u-margin-bottom-6px">📅 Jadwal Terbit &amp; Selesai Otomatis</h3>
            <p class="muted small u-margin-bottom-12px" id="waktu-keterangan">
                Tentukan tanggal dan jam untuk menerbitkan kuis secara otomatis, dan/atau mengembalikannya ke draft saat waktu berakhir. (Wajib untuk kategori Ulangan, opsional untuk kategori lainnya).
            </p>
            <div class="grid grid-2">
                <div class="field u-margin-bottom-0">
                    <label for="waktu_mulai">Tanggal &amp; Waktu Terbit Otomatis</label>
                    <input
                        type="datetime-local"
                        id="waktu_mulai"
                        name="waktu_mulai"
                        value="{{ old('waktu_mulai') }}"
                    >
                    <p class="help">Kuis otomatis berstatus "Terbit" saat waktu ini tiba.</p>
                    @error('waktu_mulai') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field u-margin-bottom-0">
                    <label for="waktu_selesai">Tanggal &amp; Waktu Selesai (Otomatis Ditutup / Draft)</label>
                    <input
                        type="datetime-local"
                        id="waktu_selesai"
                        name="waktu_selesai"
                        value="{{ old('waktu_selesai') }}"
                    >
                    <p class="help">Kuis otomatis kembali ke "Draft" saat waktu ini lewat.</p>
                    @error('waktu_selesai') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- REMEDIAL ASAL --}}
        <div id="field-remedial" class="special-card u-display-none">
            <div class="field u-margin-bottom-0">
                <label for="id_kuis_asal">Kuis Ulangan Asal</label>
                <select id="id_kuis_asal" name="id_kuis_asal">
                    <option value="">-- Pilih Kuis Asal --</option>
                    @foreach($kuisAsal as $item)
                        <option value="{{ $item->id_kuis }}" {{ old('id_kuis_asal') == $item->id_kuis ? 'selected' : '' }}>
                            {{ $item->judul }} ({{ $item->kategori }})
                        </option>
                    @endforeach
                </select>
                @error('id_kuis_asal') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-actions actions">
            <button type="submit" class="btn">
                Simpan Kuis
            </button>
            @if($materiDipreselect)
                <a href="{{ route('materi.show', $materiDipreselect->id_materi) }}" class="btn btn-secondary">
                    Batal
                </a>
            @else
                <a href="{{ route('kuis.index', $mapelTerpilih ? ['mapel' => $mapelTerpilih] : []) }}" class="btn btn-secondary">
                    Batal
                </a>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script src="{{ asset('js/guru/kuis/form.js') }}" defer></script>
@endpush

@endsection
