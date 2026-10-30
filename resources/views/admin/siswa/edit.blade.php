@extends('layouts.admin')
@section('title', 'Ubah siswa')
@section('content')
    <div class="page-heading"><div><h1>Ubah siswa</h1><p>Perbarui identitas, status, atau kata sandi akun {{ $siswa->nama_lengkap }}.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.siswa.update', $siswa) }}" method="POST">@csrf @method('PUT') @include('admin.siswa._form')</form></section>
@endsection
