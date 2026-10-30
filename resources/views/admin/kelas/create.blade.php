@extends('layouts.admin')
@section('title', 'Tambah kelas')
@section('content')
    <div class="page-heading"><div><h1>Tambah kelas</h1><p>Buat kelas pada tahun ajaran yang sesuai, lalu tambahkan anggota setelah disimpan.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.kelas.store') }}" method="POST">@csrf @include('admin.kelas._form')</form></section>
@endsection
