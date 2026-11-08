@extends('layouts.siswa')
@section('title', $materi->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/siswa/materi/show.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('siswa.materi.index', ['mapel' => $materi->id_mapel]) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>{{ $materi->judul }}</h1>
        <p>{{ $materi->bab ?? '-' }} &bull; Kelas {{ $materi->kelas ?? '-' }}</p>
    </div>
    @if($pdfUrl)
        <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-secondary btn-small">
            Buka PDF di tab baru ↗
        </a>
    @endif
</div>

<div class="grid grid-2">
    <!-- KIRI: ISI MATERI -->
    <div class="u-display-flex u-flex-direction-column u-gap-20px">
        <section class="card">
            <div class="card-header">
                <h2>Isi Dokumen Materi</h2>
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
                        <div id="pdf-loading" class="pdf-loading">Memuat dokumen materi...</div>
                        <canvas class="u-display-none" id="pdf-canvas"></canvas>
                    </div>
                </div>
            @elseif($materi->isi_materi)
                <p class="isi-teks">{{ $materi->isi_materi }}</p>
            @else
                <div class="empty">
                    <p>File atau teks materi belum tersedia.</p>
                </div>
            @endif
        </section>

        @if($materi->link_youtube)
            <section class="card">
                <div class="card-header">
                    <h2>Video Pembelajaran</h2>
                </div>
                <a href="{{ $materi->link_youtube }}" target="_blank" class="btn btn-secondary">
                    ▶ Tonton video pembelajaran di YouTube ↗
                </a>
            </section>
        @endif
    </div>

    <!-- KANAN: INFORMASI & PROGRESS -->
    <div class="u-display-flex u-flex-direction-column u-gap-20px">
        <section class="card">
            <div class="card-header">
                <h2>Informasi Materi</h2>
                @if($progress->status === 'selesai')
                    <span class="badge badge-success">✓ Selesai</span>
                @else
                    <span class="badge badge-warning">Sedang Belajar</span>
                @endif
            </div>

            <dl class="identity">
                <div>
                    <dt>Judul</dt>
                    <dd>{{ $materi->judul }}</dd>
                </div>
                <div>
                    <dt>Bab</dt>
                    <dd>{{ $materi->bab ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Kelas</dt>
                    <dd>{{ $materi->kelas ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Deskripsi</dt>
                    <dd>{{ $materi->deskripsi ?? '-' }}</dd>
                </div>
            </dl>
        </section>

        <section class="card">
            <div class="card-header">
                <h2>Tandai Pembelajaran</h2>
            </div>

            @if($progress->status !== 'selesai')
                <p class="muted small u-margin-bottom-16px">
                    Sudah selesai membaca dan memahami materi ini? Tandai agar progres belajarmu tercatat.
                </p>

                <form action="{{ route('siswa.materi.selesai', $materi->id_materi) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn">
                        ✓ Tandai Sudah Selesai Membaca
                    </button>
                </form>
            @else
                <div class="alert alert-success u-margin-bottom-0">
                    ✓ Materi ini sudah kamu tandai selesai dipelajari!
                </div>
            @endif
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
