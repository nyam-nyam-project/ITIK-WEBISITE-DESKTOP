@extends('layouts.admin')
@section('title', 'Ubah kelas')
@section('content')
    <div class="page-heading"><div><h1>Ubah kelas</h1><p>Perbarui {{ $kelas->nama_kelas }} tanpa memindahkan tahun ajaran atau riwayat anggotanya.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.kelas.update', $kelas) }}" method="POST">@csrf @method('PUT') @include('admin.kelas._form')</form></section>
@endsection
