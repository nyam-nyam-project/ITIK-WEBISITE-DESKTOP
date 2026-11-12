@extends('layouts.guru')
@section('title', 'Bank Soal - ' . $kuis->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/soal/index.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('kuis.show', $kuis->id_kuis) }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>Kelola Bank Soal</h1>
        <p>Kuis: <strong>{{ $kuis->judul }}</strong> &bull; Total: {{ $soal->count() }} Soal</p>
    </div>
    <div class="u-display-flex u-gap-10px">
        <a href="{{ route('kuis.soal.create', $kuis->id_kuis) }}" class="btn btn-primary">
            + Tambah Soal
        </a>
    </div>
</div>

@if ($soal->count() > 0)
    @foreach ($soal as $index => $item)
        <div class="soal-card-item">
            <div class="soal-head">
                <div class="u-display-flex u-gap-14px u-align-items-flex-start">
                    <div class="soal-badge-num">{{ $index + 1 }}</div>
                    <div>
                        <div class="u-font-size-15px u-font-weight-600 u-color-202044 u-line-height-1p6">
                            {{ $item->pertanyaan }}
                        </div>
                        <div class="u-margin-top-8px u-display-flex u-gap-8px">
                            <span class="badge badge-info">Bobot: {{ $item->bobot ?? 1 }}</span>
                            @if ($item->tingkat_kesulitan)
                                <span class="badge {{ $item->tingkat_kesulitan === 'sulit' ? 'badge-danger' : ($item->tingkat_kesulitan === 'sedang' ? 'badge-warning' : 'badge-info') }}">
                                    {{ ucfirst($item->tingkat_kesulitan) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="u-display-flex u-gap-6px u-flex-shrink-0">
                    <a href="{{ route('kuis.soal.show', [$kuis->id_kuis, $item->id_soal]) }}" class="btn btn-secondary small">
                        Lihat
                    </a>
                    <a href="{{ route('kuis.soal.edit', [$kuis->id_kuis, $item->id_soal]) }}" class="btn btn-secondary small">
                        Edit
                    </a>
                    <form class="u-display-inline"
                        action="{{ route('kuis.soal.destroy', [$kuis->id_kuis, $item->id_soal]) }}"
                        method="POST"
                        data-confirm="Yakin ingin menghapus butir soal ini?"

                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary small u-color-b91c1c u-border-color-fca5a5">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>

            <!-- PILIHAN JAWABAN -->
            <div class="options-grid">
                <div class="option-box {{ $item->jawaban === 'A' ? 'is-correct' : '' }}">
                    <strong>A.</strong> {{ $item->opsi_a }}
                    @if ($item->jawaban === 'A') <span class="u-float-right">✓ Benar</span> @endif
                </div>
                <div class="option-box {{ $item->jawaban === 'B' ? 'is-correct' : '' }}">
                    <strong>B.</strong> {{ $item->opsi_b }}
                    @if ($item->jawaban === 'B') <span class="u-float-right">✓ Benar</span> @endif
                </div>
                <div class="option-box {{ $item->jawaban === 'C' ? 'is-correct' : '' }}">
                    <strong>C.</strong> {{ $item->opsi_c }}
                    @if ($item->jawaban === 'C') <span class="u-float-right">✓ Benar</span> @endif
                </div>
                <div class="option-box {{ $item->jawaban === 'D' ? 'is-correct' : '' }}">
                    <strong>D.</strong> {{ $item->opsi_d }}
                    @if ($item->jawaban === 'D') <span class="u-float-right">✓ Benar</span> @endif
                </div>
                @if ($item->opsi_e)
                    <div class="option-box {{ $item->jawaban === 'E' ? 'is-correct' : '' }}">
                        <strong>E.</strong> {{ $item->opsi_e }}
                        @if ($item->jawaban === 'E') <span class="u-float-right">✓ Benar</span> @endif
                    </div>
                @endif
            </div>

            @if ($item->pembahasan)
                <div class="u-background-fbfafd u-border-radius-8px u-padding-10px-14px u-font-size-13px u-color-575279 u-border-left-3px-solid-5542dc">
                    <strong>Pembahasan:</strong> {{ $item->pembahasan }}
                </div>
            @endif
        </div>
    @endforeach
@else
    <div class="card empty-state u-text-align-center u-padding-48px-20px">
        <div class="u-font-size-40px u-margin-bottom-12px">📝</div>
        <h3 class="u-margin-bottom-6px">Belum Ada Butir Soal</h3>
        <p class="muted u-margin-bottom-20px">Kuis ini belum memiliki soal. Klik tombol di bawah untuk menambahkan soal pilihan ganda.</p>
        <a href="{{ route('kuis.soal.create', $kuis->id_kuis) }}" class="btn btn-primary">
            + Tambah Soal Pertama
        </a>
    </div>
@endif

@endsection
