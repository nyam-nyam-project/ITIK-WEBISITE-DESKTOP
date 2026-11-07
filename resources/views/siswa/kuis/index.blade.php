@extends('layouts.siswa')
@section('title', 'Daftar Kuis')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/siswa/kuis/index.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <h1>Daftar Kuis</h1>
        <p>Pilih kuis pembelajaran yang tersedia dan selesaikan tantangannya.</p>
    </div>
    <a href="{{ route('siswa.nilai.index') }}" class="btn btn-secondary">
        Lihat Nilai Saya →
    </a>
</div>

@if (!$mapelTerpilih)
    {{-- PILIH MATA PELAJARAN --}}
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Pilih Mata Pelajaran</h2>
                <p class="muted">Pilih mata pelajaran untuk melihat kuis yang tersedia</p>
            </div>
        </div>

        @if ($mapelAktif->isEmpty())
            <div class="empty">
                <h3>Belum ada mata pelajaran</h3>
                <p>Belum ada kuis yang tersedia saat ini.</p>
            </div>
        @else
            <div class="mapel-grid-siswa">
                @foreach ($mapelAktif as $mapel)
                    <a href="{{ url('/siswa/kuis?mapel=' . $mapel->id_mapel) }}" class="mapel-card-s">
                        <div>
                            <div class="mapel-icon-s">📖</div>
                            <div class="mapel-nama">{{ $mapel->nama_mapel }}</div>
                            <span class="muted small">{{ $mapel->id_mapel }}</span>
                        </div>
                        <div class="mapel-count">
                            <span>Kuis Tersedia</span>
                            <span class="mapel-count-num">{{ $mapel->kuis_count }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@else
    {{-- BREADCRUMB --}}
    <div class="breadcrumb-siswa">
        <a href="{{ url('/siswa/kuis') }}">📚 Semua Mata Pelajaran</a>
        <span>›</span>
        @if ($materiTerpilih)
            <a href="{{ url('/siswa/kuis?mapel=' . $mapelTerpilih->id_mapel) }}">{{ $mapelTerpilih->nama_mapel }}</a>
            <span>›</span>
            <strong>{{ $materiTerpilih->bab ? $materiTerpilih->bab . ' - ' : '' }}{{ $materiTerpilih->judul }}</strong>
        @else
            <strong>{{ $mapelTerpilih->nama_mapel }}</strong>
        @endif
    </div>

    {{-- BANNER MAPEL --}}
    <div class="mapel-banner">
        <div class="mapel-banner-info">
            <div class="mapel-banner-icon">📖</div>
            <div>
                <h3>{{ $mapelTerpilih->nama_mapel }}</h3>
                <p>
                    @if ($materiTerpilih)
                        Bab: {{ $materiTerpilih->bab ? $materiTerpilih->bab . ' - ' : '' }}{{ $materiTerpilih->judul }}
                    @else
                        {{ $daftarMateri->count() }} Bab Materi &bull; {{ $kuis->count() }} Kuis tersedia
                    @endif
                </p>
            </div>
        </div>

        @if ($materiTerpilih)
            <a href="{{ url('/siswa/kuis?mapel=' . $mapelTerpilih->id_mapel) }}" class="btn-back-mapel">
                ← Kembali
            </a>
        @else
            <a href="{{ url('/siswa/kuis') }}" class="btn-back-mapel">
                ← Kembali
            </a>
        @endif
    </div>

    @if (!$materiTerpilih)
        {{-- LEVEL 1: PILIH BAB MATERI --}}
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Pilih Bab Materi</h2>
                    <p class="muted">Pilih materi yang ingin kamu pelajari dan kerjakan kuisnya (Mudah → Sedang → Sulit)</p>
                </div>
            </div>

            @if ($daftarMateri->isEmpty())
                <div class="empty">
                    <h3>Belum ada bab materi</h3>
                    <p>Materi pembelajaran untuk mata pelajaran ini belum tersedia.</p>
                </div>
            @else
                <div class="bab-grid-siswa">
                    @foreach ($daftarMateri as $materiItem)
                        @php
                            $kuisDiBab = $materiItem->kuis->where('status_publikasi', 'terbit')->where('is_aktif', true);
                            $hasMudah = $kuisDiBab->contains(fn ($k) => $k->tingkat_kesulitan === 'mudah');
                            $hasSedang = $kuisDiBab->contains(fn ($k) => $k->tingkat_kesulitan === 'sedang');
                            $hasSulit = $kuisDiBab->contains(fn ($k) => $k->tingkat_kesulitan === 'sulit');
                        @endphp

                        <a href="{{ url('/siswa/kuis?mapel=' . $mapelTerpilih->id_mapel . '&materi=' . $materiItem->id_materi) }}" class="bab-card-siswa">
                            <div>
                                <div class="bab-card-header">
                                    <div class="bab-card-icon">📖</div>
                                    <div>
                                        <span class="bab-card-tag">{{ $materiItem->bab ?? 'Materi' }}</span>
                                        <div class="bab-card-title">{{ $materiItem->judul }}</div>
                                    </div>
                                </div>

                                @if ($materiItem->deskripsi)
                                    <div class="bab-card-desc">
                                        {{ Str::limit($materiItem->deskripsi, 90) }}
                                    </div>
                                @endif

                                <div class="u-display-flex u-gap-6px u-flex-wrap-wrap u-margin-top-10px">
                                    <span class="badge {{ $hasMudah ? 'badge-success' : 'badge-muted' }}">
                                        {{ $hasMudah ? '✓' : '○' }} Mudah
                                    </span>
                                    <span class="badge {{ $hasSedang ? 'badge-warning' : 'badge-muted' }}">
                                        {{ $hasSedang ? '✓' : '○' }} Sedang
                                    </span>
                                    <span class="badge {{ $hasSulit ? 'badge-danger' : 'badge-muted' }}">
                                        {{ $hasSulit ? '✓' : '○' }} Sulit
                                    </span>
                                </div>
                            </div>

                            <div class="bab-card-footer">
                                <span>{{ $kuisDiBab->count() }} Kuis Tersedia</span>
                                <span>Buka Bab Ini →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- KUIS UMUM / ULANGAN --}}
        @php
            $kuisTanpaBab = $kuis->filter(fn ($k) => $k->materi->isEmpty() || strtolower(trim($k->kategori)) === 'ulangan');
        @endphp

        @if ($kuisTanpaBab->isNotEmpty())
            <section class="card">
                <div class="card-header">
                    <div>
                        <h2>📝 Ulangan &amp; Kuis Umum</h2>
                        <p class="muted">Kuis evaluasi umum untuk mata pelajaran {{ $mapelTerpilih->nama_mapel }}</p>
                    </div>
                </div>

                <div class="kuis-grid">
                    @foreach ($kuisTanpaBab as $item)
                        @php
                            $status = $statusKuis[$item->id_kuis] ?? [];
                            $terkunci = $status['terkunci'] ?? false;
                            $sudahPernah = $status['sudah_pernah'] ?? false;
                            $lulus = $status['lulus'] ?? false;
                            $nilaiTertinggi = $status['nilai_tertinggi'] ?? null;
                            $alasanKunci = $status['alasan_kunci'] ?? null;
                            $katItem = strtolower(trim($item->kategori ?? ''));
                            $bisaUlang = in_array($katItem, ['kuis harian', 'pre-test', 'pass-test']);
                        @endphp

                        <div class="kuis-item {{ $terkunci ? 'terkunci' : '' }}">
                            <div>
                                <h3>{{ $item->judul }}</h3>
                                <div class="badges-row">
                                    <span class="badge">{{ ucfirst($item->kategori) }}</span>
                                    <span class="badge badge-muted">{{ ucfirst($item->tingkat_kesulitan ?? 'mudah') }}</span>
                                    @if ($terkunci)
                                        <span class="badge badge-muted">🔒 Terkunci</span>
                                    @elseif ($lulus)
                                        <span class="badge badge-success">✓ Lulus</span>
                                    @elseif ($sudahPernah)
                                        <span class="badge badge-warning">Belum Lulus</span>
                                    @endif
                                </div>

                                <div class="kuis-meta">
                                    <div>
                                        <div class="meta-number">{{ $item->alokasi_waktu }}</div>
                                        <div class="meta-label">Menit</div>
                                    </div>
                                    <div>
                                        <div class="meta-number">{{ $item->kkm }}</div>
                                        <div class="meta-label">KKM</div>
                                    </div>
                                    <div>
                                        <div class="meta-number">{{ $item->soal()->count() }}</div>
                                        <div class="meta-label">Soal</div>
                                    </div>
                                </div>

                                @if ($nilaiTertinggi !== null)
                                    <p class="small muted u-margin-bottom-12px">
                                        Nilai terbaik kamu: <strong class="{{ $lulus ? 'u-color-166534' : 'u-color-92400e' }}">{{ number_format($nilaiTertinggi, 1) }}</strong>
                                    </p>
                                @endif
                            </div>

                            <div>
                                @if ($terkunci)
                                    <button class="btn btn-secondary u-width-100pct u-opacity-p6" disabled>🔒 Kuis Terkunci</button>
                                    <div class="kunci-info">ℹ️ {{ $alasanKunci }}</div>
                                @elseif ($bisaUlang && $sudahPernah && !$lulus)
                                    <form action="{{ route('siswa.kuis.mulai', $item->id_kuis) }}" method="GET">
                                        <button type="submit" class="btn btn-warning u-width-100pct">↻ Kerjakan Ulang</button>
                                    </form>
                                @elseif ($katItem === 'ulangan' && $sudahPernah && !$lulus)
                                    <div class="small u-color-991b1b u-font-weight-700 u-margin-bottom-8px">
                                        ⚠️ Nilai di bawah KKM
                                    </div>
                                    <a href="{{ route('siswa.remedial.index') }}" class="btn btn-warning u-width-100pct u-text-align-center">
                                        Cek Tugas Remedial
                                    </a>
                                @else
                                    <form action="{{ route('siswa.kuis.mulai', $item->id_kuis) }}" method="GET">
                                        <button type="submit" class="btn u-width-100pct">
                                            {{ $sudahPernah ? '→ Kerjakan Lagi' : 'Mulai' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    @else
        {{-- LEVEL 2: KUIS DI BAB TERPILIH --}}
        @php
            $kuisBabTerpilih = $kuis->filter(function ($k) use ($materiTerpilih) {
                return $k->materi->contains('id_materi', $materiTerpilih->id_materi);
            })->sortBy(function ($k) {
                return match($k->tingkat_kesulitan) {
                    'mudah' => 1,
                    'sedang' => 2,
                    'sulit' => 3,
                    default => 4
                };
            });
        @endphp

        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Kuis {{ $materiTerpilih->bab ? $materiTerpilih->bab . ': ' : '' }}{{ $materiTerpilih->judul }}</h2>
                    <p class="muted">Selesaikan kuis secara berurutan: <strong>Mudah</strong> → <strong>Sedang</strong> → <strong>Sulit</strong></p>
                </div>
                <a href="{{ url('/siswa/kuis?mapel=' . $mapelTerpilih->id_mapel) }}" class="btn btn-secondary btn-small">
                    ← Pilih Bab Lain
                </a>
            </div>

            @if ($kuisBabTerpilih->isEmpty())
                <div class="empty">
                    <h3>Belum ada kuis untuk bab ini</h3>
                    <p>Kuis untuk bab materi ini belum dipublikasikan oleh guru.</p>
                </div>
            @else
                <div class="kuis-grid">
                    @foreach ($kuisBabTerpilih as $item)
                        @php
                            $status = $statusKuis[$item->id_kuis] ?? [];
                            $terkunci = $status['terkunci'] ?? false;
                            $sudahPernah = $status['sudah_pernah'] ?? false;
                            $lulus = $status['lulus'] ?? false;
                            $nilaiTertinggi = $status['nilai_tertinggi'] ?? null;
                            $alasanKunci = $status['alasan_kunci'] ?? null;
                            $katBabItem = strtolower(trim($item->kategori ?? ''));
                            $bisaBabUlang = in_array($katBabItem, ['kuis harian', 'pre-test', 'pass-test']);
                        @endphp

                        <div class="kuis-item {{ $terkunci ? 'terkunci' : '' }}">
                            <div>
                                <h3>{{ $item->judul }}</h3>
                                <div class="badges-row">
                                    <span class="badge">{{ ucfirst($item->kategori) }}</span>
                                    <span class="badge badge-muted">Tingkat {{ ucfirst($item->tingkat_kesulitan ?? 'mudah') }}</span>
                                    @if ($terkunci)
                                        <span class="badge badge-muted">🔒 Terkunci</span>
                                    @elseif ($lulus)
                                        <span class="badge badge-success">✓ Lulus</span>
                                    @elseif ($sudahPernah)
                                        <span class="badge badge-warning">Belum Lulus</span>
                                    @endif
                                </div>

                                <div class="kuis-meta">
                                    <div>
                                        <div class="meta-number">{{ $item->alokasi_waktu }}</div>
                                        <div class="meta-label">Menit</div>
                                    </div>
                                    <div>
                                        <div class="meta-number">{{ $item->kkm }}</div>
                                        <div class="meta-label">KKM</div>
                                    </div>
                                    <div>
                                        <div class="meta-number">{{ $item->soal()->count() }}</div>
                                        <div class="meta-label">Soal</div>
                                    </div>
                                </div>

                                @if ($nilaiTertinggi !== null)
                                    <p class="small muted u-margin-bottom-12px">
                                        Nilai terbaik kamu: <strong class="{{ $lulus ? 'u-color-166534' : 'u-color-92400e' }}">{{ number_format($nilaiTertinggi, 1) }}</strong>
                                    </p>
                                @endif
                            </div>

                            <div>
                                @if ($terkunci)
                                    <button class="btn btn-secondary u-width-100pct u-opacity-p6" disabled>🔒 Kuis Terkunci</button>
                                    <div class="kunci-info">ℹ️ {{ $alasanKunci }}</div>
                                @elseif ($bisaBabUlang && $sudahPernah && !$lulus)
                                    <form action="{{ route('siswa.kuis.mulai', $item->id_kuis) }}" method="GET">
                                        <button type="submit" class="btn btn-warning u-width-100pct">↻ Kerjakan Ulang</button>
                                    </form>
                                @elseif ($katBabItem === 'ulangan' && $sudahPernah && !$lulus)
                                    <div class="small u-color-991b1b u-font-weight-700 u-margin-bottom-8px">
                                        ⚠️ Nilai di bawah KKM
                                    </div>
                                    <a href="{{ route('siswa.remedial.index') }}" class="btn btn-warning u-width-100pct u-text-align-center">
                                        Cek Tugas Remedial
                                    </a>
                                @else
                                    <form action="{{ route('siswa.kuis.mulai', $item->id_kuis) }}" method="GET">
                                        <button type="submit" class="btn u-width-100pct">
                                            {{ $sudahPernah ? '→ Kerjakan Lagi' : 'Mulai' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- KUIS REMEDIAL --}}
    @if ($remedial->isNotEmpty())
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Kuis Remedial</h2>
                    <p class="muted">Tugas perbaikan nilai untukmu</p>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="u-width-40px">No</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($remedial as $item)
                            <tr>
                                <td class="number u-text-align-left">{{ $loop->iteration }}</td>
                                <td><strong>{{ $item->kuisRemedial->judul }}</strong></td>
                                <td><span class="badge">{{ ucfirst($item->kuisRemedial->kategori) }}</span></td>
                                <td>
                                    <a href="{{ route('siswa.kuis.mulai', $item->kuisRemedial->id_kuis) }}" class="btn btn-small">
                                        Kerjakan Remedial →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endif

@endsection
