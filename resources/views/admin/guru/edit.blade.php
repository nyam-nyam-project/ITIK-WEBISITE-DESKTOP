@extends('layouts.admin')
@section('title', 'Ubah guru')
@section('content')
    <div class="page-heading"><div><h1>Ubah guru</h1><p>Perbarui identitas, status, atau kata sandi akun {{ $guru->nama_lengkap }}.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.guru.update', $guru) }}" method="POST">@csrf @method('PUT') @include('admin.guru._form')</form></section>
@endsection
