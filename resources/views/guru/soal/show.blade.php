@extends('layouts.guru')
@section('title', 'Detail Soal - ' . $kuis->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/soal/show.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('kuis.soal.index', $kuis->id_kuis) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-6px">
            ← Kembali
        </a>
        <h1>Detail Butir Soal</h1>
        <p>Kuis: <strong>{{ $kuis->judul }}</strong></p>
    </div>
    <div class="u-display-flex u-gap-10px">
        <a href="{{ route('kuis.soal.edit', [$kuis->id_kuis, $soal->id_soal]) }}" class="btn btn-primary">
            ✎ Edit Soal
        </a>
    </div>
</div>

<section class="card u-margin-bottom-24px">
    <div class="card-header">
        <div>
            <h2>Pertanyaan & Kunci Jawaban</h2>
            <p class="muted">Tinjau isi pertanyaan dan konfigurasi kunci jawaban.</p>
        </div>
        <div class="u-display-flex u-gap-8px">
            <span class="badge badge-info">Bobot: {{ $soal->bobot ? number_format($soal->bobot, 2) . '%' : 'Otomatis' }}</span>
            @if ($soal->tingkat_kesulitan)
                <span class="badge {{ $soal->tingkat_kesulitan === 'sulit' ? 'badge-danger' : ($soal->tingkat_kesulitan === 'sedang' ? 'badge-warning' : 'badge-info') }}">
                    {{ ucfirst($soal->tingkat_kesulitan) }}
                </span>
            @endif
        </div>
    </div>

    <div class="soal-display">
        <strong>Soal:</strong>
        <p class="u-margin-top-8px u-white-space-pre-line">{{ $soal->pertanyaan }}</p>
    </div>

    <h3 class="u-font-size-15px u-margin-bottom-12px u-color-202044">Alternatif Pilihan Jawaban:</h3>

    @foreach (['A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c, 'D' => $soal->opsi_d, 'E' => $soal->opsi_e] as $huruf => $opsi)
        @if ($opsi !== null && $opsi !== '')
            <div class="opsi-box-item {{ $soal->jawaban === $huruf ? 'is-correct' : '' }}">
                <div class="opsi-circle">{{ $huruf }}</div>
                <div class="u-flex-1 u-font-size-15px u-color-202044">
                    {{ $opsi }}
                </div>
                @if ($soal->jawaban === $huruf)
                    <span class="badge badge-success u-font-size-12px">✓ Kunci Benar</span>
                @endif
            </div>
        @endif
    @endforeach

    @if ($soal->pembahasan)
        <div class="u-background-fbfafd u-border-radius-12px u-padding-16px-20px u-border-left-4px-solid-5542dc u-margin-top-20px">
            <strong class="u-color-202044 u-display-block u-margin-bottom-6px">Pembahasan:</strong>
            <p class="u-margin-0 u-font-size-14px u-color-575279 u-line-height-1p6">{{ $soal->pembahasan }}</p>
        </div>
    @endif
</section>

@endsection
