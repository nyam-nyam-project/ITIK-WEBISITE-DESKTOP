@extends('layouts.siswa')
@section('title', 'Materi Pembelajaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/siswa/materi/index.css') }}">
@endpush

@section('content')

@if(!$mapelTerpilih)
    {{-- ============================================================ --}}
    {{-- LEVEL 1: PILIH MATA PELAJARAN --}}
    {{-- ============================================================ --}}
    <div class="page-heading">
        <div>
            <h1>Materi Pembelajaran</h1>
            <p>Pilih mata pelajaran untuk mulai membaca dan mempelajari materi bahan ajar.</p>
        </div>
        <a href="{{ route('siswa.kuis.index') }}" class="btn btn-secondary">
            Ke Daftar Kuis →
        </a>
    </div>

    @if($mapelAktif->isEmpty())
        <div class="empty">
            <h3>Belum Ada Mata Pelajaran Aktif</h3>
            <p>Materi pembelajaran akan tersedia setelah ditambahkan oleh guru.</p>
        </div>
    @else
        <div class="mapel-grid-siswa">
            @foreach($mapelAktif as $mp)
                <a href="{{ route('siswa.materi.index', ['mapel' => $mp->id_mapel]) }}" class="mapel-card-s">
                    <div class="mapel-icon-s">📚</div>
                    <div>
                        <div class="mapel-code-s">{{ $mp->kode_mapel }}</div>
                        <h3 class="mapel-title-s">{{ $mp->nama_mapel }}</h3>
                        <div class="mapel-count-s">{{ $mp->materi_count ?? 0 }} Materi Pembelajaran</div>
                    </div>
                    <div class="mapel-arrow-s">→</div>
                </a>
            @endforeach
        </div>
    @endif

@else
    {{-- ============================================================ --}}
    {{-- LEVEL 2: DAFTAR MATERI PER MAPEL (ALA FOTO 2) --}}
    {{-- ============================================================ --}}
    <div class="breadcrumb-siswa">
        <a href="{{ route('siswa.materi.index') }}">Materi</a>
        <span class="sep">›</span>
        <span class="current">{{ $mapelTerpilih->nama_mapel }}</span>
    </div>

    <div class="page-heading">
        <div>
            <a href="{{ route('siswa.materi.index') }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
                ← Kembali
            </a>
            <h1>{{ $mapelTerpilih->nama_mapel }}</h1>
            <p>{{ $materi->count() }} materi pembelajaran tersedia untuk mata pelajaran ini.</p>
        </div>
    </div>

    @if($materi->isEmpty())
        <div class="empty">
            <h3>Belum Ada Materi untuk Mata Pelajaran Ini</h3>
            <p>Guru belum menambahkan materi pembelajaran untuk mata pelajaran ini.</p>
            <a href="{{ route('siswa.materi.index') }}" class="btn btn-secondary u-margin-top-14px">
                ← Pilih Mata Pelajaran Lain
            </a>
        </div>
    @else
        <div class="materi-list-foto2">
            @foreach($materi as $idx => $item)
                @php
                    $prog = $item->relationLoaded('progress') ? $item->progress->first() : null;
                @endphp
                <a href="{{ route('siswa.materi.show', $item->id_materi) }}" class="materi-card-link">
                    <div class="materi-main-info">
                        <div class="materi-num-badge">
                            {{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="materi-text-wrap">
                            <h3 class="materi-row-title">{{ $item->judul }}</h3>
                            <p class="materi-row-subtitle">
                                @if($item->bab)
                                    <strong>{{ $item->bab }}</strong> &bull;
                                @endif
                                Kelas {{ $item->kelas ?? '-' }}
                                @if($item->deskripsi)
                                    &bull; {{ Str::limit($item->deskripsi, 85) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="materi-side-info">
                        @if(!$prog)
                            <span class="badge badge-muted">Belum Mulai</span>
                        @elseif($prog->status === 'sedang belajar')
                            <span class="badge badge-warning">Sedang Belajar</span>
                        @elseif($prog->status === 'selesai')
                            <span class="badge badge-success">✓ Selesai</span>
                        @else
                            <span class="badge">{{ $prog->status }}</span>
                        @endif
                        <span class="arrow-icon">→</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

@endif

@endsection
