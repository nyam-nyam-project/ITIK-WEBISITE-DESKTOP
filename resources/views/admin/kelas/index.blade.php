@extends('layouts.admin')
@section('title', 'Kelas')
@section('content')
    <div class="page-heading"><div><h1>Kelas</h1><p>Atur kelas, mata pelajaran, dan keanggotaan siswa per tahun ajaran.</p></div><a class="btn" href="{{ route('admin.kelas.create') }}">Tambah kelas</a></div>
    <section class="card"><form action="{{ route('admin.kelas.index') }}" method="GET" class="filter-form"><div class="field"><label for="tahun_ajaran">Tahun ajaran</label><select id="tahun_ajaran" name="tahun_ajaran"><option value="">Tahun aktif / semua jika belum ada</option>@foreach($daftarTahun as $tahun)<option value="{{ $tahun->id_tahun_ajaran }}" @selected($idTahun == $tahun->id_tahun_ajaran)>{{ $tahun->nama }}{{ $tahun->is_aktif ? ' (aktif)' : '' }}</option>@endforeach</select></div><button class="btn" type="submit">Tampilkan</button><a class="btn btn-secondary" href="{{ route('admin.kelas.index') }}">Reset</a></form></section>
    <section class="card">
        <div class="card-header"><h2>Daftar kelas</h2><span class="badge">{{ $kelas->count() }} kelas</span></div>
        @if($kelas->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th scope="col">Kelas</th><th scope="col">Tahun ajaran</th><th scope="col">Mata pelajaran</th><th scope="col" class="number">Anggota</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
                @foreach($kelas as $item)
                    <tr><td><strong>{{ $item->nama_kelas }}</strong><span class="muted small">Tingkat {{ $item->tingkat }}</span></td><td class="nowrap">{{ $item->tahunAjaran?->nama ?? '—' }}</td><td><div class="tags">@forelse($item->mapel as $pelajaran)<span class="badge {{ $pelajaran->is_aktif ? '' : 'badge-muted' }}">{{ $pelajaran->nama_mapel }}{{ $pelajaran->is_aktif ? '' : ' (nonaktif)' }}</span>@empty<span class="muted">Belum diatur</span>@endforelse</div></td><td class="number">{{ $item->anggota_count }}</td><td><span class="badge {{ $item->is_aktif ? 'badge-success' : 'badge-muted' }}">{{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}</span></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="{{ route('admin.kelas.show', $item) }}" aria-label="Lihat anggota kelas {{ $item->nama_kelas }}">Anggota</a><a class="btn btn-secondary btn-small" href="{{ route('admin.kelas.edit', $item) }}" aria-label="Ubah kelas {{ $item->nama_kelas }}">Ubah</a></div></td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty"><h3>Belum ada kelas pada pilihan ini</h3><p>Pilih tahun ajaran lain atau tambahkan kelas baru. Tahun ajaran harus tersedia sebelum kelas dibuat.</p><a class="btn" href="{{ route('admin.kelas.create') }}">Tambah kelas</a></div>
        @endif
    </section>
@endsection
