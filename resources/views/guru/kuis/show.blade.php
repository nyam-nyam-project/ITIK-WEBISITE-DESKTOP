@extends('layouts.guru')
@section('title', 'Detail Kuis - ' . $kuis->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/kuis/show.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('kuis.index', ['mapel' => $kuis->id_mapel]) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>{{ $kuis->judul }}</h1>
        <p>Detail kuis, jadwal, daftar butir soal, dan publikasi.</p>
    </div>
    <div class="u-display-flex u-gap-10px">
        @if ($kuis->status_publikasi === 'terbit')
            <a href="{{ route('kuis.edit', $kuis->id_kuis) }}" class="btn btn-secondary" data-alert="Kuis tersebut tidak dapat diedit karena berstatus terbit. Ubah ke status draft terlebih dahulu.">
                ✎ Edit Kuis
            </a>
        @else
            <a href="{{ route('kuis.edit', $kuis->id_kuis) }}" class="btn btn-secondary">
                ✎ Edit Kuis
            </a>
        @endif
        <a href="{{ route('kuis.soal.index', $kuis->id_kuis) }}" class="btn btn-primary">
            ⚙ Kelola Bank Soal
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger u-background-fde8e8 u-border-1px-solid-f8b4b4 u-color-9b1c1c u-padding-14px-18px u-border-radius-12px u-margin-bottom-20px">
        <strong>Kuis belum dapat dipublikasikan:</strong>
        <ul class="u-margin-8px-0-0-20px">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- STATUS PUBLIKASI BANNER -->
@if ($kuis->status_publikasi === 'draft' && $kuis->is_aktif)
    <div class="card u-background-fffdf5 u-border-color-fde8a0 u-margin-bottom-24px u-padding-18px-24px u-display-flex u-align-items-center u-justify-content-space-between u-gap-20px u-flex-wrap-wrap">
        <div>
            <strong class="u-color-946200 u-font-size-16px u-display-block u-margin-bottom-4px">Kuis masih dalam status DRAFT</strong>
            <p class="muted small u-margin-0">Publikasikan kuis agar siswa dapat melihat dan mengerjakan kuis ini sesuai jadwal.</p>
        </div>
        <form action="{{ route('kuis.publish', $kuis->id_kuis) }}" method="POST" data-confirm="Yakin ingin mempublikasikan kuis ini?">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-primary u-background-10b981 u-border-color-10b981">
                ✓ Publikasikan Kuis
            </button>
        </form>
    </div>
@elseif ($kuis->status_publikasi === 'terbit' && $kuis->is_aktif)
    <div class="card u-background-f0fdf4 u-border-color-bbf7d0 u-margin-bottom-24px u-padding-18px-24px u-display-flex u-align-items-center u-justify-content-space-between u-gap-20px u-flex-wrap-wrap">
        <div>
            <strong class="u-color-166534 u-font-size-16px u-display-block u-margin-bottom-4px">🟢 Kuis AKTIF / TERBIT</strong>
            <p class="muted small u-margin-0">Kuis ini dapat dikerjakan siswa. Tarik kuis kembali ke draft jika ingin menghentikan pengerjaan sementara atau mengubah butir soal.</p>
        </div>
        <form action="{{ route('kuis.unpublish', $kuis->id_kuis) }}" method="POST" data-confirm="Yakin ingin menarik kuis ini kembali ke draft? Siswa tidak akan bisa mengerjakan kuis ini.">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-secondary u-color-b91c1c u-border-color-fca5a5">
                ✕ Tarik ke Draft
            </button>
        </form>
    </div>
@endif

<!-- METADATA GRID -->
<section class="card u-margin-bottom-24px">
    <div class="card-header">
        <div>
            <h2>Informasi Kuis</h2>
            <p class="muted">Ringkasan konfigurasi dan parameter pengerjaan.</p>
        </div>
        <div>
            @if ($kuis->status_publikasi === 'draft')
                <span class="badge badge-warning">Draft</span>
            @else
                <span class="badge badge-success">Terbit</span>
            @endif
        </div>
    </div>

    <div class="meta-box">
        <div class="meta-item">
            <div class="meta-label">Mata Pelajaran</div>
            <div class="meta-val">{{ $kuis->mapel?->nama_mapel ?? '-' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Kategori</div>
            <div class="meta-val u-text-transform-capitalize">{{ $kuis->kategori }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Tingkat Kesulitan</div>
            <div class="meta-val">
                <span class="badge {{ $kuis->tingkat_kesulitan === 'sulit' ? 'badge-danger' : ($kuis->tingkat_kesulitan === 'sedang' ? 'badge-warning' : 'badge-info') }}">
                    {{ ucfirst($kuis->tingkat_kesulitan ?? 'mudah') }}
                </span>
            </div>
        </div>
        <div class="meta-item">
            <div class="meta-label">KKM</div>
            <div class="meta-val">{{ $kuis->kkm ?? '-' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Alokasi Waktu</div>
            <div class="meta-val">{{ $kuis->alokasi_waktu ? $kuis->alokasi_waktu . ' Menit' : 'Tidak Dibatasi' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Tahun Ajaran</div>
            <div class="meta-val">{{ $kuis->tahunAjaran?->nama ?? '-' }}</div>
        </div>
        @if ($kuis->waktu_mulai || $kuis->waktu_selesai)
            <div class="meta-item">
                <div class="meta-label">Waktu Mulai</div>
                <div class="meta-val">{{ $kuis->waktu_mulai?->format('d M Y, H:i') ?? '-' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Waktu Selesai</div>
                <div class="meta-val">{{ $kuis->waktu_selesai?->format('d M Y, H:i') ?? '-' }}</div>
            </div>
        @endif
    </div>

    @if ($kuis->deskripsi)
        <div class="u-background-fbfafd u-border-radius-12px u-padding-14px-18px u-border-1px-solid-ebe6f7">
            <div class="meta-label">Deskripsi / Petunjuk</div>
            <div class="u-font-size-14px u-line-height-1p6 u-color-373356">{!! nl2br(e($kuis->deskripsi)) !!}</div>
        </div>
    @endif
</section>

<div class="grid grid-2">
    <!-- MATERI TERKAIT -->
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Materi Terkait</h2>
                <p class="muted">Bab pembelajaran yang dicakup oleh kuis ini.</p>
            </div>
        </div>

        <div class="u-display-flex u-flex-direction-column u-gap-10px">
            @forelse ($kuis->materi as $materi)
                <div class="u-display-flex u-align-items-center u-gap-12px u-padding-12px-14px u-background-fbfafd u-border-radius-10px u-border-1px-solid-f0eff6">
                    <div class="u-width-28px u-height-28px u-border-radius-8px u-background-ede9fc u-color-5542dc u-display-flex u-align-items-center u-justify-content-center u-font-weight-700 u-font-size-13px">
                        📖
                    </div>
                    <div>
                        <strong class="u-font-size-14px u-color-202044">{{ $materi->judul }}</strong>
                        @if ($materi->bab)
                            <span class="muted small u-display-block">Bab: {{ $materi->bab }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state u-padding-24px-16px">
                    <p class="muted">Tidak ada materi yang ditautkan ke kuis ini.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- DAFTAR SOAL -->
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Butir Soal ({{ $kuis->soal->count() }})</h2>
                <p class="muted">Pratinjau butir pertanyaan dalam kuis ini.</p>
            </div>
            <a href="{{ route('kuis.soal.index', $kuis->id_kuis) }}" class="btn btn-secondary small">
                Buka Bank Soal →
            </a>
        </div>

        <div class="u-max-height-400px u-overflow-y-auto">
            @forelse ($kuis->soal as $index => $soal)
                <div class="soal-item">
                    <div class="soal-num">{{ $index + 1 }}</div>
                    <div class="u-flex-1 u-font-size-14px u-line-height-1p5 u-color-202044">
                        {{ \Illuminate\Support\Str::limit($soal->pertanyaan, 110) }}
                        <div class="u-margin-top-4px">
                            <span class="badge badge-info u-font-size-11px">Bobot: {{ $soal->bobot }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state u-padding-30px-16px u-text-align-center">
                    <p class="muted u-margin-bottom-12px">Kuis ini belum memiliki butir soal.</p>
                    <a href="{{ route('kuis.soal.create', $kuis->id_kuis) }}" class="btn btn-primary small">
                        + Tambah Soal Pertama
                    </a>
                </div>
            @endforelse
        </div>
    </section>
</div>

@endsection
