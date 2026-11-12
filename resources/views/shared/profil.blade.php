@extends('layouts.app')
@section('title', 'Profil Saya')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shared/profil.css') }}">
@endpush

@section('content')
@php($pengguna = auth()->user())

<div class="page-heading">
    <div>
        <h1>Profil Saya</h1>
        <p>Informasi akun dan pengaturan keamanan.</p>
    </div>
</div>

<section class="card form-card">
    <div class="card-header">
        <div>
            <h2>{{ $pengguna?->nama_lengkap }}</h2>
            <p class="muted">Data akun yang sedang aktif digunakan.</p>
        </div>
        <span class="badge">{{ ucfirst($pengguna?->role ?? 'Pengguna') }}</span>
    </div>

    <dl class="identity">
        <div>
            <dt>Nomor Induk (NISN / NIP)</dt>
            <dd>{{ $pengguna?->nomor_induk ?? '—' }}</dd>
        </div>
        <div>
            <dt>Nama Lengkap</dt>
            <dd>{{ $pengguna?->nama_lengkap ?? '—' }}</dd>
        </div>
        <div>
            <dt>Peran</dt>
            <dd>{{ ucfirst($pengguna?->role ?? '—') }}</dd>
        </div>
        <div>
            <dt>Kelas</dt>
            <dd>{{ $pengguna?->kelas ?? '—' }}</dd>
        </div>
        <div>
            <dt>Status Akun</dt>
            <dd>{{ ucfirst($pengguna?->status ?? 'Aktif') }}</dd>
        </div>
    </dl>

    <div class="form-actions actions">
        <button type="button" class="btn" id="buka-password">
            🔑 Ganti Password
        </button>
        <a class="btn btn-secondary" href="{{ $pengguna?->role === 'siswa' ? route('siswa.dashboard') : route('dashboard') }}">
            ← Kembali
        </a>
    </div>
</section>

{{-- MODAL GANTI PASSWORD --}}
<div id="modalPassword" class="modal-overlay" data-password-errors="{{ ($errors->has('password_lama') || $errors->has('password_baru')) ? '1' : '0' }}">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Ganti Password</h2>
            <button type="button" class="btn-close" data-close-password>&times;</button>
        </div>

        @if ($errors->has('password_lama') || $errors->has('password_baru'))
            <div class="alert alert-error">
                {{ $errors->first('password_lama') ?: $errors->first('password_baru') }}
            </div>
        @endif

        <form action="{{ route('profil.password') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="password_lama">Password Saat Ini</label>
                <input
                    type="password"
                    id="password_lama"
                    name="password_lama"
                    placeholder="Masukkan password saat ini"
                    required
                >
            </div>

            <div class="field">
                <label for="password_baru">Password Baru</label>
                <input
                    type="password"
                    id="password_baru"
                    name="password_baru"
                    placeholder="Minimal 6 karakter"
                    required
                >
            </div>

            <div class="field">
                <label for="password_baru_confirmation">Konfirmasi Password Baru</label>
                <input
                    type="password"
                    id="password_baru_confirmation"
                    name="password_baru_confirmation"
                    placeholder="Ulangi password baru"
                    required
                >
            </div>

            <div class="actions u-margin-top-20px">
                <button type="submit" class="btn">
                    Simpan Password
                </button>
                <button type="button" class="btn btn-secondary" data-close-password>
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script defer src="{{ asset('js/shared/profil.js') }}"></script>
@endpush

@endsection
