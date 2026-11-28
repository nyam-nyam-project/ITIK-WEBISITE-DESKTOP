@extends('layouts.siswa')
@section('title', 'Hasil Kuis - ' . $kuis->judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/siswa/kuis/hasil.css') }}">
@endpush

@section('content')
@php
    $lulus = $lulus ?? ($hasil->nilai !== null && $kuis->kkm !== null && (float) $hasil->nilai >= (float) $kuis->kkm);
    $menungguPenilaian = $hasil->status_validasi === 'menunggu penilaian';
@endphp

<div class="page-heading">
    <div>
        <h1>Hasil Kuis</h1>
        <p>{{ $kuis->judul }}</p>
    </div>
    <div class="actions">
        @php
            $katKuis = strtolower(trim($kuis->kategori ?? ''));
            $bolehUlang = $kuis->bolehDiulang() && in_array($katKuis, ['kuis harian', 'pre-test', 'post-test']);
        @endphp

        @if($katKuis === 'pre-test' && isset($materiTerkait))
            <a href="{{ route('siswa.materi.show', $materiTerkait->id_materi) }}" class="btn">
                Buka Materi Pembelajaran @include('partials.icon', ['name' => 'arrow-right'])
            </a>
        @endif
        @if(!$menungguPenilaian && !$lulus && $bolehUlang)
            <a href="{{ route('siswa.kuis.mulai', $kuis->id_kuis) }}" class="btn btn-warning">
                @include('partials.icon', ['name' => 'rotate-ccw']) Kerjakan Ulang Kuis
            </a>
        @elseif(!$menungguPenilaian && !$lulus && $katKuis === 'ulangan')
            <a href="{{ route('siswa.remedial.index') }}" class="btn btn-warning">
                Lihat Tugas Remedial
            </a>
        @endif
        <x-tombol-kembali :fallback="route('siswa.kuis.index')" />
    </div>
</div>

<div class="grid grid-2">
    <!-- KIRI: KARTU NILAI -->
    <section class="card score-card">
        <span class="muted small">Nilai Akhir Kamu</span>
        <div class="score-number {{ $menungguPenilaian ? 'pending' : ($lulus ? '' : 'gagal') }}">
            {{ $menungguPenilaian ? '—' : $hasil->nilai }}
        </div>

        <div>
            @if($menungguPenilaian)
                <span class="badge">Menunggu penilaian</span>
                <p class="essay-review-note">Nilai akhir tersedia setelah seluruh essay dinilai oleh guru.</p>
            @elseif($lulus)
                <span class="badge badge-success">@include('partials.icon', ['name' => 'check']) Lulus (≥ KKM {{ $kuis->kkm ?? '-' }})</span>
            @else
                <span class="badge badge-warning">@include('partials.icon', ['name' => 'alert-triangle']) Tidak Lulus (&lt; KKM {{ $kuis->kkm ?? '-' }})</span>
            @endif
        </div>

        @if($tampilDetail ?? false)
        <div class="eval-stats">
            <div class="eval-stat-box eval-stat-box--benar">
                <div class="eval-stat-num eval-stat-num--benar">{{ $totalBenar ?? 0 }}</div>
                <div class="eval-stat-label">Jawaban Benar</div>
            </div>
            <div class="eval-stat-box eval-stat-box--salah">
                <div class="eval-stat-num eval-stat-num--salah">{{ $totalSalah ?? 0 }}</div>
                <div class="eval-stat-label">Jawaban Salah</div>
            </div>
            <div class="eval-stat-box eval-stat-box--total">
                <div class="eval-stat-num eval-stat-num--total">{{ count($soal ?? []) }}</div>
                <div class="eval-stat-label">Total Butir Soal</div>
            </div>
        </div>
        @endif
    </section>

    <!-- KANAN: DETAIL KUIS -->
    <section class="card">
        <div class="card-header">
            <h2>Detail Pengerjaan</h2>
            <span class="badge">{{ ucfirst($kuis->kategori) }}</span>
        </div>

        <dl class="identity">
            <div>
                <dt>Judul Kuis</dt>
                <dd>{{ $kuis->judul }}</dd>
            </div>
            <div>
                <dt>KKM</dt>
                <dd>{{ $kuis->kkm ?? '-' }}</dd>
            </div>
            <div>
                <dt>Percobaan Ke</dt>
                <dd>{{ $hasil->percobaan_ke }}</dd>
            </div>
            <div>
                <dt>Waktu Pengerjaan</dt>
                <dd>{{ $durasiTampil ?? 'Belum tercatat' }}</dd>
            </div>
        </dl>
    </section>
</div>

<!-- EVALUASI SOAL BENAR & SALAH -->
@if($tampilDetail ?? false)
    @if(isset($soal) && $soal->isNotEmpty())
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Evaluasi Soal</h2>
                <p class="muted">Pelajari butir soal untuk mengevaluasi pemahaman materi Anda.</p>
            </div>
        </div>

        @foreach($soal as $itemSoal)
            @if($itemSoal->tipe === 'essay')
                <div class="soal-card essay-review" data-essay-review="{{ $itemSoal->id_soal }}">
                    <div class="soal-card-header">
                        <div class="soal-card-title">Soal #{{ $loop->iteration }}</div>
                        <span class="badge">Essay</span>
                    </div>
                    <div class="soal-pertanyaan">
                        <div>{{ $itemSoal->pertanyaan }}</div>
                        @if ($itemSoal->gambar)
                            <div class="soal-gambar-wrap">
                                <img src="{{ asset('storage/' . $itemSoal->gambar) }}" alt="{{ Str::limit(strip_tags($itemSoal->pertanyaan), 60) }}" class="soal-gambar">
                            </div>
                        @endif
                    </div>
                    <p class="essay-review-note">Soal essay dinilai oleh guru berdasarkan bobot {{ $itemSoal->bobot }}.</p>
                </div>
                @continue
            @endif
            @php
                $jawabanDipilih = $draftJawaban->get($itemSoal->id_soal)?->jawaban_dipilih;
                $kunciBenar = $itemSoal->jawaban ? strtoupper($itemSoal->jawaban) : null;
                $isBenar = $jawabanDipilih && $kunciBenar && strtoupper($jawabanDipilih) === $kunciBenar;
                $opsiMap = [
                    'A' => $itemSoal->opsi_a,
                    'B' => $itemSoal->opsi_b,
                    'C' => $itemSoal->opsi_c,
                    'D' => $itemSoal->opsi_d,
                    'E' => $itemSoal->opsi_e,
                ];
            @endphp

            <div class="soal-card {{ $isBenar ? 'benar' : 'salah' }}">
                <div class="soal-card-header">
                    <div class="soal-card-title">
                        Soal #{{ $loop->iteration }}
                        <span class="muted small u-margin-left-6px">(Bobot: {{ $itemSoal->bobot ?? 0 }}%)</span>
                    </div>

                    @if($isBenar)
                        <span class="badge badge-success">@include('partials.icon', ['name' => 'check']) Jawaban Benar (+{{ $itemSoal->bobot ?? 0 }}%)</span>
                    @elseif($jawabanDipilih)
                        <span class="badge badge-warning">@include('partials.icon', ['name' => 'x']) Jawaban Salah (Kamu pilih: {{ strtoupper($jawabanDipilih) }})</span>
                    @else
                        <span class="badge badge-muted">@include('partials.icon', ['name' => 'info']) Tidak Dijawab</span>
                    @endif
                </div>

                <div class="soal-pertanyaan">
                    <div>{{ $itemSoal->pertanyaan }}</div>
                    @if ($itemSoal->gambar)
                        <div class="soal-gambar-wrap">
                            <img src="{{ asset('storage/' . $itemSoal->gambar) }}" alt="{{ Str::limit(strip_tags($itemSoal->pertanyaan), 60) }}" class="soal-gambar">
                        </div>
                    @endif
                </div>

                <div class="opsi-review-list">
                    @foreach($opsiMap as $huruf => $teksOpsi)
                        @if($teksOpsi)
                            @php
                                $isKunci = $kunciBenar === $huruf;
                                $isDipilihSiswa = strtoupper($jawabanDipilih ?? '') === $huruf;
                                $itemClass = '';
                                if ($isKunci) {
                                    $itemClass = 'kunci';
                                } elseif ($isDipilihSiswa && !$isBenar) {
                                    $itemClass = 'jawaban-salah';
                                }
                            @endphp

                            <div class="opsi-review-item {{ $itemClass }}">
                                <div class="opsi-label">{{ $huruf }}.</div>
                                <div class="u-flex-1">{{ $teksOpsi }}</div>

                                @if($isKunci && $isDipilihSiswa)
                                    <span class="opsi-status-text kunci">@include('partials.icon', ['name' => 'check']) Pilihan Kamu (Benar)</span>
                                @elseif($isKunci)
                                    <span class="opsi-status-text kunci">@include('partials.icon', ['name' => 'check']) Kunci Jawaban</span>
                                @elseif($isDipilihSiswa)
                                    <span class="opsi-status-text jawaban-kamu">@include('partials.icon', ['name' => 'x']) Pilihan Kamu (Salah)</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
    @endif
@else
    {{-- Evaluasi tidak ditampilkan sesuai pengaturan kuis --}}
    <section class="card">
        <p class="muted">Evaluasi per soal tidak tersedia untuk kuis ini.</p>
    </section>
@endif

@endsection
