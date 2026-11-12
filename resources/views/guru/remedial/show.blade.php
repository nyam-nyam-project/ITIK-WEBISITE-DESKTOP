@extends('layouts.guru')
@section('title', 'Detail Kuis Remedial')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/remedial/show.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <a href="{{ route('guru.remedial.index') }}" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px">
            ← Kembali
        </a>
        <h1>Detail Kuis Remedial</h1>
        <p>Pantau perkembangan dan hasil pengerjaan remedial siswa.</p>
    </div>
    <div class="u-display-flex u-gap-10px">
        <a href="{{ route('guru.remedial.siswa', $remedial->id_remedial) }}" class="btn btn-primary">
            👥 Tugaskan Siswa
        </a>
    </div>
</div>

<section class="card u-margin-bottom-24px">
    <div class="card-header">
        <div>
            <h2>Informasi Kuis Remedial</h2>
            <p class="muted">Kuis remedial yang dihubungkan dengan kuis ulangan.</p>
        </div>
    </div>

    <div class="meta-box">
        <div class="meta-item">
            <div class="meta-label">Kuis Remedial</div>
            <div class="meta-val u-color-5542dc">{{ $remedial->kuisRemedial->judul ?? '-' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Kuis Asal (Ulangan)</div>
            <div class="meta-val">{{ $remedial->kuisAsal->judul ?? '-' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">KKM Kelulusan</div>
            <div class="meta-val u-color-c98200">{{ number_format($remedial->kuisRemedial->kkm ?? $remedial->kuisAsal->kkm, 1) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Jumlah Butir Soal</div>
            <div class="meta-val">{{ $remedial->kuisRemedial?->soal?->count() ?? 0 }} Soal</div>
        </div>
    </div>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2>Daftar Siswa yang Ditugaskan</h2>
            <p class="muted">Status dan skor remedial siswa yang terdaftar.</p>
        </div>
    </div>

    @if($remedial->remedialSiswa->isEmpty())
        <div class="empty-state u-padding-48px-20px u-text-align-center">
            <div class="u-font-size-40px u-margin-bottom-12px">📝</div>
            <h3 class="u-margin-bottom-6px">Belum Ada Siswa yang Ditugaskan</h3>
            <p class="muted u-margin-bottom-20px">Klik tombol "Tugaskan Siswa" di atas untuk memilih siswa yang nilainya di bawah KKM pada Ulangan.</p>
            <a href="{{ route('guru.remedial.siswa', $remedial->id_remedial) }}" class="btn btn-primary">
                👥 Tugaskan Siswa Sekarang
            </a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="u-width-50px">No</th>
                        <th>Nama Siswa</th>
                        <th>Nilai Ulangan Asal</th>
                        <th>KKM</th>
                        <th>Nilai Remedial</th>
                        <th>Status Pengerjaan</th>
                        <th>Hasil Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($remedial->remedialSiswa as $item)
                        @php
                            $nilaiAsal = $item->mengerjakanAsal?->nilai;
                            $nilaiRemedial = $item->hasilRemedial?->nilai;
                            $kkm = $remedial->kuisRemedial->kkm ?? $remedial->kuisAsal->kkm;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->mengerjakanAsal?->siswa?->nama_lengkap ?? '-' }}</strong>
                                <div class="muted small">NISN: {{ $item->mengerjakanAsal?->siswa?->nomor_induk ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="u-color-b91c1c u-font-weight-700">
                                    {{ $nilaiAsal !== null ? number_format($nilaiAsal, 1) : '-' }}
                                </span>
                            </td>
                            <td>{{ number_format($kkm, 1) }}</td>
                            <td>
                                @if($nilaiRemedial !== null)
                                    <strong class="{{ $nilaiRemedial >= $kkm ? 'u-color-15803d' : 'u-color-b91c1c' }}">
                                        {{ number_format($nilaiRemedial, 1) }}
                                    </strong>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($nilaiRemedial !== null)
                                    <span class="badge badge-success">Selesai</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($item->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($nilaiRemedial === null)
                                    <span class="badge badge-secondary">Belum Mengerjakan</span>
                                @elseif($nilaiRemedial >= $kkm)
                                    <span class="badge badge-success">✓ Lulus Remedial</span>
                                @else
                                    <span class="badge badge-danger">Belum Lulus</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@endsection
