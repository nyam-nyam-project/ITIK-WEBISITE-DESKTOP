@extends('layouts.siswa')
@section('title', 'Tugas Remedial')

@section('content')

<div class="page-heading">
    <div>
        <h1>Tugas Remedial</h1>
        <p>Perbaiki nilai kuis yang belum mencapai KKM.</p>
    </div>
    <a href="{{ route('siswa.kuis.index') }}" class="btn btn-secondary">
        ← Ke Daftar Kuis
    </a>
</div>

@include('siswa.remedial._tabel')

@endsection
