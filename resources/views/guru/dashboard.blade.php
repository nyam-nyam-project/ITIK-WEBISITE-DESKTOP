@extends('layouts.guru')
@section('title', 'Dashboard Guru')
@section('content')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/guru/dashboard.css') }}">
@endpush

<div class="page-heading">
    <div>
        <h1>Dashboard</h1>
        <p>Ringkasan aktivitas pembelajaran TIK.</p>
    </div>
    <a class="btn btn-secondary" href="{{ route('kuis.index') }}">Lihat semua kuis</a>
</div>

<div class="stats">
    <div class="stat">
        <span class="label">Siswa</span>
        <span class="value">{{ $jumlahSiswa }}</span>
        <small>Di kelas yang diajar</small>
    </div>
    <div class="stat">
        <span class="label">Materi</span>
        <span class="value">{{ $jumlahMateri }}</span>
        <small><a href="{{ route('materi.index') }}">Kelola materi</a></small>
    </div>
    <div class="stat">
        <span class="label">Kuis Aktif</span>
        <span class="value">{{ $jumlahKuis }}</span>
        <small><a href="{{ route('kuis.index') }}">Kelola kuis</a></small>
    </div>
    <div class="stat">
        <span class="label">Rata-rata Nilai</span>
        <span class="value">{{ $rataNilai ? number_format($rataNilai, 0) : '—' }}</span>
        <small>Dari kuis kelas yang diajar</small>
    </div>
</div>

<section class="card grafik-dashboard" data-grafik="{{ json_encode($grafik) }}">
    <div class="card-header">
        <div>
            <h2>Monitoring Siswa</h2>
            <p class="muted">Ringkasan nilai, ketuntasan kuis, dan progres belajar di kelas ajar.</p>
        </div>
    </div>
    <div class="grafik-grid">
        <div class="grafik-panel">
            <h3>Nilai per Tingkat Kesulitan</h3>
            <canvas id="grafik-nilai-per-tingkat" role="img" aria-label="Grafik nilai per tingkat kesulitan"></canvas>
        </div>
        <div class="grafik-panel">
            <h3>Ketuntasan per Kuis</h3>
            <canvas id="grafik-ketuntasan-per-kuis" role="img" aria-label="Grafik ketuntasan siswa per kuis"></canvas>
        </div>
        <div class="grafik-panel">
            <h3>Progres Materi</h3>
            <canvas id="grafik-progres-materi" role="img" aria-label="Grafik progres penyelesaian materi"></canvas>
        </div>
        <div class="grafik-panel">
            <h3>Pre-test ke Post-test</h3>
            <canvas id="grafik-pre-post-test" role="img" aria-label="Grafik perbandingan nilai pre-test dan post-test siswa"></canvas>
        </div>
    </div>
</section>

<div class="grid grid-2">

    <section class="card">
        <div class="card-header">
            <div>
                <h2>Kuis Terbaru</h2>
                <p class="muted">5 kuis yang terakhir dibuat</p>
            </div>
            <a href="{{ route('kuis.index') }}" class="btn btn-secondary btn-small">Lihat semua</a>
        </div>
        @if($kuisTerbaru->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kuisTerbaru as $kuis)
                            <tr>
                                <td>
                                    <strong><a href="{{ route('kuis.show', $kuis->id_kuis) }}">{{ $kuis->judul }}</a></strong>
                                    <span class="muted small">{{ $kuis->alokasi_waktu ? $kuis->alokasi_waktu.' menit' : 'Waktu bebas' }}</span>
                                </td>
                                <td>{{ ucfirst($kuis->kategori) }}</td>
                                <td>
                                    @if($kuis->status_publikasi === 'terbit')
                                        <span class="badge badge-success">Terbit</span>
                                    @else
                                        <span class="badge badge-muted">Draft</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                <p>Belum ada kuis yang dibuat.</p>
                <a href="{{ route('kuis.create') }}" class="btn">Buat kuis pertama</a>
            </div>
        @endif
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2>Materi Terbaru</h2>
                <p class="muted">5 materi yang terakhir ditambahkan</p>
            </div>
            <a href="{{ route('materi.index') }}" class="btn btn-secondary btn-small">Lihat semua</a>
        </div>
        @if($materiTerbaru->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Bab</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materiTerbaru as $materi)
                            <tr>
                                <td>
                                    <strong>{{ $materi->judul }}</strong>
                                </td>
                                <td class="muted">{{ $materi->bab ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                <p>Belum ada materi yang ditambahkan.</p>
                <a href="{{ route('materi.index') }}" class="btn">Tambah materi</a>
            </div>
        @endif
    </section>

</div>

@push('scripts')
    <script src="{{ asset('js/vendor/chart.umd.min.js') }}" defer></script>
    <script src="{{ asset('js/guru/dashboard/grafik.js') }}" defer></script>
@endpush

@endsection
