@extends('layouts.admin')
@section('title', 'Dasbor admin')
@section('content')
    <div class="page-heading">
        <div><h1>Dasbor admin</h1><p>Ringkasan akun, kelas, dan kesiapan pembelajaran sekolah.</p></div>
        <a class="btn btn-secondary" href="{{ route('admin.rekap.index') }}">Lihat rekap nilai</a>
    </div>
    @if($tahunAktif)
        <div class="alert alert-info"><strong>Tahun ajaran aktif: {{ $tahunAktif->nama }}</strong><p class="small">Ringkasan kelas dan siswa tanpa kelas menggunakan tahun ajaran ini.</p></div>
    @else
        <div class="alert alert-warning"><strong>Belum ada tahun ajaran aktif.</strong><p>Tambahkan atau aktifkan tahun ajaran agar ringkasan kelas dapat ditampilkan. <a href="{{ route('admin.tahun-ajaran.index') }}">Kelola tahun ajaran</a></p></div>
    @endif
    <div class="stats">
        <div class="stat"><span class="label">Siswa aktif</span><span class="value">{{ number_format($jumlahSiswaAktif, 0, ',', '.') }}</span><small>{{ $jumlahSiswaNonaktif }} akun nonaktif</small></div>
        <div class="stat"><span class="label">Guru aktif</span><span class="value">{{ number_format($jumlahGuruAktif, 0, ',', '.') }}</span><small>{{ $jumlahGuruNonaktif }} akun nonaktif</small></div>
        <div class="stat"><span class="label">Mata pelajaran aktif</span><span class="value">{{ $jumlahMapel }}</span><small><a href="{{ route('admin.mapel.index') }}">Kelola mata pelajaran</a></small></div>
        <div class="stat"><span class="label">Kelas tahun aktif</span><span class="value">{{ $kelasTahunAktif->count() }}</span><small>{{ $tahunAktif?->nama ?? 'Tahun aktif belum ditentukan' }}</small></div>
    </div>
    @if($tahunAktif && $siswaTanpaKelas > 0)
        <div class="alert alert-warning"><strong>{{ $siswaTanpaKelas }} siswa aktif belum memiliki kelas pada {{ $tahunAktif->nama }}.</strong><p>Tambahkan keanggotaan melalui halaman detail kelas. <a href="{{ route('admin.kelas.index', ['tahun_ajaran' => $tahunAktif->id_tahun_ajaran]) }}">Atur anggota kelas</a></p></div>
    @endif
    <section class="card">
        <div class="card-header"><div><h2>Kelas tahun ajaran aktif</h2><p class="muted small">Jumlah anggota mencakup keanggotaan historis, termasuk akun nonaktif.</p></div><a class="btn btn-secondary btn-small" href="{{ route('admin.kelas.create') }}">Tambah kelas</a></div>
        @if($kelasTahunAktif->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th scope="col">Kelas</th><th scope="col">Tingkat</th><th scope="col">Status</th><th scope="col" class="number">Anggota</th><th scope="col">Tindakan</th></tr></thead><tbody>
                @foreach($kelasTahunAktif as $item)
                    <tr><td><strong>{{ $item->nama_kelas }}</strong></td><td>{{ $item->tingkat }}</td><td><span class="badge {{ $item->is_aktif ? 'badge-success' : 'badge-muted' }}">{{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}</span></td><td class="number">{{ $item->anggota_count }}</td><td><a href="{{ route('admin.kelas.show', $item) }}" class="btn btn-secondary btn-small" aria-label="Lihat kelas {{ $item->nama_kelas }}">Lihat anggota</a></td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty"><h3>Belum ada kelas yang ditampilkan</h3><p>{{ $tahunAktif ? 'Buat kelas untuk tahun ajaran aktif, lalu tambahkan siswa dan mata pelajarannya.' : 'Aktifkan tahun ajaran terlebih dahulu untuk melihat kelasnya.' }}</p></div>
        @endif
    </section>
    <section class="card"><h2>Akses cepat</h2><p class="muted">Siapkan data akademik, kemudian kelola akun dan keanggotaan kelas.</p><div class="actions"><a class="btn" href="{{ route('admin.siswa.create') }}">Tambah siswa</a><a class="btn btn-secondary" href="{{ route('admin.guru.create') }}">Tambah guru</a><a class="btn btn-secondary" href="{{ route('admin.tahun-ajaran.index') }}">Tahun ajaran</a><a class="btn btn-secondary" href="{{ route('admin.mapel.index') }}">Mata pelajaran</a></div></section>
@endsection
