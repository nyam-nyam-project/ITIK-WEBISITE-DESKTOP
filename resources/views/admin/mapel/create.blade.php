@extends('layouts.admin')
@section('title', 'Tambah mata pelajaran')
@section('content')
    <div class="page-heading"><div><h1>Tambah mata pelajaran</h1><p>Lengkapi identitas mata pelajaran untuk katalog sekolah.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.mapel.store') }}" method="POST">@csrf @include('admin.mapel._form')</form></section>
@endsection
