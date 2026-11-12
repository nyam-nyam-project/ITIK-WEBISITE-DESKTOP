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
        @if($tanpaKelas ?? false)
            <div class="empty">
                <h3>Kamu Belum Terdaftar di Kelas</h3>
                <p>Hubungi admin atau guru untuk didaftarkan ke kelas agar dapat mengakses materi pembelajaran.</p>
            </div>
        @else
            <div class="empty">
                <h3>Belum Ada Mata Pelajaran Aktif</h3>
                <p>Materi pembelajaran akan tersedia setelah ditambahkan oleh guru.</p>
            </div>
        @endif
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
                    $preTest = $item->relationLoaded('kuis')
                        ? $item->kuis->first(fn($k) => strtolower(trim($k->kategori ?? '')) === 'pre-test')
                        : null;
                    $postTest = $item->relationLoaded('kuis')
                        ? $item->kuis->first(fn($k) => strtolower(trim($k->kategori ?? '')) === 'post-test')
                        : null;

                    $preTestSelesai = $preTest && isset($kuisSelesaiIds[$preTest->id_kuis]);
                    $materiSelesai = $prog && $prog->status === 'selesai';
                    $postTestSelesai = $postTest && isset($kuisSelesaiIds[$postTest->id_kuis]);

                    $isMateriTerkunci = (bool) ($preTest && !$preTestSelesai);
                    $isPostTestTerkunci = (bool) ($postTest && !$materiSelesai);
                @endphp
                <div class="materi-card-link">
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
                            <div class="materi-badge-group">
                                @if($preTest)
                                    @if($preTestSelesai)
                                        <span class="badge badge-success">✓ Pre-test Selesai</span>
                                    @else
                                        <span class="badge badge-warning">Pre-test Wajib</span>
                                    @endif
                                @endif

                                @if($isMateriTerkunci)
                                    <span class="badge badge-muted">🔒 Materi Terkunci</span>
                                @elseif(!$prog)
                                    <span class="badge badge-muted">Belum Mulai</span>
                                @elseif($prog->status === 'sedang belajar')
                                    <span class="badge badge-warning">Sedang Belajar</span>
                                @elseif($materiSelesai)
                                    <span class="badge badge-success">✓ Materi Selesai</span>
                                @else
                                    <span class="badge">{{ $prog->status }}</span>
                                @endif

                                @if($postTest)
                                    @if($postTestSelesai)
                                        <span class="badge badge-success">✓ Post-test Selesai</span>
                                    @elseif($isPostTestTerkunci)
                                        <span class="badge badge-muted">🔒 Post-test Terkunci</span>
                                    @else
                                        <span class="badge badge-warning">Post-test Siap</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="materi-side-info">
                        @if($isMateriTerkunci)
                            <a href="{{ route('siswa.kuis.mulai', $preTest->id_kuis) }}" class="btn btn-small">
                                Mulai Pre-test
                            </a>
                            <button type="button" class="btn btn-secondary btn-small" disabled title="Selesaikan Pre-test terlebih dahulu">
                                🔒 Baca Materi
                            </button>
                        @else
                            <a href="{{ route('siswa.materi.show', $item->id_materi) }}" class="btn btn-secondary btn-small">
                                Baca Materi
                            </a>

                            @if($postTest)
                                @if($postTestSelesai)
                                    <a href="{{ route('siswa.kuis.hasil', $postTest->id_kuis) }}" class="btn btn-secondary btn-small">
                                        Hasil Post-test
                                    </a>
                                @elseif($isPostTestTerkunci)
                                    <button type="button" class="btn btn-secondary btn-small" disabled title="Selesaikan materi terlebih dahulu">
                                        🔒 Post-test
                                    </button>
                                @else
                                    <a href="{{ route('siswa.kuis.mulai', $postTest->id_kuis) }}" class="btn btn-small">
                                        Kerjakan Post-test
                                    </a>
                                @endif
                            @endif
                        @endif

                        <a href="{{ route('siswa.kuis.index', ['mapel' => $item->id_mapel, 'materi' => $item->id_materi]) }}" class="btn btn-secondary btn-small">
                            Kuis Materi
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endif

@endsection
