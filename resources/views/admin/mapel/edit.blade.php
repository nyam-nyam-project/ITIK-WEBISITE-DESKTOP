@extends('layouts.admin')
@section('title', 'Ubah mata pelajaran')
@section('content')
    <div class="page-heading"><div><h1>Ubah mata pelajaran</h1><p>Perbarui data {{ $mapel->nama_mapel }} atau ubah status ketersediaannya.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.mapel.update', $mapel) }}" method="POST">@csrf @method('PUT') @include('admin.mapel._form')</form></section>
@endsection
