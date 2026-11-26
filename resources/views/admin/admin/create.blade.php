@extends('layouts.admin')
@section('title', 'Tambah admin')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah admin</h1>
            <p>Admin baru dapat masuk menggunakan username dan wajib mengganti password awal.</p>
        </div>
        <x-tombol-kembali :fallback="route('admin.admin.index')" :dengan-ikon="false" />
    </div>

    <section class="card">
        <div class="card-header"><h2>Informasi akun</h2></div>
        <form action="{{ route('admin.admin.store') }}" method="POST">
            @csrf
            <div class="field">
                <label for="nama_lengkap">Nama lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" type="text" value="{{ old('nama_lengkap') }}" maxlength="100" required>
                @error('nama_lengkap')<small class="muted">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="username">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    minlength="4"
                    maxlength="30"
                    pattern="[a-z0-9_]{4,30}"
                    autocomplete="username"
                    required
                >
                <small class="muted">4–30 karakter; gunakan huruf kecil, angka, atau garis bawah.</small>
                @error('username')<small class="muted">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="email">Email <span class="muted">(opsional)</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="100" autocomplete="email">
                @error('email')<small class="muted">{{ $message }}</small>@enderror
            </div>
            <div class="actions">
                <button class="btn" type="submit">Simpan admin</button>
                <x-tombol-kembali :fallback="route('admin.admin.index')" label="Batal" :dengan-ikon="false" />
            </div>
        </form>
    </section>
@endsection
