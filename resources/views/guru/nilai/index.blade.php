@extends('layouts.guru')
@section('title', 'Daftar Nilai Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/nilai/index.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <h1>Daftar Nilai Siswa</h1>
        <p>Pantau hasil pengerjaan kuis siswa per kelas dan berikan tugas remedial bagi yang belum lulus ulangan.</p>
    </div>
</div>

{{-- LEVEL 1: KELOMPOK KELAS GURU --}}
@if(isset($kelasList) && $kelasList->isNotEmpty())
    <div class="kelas-section">
        <div class="u-font-size-11px u-font-weight-700 u-text-transform-uppercase u-letter-spacing-1p2px u-color-var---muted u-margin-bottom-12px">
            @include('partials.icon', ['name' => 'school']) Pilih Kelas Pembelajaran
        </div>
        <div class="kelas-grid-guru">
            @foreach($kelasList as $k)
                @php
                    $isActive = ($idKelas === $k->id_kelas);
                @endphp
                <a href="{{ $isActive ? route('guru.nilai.index') : route('guru.nilai.index', ['kelas' => $k->id_kelas]) }}"
                   class="kelas-card-guru {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="true" @endif data-reveal>
                    <div>
                        <div class="kelas-card-header">
                            <h3 class="kelas-card-title">{{ $k->nama_kelas }}</h3>
                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-muted' }}">
                                Tingkat {{ $k->tingkat }}
                            </span>
                        </div>
                        <div class="kelas-stat-grid">
                            <div class="kelas-stat-box">
                                <span class="label">@include('partials.icon', ['name' => 'users']) Siswa</span>
                                <span class="value">{{ $k->total_siswa }}</span>
                            </div>
                            <div class="kelas-stat-box">
                                <span class="label">@include('partials.icon', ['name' => 'book-open']) Materi</span>
                                <span class="value">{{ $k->total_materi }}</span>
                            </div>
                            <div class="kelas-stat-box">
                                <span class="label">@include('partials.icon', ['name' => 'check-square']) Kuis Aktif</span>
                                <span class="value">{{ $k->kuis_aktif }}</span>
                            </div>
                            <div class="kelas-stat-box">
                                <span class="label">@include('partials.icon', ['name' => 'chart']) Rata Nilai</span>
                                <span class="value {{ $k->rata_nilai && $k->rata_nilai >= 75 ? 'u-color-166534' : 'u-color-92400e' }}">
                                    {{ $k->rata_nilai !== null ? $k->rata_nilai : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="kelas-card-footer">
                        <span>
                            @if($isActive)
                                @include('partials.icon', ['name' => 'check']) Sedang Dilihat
                            @else
                                Buka Nilai Kelas
                            @endif
                        </span>
                        <span>
                            @if($isActive)
                                @include('partials.icon', ['name' => 'x'])
                            @else
                                @include('partials.icon', ['name' => 'arrow-right'])
                            @endif
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif

@if($kelasTerpilih)
    <div class="banner-kelas-active">
        <div>
            <strong class="u-color-var---purple u-font-size-15px">
                @include('partials.icon', ['name' => 'folder']) Menampilkan Data Nilai: Kelas {{ $kelasTerpilih->nama_kelas }}
            </strong>
            <span class="muted small u-margin-left-8px">
                ({{ $kelasTerpilih->total_siswa }} Siswa &bull; Rata-rata Nilai: {{ $kelasTerpilih->rata_nilai ?? '-' }})
            </span>
        </div>
        <div class="u-display-flex u-gap-8px">
            <a href="{{ route('guru.nilai.unduh', ['kelas' => $kelasTerpilih->id_kelas]) }}"
               class="btn btn-secondary btn-small">
                @include('partials.icon', ['name' => 'download']) Unduh CSV
            </a>
            <a href="{{ route('guru.nilai.index') }}" class="btn btn-secondary btn-small">
                @include('partials.icon', ['name' => 'x']) Tampilkan Semua Kelas
            </a>
        </div>
    </div>
@endif

<!-- WHATSAPP-LIKE FILTER PILLS BAR -->
<div class="filter-section"
     data-kategori="{{ strtolower($kategori ?? '') }}"
     data-percobaan="{{ $percobaan ?? '' }}"
     data-kelulusan="{{ $kelulusan ?? '' }}"
     data-tanggal="{{ $tanggal ?? '' }}">
    <div class="filter-header-label">
        <span>@include('partials.icon', ['name' => 'check-square']) Klasifikasi &amp; Filter Cepat</span>
    </div>

    <div class="filter-pills-bar">

        <!-- 1. CHIP KATEGORI (DROPDOWN) -->
        <div class="pill-item">
            <button type="button" class="pill-btn" id="btn-chip-kategori" data-toggle-dropdown="dropdown-kategori">
                <span>@include('partials.icon', ['name' => 'tag']) Kategori:</span>
                <strong id="label-kategori">{{ $kategori ? ucfirst($kategori) : 'Semua' }}</strong>
                <span class="caret">@include('partials.icon', ['name' => 'chevron-down'])</span>
            </button>
            <div class="pill-dropdown" id="dropdown-kategori">
                <button type="button" class="pill-dropdown-item {{ !$kategori ? 'selected' : '' }}" data-filter-key="kategori" data-filter-value="">
                    Semua Kategori
                </button>
                <button type="button" class="pill-dropdown-item {{ strtolower($kategori) === 'ulangan' ? 'selected' : '' }}" data-filter-key="kategori" data-filter-value="ulangan">
                    Ulangan
                </button>
                <button type="button" class="pill-dropdown-item {{ strtolower($kategori) === 'kuis harian' ? 'selected' : '' }}" data-filter-key="kategori" data-filter-value="kuis harian">
                    Kuis Harian
                </button>
                <button type="button" class="pill-dropdown-item {{ strtolower($kategori) === 'pre-test' ? 'selected' : '' }}" data-filter-key="kategori" data-filter-value="pre-test">
                    Pre-test
                </button>
                <button type="button" class="pill-dropdown-item {{ strtolower($kategori) === 'post-test' ? 'selected' : '' }}" data-filter-key="kategori" data-filter-value="post-test">
                    Post-test
                </button>
                <button type="button" class="pill-dropdown-item {{ strtolower($kategori) === 'remedial' ? 'selected' : '' }}" data-filter-key="kategori" data-filter-value="remedial">
                    Remedial
                </button>
            </div>
        </div>

        <!-- 2. CHIP PERCOBAAN (INLINE FORM INPUT HANYA ANGKA) -->
        <div class="pill-item">
            <button type="button" class="pill-btn {{ $percobaan ? 'active' : '' }}" id="btn-chip-percobaan" data-show-percobaan>
                <span>@include('partials.icon', ['name' => 'refresh-cw']) Percobaan:</span>
                <strong id="label-percobaan">{{ $percobaan ? 'Ke-' . $percobaan : 'Semua' }}</strong>
                <span class="caret">@include('partials.icon', ['name' => 'chevron-down'])</span>
            </button>
            <div class="pill-input-form" id="form-chip-percobaan">
                <label class="u-font-size-12px u-font-weight-bold u-color-var---purple" for="input-percobaan">Ke-</label>
                <input type="number" id="input-percobaan" min="1" placeholder="1" value="{{ $percobaan ?? '' }}" data-submit-percobaan-on-enter>
                <button type="button" data-submit-percobaan aria-label="Terapkan percobaan" title="Terapkan">@include('partials.icon', ['name' => 'check'])</button>
                <button class="u-background-ddd u-color-333" type="button" data-reset-percobaan aria-label="Batal" title="Batal">@include('partials.icon', ['name' => 'x'])</button>
            </div>
        </div>

        <!-- 3. CHIP KELULUSAN (DROPDOWN) -->
        <div class="pill-item">
            <button type="button" class="pill-btn {{ $kelulusan ? 'active' : '' }}" id="btn-chip-kelulusan" data-toggle-dropdown="dropdown-kelulusan">
                <span>@include('partials.icon', ['name' => 'graduation-cap']) Kelulusan:</span>
                <strong id="label-kelulusan">
                    @if($kelulusan === 'lulus') Lulus (≥ KKM)
                    @elseif($kelulusan === 'tidak_lulus') Tidak Lulus (&lt; KKM)
                    @else Semua
                    @endif
                </strong>
                <span class="caret">@include('partials.icon', ['name' => 'chevron-down'])</span>
            </button>
            <div class="pill-dropdown" id="dropdown-kelulusan">
                <button type="button" class="pill-dropdown-item {{ !$kelulusan ? 'selected' : '' }}" data-filter-key="kelulusan" data-filter-value="">
                    Semua Kelulusan
                </button>
                <button type="button" class="pill-dropdown-item {{ $kelulusan === 'lulus' ? 'selected' : '' }}" data-filter-key="kelulusan" data-filter-value="lulus">
                    @include('partials.icon', ['name' => 'check']) Lulus (≥ KKM)
                </button>
                <button type="button" class="pill-dropdown-item {{ $kelulusan === 'tidak_lulus' ? 'selected' : '' }}" data-filter-key="kelulusan" data-filter-value="tidak_lulus">
                    @include('partials.icon', ['name' => 'alert-triangle']) Tidak Lulus (&lt; KKM)
                </button>
            </div>
        </div>

        <!-- 4. CHIP TANGGAL (DATE PICKER) -->
        <div class="pill-item">
            <button type="button" class="pill-btn {{ $tanggal ? 'active' : '' }}" id="btn-chip-tanggal" data-show-tanggal>
                <span>@include('partials.icon', ['name' => 'calendar']) Tanggal:</span>
                <strong id="label-tanggal">{{ $tanggal ? date('d/m/Y', strtotime($tanggal)) : 'Semua' }}</strong>
                <span class="caret">@include('partials.icon', ['name' => 'chevron-down'])</span>
            </button>
            <div class="pill-input-form" id="form-chip-tanggal">
                <input type="date" id="input-tanggal" value="{{ $tanggal ?? '' }}" aria-label="Tanggal pengerjaan" data-submit-tanggal>
                <button class="u-background-ddd u-color-333" type="button" data-reset-tanggal aria-label="Batal" title="Batal">@include('partials.icon', ['name' => 'x'])</button>
            </div>
        </div>

        <!-- 5. CHIP RESET (JIKA ADA FILTER AKTIF) -->
        <div class="pill-item {{ ($kategori || $percobaan || $kelulusan || $tanggal) ? '' : 'u-display-none' }}" id="pill-reset-wrapper">
            <button type="button" class="pill-btn btn-pill-reset" data-reset-filters>
                @include('partials.icon', ['name' => 'x']) Reset Filter
            </button>
        </div>

    </div>
</div>

<div class="u-display-flex u-justify-content-flex-end u-margin-bottom-16px">
    <a href="{{ route('guru.nilai.unduh', $idKelas ? ['kelas' => $idKelas] : []) }}"
       class="btn btn-secondary btn-small">
        @include('partials.icon', ['name' => 'download']) Unduh CSV
    </a>
</div>

@if($hasil->isEmpty())
    <div class="empty">
        <h3>Tidak Ada Hasil Kuis</h3>
        <p>Tidak ditemukan data hasil kuis yang sesuai dengan kriteria filter saat ini.</p>
        @if($kategori || $percobaan || $kelulusan || $tanggal)
            <button type="button" data-reset-filters class="btn btn-secondary btn-small">
                @include('partials.icon', ['name' => 'x']) Hapus Semua Filter
            </button>
        @endif
    </div>
@else
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Hasil Pengerjaan Kuis Siswa</h2>
                <p class="muted" id="info-count-row">Menampilkan {{ $hasil->total() }} data pengerjaan kuis siswa</p>
            </div>
        </div>

        <div class="table-wrap">
            <table id="table-nilai">
                <thead>
                    <tr>
                        <th class="u-width-40px">No</th>
                        <th>Siswa</th>
                        <th>Kuis</th>
                        <th>Kategori</th>
                        <th class="u-width-90px u-text-align-center">Percobaan</th>
                        <th class="u-width-70px">Nilai</th>
                        <th class="u-width-70px">KKM</th>
                        <th>Kelulusan</th>
                        <th>Tanggal</th>
                        <th class="u-min-width-220px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hasil as $item)
                        @php
                            $kat = strtolower(trim($item->kuis->kategori ?? ''));
                            $isUlangan = $kat === 'ulangan';
                            $lulus = $item->nilai !== null && $item->kuis && $item->kuis->kkm !== null && $item->nilai >= $item->kuis->kkm;
                            $sudahRemedial = in_array($item->id_mengerjakan, $sudahDitugaskanRemedial);
                        @endphp
                        <tr class="data-row" data-reveal
                            data-kategori="{{ $kat }}"
                            data-percobaan="{{ $item->percobaan_ke }}"
                            data-lulus="{{ $lulus ? 'lulus' : 'tidak_lulus' }}"
                            data-tanggal="{{ $item->tanggal ? date('Y-m-d', strtotime($item->tanggal)) : '' }}">

                            <td class="row-num number u-text-align-left">{{ ($hasil->firstItem() ?? 1) + $loop->index }}</td>

                            <td>
                                <strong>{{ $item->siswa->nama_lengkap ?? '-' }}</strong>
                                <span class="muted small">NISN: {{ $item->siswa->nomor_induk ?? '-' }}</span>
                            </td>

                            <td>
                                <strong>{{ $item->kuis->judul ?? '-' }}</strong>
                            </td>

                            <td>
                                <span class="badge-category {{ $kat }}">
                                    {{ $item->kuis->kategori ?? '-' }}
                                </span>
                            </td>

                            <td class="u-text-align-center">
                                <strong>Ke-{{ $item->percobaan_ke }}</strong>
                            </td>

                            <td>
                                <strong class="score {{ $lulus ? 'pass' : 'fail' }}">
                                    {{ $item->nilai !== null ? number_format($item->nilai, 1) : '-' }}
                                </strong>
                            </td>

                            <td>
                                {{ $item->kuis->kkm !== null ? number_format($item->kuis->kkm, 1) : '-' }}
                            </td>

                            <td>
                                @if($item->nilai === null)
                                    -
                                @elseif($lulus)
                                    <span class="pass">@include('partials.icon', ['name' => 'check']) Lulus</span>
                                @else
                                    <span class="fail">@include('partials.icon', ['name' => 'alert-triangle']) Tidak Lulus</span>
                                @endif
                            </td>

                            <td>
                                {{ $item->tanggal ? date('d/m/Y', strtotime($item->tanggal)) : '-' }}
                            </td>

                            <td>
                                <div class="u-display-flex u-align-items-center u-gap-8px u-flex-wrap-wrap">
                                    <a href="{{ route('guru.nilai.show', $item->id_mengerjakan) }}" class="btn btn-secondary btn-small">
                                        Lihat Detail
                                    </a>

                                    {{-- FITUR BERIKAN REMEDIAL: HANYA UNTUK KUIS KATEGORI ULANGAN YANG TIDAK LULUS --}}
                                    @if($isUlangan && !$lulus)
                                        @if($sudahRemedial)
                                            <span class="badge-remedial-done" title="Siswa sudah diberikan tugas remedial">
                                                @include('partials.icon', ['name' => 'check']) Remedial Ditugaskan
                                            </span>
                                        @else
                                            <form class="u-display-inline" action="{{ route('guru.nilai.remedial', $item->id_mengerjakan) }}" method="POST" data-confirm="Berikan tugas remedial untuk siswa {{ $item->siswa->nama_lengkap ?? 'ini' }}?">
                                                @csrf
                                                <button type="submit" class="btn-action-remedial" title="Tugaskan kuis remedial ke siswa ini">
                                                    @include('partials.icon', ['name' => 'check-square']) Berikan Remedial
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $hasil])
    </section>
@endif

@push('scripts')
<script src="{{ asset('js/guru/nilai/index.js') }}" defer></script>
@endpush

@endsection
