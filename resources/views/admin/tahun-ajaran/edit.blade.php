@extends('layouts.admin')
@section('title', 'Ubah tahun ajaran')
@section('content')
    <div class="page-heading"><div><h1>Ubah tahun ajaran</h1><p>Perbarui nama atau tanggal periode {{ $tahunAjaran->nama }}.</p></div></div>
    <section class="card form-card"><form action="{{ route('admin.tahun-ajaran.update', $tahunAjaran) }}" method="POST">@csrf @method('PUT') @include('admin.tahun-ajaran._form')</form></section>
@endsection
