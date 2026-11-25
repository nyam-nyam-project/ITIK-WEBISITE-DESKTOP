@extends('layouts.guru')
@section('title', 'Kotak Arsip Pembelajaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/arsip/index.css') }}">
@endpush

@section('content')

<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sep">›</span>
    <span class="current">Kotak Arsip</span>
</div>

<div class="page-heading">
    <div>
        <h1>Kotak Arsip Pembelajaran</h1>
        <p>Kelola kuis dan materi yang telah diarsipkan. Anda dapat memulihkan kembali ke daftar aktif atau menghapusnya secara permanen.</p>
    </div>
    <div class="actions">
        <a href="{{ route('kuis.index') }}" class="btn btn-secondary">
            ← Kembali ke Kuis
        </a>
        <a href="{{ route('materi.index') }}" class="btn btn-secondary">
            ← Kembali ke Materi
        </a>
    </div>
</div>

{{-- TAB NAVIGASI --}}
<div class="arsip-tabs">
    <a href="{{ route('guru.arsip.index', ['tab' => 'kuis', 'mapel' => $idMapel]) }}"
       class="arsip-tab-btn {{ $tab === 'kuis' ? 'active' : '' }}">
        <span>📝 Kuis Diarsipkan</span>
        <span class="arsip-count-badge">{{ $totalKuisArsip }}</span>
    </a>
    <a href="{{ route('guru.arsip.index', ['tab' => 'materi', 'mapel' => $idMapel]) }}"
       class="arsip-tab-btn {{ $tab === 'materi' ? 'active' : '' }}">
        <span>📚 Materi Diarsipkan</span>
        <span class="arsip-count-badge">{{ $totalMateriArsip }}</span>
    </a>
</div>

{{-- FILTER MAPEL --}}
@if($mapelList->count() > 1)
    <div class="arsip-filter-card">
        <div class="arsip-filter-group">
            <label for="filter-mapel">Filter Mata Pelajaran:</label>
            <select id="filter-mapel">
                <option value="{{ route('guru.arsip.index', ['tab' => $tab]) }}">
                    Semua Mata Pelajaran
                </option>
                @foreach($mapelList as $mp)
                    <option value="{{ route('guru.arsip.index', ['tab' => $tab, 'mapel' => $mp->id_mapel]) }}"
                        @selected($idMapel === $mp->id_mapel)>
                        {{ $mp->nama_mapel }}
                    </option>
                @endforeach
            </select>
        </div>
        <span class="muted small">Menampilkan data arsip sesuai penugasan mengajar Anda.</span>
    </div>
@endif

{{-- KONTEN TAB KUIS --}}
@if($tab === 'kuis')
    @if($kuisArsip->isEmpty())
        <div class="empty">
            <h3>Kotak Arsip Kuis Kosong</h3>
            <p>Tidak ada kuis yang sedang diarsipkan untuk filter yang dipilih.</p>
            <a href="{{ route('kuis.index') }}" class="btn u-margin-top-14px">
                Buka Daftar Kuis Aktif
            </a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID Kuis</th>
                        <th>Judul Kuis</th>
                        <th>Mata Pelajaran</th>
                        <th>Kategori</th>
                        <th>Soal</th>
                        <th>Pengerjaan Siswa</th>
                        <th>Status Asal</th>
                        <th class="u-min-width-180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kuisArsip as $k)
                        <tr>
                            <td><code>{{ $k->id_kuis }}</code></td>
                            <td><strong>{{ $k->judul }}</strong></td>
                            <td>{{ $k->mapel?->nama_mapel ?? '-' }}</td>
                            <td><span class="badge">{{ ucfirst($k->kategori) }}</span></td>
                            <td>{{ $k->soal_count }} butir</td>
                            <td>
                                @if($k->mengerjakan_count > 0)
                                    <span class="badge badge-success">{{ $k->mengerjakan_count }} siswa</span>
                                @else
                                    <span class="muted small">Belum ada</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $k->status_publikasi === 'terbit' ? 'badge-success' : 'badge-muted' }}">
                                    {{ ucfirst($k->status_publikasi) }}
                                </span>
                            </td>
                            <td>
                                <div class="arsip-actions-cell">
                                    <form action="{{ route('guru.arsip.kuis.pulihkan', $k->id_kuis) }}" method="POST"
                                          data-confirm="Pulihkan kuis '{{ $k->judul }}' kembali ke daftar kuis aktif?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-restore" title="Kembalikan ke daftar kuis aktif">
                                            ↻ Pulihkan
                                        </button>
                                    </form>

                                    <form action="{{ route('guru.arsip.kuis.hapus-permanen', $k->id_kuis) }}" method="POST"
                                          data-confirm="YAKIN HAPUS PERMANEN kuis '{{ $k->judul }}'? Tindakan ini tidak dapat dibatalkan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-force-delete" title="Hapus permanen dari database">
                                            🗑 Hapus Permanen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $kuisArsip])
    @endif

{{-- KONTEN TAB MATERI --}}
@else
    @if($materiArsip->isEmpty())
        <div class="empty">
            <h3>Kotak Arsip Materi Kosong</h3>
            <p>Tidak ada materi yang sedang diarsipkan untuk filter yang dipilih.</p>
            <a href="{{ route('materi.index') }}" class="btn u-margin-top-14px">
                Buka Daftar Materi Aktif
            </a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID Materi</th>
                        <th>Judul Materi</th>
                        <th>Mata Pelajaran</th>
                        <th>Bab &amp; Kelas</th>
                        <th>Dokumen PDF</th>
                        <th class="u-min-width-180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materiArsip as $m)
                        <tr>
                            <td><code>{{ $m->id_materi }}</code></td>
                            <td><strong>{{ $m->judul }}</strong></td>
                            <td>{{ $m->mapel?->nama_mapel ?? '-' }}</td>
                            <td>
                                {{ $m->bab ?? '-' }} &bull; Kelas {{ $m->kelas ?? '-' }}
                            </td>
                            <td>
                                @if($m->isi_materi)
                                    <span class="badge badge-success">📄 Tersimpan</span>
                                @else
                                    <span class="muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="arsip-actions-cell">
                                    <form action="{{ route('guru.arsip.materi.pulihkan', $m->id_materi) }}" method="POST"
                                          data-confirm="Pulihkan materi '{{ $m->judul }}' kembali ke daftar aktif?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-restore" title="Kembalikan ke daftar materi aktif">
                                            ↻ Pulihkan
                                        </button>
                                    </form>

                                    <form action="{{ route('guru.arsip.materi.hapus-permanen', $m->id_materi) }}" method="POST"
                                          data-confirm="YAKIN HAPUS PERMANEN materi '{{ $m->judul }}' beserta dokumen filenya? Tindakan ini tidak dapat dibatalkan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-force-delete" title="Hapus permanen dari database dan penyimpanan">
                                            🗑 Hapus Permanen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $materiArsip])
    @endif
@endif

@push('scripts')
<script src="{{ asset('js/guru/arsip/index.js') }}" defer></script>
@endpush

@endsection
