@extends('layouts.admin')
@section('title', 'Profil saya')
@section('content')
    @php($pengguna = auth()->user())
    <div class="page-heading"><div><h1>Profil saya</h1><p>Identitas akun administrator yang sedang masuk.</p></div></div>
    <section class="card form-card">
        <div class="card-header"><div><h2>{{ $pengguna?->nama_lengkap ?? 'Administrator' }}</h2><p class="muted">Informasi akun hanya dapat dilihat pada halaman ini.</p></div><span class="badge">{{ ucfirst($pengguna?->role ?? 'admin') }}</span></div>
        <dl class="identity">
            <div><dt>ID pengguna</dt><dd>{{ $pengguna?->id_user ?? '—' }}</dd></div>
            <div><dt>Nomor induk</dt><dd>{{ $pengguna?->nomor_induk ?? '—' }}</dd></div>
            <div><dt>Nama lengkap</dt><dd>{{ $pengguna?->nama_lengkap ?? '—' }}</dd></div>
            <div><dt>Email login</dt><dd>{{ $pengguna?->email ?? 'Belum diisi' }}</dd></div>
            <div><dt>Peran</dt><dd>{{ ucfirst($pengguna?->role ?? 'admin') }}</dd></div>
            <div><dt>Status akun</dt><dd>{{ ucfirst($pengguna?->status ?? '—') }}</dd></div>
        </dl>
        <div class="form-actions"><a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Kembali ke dasbor</a></div>
    </section>
    <div class="alert alert-info">Identitas dan kata sandi tidak dapat diubah dari halaman profil admin. Kata sandi tidak ditampilkan untuk menjaga keamanan akun.</div>
@endsection
