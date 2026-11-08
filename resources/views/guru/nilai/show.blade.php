@extends('layouts.guru')
@section('title', 'Detail Nilai Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/nilai/show.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <h1>Detail Nilai Siswa</h1>
        <p>Detail hasil pengerjaan kuis siswa dan evaluasi butir soal.</p>
    </div>
    <a href="{{ route('guru.nilai.index') }}" class="btn btn-secondary">
        ← Kembali
    </a>
</div>

<div class="grid grid-2">
    {{-- DATA SISWA --}}
    <section class="card">
        <div class="card-header">
            <h2>Data Siswa</h2>
        </div>
        <dl class="identity">
            <div>
                <dt>Nama Lengkap</dt>
                <dd>{{ $mengerjakan->siswa->nama_lengkap ?? '-' }}</dd>
            </div>
            <div>
                <dt>NISN</dt>
                <dd>{{ $mengerjakan->siswa->nomor_induk ?? '-' }}</dd>
            </div>
            <div>
                <dt>Kelas</dt>
                <dd>{{ $mengerjakan->siswa->kelas ?? '-' }}</dd>
            </div>
        </dl>
    </section>

    {{-- DATA KUIS --}}
    <section class="card">
        <div class="card-header">
            <h2>Data Kuis</h2>
            <span class="badge">{{ ucfirst($mengerjakan->kuis->kategori ?? '-') }}</span>
        </div>
        <dl class="identity">
            <div>
                <dt>Judul Kuis</dt>
                <dd>{{ $mengerjakan->kuis->judul ?? '-' }}</dd>
            </div>
            <div>
                <dt>Percobaan</dt>
                <dd>Ke-{{ $mengerjakan->percobaan_ke }}</dd>
            </div>
            <div>
                <dt>Nilai Akhir</dt>
                <dd>
                    <strong class="u-font-size-20px u-color-var---purple">{{ $mengerjakan->nilai !== null ? number_format($mengerjakan->nilai, 1) : '-' }}</strong>
                </dd>
            </div>
            <div>
                <dt>KKM Kuis</dt>
                <dd>{{ $mengerjakan->kuis->kkm !== null ? number_format($mengerjakan->kuis->kkm, 1) : '-' }}</dd>
            </div>
            <div>
                <dt>Status Kelulusan</dt>
                <dd>
                    @if($mengerjakan->nilai === null)
                        —
                    @elseif($mengerjakan->kuis->kkm !== null && $mengerjakan->nilai >= $mengerjakan->kuis->kkm)
                        <span class="badge badge-success">✓ Lulus</span>
                    @else
                        <span class="badge badge-warning">⚠️ Tidak Lulus</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>
</div>

{{-- KHUSUS REMEDIAL --}}
@if($mengerjakan->kuis->kategori === 'remedial')
    <section class="card">
        <div class="card-header">
            <h2>Informasi Remedial</h2>
        </div>
        @if($remedial && $remedial->mengerjakanAsal)
            <dl class="identity">
                <div>
                    <dt>Kuis Asal</dt>
                    <dd>{{ $remedial->mengerjakanAsal->kuis->judul ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Nilai Sebelum Remedial</dt>
                    <dd>{{ $remedial->mengerjakanAsal->nilai }}</dd>
                </div>
                <div>
                    <dt>Nilai Setelah Remedial</dt>
                    <dd><strong class="u-color-var---purple">{{ $mengerjakan->nilai }}</strong></dd>
                </div>
                <div>
                    <dt>Status Remedial</dt>
                    <dd>
                        @if($mengerjakan->kuis->kkm !== null && $mengerjakan->nilai >= $mengerjakan->kuis->kkm)
                            <span class="badge badge-success">Lulus setelah remedial</span>
                        @else
                            <span class="badge badge-warning">Belum lulus setelah remedial</span>
                        @endif
                    </dd>
                </div>
            </dl>
        @else
            <div class="empty">Data nilai asal remedial tidak ditemukan.</div>
        @endif
    </section>
@endif

{{-- EVALUASI JAWABAN SISWA PER BUTIR SOAL --}}
@if(isset($soal) && $soal->isNotEmpty())
    <section class="card">
        <div class="card-header">
            <div>
                <h2>📋 Evaluasi Jawaban Siswa per Butir Soal</h2>
                <p class="muted">Daftar butir soal, jawaban yang dipilih siswa, serta kunci jawaban sistem.</p>
            </div>
        </div>

        <div class="eval-stats-guru">
            <div class="eval-stat-guru-box">
                <div class="eval-stat-guru-num u-color-166534">{{ $totalBenar ?? 0 }}</div>
                <div class="eval-stat-guru-label">Jawaban Benar</div>
            </div>
            <div class="eval-stat-guru-box">
                <div class="eval-stat-guru-num u-color-991b1b">{{ $totalSalah ?? 0 }}</div>
                <div class="eval-stat-guru-label">Jawaban Salah</div>
            </div>
            <div class="eval-stat-guru-box">
                <div class="eval-stat-guru-num u-color-var---purple">{{ count($soal) }}</div>
                <div class="eval-stat-guru-label">Total Butir Soal</div>
            </div>
        </div>

        @foreach($soal as $itemSoal)
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

            <div class="soal-guru-item {{ $isBenar ? 'benar' : 'salah' }}">
                <div class="soal-guru-head">
                    <strong>
                        Soal #{{ $loop->iteration }}
                        <span class="muted small u-margin-left-6px">(Bobot: {{ $itemSoal->bobot ?? 0 }}%)</span>
                    </strong>

                    @if($isBenar)
                        <span class="badge badge-success">✓ Benar (+{{ $itemSoal->bobot ?? 0 }}%)</span>
                    @elseif($jawabanDipilih)
                        <span class="badge badge-warning">✗ Salah (Siswa pilih: {{ strtoupper($jawabanDipilih) }})</span>
                    @else
                        <span class="badge badge-muted">○ Tidak Dijawab</span>
                    @endif
                </div>

                <div class="u-font-size-14px u-line-height-1p6 u-margin-bottom-12px">
                    {{ $itemSoal->pertanyaan }}
                </div>

                <div class="opsi-guru-list">
                    @foreach($opsiMap as $huruf => $teksOpsi)
                        @if($teksOpsi)
                            @php
                                $isKunci = $kunciBenar === $huruf;
                                $isDipilih = strtoupper($jawabanDipilih ?? '') === $huruf;
                                $cls = '';
                                if ($isKunci) {
                                    $cls = 'kunci';
                                } elseif ($isDipilih && !$isBenar) {
                                    $cls = 'jawaban-salah';
                                }
                            @endphp

                            <div class="opsi-guru-item {{ $cls }}">
                                <strong>{{ $huruf }}.</strong>
                                <span>{{ $teksOpsi }}</span>

                                @if($isKunci && $isDipilih)
                                    <span class="opsi-guru-badge badge-green">✓ Jawaban Siswa (Benar)</span>
                                @elseif($isKunci)
                                    <span class="opsi-guru-badge badge-green">✓ Kunci Jawaban</span>
                                @elseif($isDipilih)
                                    <span class="opsi-guru-badge badge-red">✗ Pilihan Siswa (Salah)</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif

@endsection
