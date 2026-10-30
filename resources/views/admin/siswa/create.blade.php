@extends('layouts.admin')
@section('title', 'Tambah siswa')
@section('content')
    <div class="page-heading"><div><h1>Tambah siswa</h1><p>Buat akun siswa dengan NISN, email login, dan kata sandi sementara.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.siswa.store') }}" method="POST">@csrf @include('admin.siswa._form')</form></section>
@endsection
