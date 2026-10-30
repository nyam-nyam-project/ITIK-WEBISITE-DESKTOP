@extends('layouts.admin')
@section('title', 'Mata pelajaran')
@section('content')
    <div class="page-heading"><div><h1>Mata pelajaran</h1><p>Kelola katalog mata pelajaran dan status ketersediaannya.</p></div><a class="btn" href="{{ route('admin.mapel.create') }}">Tambah mata pelajaran</a></div>
    <section class="card">
        <div class="card-header"><h2>Daftar mata pelajaran</h2><span class="badge">{{ $mapel->count() }} mata pelajaran</span></div>
        @if($mapel->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th scope="col">Kode</th><th scope="col">Mata pelajaran</th><th scope="col" class="number">Kelas</th><th scope="col" class="number">Materi</th><th scope="col" class="number">Kuis</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
                @foreach($mapel as $item)
                    <tr><td class="nowrap"><strong>{{ $item->kode_mapel }}</strong></td><td class="text-break"><strong>{{ $item->nama_mapel }}</strong>@if($item->deskripsi)<span class="muted small">{{ $item->deskripsi }}</span>@endif</td><td class="number">{{ $item->kelas_count }}</td><td class="number">{{ $item->materi_count }}</td><td class="number">{{ $item->kuis_count }}</td><td><span class="badge {{ $item->is_aktif ? 'badge-success' : 'badge-muted' }}">{{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}</span></td><td><a class="btn btn-secondary btn-small" href="{{ route('admin.mapel.edit', $item) }}" aria-label="Ubah mata pelajaran {{ $item->nama_mapel }}">Ubah</a></td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty"><h3>Belum ada mata pelajaran</h3><p>Tambahkan mata pelajaran agar dapat dipasangkan ke kelas.</p><a class="btn" href="{{ route('admin.mapel.create') }}">Tambah mata pelajaran</a></div>
        @endif
    </section>
    <div class="alert alert-info">Mata pelajaran tidak dihapus melalui halaman ini. Ubah status menjadi nonaktif jika tidak lagi digunakan; data materi dan kuis tetap tersimpan.</div>
@endsection
