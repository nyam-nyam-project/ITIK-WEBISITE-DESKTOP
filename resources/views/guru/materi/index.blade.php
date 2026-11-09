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
                <a href="{{ route('materi.index', ['mapel' => $mp->id_mapel]) }}" class="mapel-card">
                    <div class="mapel-icon">📚</div>
                    <div>
                        <div class="mapel-code">{{ $mp->kode_mapel }}</div>
                        <h3 class="mapel-title">{{ $mp->nama_mapel }}</h3>
                        <div class="mapel-count">{{ $mp->materi_count ?? 0 }} Materi Pembelajaran</div>
                    </div>
                    <div class="mapel-arrow">→</div>
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
        <span class="sep">›</span>
        <span class="current">{{ $mapelTerpilih->nama_mapel }}</span>
    </div>

    <div class="page-heading">
        <div>
            <a href="{{ route('materi.index') }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
                ← Kembali
            </a>
            <h1>{{ $mapelTerpilih->nama_mapel }}</h1>
            <p>{{ $materi->count() }} materi pembelajaran tersedia untuk mata pelajaran ini.</p>
        </div>
        <a href="{{ route('materi.create', ['mapel' => $mapelTerpilih->id_mapel]) }}" class="btn">
            + Tambah Materi
        </a>
    </div>

    @if($materi->isEmpty())
        <div class="empty">
            <h3>Belum Ada Materi untuk Mata Pelajaran Ini</h3>
            <p>Silakan tambahkan materi pembelajaran pertama untuk siswa.</p>
            <a href="{{ route('materi.create', ['mapel' => $mapelTerpilih->id_mapel]) }}" class="btn u-margin-top-14px">
                + Tambah Materi Pertama
            </a>
        </div>
    @else
        <div class="materi-list-foto2">
            @foreach($materi as $idx => $item)
                <div class="materi-row-foto2">
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
                                    &bull; {{ Str::limit($item->deskripsi, 90) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="materi-actions">
                        <a href="{{ route('materi.show', $item->id_materi) }}" class="btn btn-secondary btn-small">
                            Lihat Dokumen
                        </a>
                        <a href="{{ route('kuis.create', ['mapel' => $item->id_mapel, 'materi' => $item->id_materi]) }}" class="btn btn-secondary btn-small">
                            Buat Kuis
                        </a>
                        <a href="{{ route('materi.edit', $item->id_materi) }}" class="btn btn-secondary btn-small">
                            Edit
                        </a>
                        <form class="u-display-inline"
                            action="{{ route('materi.destroy', $item->id_materi) }}"
                            method="POST"

                            data-confirm="Yakin ingin menghapus materi ini?"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-small">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endif

@endsection
