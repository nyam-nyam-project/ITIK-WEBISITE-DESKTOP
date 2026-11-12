@extends('layouts.guru')
@section('title', 'Kelola Remedial')

@section('content')

<div class="page-heading">
    <div>
        <h1>Kelola Remedial</h1>
        <p>Pengelolaan kuis remedial khusus untuk kuis ber-kategori Ulangan.</p>
    </div>
    <div>
        <a href="{{ route('guru.remedial.create') }}" class="btn btn-primary">
            + Buat Kuis Remedial
        </a>
    </div>
</div>

<section class="card">
    <div class="card-header">
        <div>
            <h2>Daftar Kuis Remedial</h2>
            <p class="muted">Remedial yang ditugaskan dari evaluasi ulangan siswa.</p>
        </div>
    </div>

    @if($remedials->isEmpty())
        <div class="empty-state u-padding-48px-20px u-text-align-center">
            <div class="u-font-size-40px u-margin-bottom-12px">📋</div>
            <h3 class="u-margin-bottom-6px">Belum Ada Kuis Remedial</h3>
            <p class="muted u-margin-bottom-20px">Silakan buat kuis remedial baru untuk siswa yang nilainya di bawah KKM pada Ulangan.</p>
            <a href="{{ route('guru.remedial.create') }}" class="btn btn-primary">
                + Buat Kuis Remedial Sekarang
            </a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="u-width-50px">No</th>
                        <th>Judul Kuis Remedial</th>
                        <th>Kuis Asal (Ulangan)</th>
                        <th>Mata Pelajaran</th>
                        <th>KKM</th>
                        <th>Jumlah Soal</th>
                        <th>Status</th>
                        <th class="u-width-200px u-text-align-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($remedials as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->kuisRemedial->judul ?? '-' }}</strong>
                                <div><span class="badge badge-warning u-font-size-11px">Remedial</span></div>
                            </td>
                            <td>
                                {{ $item->kuisAsal->judul ?? '-' }}
                                <div><span class="badge badge-info u-font-size-11px">{{ $item->kuisAsal->kategori ?? 'Ulangan' }}</span></div>
                            </td>
                            <td>
                                {{ $item->kuisAsal->mapel->nama_mapel ?? '-' }}
                            </td>
                            <td>
                                <strong>{{ number_format($item->kuisRemedial->kkm ?? $item->kuisAsal->kkm, 1) }}</strong>
                            </td>
                            <td>
                                {{ $item->kuisRemedial?->soal?->count() ?? 0 }} Soal
                            </td>
                            <td>
                                @if(($item->kuisRemedial->status_publikasi ?? '') === 'terbit')
                                    <span class="badge badge-success">Terbit</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="u-text-align-right">
                                <div class="u-display-inline-flex u-gap-6px">
                                    <a href="{{ route('guru.remedial.siswa', $item->id_remedial) }}" class="btn btn-primary small">
                                        👥 Tugaskan Siswa
                                    </a>
                                    <a href="{{ route('guru.remedial.show', $item->id_remedial) }}" class="btn btn-secondary small">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@endsection
