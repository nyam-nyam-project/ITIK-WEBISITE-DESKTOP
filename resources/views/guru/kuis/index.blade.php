@extends('layouts.guru')
@section('title', 'Kuis & Soal')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/kuis/index.css') }}">
@endpush

@section('content')

<div class="page-heading">
    <div>
        <h1>Kuis &amp; Soal</h1>
        <p>{{ $mapelTerpilih ? 'Kelola kuis mata pelajaran: ' . $mapelTerpilih->nama_mapel : 'Pilih mata pelajaran untuk mengelola kuis dan soal.' }}</p>
    </div>
    @if ($mapelTerpilih)
        <a href="{{ route('kuis.create', ['mapel' => $mapelTerpilih->id_mapel]) }}" class="btn">
            + Tambah Kuis
        </a>
    @endif
</div>

@if (!$mapelTerpilih)
    {{-- PILIH MATA PELAJARAN --}}
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Pilih Mata Pelajaran</h2>
                <p class="muted">Klik mata pelajaran untuk mengelola kuis dan soal di dalamnya.</p>
            </div>
        </div>

        @if ($mapelAktif->isEmpty())
            <div class="empty">
                <h3>Belum ada mata pelajaran aktif</h3>
                <p>Hubungi admin untuk menambahkan mata pelajaran terlebih dahulu.</p>
            </div>
        @else
            <div class="mapel-grid">
                @foreach ($mapelAktif as $mapel)
                    <a href="{{ route('kuis.index', ['mapel' => $mapel->id_mapel]) }}" class="mapel-card">
                        <div>
                            <div class="mapel-icon">📖</div>
                            <h3 class="u-font-size-17px u-margin-bottom-4px">{{ $mapel->nama_mapel }}</h3>
                            <span class="muted small">{{ $mapel->id_mapel }}</span>
                        </div>
                        <div class="u-margin-top-16px u-padding-top-12px u-border-top-1px-solid-var---border u-display-flex u-justify-content-space-between u-align-items-center u-font-size-12px">
                            <span class="muted"><strong>{{ $mapel->kuis_count }}</strong> kuis aktif</span>
                            <span class="u-font-weight-700 u-color-var---purple">Kelola →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@else
    {{-- BANNER MAPEL TERPILIH --}}
    <div class="mapel-banner">
        <div>
            <h3>{{ $mapelTerpilih->nama_mapel }}</h3>
            <p>{{ $kuis->count() }} kuis tersedia &bull; Kode: {{ $mapelTerpilih->id_mapel }}</p>
        </div>
        <a href="{{ route('kuis.index') }}" class="btn-back-mapel">
            ← Ganti Mata Pelajaran
        </a>
    </div>

    {{-- TOGGLE VIEW --}}
    <div class="view-toggle">
        <button type="button" class="view-btn active" id="btn-view-bab" data-view="bab">
            📁 Kelompokkan Per Bab Materi
        </button>
        <button type="button" class="view-btn" id="btn-view-all" data-view="all">
            📋 Semua Kuis (Tabel Lengkap)
        </button>
    </div>

    @php
        $kuisPerMateri = [];
        $kuisUmum = [];
        foreach ($kuis as $k) {
            if ($k->materi->isNotEmpty()) {
                foreach ($k->materi as $m) {
                    $kuisPerMateri[$m->id_materi][$k->id_kuis] = $k;
                }
            } else {
                $kuisUmum[$k->id_kuis] = $k;
            }
        }
    @endphp

    {{-- VIEW 1: PER BAB --}}
    <div id="view-bab">
        @if ($daftarMateri->isNotEmpty())
            @foreach ($daftarMateri as $materiItem)
                @php
                    $listKuisBab = array_values($kuisPerMateri[$materiItem->id_materi] ?? []);
                    $hasMudah = collect($listKuisBab)->contains(fn ($k) => $k->tingkat_kesulitan === 'mudah');
                    $hasSedang = collect($listKuisBab)->contains(fn ($k) => $k->tingkat_kesulitan === 'sedang');
                    $hasSulit = collect($listKuisBab)->contains(fn ($k) => $k->tingkat_kesulitan === 'sulit');
                @endphp

                <div class="bab-card">
                    <div class="bab-header">
                        <div>
                            <h2 class="u-font-size-17px u-margin-bottom-2px">
                                {{ $materiItem->bab ? $materiItem->bab . ' - ' : '' }}{{ $materiItem->judul }}
                            </h2>
                            <span class="muted small">{{ count($listKuisBab) }} kuis terdaftar</span>
                        </div>

                        <div class="u-display-flex u-gap-6px">
                            <span class="level-pill {{ $hasMudah ? 'active-mudah' : 'inactive' }}">
                                {{ $hasMudah ? '✓' : '○' }} Mudah
                            </span>
                            <span class="level-pill {{ $hasSedang ? 'active-sedang' : 'inactive' }}">
                                {{ $hasSedang ? '✓' : '○' }} Sedang
                            </span>
                            <span class="level-pill {{ $hasSulit ? 'active-sulit' : 'inactive' }}">
                                {{ $hasSulit ? '✓' : '○' }} Sulit
                            </span>
                        </div>
                    </div>

                    @if (count($listKuisBab) > 0)
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul Kuis</th>
                                        <th>Kategori</th>
                                        <th>Tingkat</th>
                                        <th class="u-width-50px">Soal</th>
                                        <th>KKM</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th class="u-min-width-170px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listKuisBab as $item)
                                        <tr>
                                            <td><code>{{ $item->id_kuis }}</code></td>
                                            <td><strong>{{ $item->judul }}</strong></td>
                                            <td><span class="badge">{{ ucfirst($item->kategori) }}</span></td>
                                            <td>
                                                <span class="badge {{ $item->tingkat_kesulitan === 'mudah' ? 'badge-success' : ($item->tingkat_kesulitan === 'sedang' ? 'badge-warning' : 'badge-danger') }}">
                                                    {{ ucfirst($item->tingkat_kesulitan ?? 'sedang') }}
                                                </span>
                                            </td>
                                            <td>{{ $item->soal->count() }}</td>
                                            <td>{{ $item->kkm ?? '-' }}</td>
                                            <td>{{ $item->alokasi_waktu ?? '-' }}m</td>
                                            <td>
                                                @if($item->status_publikasi === 'terbit')
                                                    <span class="badge badge-success">Terbit</span>
                                                @else
                                                    <span class="badge badge-muted">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="actions">
                                                    <a href="{{ route('kuis.show', $item->id_kuis) }}" class="btn btn-secondary btn-small">Lihat</a>
                                                    @if ($item->status_publikasi === 'terbit')
                                                        <a href="{{ route('kuis.edit', $item->id_kuis) }}" class="btn btn-secondary btn-small" data-alert="Kuis tersebut tidak dapat diedit karena berstatus terbit. Ubah ke status draft terlebih dahulu.">Edit</a>
                                                    @else
                                                        <a href="{{ route('kuis.edit', $item->id_kuis) }}" class="btn btn-secondary btn-small">Edit</a>
                                                    @endif
                                                    <form class="u-display-inline" action="{{ route('kuis.destroy', $item->id_kuis) }}" method="POST" data-confirm="Arsipkan kuis ini?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-small">Arsip</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty u-padding-16px">
                            <p class="muted small u-margin-bottom-8px">Belum ada kuis untuk bab materi ini.</p>
                            <a href="{{ route('kuis.create', ['mapel' => $mapelTerpilih->id_mapel]) }}" class="btn btn-secondary btn-small">
                                + Buat Kuis Bab Ini
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        {{-- KUIS UMUM --}}
        @if (count($kuisUmum) > 0)
            <div class="bab-card">
                <div class="bab-header">
                    <div>
                        <h2 class="u-font-size-17px u-margin-bottom-2px">📝 Ulangan &amp; Kuis Umum</h2>
                        <span class="muted small">{{ count($kuisUmum) }} kuis umum tanpa materi spesifik</span>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Judul Kuis</th>
                                <th>Kategori</th>
                                <th>Tingkat</th>
                                <th>Soal</th>
                                <th>KKM</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kuisUmum as $item)
                                <tr>
                                    <td><code>{{ $item->id_kuis }}</code></td>
                                    <td><strong>{{ $item->judul }}</strong></td>
                                    <td><span class="badge">{{ ucfirst($item->kategori) }}</span></td>
                                    <td>
                                        <span class="badge {{ $item->tingkat_kesulitan === 'mudah' ? 'badge-success' : ($item->tingkat_kesulitan === 'sedang' ? 'badge-warning' : 'badge-danger') }}">
                                            {{ ucfirst($item->tingkat_kesulitan ?? 'sedang') }}
                                        </span>
                                    </td>
                                    <td>{{ $item->soal->count() }}</td>
                                    <td>{{ $item->kkm ?? '-' }}</td>
                                    <td>{{ $item->alokasi_waktu ?? '-' }}m</td>
                                    <td>
                                        @if($item->status_publikasi === 'terbit')
                                            <span class="badge badge-success">Terbit</span>
                                        @else
                                            <span class="badge badge-muted">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="{{ route('kuis.show', $item->id_kuis) }}" class="btn btn-secondary btn-small">Lihat</a>
                                            @if ($item->status_publikasi === 'terbit')
                                                <a href="{{ route('kuis.edit', $item->id_kuis) }}" class="btn btn-secondary btn-small" data-alert="Kuis tersebut tidak dapat diedit karena berstatus terbit. Ubah ke status draft terlebih dahulu.">Edit</a>
                                            @else
                                                <a href="{{ route('kuis.edit', $item->id_kuis) }}" class="btn btn-secondary btn-small">Edit</a>
                                            @endif
                                            <form class="u-display-inline" action="{{ route('kuis.destroy', $item->id_kuis) }}" method="POST" data-confirm="Arsipkan kuis ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-small">Arsip</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- VIEW 2: SEMUA KUIS TABEL LENGKAP --}}
    <div class="u-display-none" id="view-all">
        <section class="card">
            @if ($kuis->count() > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Tingkat</th>
                                <th>Soal</th>
                                <th>KKM</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kuis as $item)
                                <tr>
                                    <td><code>{{ $item->id_kuis }}</code></td>
                                    <td><strong>{{ $item->judul }}</strong></td>
                                    <td><span class="badge">{{ ucfirst($item->kategori) }}</span></td>
                                    <td>
                                        <span class="badge {{ $item->tingkat_kesulitan === 'mudah' ? 'badge-success' : ($item->tingkat_kesulitan === 'sedang' ? 'badge-warning' : 'badge-danger') }}">
                                            {{ ucfirst($item->tingkat_kesulitan ?? 'sedang') }}
                                        </span>
                                    </td>
                                    <td>{{ $item->soal->count() }}</td>
                                    <td>{{ $item->kkm ?? '-' }}</td>
                                    <td>{{ $item->alokasi_waktu ?? '-' }}m</td>
                                    <td>
                                        @if($item->status_publikasi === 'terbit')
                                            <span class="badge badge-success">Terbit</span>
                                        @else
                                            <span class="badge badge-muted">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="{{ route('kuis.show', $item->id_kuis) }}" class="btn btn-secondary btn-small">Lihat</a>
                                            @if ($item->status_publikasi === 'terbit')
                                                <a href="{{ route('kuis.edit', $item->id_kuis) }}" class="btn btn-secondary btn-small" data-alert="Kuis tersebut tidak dapat diedit karena berstatus terbit. Ubah ke status draft terlebih dahulu.">Edit</a>
                                            @else
                                                <a href="{{ route('kuis.edit', $item->id_kuis) }}" class="btn btn-secondary btn-small">Edit</a>
                                            @endif
                                            <form class="u-display-inline" action="{{ route('kuis.destroy', $item->id_kuis) }}" method="POST" data-confirm="Arsipkan kuis ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-small">Arsip</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty">
                    <h3>Belum ada kuis di mata pelajaran ini</h3>
                    <p>Klik tombol "Tambah Kuis" untuk membuat kuis baru.</p>
                </div>
            @endif
        </section>
    </div>
@endif

@push('scripts')
<script src="{{ asset('js/guru/kuis/index.js') }}" defer></script>
@endpush

@endsection
