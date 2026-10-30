@extends('layouts.admin')
@section('title', 'Guru')
@section('content')
    <div class="page-heading"><div><h1>Guru</h1><p>Kelola akun pengajar tanpa menghapus materi dan kuis yang dimilikinya.</p></div><a class="btn" href="{{ route('admin.guru.create') }}">Tambah guru</a></div>
    <section class="card"><form action="{{ route('admin.guru.index') }}" method="GET" class="filter-form"><div class="field"><label for="status">Status akun</label><select id="status" name="status"><option value="">Semua status</option><option value="aktif" @selected(request('status') === 'aktif')>Aktif</option><option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option></select></div><div class="field field-search"><label for="cari">Cari nama atau NIP</label><input id="cari" name="cari" type="search" value="{{ request('cari') }}" placeholder="Nama atau NIP"></div><button class="btn" type="submit">Tampilkan</button><a class="btn btn-secondary" href="{{ route('admin.guru.index') }}">Reset</a></form></section>
    <div class="alert alert-info">Guru yang keluar ditandai <strong>nonaktif</strong>. Akun tidak dihapus sehingga kepemilikan materi, kuis, dan riwayat pembelajaran tetap tersimpan.</div>
    <section class="card">
        <div class="card-header"><h2>Daftar guru</h2><span class="badge">{{ $guru->total() }} guru</span></div>
        @if($guru->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th scope="col">NIP</th><th scope="col">Nama dan email</th><th scope="col" class="number">Materi</th><th scope="col" class="number">Kuis</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
                @foreach($guru as $item)
                    <tr><td class="nowrap">{{ $item->nomor_induk ?? '—' }}</td><td class="text-break"><strong>{{ $item->nama_lengkap }}</strong><span class="muted small">{{ $item->email ?? 'Email belum diisi' }}</span></td><td class="number">{{ $item->materi_count }}</td><td class="number">{{ $item->kuis_count }}</td><td><span class="badge {{ $item->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">{{ $item->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="{{ route('admin.guru.edit', $item) }}" aria-label="Ubah guru {{ $item->nama_lengkap }}">Ubah</a>
                        <form action="{{ route($item->status === 'aktif' ? 'admin.guru.nonaktifkan' : 'admin.guru.aktifkan', $item) }}" method="POST" data-confirm="{{ $item->status === 'aktif' ? 'Nonaktifkan akun '.$item->nama_lengkap.'? Materi dan kuis tetap disimpan.' : 'Aktifkan kembali akun '.$item->nama_lengkap.'?' }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-small {{ $item->status === 'aktif' ? 'btn-warning' : '' }}" type="submit" aria-label="{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} {{ $item->nama_lengkap }}">{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div></td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty"><h3>Tidak ada guru yang ditampilkan</h3><p>Ubah status atau kata pencarian. Jika belum ada akun pengajar, tambahkan guru pertama.</p><div class="actions" style="justify-content:center"><a class="btn btn-secondary" href="{{ route('admin.guru.index') }}">Reset filter</a><a class="btn" href="{{ route('admin.guru.create') }}">Tambah guru</a></div></div>
        @endif
        @include('admin.partials.pagination', ['paginator' => $guru])
    </section>
@endsection
