@extends('layouts.guru')
@section('title', 'Dashboard Guru')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/guru/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animasi.css') }}">
@endpush
@section('content')

<div class="page-heading dashboard-heading">
    <div class="dashboard-heading__title">
        <h1>Dashboard</h1>
        <p>Ringkasan kelas</p>
    </div>
    @if($kelasDipilih)
        <form class="dashboard-heading__filters" method="GET" action="{{ route('dashboard') }}">
            <div class="field">
                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas">
                    @foreach($kelasUntukGuru->unique('id_kelas') as $kelas)
                        <option value="{{ $kelas->id_kelas }}" @selected($kelasDipilih->id_kelas === $kelas->id_kelas)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="mapel">Mata pelajaran</label>
                <select id="mapel" name="mapel">
                    @foreach($mapelUntukKelas as $mapel)
                        <option value="{{ $mapel->id_mapel }}" @selected($kelasDipilih->id_mapel === $mapel->id_mapel)>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn">Tampilkan</button>
        </form>
    @endif
</div>

@if(!$kelasDipilih)
    <section class="card">
        <div class="empty">
            <p>Belum ada kelas dan mapel yang diajarkan. Hubungi admin.</p>
        </div>
    </section>
@else
    <div class="stats dashboard-kelas-stats anim-stagger">
        <div class="stat anim-item">
            <span class="label">Siswa aktif</span>
            <span class="value" data-count-up="{{ $ringkasan['jumlah_siswa'] }}">{{ $ringkasan['jumlah_siswa'] }}</span>
        </div>
        <div class="stat anim-item">
            <span class="label">Rata-rata kelas</span>
            <span class="value" @if($ringkasan['rata_rata_nilai'] !== null) data-count-up="{{ number_format($ringkasan['rata_rata_nilai'], 1, '.', '') }}" data-decimals="1" @endif>{{ $ringkasan['rata_rata_nilai'] === null ? '—' : number_format($ringkasan['rata_rata_nilai'], 1) }}</span>
        </div>
        <div class="stat anim-item">
            <span class="label">Perlu dibantu</span>
            <span class="value" data-count-up="{{ $ringkasan['jumlah_perlu_dibantu'] }}">{{ $ringkasan['jumlah_perlu_dibantu'] }}</span>
        </div>
    </div>

    <section class="card dashboard-progres-card anim-item">
        <div class="dashboard-progres-card__body">
            <div class="dashboard-progres-card__circle">
                <svg class="dashboard-progres-ring" viewBox="0 0 120 120" role="img" aria-labelledby="progres-materi-title">
                    <title id="progres-materi-title">Materi dibaca {{ number_format($progresKelas['persentase_materi'], 0) }} persen</title>
                    <circle cx="60" cy="60" r="42" fill="none" class="dashboard-progres-ring__bg" stroke-width="11" />
                    <circle
                        cx="60"
                        cy="60"
                        r="42"
                        fill="none"
                        class="dashboard-progres-ring__val"
                        stroke-width="11"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $progresKelas['keliling_cincin'] }}"
                        stroke-dashoffset="{{ $progresKelas['offset_cincin'] }}"
                        data-ring-target="{{ $progresKelas['offset_cincin'] }}"
                        transform="rotate(-90 60 60)"
                    />
                    <text x="60" y="66" text-anchor="middle" class="dashboard-progres-ring__text" data-count-up="{{ number_format($progresKelas['persentase_materi'], 0, '.', '') }}" data-suffix="%">
                        {{ number_format($progresKelas['persentase_materi'], 0) }}%
                    </text>
                </svg>
                <span class="dashboard-progres-ring__caption">Materi dibaca</span>
            </div>

            <div class="dashboard-progres-card__content">
                <h2 class="dashboard-progres-card__title">Progres kuis</h2>
                <div class="dashboard-progres-card__list">
                    @foreach($kategoriKelulusan as $kategori)
                        <div class="dashboard-progres-row">
                            <div class="dashboard-progres-row__meta">
                                <strong class="dashboard-progres-row__name">{{ ucfirst($kategori->kategori) }}</strong>
                                <span class="dashboard-progres-row__numbers">
                                    {{ $kategori->jumlah_dikerjakan }} dari {{ $kategori->jumlah_peluang }}
                                    @if($kategori->jumlah_lulus > 0)
                                        · <span class="text-lulus">{{ $kategori->jumlah_lulus }} lulus</span>
                                    @endif
                                    @if($kategori->jumlah_menunggu > 0)
                                        · <span class="text-menunggu">{{ $kategori->jumlah_menunggu }} menunggu penilaian</span>
                                    @endif
                                    @if($kategori->jumlah_belum > 0)
                                        · <span class="text-belum">{{ $kategori->jumlah_belum }} belum</span>
                                    @endif
                                </span>
                            </div>
                            <div class="dashboard-progres-track">
                                <progress
                                    max="{{ max(1, $kategori->jumlah_peluang) }}"
                                    value="{{ $kategori->jumlah_dikerjakan }}"
                                    data-progress-target="{{ $kategori->jumlah_dikerjakan }}"
                                    aria-label="Progres pengerjaan {{ $kategori->kategori }}"
                                >
                                    {{ $kategori->jumlah_dikerjakan }} dari {{ $kategori->jumlah_peluang }}
                                </progress>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="card dashboard-search">
        <form class="filter-form" method="GET" action="{{ route('dashboard') }}">
            <input type="hidden" name="kelas" value="{{ $kelasDipilih->id_kelas }}">
            <input type="hidden" name="mapel" value="{{ $kelasDipilih->id_mapel }}">
            <div class="field field-search">
                <label for="cari">Cari nama atau NISN</label>
                <input id="cari" name="cari" type="search" value="{{ $cari }}" maxlength="100" placeholder="Masukkan nama atau NISN siswa">
            </div>
            <button type="submit" class="btn">Cari</button>
        </form>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2>Daftar siswa</h2>
                <p class="muted">{{ $kelasDipilih->nama_kelas }} · {{ $kelasDipilih->nama_mapel }}</p>
            </div>
        </div>
        @if($daftarSiswa->isNotEmpty())
            <div class="table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Materi dibaca</th>
                            <th>Kuis dikerjakan</th>
                            <th>Rata-rata</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daftarSiswa as $siswa)
                            <tr>
                                <td>
                                    <strong>
                                        <a href="{{ route('dashboard', ['kelas' => $kelasDipilih->id_kelas, 'mapel' => $kelasDipilih->id_mapel, 'siswa' => $siswa->id_user]) }}">
                                            {{ $siswa->nama_lengkap }}
                                        </a>
                                    </strong>
                                    <span class="muted small">{{ $siswa->nomor_induk ?? 'NISN belum tersedia' }}</span>
                                </td>
                                <td>{{ number_format($siswa->persentase_materi_dibaca, 0) }}%</td>
                                <td>{{ $siswa->jumlah_kuis_dikerjakan }} dari {{ $siswa->jumlah_kuis_total }}</td>
                                <td>{{ $siswa->rata_rata_nilai === null ? '—' : number_format($siswa->rata_rata_nilai, 1) }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($siswa->status) {
                                            'Perlu dibantu' => 'dashboard-status--perlu-dibantu',
                                            'Belum mulai' => 'dashboard-status--belum-mulai',
                                            default => 'dashboard-status--lancar',
                                        };
                                    @endphp
                                    <span class="dashboard-status {{ $statusClass }}">{{ $siswa->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                <p>Tidak ada siswa yang cocok dengan pencarian di kelas ini.</p>
            </div>
        @endif
    </section>

    @if($detailSiswa)
        <section class="card dashboard-siswa-detail" id="detail-siswa">
            <div class="card-header">
                <div>
                    <h2>Detail perkembangan siswa</h2>
                    <p class="muted">{{ $detailSiswa['siswa']->nama_lengkap }} · {{ $detailSiswa['siswa']->nomor_induk ?? 'NISN belum tersedia' }}</p>
                </div>
            </div>

            <div class="dashboard-detail-overview">
                <div class="dashboard-detail-material">
                    <h3>Cakupan materi</h3>
                    <svg class="dashboard-detail-ring" viewBox="0 0 120 120" role="img" aria-labelledby="cakupan-materi-title">
                        <title id="cakupan-materi-title">Cakupan materi {{ number_format($detailSiswa['persentase_materi'], 1) }} persen</title>
                        <circle cx="60" cy="60" r="42" fill="none" class="dashboard-detail-ring__bg" stroke-width="12" />
                        <circle
                            cx="60"
                            cy="60"
                            r="42"
                            fill="none"
                            class="dashboard-detail-ring__val"
                            stroke-width="12"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $detailSiswa['keliling_cincin'] }}"
                            stroke-dashoffset="{{ $detailSiswa['offset_cincin'] }}"
                            transform="rotate(-90 60 60)"
                        />
                        <text x="60" y="66" text-anchor="middle" class="dashboard-detail-ring__value">
                            {{ number_format($detailSiswa['persentase_materi'], 0) }}%
                        </text>
                    </svg>
                    <p>{{ $detailSiswa['jumlah_materi_dibaca'] }} dari {{ $detailSiswa['jumlah_materi'] }} materi sudah dibaca</p>
                </div>

                <div class="dashboard-detail-categories">
                    <h3>Progres per kategori</h3>
                    @forelse($detailSiswa['progres_kategori'] as $kategori)
                        <div class="dashboard-detail-category">
                            <strong>{{ ucfirst($kategori['kategori']) }}</strong>
                            <p>
                                {{ $kategori['dikerjakan'] }} dari {{ $kategori['jumlah_kuis'] }} dikerjakan
                                · {{ $kategori['lulus'] }} lulus
                                @if($kategori['menunggu'] > 0)
                                    · {{ $kategori['menunggu'] }} menunggu penilaian
                                @endif
                                · {{ $kategori['belum'] }} belum
                            </p>
                        </div>
                    @empty
                        <p class="muted">Belum ada kategori kuis untuk mata pelajaran ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="dashboard-detail-chart">
                <h3>Nilai terbaik per kuis</h3>
                @if($detailSiswa['grafik_nilai']['points'] !== [])
                    <div class="dashboard-detail-chart__scroll">
                        <svg
                            id="grafik-nilai-per-kuis"
                            class="dashboard-detail-chart__svg"
                            viewBox="0 0 {{ $detailSiswa['grafik_nilai']['lebar'] }} {{ $detailSiswa['grafik_nilai']['tinggi'] }}"
                            role="img"
                            aria-labelledby="grafik-nilai-title"
                        >
                            <title id="grafik-nilai-title">Nilai terbaik siswa per kuis</title>
                            @foreach($detailSiswa['grafik_nilai']['ticks'] as $tick)
                                <line
                                    x1="{{ $detailSiswa['grafik_nilai']['kiri'] }}"
                                    x2="{{ $detailSiswa['grafik_nilai']['lebar'] - 20 }}"
                                    y1="{{ $tick['y'] }}"
                                    y2="{{ $tick['y'] }}"
                                    class="dashboard-detail-chart__grid"
                                    stroke-width="1"
                                />
                                <text x="{{ $detailSiswa['grafik_nilai']['kiri'] - 10 }}" y="{{ $tick['y'] + 4 }}" text-anchor="end">
                                    {{ $tick['nilai'] }}
                                </text>
                            @endforeach
                            <line
                                x1="{{ $detailSiswa['grafik_nilai']['kiri'] }}"
                                x2="{{ $detailSiswa['grafik_nilai']['lebar'] - 20 }}"
                                y1="{{ $detailSiswa['grafik_nilai']['dasar'] }}"
                                y2="{{ $detailSiswa['grafik_nilai']['dasar'] }}"
                                class="dashboard-detail-chart__axis"
                                stroke-width="1.5"
                            />
                            @foreach($detailSiswa['grafik_nilai']['points'] as $point)
                                <g
                                    data-quiz-id="{{ $point['id_kuis'] }}"
                                    data-kkm="{{ $point['kkm_display'] ?? '' }}"
                                    data-lulus="{{ $point['lulus'] === null ? 'na' : ($point['lulus'] ? 'true' : 'false') }}"
                                    aria-label="{{ $point['judul'] }}: nilai {{ number_format($point['nilai'], 1) }}, KKM {{ $point['kkm_display'] ?? 'tidak diatur' }}"
                                >
                                    <title>{{ $point['judul'] }} — nilai {{ number_format($point['nilai'], 1) }}@if($point['kkm_display'] !== null), KKM {{ $point['kkm_display'] }}@endif</title>
                                    <rect
                                        x="{{ $point['x'] }}"
                                        y="{{ $point['bar_y'] }}"
                                        width="44"
                                        height="{{ $point['bar_tinggi'] }}"
                                        rx="5"
                                        fill="{{ $point['warna'] }}"
                                    />
                                    @if($point['kkm_y'] !== null)
                                        <line
                                            x1="{{ $point['x'] - 7 }}"
                                            x2="{{ $point['x'] + 51 }}"
                                            y1="{{ $point['kkm_y'] }}"
                                            y2="{{ $point['kkm_y'] }}"
                                            class="dashboard-detail-chart__kkm"
                                            stroke-width="3"
                                            stroke-linecap="round"
                                            data-kkm="{{ $point['kkm_display'] }}"
                                        />
                                    @endif
                                    <text x="{{ $point['tengah_x'] }}" y="{{ $point['bar_y'] - 8 }}" text-anchor="middle">
                                        {{ number_format($point['nilai'], 1) }}
                                    </text>
                                    <text
                                        x="{{ $point['tengah_x'] }}"
                                        y="{{ $point['label_y'] }}"
                                        text-anchor="end"
                                        transform="rotate(-30 {{ $point['tengah_x'] }} {{ $point['label_y'] }})"
                                    >
                                        {{ $point['judul'] }}
                                    </text>
                                </g>
                            @endforeach
                        </svg>
                    </div>
                @else
                    <div class="empty">
                        <p>{{ $detailSiswa['grafik_nilai']['pesan'] }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    <section class="card dashboard-remedial anim-item">
        <div class="card-header">
            <div>
                <h2>Remedial</h2>
                <p class="muted">
                    {{ $remedial['ditugaskan'] }} siswa ditugaskan
                    · {{ $remedial['selesai'] }} selesai
                    · {{ $remedial['belum'] }} belum
                </p>
            </div>
        </div>
    </section>
@endif

@endsection

@push('scripts')
    <script src="{{ asset('js/animasi.js') }}" defer></script>
@endpush
