@extends('layouts.guru')
@section('title', 'Konfirmasi Hapus Permanen Kuis')

@section('content')

<div class="page-heading">
    <div>
        <x-tombol-kembali :fallback="route('guru.arsip.index', ['tab' => 'kuis'])" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px" />
        <h1>Konfirmasi Hapus Permanen Kuis</h1>
        <p>Tinjau dampak penghapusan nilai dan riwayat siswa sebelum melanjutkan.</p>
    </div>
</div>

<section class="card">
    <div class="card-header">
        <div>
            <h2>{{ $kuis->judul }}</h2>
            <p class="muted">{{ $kuis->mapel?->nama_mapel ?? '-' }} &bull; {{ ucfirst($kuis->kategori) }}</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <span class="label">Siswa Terdampak</span>
            <span class="value">{{ $jumlahSiswaTerdampak }} siswa</span>
        </div>
        <div class="stat">
            <span class="label">Riwayat Pengerjaan</span>
            <span class="value">{{ $jumlahRiwayat }} riwayat</span>
        </div>
    </div>

    <div class="alert alert-danger u-margin-top-14px">
        <strong>Peringatan Penghapusan Permanen:</strong>
        Menghapus kuis ini akan menghapus secara permanen seluruh <strong>nilai</strong>, <strong>sesi pengerjaan</strong>, dan <strong>rincian jawaban siswa</strong> (termasuk draf jawaban serta nilai essay) pada kuis ini. Data yang dihapus tidak dapat dipulihkan kembali.
    </div>

    <form action="{{ route('guru.arsip.kuis.hapus-permanen', $kuis->id_kuis) }}" method="POST" class="u-margin-top-20px">
        @csrf
        @method('DELETE')

        <label class="check">
            <input type="checkbox" name="konfirmasi_hapus_nilai" value="1" required>
            <span>Saya memahami dan menyetujui bahwa seluruh nilai, sesi pengerjaan, dan rincian jawaban siswa pada kuis ini akan dihapus permanen.</span>
        </label>

        @error('konfirmasi_hapus_nilai')
            <p class="field-error">{{ $message }}</p>
        @enderror

        <div class="form-actions actions u-margin-top-20px">
            <button type="submit" class="btn btn-danger">
                @include('partials.icon', ['name' => 'trash']) Ya, Hapus Permanen Kuis &amp; Nilai
            </button>
            <a href="{{ route('guru.arsip.index', ['tab' => 'kuis']) }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</section>

@endsection
