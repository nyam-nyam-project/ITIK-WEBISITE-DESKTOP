@extends('layouts.admin')
@section('title', 'Tahun ajaran')
@section('content')
    <div class="page-heading"><div><h1>Tahun ajaran</h1><p>Kelola periode akademik. Hanya satu tahun ajaran dapat aktif pada satu waktu.</p></div><a class="btn" href="{{ route('admin.tahun-ajaran.create') }}">Tambah tahun ajaran</a></div>
    <div class="alert alert-info">Mengaktifkan tahun ajaran akan menonaktifkan tahun aktif sebelumnya, tanpa menghapus kelas atau riwayat nilainya.</div>
    <section class="card">
        <div class="card-header"><h2>Daftar tahun ajaran</h2><span class="badge">{{ $tahunAjaran->count() }} periode</span></div>
        @if($tahunAjaran->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th scope="col">Tahun ajaran</th><th scope="col">Tanggal mulai</th><th scope="col">Tanggal selesai</th><th scope="col" class="number">Kelas</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
                @foreach($tahunAjaran as $item)
                    <tr>
                        <td><strong>{{ $item->nama }}</strong><span class="muted small">{{ $item->id_tahun_ajaran }}</span></td>
                        <td class="nowrap">{{ $item->tanggal_mulai?->format('d/m/Y') ?? '—' }}</td><td class="nowrap">{{ $item->tanggal_selesai?->format('d/m/Y') ?? '—' }}</td>
                        <td class="number"><a href="{{ route('admin.kelas.index', ['tahun_ajaran' => $item->id_tahun_ajaran]) }}">{{ $item->kelas_count }}</a></td>
                        <td><span class="badge {{ $item->is_aktif ? 'badge-success' : 'badge-muted' }}">{{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td><div class="actions"><a class="btn btn-secondary btn-small" href="{{ route('admin.tahun-ajaran.edit', $item) }}" aria-label="Ubah tahun ajaran {{ $item->nama }}">Ubah</a>
                            @unless($item->is_aktif)
                                <form action="{{ route('admin.tahun-ajaran.aktifkan', $item) }}" method="POST" data-confirm="Aktifkan tahun ajaran {{ $item->nama }}? Tahun aktif sebelumnya akan dinonaktifkan.">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-small" type="submit" aria-label="Aktifkan tahun ajaran {{ $item->nama }}">Aktifkan</button>
                                </form>
                            @endunless
                        </div></td>
                    </tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty"><h3>Belum ada tahun ajaran</h3><p>Tambahkan periode akademik pertama, kemudian gunakan tombol Aktifkan untuk menjadikannya tahun berjalan.</p><a class="btn" href="{{ route('admin.tahun-ajaran.create') }}">Tambah tahun ajaran</a></div>
        @endif
    </section>
@endsection
