@extends('layouts.siswa')
@section('title', 'Tugas Remedial')

@section('content')

<div class="page-heading">
    <div>
        <h1>Tugas Remedial</h1>
        <p>Perbaiki nilai kuis yang belum mencapai KKM.</p>
    </div>
    <a href="{{ route('siswa.kuis.index') }}" class="btn btn-secondary">
        ← Ke Daftar Kuis
    </a>
</div>

<section class="card">
    <div class="card-header">
        <div>
            <h2>Tugas Remedial Saya</h2>
            <p class="muted">{{ $tugas->count() }} tugas remedial tercatat</p>
        </div>
    </div>

    @if($tugas->isEmpty())
        <div class="empty">
            <h3>Tidak ada tugas remedial</h3>
            <p>Tugas remedial akan muncul di sini jika nilaimu pada kuis ulangan belum mencapai KKM.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="u-width-40px">No</th>
                        <th>Kuis Asal</th>
                        <th>Nilai Awal</th>
                        <th>KKM</th>
                        <th>Kuis Remedial</th>
                        <th>Tanggal Ditugaskan</th>
                        <th>Status</th>
                        <th class="u-min-width-150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tugas as $item)
                        @php
                            $kkm = $item->mengerjakanAsal?->kuis?->kkm;
                        @endphp
                        <tr>
                            <td class="number u-text-align-left">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->mengerjakanAsal?->kuis?->judul ?? '-' }}</strong>
                            </td>
                            <td>
                                <strong class="u-color-991b1b">{{ $item->mengerjakanAsal?->nilai ?? '-' }}</strong>
                            </td>
                            <td>{{ $kkm !== null ? number_format($kkm, 1) : '-' }}</td>
                            <td>
                                <strong>{{ $item->kuisRemedial?->judul ?? '-' }}</strong>
                            </td>
                            <td>
                                {{ $item->tanggal_ditugaskan ? date('d/m/Y', strtotime($item->tanggal_ditugaskan)) : '-' }}
                            </td>
                            <td>
                                @if($item->status === 'ditugaskan')
                                    <span class="badge badge-warning">Belum Dikerjakan</span>
                                @elseif($item->status === 'selesai')
                                    <span class="badge badge-success">✓ Selesai</span>
                                @else
                                    <span class="badge">{{ $item->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status === 'ditugaskan')
                                    <a href="{{ route('siswa.remedial.kerjakan', $item->id_remedial_siswa) }}" class="btn btn-small">
                                        Kerjakan Remedial →
                                    </a>
                                @elseif($item->status === 'selesai')
                                    <span class="muted small u-color-166534 u-font-weight-600">✓ Sudah Selesai</span>
                                @else
                                    {{ $item->status }}
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
