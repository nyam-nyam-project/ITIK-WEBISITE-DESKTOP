@extends('layouts.guru')
@section('title', 'Dashboard Guru')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/guru/dashboard.css') }}">
@endpush
@section('content')

<div class="page-heading dashboard-heading">
    <div class="dashboard-heading__title">
        <h1>Dashboard</h1>
        <p>Ringkasan kelas</p>
    </div>
    @if($kelasDipilih)
        <form class="dashboard-heading__filters" method="GET" action="{{ route('dashboard') }}">
            <div class="field">
                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas">
                    @foreach($kelasUntukGuru->unique('id_kelas') as $kelas)
                        <option value="{{ $kelas->id_kelas }}" @selected($kelasDipilih->id_kelas === $kelas->id_kelas)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="mapel">Mata pelajaran</label>
                <select id="mapel" name="mapel">
                    @foreach($mapelUntukKelas as $mapel)
                        <option value="{{ $mapel->id_mapel }}" @selected($kelasDipilih->id_mapel === $mapel->id_mapel)>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn">Tampilkan</button>
        </form>
    @endif
</div>

@if(!$kelasDipilih)
    <section class="card">
        <div class="empty">
            <p>Belum ada kelas dan mapel yang diajarkan. Hubungi admin.</p>
        </div>
    </section>
@else
    <div class="stats dashboard-kelas-stats">
        <div class="stat">
            <span class="label">Siswa aktif</span>
            <span class="value">{{ $ringkasan['jumlah_siswa'] }}</span>
        </div>
        <div class="stat">
            <span class="label">Rata-rata kelas</span>
            <span class="value">{{ $ringkasan['rata_rata_nilai'] === null ? '—' : number_format($ringkasan['rata_rata_nilai'], 1) }}</span>
        </div>
        <div class="stat">
            <span class="label">Perlu dibantu</span>
            <span class="value">{{ $ringkasan['jumlah_perlu_dibantu'] }}</span>
        </div>
    </div>

    <section class="card dashboard-search">
        <form class="filter-form" method="GET" action="{{ route('dashboard') }}">
            <input type="hidden" name="kelas" value="{{ $kelasDipilih->id_kelas }}">
            <input type="hidden" name="mapel" value="{{ $kelasDipilih->id_mapel }}">
            <div class="field field-search">
                <label for="cari">Cari nama atau NISN</label>
                <input id="cari" name="cari" type="search" value="{{ $cari }}" maxlength="100" placeholder="Masukkan nama atau NISN siswa">
            </div>
            <button type="submit" class="btn">Cari</button>
        </form>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2>Daftar siswa</h2>
                <p class="muted">{{ $kelasDipilih->nama_kelas }} · {{ $kelasDipilih->nama_mapel }}</p>
            </div>
        </div>
        @if($daftarSiswa->isNotEmpty())
            <div class="table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Materi dibaca</th>
                            <th>Kuis dikerjakan</th>
                            <th>Rata-rata</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daftarSiswa as $siswa)
                            <tr>
                                <td>
                                    <strong>
                                        <a href="{{ route('dashboard', ['kelas' => $kelasDipilih->id_kelas, 'mapel' => $kelasDipilih->id_mapel, 'siswa' => $siswa->id_user]) }}">
                                            {{ $siswa->nama_lengkap }}
                                        </a>
                                    </strong>
                                    <span class="muted small">{{ $siswa->nomor_induk ?? 'NISN belum tersedia' }}</span>
                                </td>
                                <td>{{ number_format($siswa->persentase_materi_dibaca, 0) }}%</td>
                                <td>{{ $siswa->jumlah_kuis_dikerjakan }} dari {{ $siswa->jumlah_kuis_total }}</td>
                                <td>{{ $siswa->rata_rata_nilai === null ? '—' : number_format($siswa->rata_rata_nilai, 1) }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($siswa->status) {
                                            'Perlu dibantu' => 'dashboard-status--perlu-dibantu',
                                            'Belum mulai' => 'dashboard-status--belum-mulai',
                                            default => 'dashboard-status--lancar',
                                        };
                                    @endphp
                                    <span class="dashboard-status {{ $statusClass }}">{{ $siswa->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                <p>Tidak ada siswa yang cocok dengan pencarian di kelas ini.</p>
            </div>
        @endif
    </section>

    <section class="card dashboard-categories">
        <div class="card-header">
            <div>
                <h2>Kelulusan per kategori</h2>
                <p class="muted">Persentase kelulusan dari pengerjaan yang dinilai</p>
            </div>
        </div>
        <div class="dashboard-category-list">
            @foreach($kategoriKelulusan as $kategori)
                <div class="dashboard-category">
                    <div class="dashboard-category__label">
                        <strong>{{ ucfirst($kategori->kategori) }}</strong>
                        <span>{{ number_format($kategori->persentase_lulus, 0) }}%</span>
                    </div>
                    <div class="dashboard-bar">
                        <progress max="100" value="{{ $kategori->bar_width }}" aria-label="Kelulusan {{ $kategori->kategori }}">
                            {{ $kategori->persentase_lulus }}%
                        </progress>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card dashboard-remedial">
        <div class="card-header">
            <div>
                <h2>Remedial</h2>
                <p class="muted">
                    {{ $remedial['ditugaskan'] }} siswa ditugaskan
                    · {{ $remedial['selesai'] }} selesai
                    · {{ $remedial['belum'] }} belum
                </p>
            </div>
        </div>
    </section>
@endif

@endsection
