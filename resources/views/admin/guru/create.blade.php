@extends('layouts.admin')
@section('title', 'Tambah guru')
@section('content')
    <div class="page-heading"><div><h1>Tambah guru</h1><p>Buat akun pengajar dengan NIP, email login, dan kata sandi sementara.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.guru.store') }}" method="POST">@csrf @include('admin.guru._form')</form></section>
@endsection
