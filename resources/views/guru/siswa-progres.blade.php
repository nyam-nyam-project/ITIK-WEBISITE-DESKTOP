@extends('layouts.guru')
@section('title', 'Progres ' . $detailSiswa['siswa']->nama_lengkap)
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/guru/siswa-progres.css') }}">
@endpush
@section('content')

<div class="page-heading">
    <div>
        <h1>Progres Siswa</h1>
        <p class="muted">{{ $kelasDipilih->nama_kelas }} · {{ $kelasDipilih->nama_mapel }}</p>
    </div>
    <x-tombol-kembali :fallback="route('dashboard', ['kelas' => $kelasDipilih->id_kelas, 'mapel' => $kelasDipilih->id_mapel])" label="Kembali ke Dashboard" />
</div>

<section class="progres-profil card">
    <div class="progres-profil__info">
        <span class="progres-profil__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($detailSiswa['siswa']->nama_lengkap, 0, 1)) }}</span>
        <div>
            <h2 class="progres-profil__nama">{{ $detailSiswa['siswa']->nama_lengkap }}</h2>
            <p class="progres-profil__nisn muted">{{ $detailSiswa['siswa']->nomor_induk ?? 'NISN belum tersedia' }}</p>
        </div>
    </div>
</section>

<div class="progres-ringkasan">
    <section class="card progres-ringkasan__materi">
        <h3>Cakupan Materi</h3>
        <div class="progres-ring-wrap">
            <svg class="progres-ring" viewBox="0 0 120 120" role="img" aria-labelledby="cakupan-materi-title">
                <title id="cakupan-materi-title">Cakupan materi {{ number_format($detailSiswa['persentase_materi'], 1) }} persen</title>
                <circle cx="60" cy="60" r="42" fill="none" class="progres-ring__bg" stroke-width="10" />
                <circle
                    cx="60"
                    cy="60"
                    r="42"
                    fill="none"
                    class="progres-ring__val"
                    stroke-width="10"
                    stroke-linecap="round"
                    stroke-dasharray="{{ $detailSiswa['keliling_cincin'] }}"
                    stroke-dashoffset="{{ $detailSiswa['offset_cincin'] }}"
                    transform="rotate(-90 60 60)"
                />
                <text x="60" y="65" text-anchor="middle" class="progres-ring__persen">
                    {{ number_format($detailSiswa['persentase_materi'], 0) }}%
                </text>
            </svg>
        </div>
        <p class="progres-ringkasan__keterangan">
            {{ $detailSiswa['jumlah_materi_dibaca'] }} dari {{ $detailSiswa['jumlah_materi'] }} materi selesai dibaca
        </p>
    </section>

    <section class="card progres-ringkasan__kategori">
        <h3>Progres per Kategori</h3>
        <div class="progres-kategori-list">
            @forelse($detailSiswa['progres_kategori'] as $kategori)
                @php
                    $persen = $kategori['jumlah_kuis'] === 0 ? 0 : round(($kategori['dikerjakan'] / $kategori['jumlah_kuis']) * 100);
                @endphp
                <div class="progres-kategori-item">
                    <div class="progres-kategori-item__head">
                        <strong>{{ ucfirst($kategori['kategori']) }}</strong>
                        <span class="progres-kategori-item__angka">{{ $kategori['dikerjakan'] }} dari {{ $kategori['jumlah_kuis'] }} dikerjakan</span>
                    </div>
                    <div class="progres-kategori-track">
                        <div class="progres-kategori-bar" data-persen="{{ $persen }}"></div>
                    </div>
                    <div class="progres-kategori-item__detail">
                        <span class="progres-label--lulus">{{ $kategori['lulus'] }} lulus</span>
                        @if($kategori['menunggu'] > 0)
                            <span class="progres-label--menunggu">{{ $kategori['menunggu'] }} menunggu penilaian</span>
                        @endif
                        @if($kategori['belum'] > 0)
                            <span class="progres-label--belum">{{ $kategori['belum'] }} belum</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="muted">Belum ada kategori kuis untuk mata pelajaran ini.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="card progres-grafik">
    <h3>Nilai Terbaik per Kuis</h3>
    @if($detailSiswa['grafik_nilai']['points'] !== [])
        <div class="progres-grafik__legend">
            <span class="progres-grafik__legend-item progres-grafik__legend--lulus">Lulus KKM</span>
            <span class="progres-grafik__legend-item progres-grafik__legend--gagal">Belum lulus KKM</span>
            <span class="progres-grafik__legend-item progres-grafik__legend--na">KKM belum diatur</span>
            <span class="progres-grafik__legend-item progres-grafik__legend--kkm">— Garis KKM</span>
        </div>
        <div class="progres-grafik__table-wrap">
            <table class="progres-grafik__table">
                <thead>
                    <tr>
                        <th>Kuis</th>
                        <th>Nilai</th>
                        <th>KKM</th>
                        <th>Status</th>
                        <th>Grafik</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailSiswa['grafik_nilai']['points'] as $point)
                        @php
                            $persen = max(0, min(100, $point['nilai']));
                            $kkmPersen = $point['kkm'] !== null ? max(0, min(100, $point['kkm'])) : null;
                            $statusClass = match (true) {
                                $point['lulus'] === null => 'progres-badge--na',
                                $point['lulus'] === true => 'progres-badge--lulus',
                                default => 'progres-badge--gagal',
                            };
                            $statusLabel = match (true) {
                                $point['lulus'] === null => 'Tanpa KKM',
                                $point['lulus'] === true => 'Lulus',
                                default => 'Belum lulus',
                            };
                        @endphp
                        <tr
                            data-quiz-id="{{ $point['id_kuis'] }}"
                            data-kkm="{{ $point['kkm_display'] ?? '' }}"
                            data-lulus="{{ $point['lulus'] === null ? 'na' : ($point['lulus'] ? 'true' : 'false') }}"
                        >
                            <td class="progres-grafik__judul">{{ $point['judul'] }}</td>
                            <td class="progres-grafik__nilai">{{ number_format($point['nilai'], 1) }}</td>
                            <td class="progres-grafik__kkm">{{ $point['kkm_display'] ?? '—' }}</td>
                            <td><span class="progres-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td class="progres-grafik__bar-cell">
                                <div class="progres-bar-inline">
                                    <div class="progres-bar-inline__fill {{ $statusClass }}" data-persen="{{ $persen }}"></div>
                                    @if($kkmPersen !== null)
                                        <div class="progres-bar-inline__kkm" data-persen="{{ $kkmPersen }}"></div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">
            <p>{{ $detailSiswa['grafik_nilai']['pesan'] }}</p>
        </div>
    @endif
</section>

@endsection

@push('scripts')
    <script src="{{ asset('js/guru/siswa-progres.js') }}" defer></script>
@endpush
