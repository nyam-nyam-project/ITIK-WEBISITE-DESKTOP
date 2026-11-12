@extends('layouts.siswa')
@section('title', 'Dashboard')
@section('content')

<div class="page-heading">
    <div>
        <h1>Selamat datang, {{ Auth::user()->nama_lengkap }}!</h1>
        <p>Pantau progress belajar dan kerjakan kuis yang tersedia.</p>
    </div>
    <a class="btn" href="{{ route('siswa.kuis.index') }}">Lihat semua kuis</a>
</div>

{{-- Peringatan remedial --}}
@if(isset($remedialAktif) && $remedialAktif->isNotEmpty())
    <div class="alert alert-warning" role="alert">
        <strong>Kamu memiliki {{ $remedialAktif->count() }} tugas remedial yang belum dikerjakan.</strong>
        <p class="u-margin-bottom-10px">Selesaikan tugas remedial agar nilaimu dapat meningkat.</p>
        <a href="{{ route('siswa.remedial.index') }}" class="btn btn-warning btn-small">Lihat Tugas Remedial</a>
    </div>
@endif

{{-- Statistik --}}
<div class="stats">
    <div class="stat">
        <span class="label">Materi Tersedia</span>
        <span class="value">{{ $totalMateri ?? 0 }}</span>
        <small><a href="{{ route('siswa.materi.index') }}">Lihat materi</a></small>
    </div>
    <div class="stat">
        <span class="label">Materi Selesai</span>
        <span class="value">{{ $materiSelesai ?? 0 }}</span>
        <small>dari {{ $totalMateri ?? 0 }} materi</small>
    </div>
    <div class="stat">
        <span class="label">Kuis Tersedia</span>
        <span class="value">{{ $kuisTersedia ?? 0 }}</span>
        <small><a href="{{ route('siswa.kuis.index') }}">Kerjakan kuis</a></small>
    </div>
    <div class="stat">
        <span class="label">Rata-rata Nilai</span>
        <span class="value">{{ isset($rataNilai) && $rataNilai ? number_format($rataNilai, 0) : '—' }}</span>
        <small><a href="{{ route('siswa.nilai.index') }}">Lihat nilai</a></small>
    </div>
</div>

<div class="grid grid-2">

    {{-- Materi Terbaru --}}
    @isset($materiTerbaru)
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Materi Terbaru</h2>
                    <p class="muted">Materi yang baru ditambahkan</p>
                </div>
                <a href="{{ route('siswa.materi.index') }}" class="btn btn-secondary btn-small">Lihat semua</a>
            </div>
            @if($materiTerbaru->isNotEmpty())
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materiTerbaru as $materi)
                                @php $progress = $materi->progress->first(); @endphp
                                <tr>
                                    <td>
                                        <strong>
                                            <a href="{{ route('siswa.materi.show', $materi->id_materi) }}">{{ $materi->judul }}</a>
                                        </strong>
                                        <span class="muted small">{{ $materi->bab ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if(!$progress)
                                            <span class="badge badge-muted">Belum Mulai</span>
                                        @elseif($progress->status === 'selesai')
                                            <span class="badge badge-success">Selesai</span>
                                        @else
                                            <span class="badge">Sedang Belajar</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty"><p>Belum ada materi yang tersedia.</p></div>
            @endif
        </section>
    @endisset

    {{-- Kuis Terbaru --}}
    @isset($kuisTerbaru)
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Kuis Terbaru</h2>
                    <p class="muted">Kuis yang bisa kamu kerjakan</p>
                </div>
                <a href="{{ route('siswa.kuis.index') }}" class="btn btn-secondary btn-small">Lihat semua</a>
            </div>
            @if($kuisTerbaru->isNotEmpty())
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kuisTerbaru as $kuis)
                                <tr>
                                    <td>
                                        <strong>{{ $kuis->judul }}</strong>
                                        <span class="muted small">{{ $kuis->alokasi_waktu }} menit &middot; KKM {{ $kuis->kkm }}</span>
                                    </td>
                                    <td><span class="badge">{{ ucfirst($kuis->kategori) }}</span></td>
                                    <td>
                                        <a href="{{ route('siswa.kuis.mulai', $kuis->id_kuis) }}" class="btn btn-small">Mulai</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty"><p>Belum ada kuis yang tersedia.</p></div>
            @endif
        </section>
    @endisset

</div>

@endsection
