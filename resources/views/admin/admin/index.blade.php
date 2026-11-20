@extends('layouts.admin')
@section('title', 'Akun admin')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Akun admin</h1>
            <p>Kelola akses administrator. Akun hanya dapat dinonaktifkan, bukan dihapus.</p>
        </div>
        <a class="btn" href="{{ route('admin.admin.create') }}">Tambah admin</a>
    </div>

    <section class="card">
        <form action="{{ route('admin.admin.index') }}" method="GET" class="filter-form">
            <div class="field">
                <label for="status">Status akun</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="field field-search">
                <label for="cari">Cari username atau nama</label>
                <input id="cari" name="cari" type="search" value="{{ request('cari') }}" placeholder="Username atau nama">
            </div>
            <button class="btn" type="submit">Tampilkan</button>
            <a class="btn btn-secondary" href="{{ route('admin.admin.index') }}">Reset</a>
        </form>
    </section>

    <div class="alert alert-info">
        Admin yang dibuat dari seeder ditampilkan sebagai <strong>Sistem</strong>. Nonaktifkan akun untuk mencabut akses tanpa menghapus riwayat.
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Daftar admin</h2>
            <span class="badge">{{ $admins->total() }} admin</span>
        </div>
        @if($admins->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Username</th>
                            <th scope="col">Nama lengkap</th>
                            <th scope="col">Status</th>
                            <th scope="col">Dibuat oleh</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $item)
                            <tr>
                                <td class="nowrap">{{ $item->nomor_induk ?? '—' }}</td>
                                <td class="text-break"><strong>{{ $item->nama_lengkap }}</strong></td>
                                <td>
                                    <span class="badge {{ $item->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">
                                        {{ $item->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>{{ $item->pembuatAkun?->nama_lengkap ?? 'Sistem' }}</td>
                                <td>
                                    @if($item->status === 'aktif' && $jumlahAdminAktif <= 1)
                                        <span class="muted small">Admin aktif terakhir</span>
                                    @elseif($item->status === 'aktif' && $item->id_user === auth()->id())
                                        <span class="muted small">Akun Anda</span>
                                    @else
                                        <form
                                            action="{{ route('admin.admin.toggle-status', $item) }}"
                                            method="POST"
                                            data-confirm="{{ $item->status === 'aktif' ? 'Nonaktifkan akun '.$item->nama_lengkap.'?' : 'Aktifkan kembali akun '.$item->nama_lengkap.'?' }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-small {{ $item->status === 'aktif' ? 'btn-warning' : '' }}" type="submit">
                                                {{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                <h3>Tidak ada admin yang ditampilkan</h3>
                <p>Ubah filter atau tambahkan akun admin.</p>
                <div class="actions u-justify-content-center">
                    <a class="btn btn-secondary" href="{{ route('admin.admin.index') }}">Reset filter</a>
                    <a class="btn" href="{{ route('admin.admin.create') }}">Tambah admin</a>
                </div>
            </div>
        @endif
        @include('admin.partials.pagination', ['paginator' => $admins])
    </section>
@endsection
