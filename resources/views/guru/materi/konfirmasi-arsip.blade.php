@extends('layouts.guru')
@section('title', 'Konfirmasi Arsip Materi')

@section('content')

<div class="page-heading">
    <div>
        <x-tombol-kembali :fallback="route('materi.index', ['mapel' => $materi->id_mapel])" class="btn btn-secondary u-display-inline-block u-margin-bottom-10px" />
        <h1>Konfirmasi Arsip Materi</h1>
        <p>Periksa dampak pengarsipan sebelum melanjutkan.</p>
    </div>
</div>

<section class="card">
    <div class="card-header">
        <div>
            <h2>{{ $materi->judul }}</h2>
            <p class="muted">{{ $materi->mapel->nama_mapel ?? '-' }} &bull; {{ $materi->bab ?? 'Tanpa Bab' }}</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <span class="label">Kuis Ikut Diarsipkan</span>
            <span class="value">{{ $kuisIkut->count() }}</span>
        </div>
        <div class="stat">
            <span class="label">Siswa Kehilangan Akses</span>
            <span class="value">{{ $jumlahSiswaTerdampak }}</span>
        </div>
    </div>

    <p class="muted u-margin-top-14px">
        Mengarsipkan materi ini akan menyembunyikan materi dari <strong>{{ $jumlahSiswaTerdampak }} siswa</strong> aktif dan ikut mengarsipkan <strong>{{ $kuisIkut->count() }} kuis</strong> terkait. Data nilai dan riwayat belajar siswa tetap aman dan tidak dihapus.
    </p>

    @if ($kuisIkut->isNotEmpty())
        <div class="u-margin-top-14px">
            <h3>Daftar Kuis yang Ikut Diarsipkan</h3>
            <ul>
                @foreach ($kuisIkut as $kuis)
                    <li><strong>{{ $kuis->judul }}</strong> ({{ ucfirst($kuis->kategori) }})</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('materi.destroy', $materi->id_materi) }}" method="POST" class="form-actions actions u-margin-top-20px">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">
            @include('partials.icon', ['name' => 'archive']) Ya, Arsipkan Materi
        </button>
        <x-tombol-kembali :fallback="route('materi.index', ['mapel' => $materi->id_mapel])" label="Batal" :dengan-ikon="false" />
    </form>
</section>

@endsection
