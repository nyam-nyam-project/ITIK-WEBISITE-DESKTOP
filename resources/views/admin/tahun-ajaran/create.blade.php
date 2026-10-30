@extends('layouts.admin')
@section('title', 'Tambah tahun ajaran')
@section('content')
    <div class="page-heading"><div><h1>Tambah tahun ajaran</h1><p>Siapkan periode akademik baru tanpa mengubah riwayat tahun sebelumnya.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.tahun-ajaran.store') }}" method="POST">@csrf @include('admin.tahun-ajaran._form')</form></section>
@endsection
