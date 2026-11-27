@extends('layouts.guru')
@section('title', 'Materi Pembelajaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/materi/index.css') }}">
@endpush

@section('content')

@if(!$mapelTerpilih)
    {{-- ============================================================ --}}
    {{-- LEVEL 1: PILIH MATA PELAJARAN --}}
    {{-- ============================================================ --}}
    <div class="page-heading">
        <div>
            <h1>Materi Pembelajaran</h1>
            <p>Pilih mata pelajaran untuk melihat dan mengelola materi pembelajaran siswa.</p>
        </div>
        <div class="actions">
            <a href="{{ route('guru.arsip.index', ['tab' => 'materi']) }}" class="btn btn-secondary">
                @include('partials.icon', ['name' => 'folder']) Kotak Arsip
            </a>
        </div>
    </div>

    @if($tanpaPengajaran ?? false)
        <div class="empty">
            <h3>Belum Ada Penugasan Pengajaran</h3>
            <p>Anda belum ditugaskan untuk mengajar mata pelajaran apa pun. Silakan hubungi administrator sekolah.</p>
        </div>
    @elseif($mapelAktif->isEmpty())
        <div class="empty">
            <h3>Belum Ada Mata Pelajaran Aktif</h3>
            <p>Hubungi admin untuk menambahkan mata pelajaran pembelajaran.</p>
        </div>
    @else
        <div class="mapel-grid">
            @foreach($mapelAktif as $mp)
                <a href="{{ route('materi.index', ['mapel' => $mp->id_mapel]) }}" class="mapel-card" data-reveal>
                    <div class="mapel-icon">@include('partials.icon', ['name' => 'book-open'])</div>
                    <div>
                        <div class="mapel-code">{{ $mp->kode_mapel }}</div>
                        <h3 class="mapel-title">{{ $mp->nama_mapel }}</h3>
                        <div class="mapel-count">{{ $mp->materi_count ?? 0 }} Materi Pembelajaran</div>
                    </div>
                    <div class="mapel-arrow">@include('partials.icon', ['name' => 'arrow-right'])</div>
                </a>
            @endforeach
        </div>
    @endif

@else
    {{-- ============================================================ --}}
    {{-- LEVEL 2: DAFTAR MATERI PER MATA PELAJARAN (ALA FOTO 2) --}}
    {{-- ============================================================ --}}
    <div class="breadcrumb">
        <a href="{{ route('materi.index') }}">Materi</a>
        <span class="sep">@include('partials.icon', ['name' => 'chevron-right'])</span>
        <span class="current">{{ $mapelTerpilih->nama_mapel }}</span>
    </div>

    <div class="page-heading">
        <div>
            <x-tombol-kembali :fallback="route('materi.index')" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px" />
            <h1>{{ $mapelTerpilih->nama_mapel }}</h1>
            <p>{{ $materi->count() }} materi pembelajaran tersedia untuk mata pelajaran ini.</p>
        </div>
        <div class="actions">
            <a href="{{ route('guru.arsip.index', ['tab' => 'materi', 'mapel' => $mapelTerpilih->id_mapel]) }}" class="btn btn-secondary">
                @include('partials.icon', ['name' => 'folder']) Kotak Arsip
            </a>
            <a href="{{ route('materi.create', ['mapel' => $mapelTerpilih->id_mapel]) }}" class="btn">
                @include('partials.icon', ['name' => 'plus']) Tambah Materi
            </a>
        </div>
    </div>

    @if($materi->isEmpty())
        <div class="empty">
            <h3>Belum Ada Materi untuk Mata Pelajaran Ini</h3>
            <p>Silakan tambahkan materi pembelajaran pertama untuk siswa.</p>
            <a href="{{ route('materi.create', ['mapel' => $mapelTerpilih->id_mapel]) }}" class="btn u-margin-top-14px">
                @include('partials.icon', ['name' => 'plus']) Tambah Materi Pertama
            </a>
        </div>
    @else
        <div class="materi-list-foto2">
            @foreach($materi as $idx => $item)
                <div class="materi-row-foto2" data-reveal>
                    <div class="materi-main-info">
                        <div class="materi-num-badge">
                            {{ str_pad(($materi->firstItem() ?? 1) + $idx, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="materi-text-wrap">
                            <h3 class="materi-row-title">{{ $item->judul }}</h3>
                            <p class="materi-row-subtitle">
                                @if($item->bab)
                                    <strong>{{ $item->bab }}</strong> &bull;
                                @endif
                                Kelas {{ $item->kelasTerkait->pluck('nama_kelas')->join(', ') ?: '-' }}
                                @if($item->deskripsi)
                                    &bull; {{ Str::limit($item->deskripsi, 90) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="materi-actions">
                        <a href="{{ route('materi.show', $item->id_materi) }}" class="btn btn-secondary btn-small">
                            Lihat Dokumen
                        </a>
                        @unless($item->kuis->contains('kategori', 'post-test'))
                            <a href="{{ route('kuis.create', ['mapel' => $item->id_mapel, 'materi' => $item->id_materi, 'kategori' => 'post-test']) }}" class="btn btn-secondary btn-small">
                                Buat Post-test
                            </a>
                        @endunless
                        @unless($item->kuis->contains('kategori', 'pre-test'))
                            <a href="{{ route('kuis.create', ['mapel' => $item->id_mapel, 'materi' => $item->id_materi, 'kategori' => 'pre-test']) }}" class="btn btn-secondary btn-small">
                                Buat Pre-test
                            </a>
                        @endunless
                        <a href="{{ route('materi.edit', $item->id_materi) }}" class="btn btn-secondary btn-small">
                            Edit
                        </a>
                        <a href="{{ route('materi.konfirmasi-arsip', $item->id_materi) }}" class="btn btn-danger btn-small">
                            Arsip
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        @include('admin.partials.pagination', ['paginator' => $materi])
    @endif

@endif

@endsection
