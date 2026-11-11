@extends('layouts.guru')
@section('title', 'Detail Materi - ' . $materi->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/materi/show.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('materi.index', ['mapel' => $materi->id_mapel]) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>{{ $materi->judul }}</h1>
        <p>{{ $materi->bab ?? 'Materi Pembelajaran' }} &bull; Kelas {{ $materi->kelas ?? '-' }}</p>
    </div>
    <div class="actions">
        <a href="{{ route('kuis.create', ['mapel' => $materi->id_mapel, 'materi' => $materi->id_materi, 'kategori' => 'post-test']) }}" class="btn btn-secondary">
            Buat Post-test
        </a>
        <a href="{{ route('kuis.create', ['mapel' => $materi->id_mapel, 'materi' => $materi->id_materi, 'kategori' => 'pre-test']) }}" class="btn btn-secondary">
            Buat Pre-test
        </a>
        <a href="{{ route('materi.edit', $materi->id_materi) }}" class="btn">
            Edit Materi
        </a>
    </div>
</div>

<div class="grid grid-2">
    <!-- KIRI: DOKUMEN PDF / ISI -->
    <div>
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Isi &amp; Dokumen Pembelajaran</h2>
                    <p class="muted">Pratinjau dokumen materi siswa</p>
                </div>
                @if($pdfUrl)
                    <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-secondary btn-small">
                        Buka PDF di Tab Baru ↗
                    </a>
                @endif
            </div>

            @if($pdfUrl)
                <div class="pdf-viewer-container" data-pdf-url="{{ $pdfUrl }}">
                    <div class="pdf-toolbar">
                        <button type="button" id="prev-page" class="btn btn-secondary btn-small">
                            ← Sebelumnya
                        </button>
                        <span class="pdf-page-indicator">
                            Halaman <strong id="page_num">1</strong> dari <strong id="page_count">-</strong>
                        </span>
                        <button type="button" id="next-page" class="btn btn-secondary btn-small">
                            Selanjutnya →
                        </button>
                    </div>
                    <div class="pdf-canvas-wrapper">
                        <div id="pdf-loading" class="pdf-loading">Memuat dokumen PDF...</div>
                        <canvas class="u-display-none" id="pdf-canvas"></canvas>
                    </div>
                </div>
            @elseif($materi->isi_materi)
                <div class="alert alert-warning">
                    File tercatat sebagai <strong>{{ basename($materi->isi_materi) }}</strong>, namun tidak ditemukan di penyimpanan server.
                </div>
            @else
                <div class="empty">
                    <p>Belum ada file dokumen PDF yang diunggah untuk materi ini.</p>
                </div>
            @endif
        </section>

        @if($materi->link_youtube)
            <section class="card u-margin-top-20px">
                <div class="card-header">
                    <h2>Video Pembelajaran</h2>
                </div>
                <a href="{{ $materi->link_youtube }}" target="_blank" class="btn btn-secondary">
                    ▶ Tonton Video di YouTube ↗
                </a>
            </section>
        @endif
    </div>

    <!-- KANAN: DETAIL INFORMASI -->
    <div>
        <section class="card">
            <div class="card-header">
                <h2>Informasi Materi</h2>
                <span class="badge">Kelas {{ $materi->kelas ?? '-' }}</span>
            </div>

            <dl class="identity">
                <div>
                    <dt>Mata Pelajaran</dt>
                    <dd>{{ $materi->mapel?->nama_mapel ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Bab</dt>
                    <dd>{{ $materi->bab ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Kelas</dt>
                    <dd>{{ $materi->kelas ?? '-' }}</dd>
                </div>
            </dl>

            <div class="u-margin-top-20px u-padding-top-16px u-border-top-1px-solid-var---border">
                <span class="muted small u-display-block u-margin-bottom-6px u-font-weight-600">Deskripsi Materi:</span>
                <p class="u-margin-0 u-line-height-1p6 u-color-var---ink">
                    {{ $materi->deskripsi ?? 'Tidak ada deskripsi materi.' }}
                </p>
            </div>
        </section>
    </div>
</div>

@if($pdfUrl)
@push('scripts')
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script defer src="{{ asset('js/shared/pdf-viewer.js') }}"></script>
@endpush
@endif

@endsection
