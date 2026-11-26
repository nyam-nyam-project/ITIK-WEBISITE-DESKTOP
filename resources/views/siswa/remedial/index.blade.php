@extends('layouts.siswa')
@section('title', 'Tugas Remedial')

@section('content')

<div class="page-heading">
    <div>
        <h1>Tugas Remedial</h1>
        <p>Perbaiki nilai kuis yang belum mencapai KKM.</p>
    </div>
    <x-tombol-kembali :fallback="route('siswa.kuis.index')" label="Ke Daftar Kuis" />
</div>

@include('siswa.remedial._tabel')

@endsection
