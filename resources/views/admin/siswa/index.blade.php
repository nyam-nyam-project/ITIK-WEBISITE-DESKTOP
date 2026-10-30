@extends('layouts.admin')
@section('title', 'Siswa')
@section('content')
    <div class="page-heading"><div><h1>Siswa</h1><p>Kelola identitas dan status akun siswa tanpa menghapus riwayat nilainya.</p></div><a class="btn" href="{{ route('admin.siswa.create') }}">Tambah siswa</a></div>
    <section class="card">
        <form action="{{ route('admin.siswa.index') }}" method="GET" class="filter-form">
            <div class="field"><label for="tahun_ajaran">Tahun ajaran kelas</label><select id="tahun_ajaran" name="tahun_ajaran"><option value="">Tahun aktif / semua jika belum ada</option>@foreach($daftarTahun as $tahun)<option value="{{ $tahun->id_tahun_ajaran }}" @selected($idTahun == $tahun->id_tahun_ajaran)>{{ $tahun->nama }}{{ $tahun->is_aktif ? ' (aktif)' : '' }}</option>@endforeach</select></div>
            <div class="field"><label for="filter-kelas">Kelas</label><select id="filter-kelas" name="kelas"><option value="">Semua kelas</option>@foreach($daftarKelas as $kelasPilihan)<option value="{{ $kelasPilihan->id_kelas }}" @selected(request('kelas') == $kelasPilihan->id_kelas)>{{ $kelasPilihan->nama_kelas }}</option>@endforeach</select></div>
            <div class="field"><label for="status">Status akun</label><select id="status" name="status"><option value="">Semua status</option><option value="aktif" @selected(request('status') === 'aktif')>Aktif</option><option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option></select></div>
            <div class="field field-search"><label for="cari">Cari nama atau NISN</label><input id="cari" name="cari" type="search" value="{{ request('cari') }}" placeholder="Nama atau NISN"></div>
            <button class="btn" type="submit">Tampilkan</button><a class="btn btn-secondary" href="{{ route('admin.siswa.index') }}">Reset</a>
        </form>
        <p class="help">Tahun ajaran menentukan daftar dan tampilan kelas, bukan tahun pembuatan akun. Pilih kelas untuk membatasi hasil ke anggotanya.</p>
    </section>
    <div class="alert alert-info">Siswa keluar sekolah ditandai <strong>nonaktif</strong>, bukan dihapus. Nilai dan keanggotaan historisnya tetap tersimpan. Pengaturan anggota dilakukan di <a href="{{ route('admin.kelas.index', ['tahun_ajaran' => $idTahun]) }}">halaman kelas</a>.</div>
    <section class="card">
        <div class="card-header"><h2>Daftar siswa</h2><span class="badge">{{ $siswa->total() }} siswa</span></div>
        @if($siswa->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th scope="col">NISN</th><th scope="col">Nama dan email</th><th scope="col">Kelas tahun pilihan</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
                @foreach($siswa as $item)
                    <tr><td class="nowrap">{{ $item->nomor_induk ?? '—' }}</td><td class="text-break"><strong>{{ $item->nama_lengkap }}</strong><span class="muted small">{{ $item->email ?? 'Email belum diisi' }}</span></td><td><div class="tags">@forelse($item->kelasDiikuti as $kelasSiswa)<a class="badge" href="{{ route('admin.kelas.show', $kelasSiswa) }}">{{ $kelasSiswa->nama_kelas }}</a>@empty<span class="muted small">Belum memiliki kelas</span>@endforelse</div></td><td><span class="badge {{ $item->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">{{ $item->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td><div class="actions"><a class="btn btn-secondary btn-small" href="{{ route('admin.siswa.edit', $item) }}" aria-label="Ubah siswa {{ $item->nama_lengkap }}">Ubah</a>
                        <form action="{{ route($item->status === 'aktif' ? 'admin.siswa.nonaktifkan' : 'admin.siswa.aktifkan', $item) }}" method="POST" data-confirm="{{ $item->status === 'aktif' ? 'Nonaktifkan akun '.$item->nama_lengkap.'? Nilai dan keanggotaan historis tetap disimpan.' : 'Aktifkan kembali akun '.$item->nama_lengkap.'?' }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-small {{ $item->status === 'aktif' ? 'btn-warning' : '' }}" aria-label="{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} {{ $item->nama_lengkap }}">{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div></td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty"><h3>Tidak ada siswa yang ditampilkan</h3><p>Ubah filter atau kata pencarian. Jika belum ada akun siswa, tambahkan siswa pertama.</p><div class="actions" style="justify-content:center"><a class="btn btn-secondary" href="{{ route('admin.siswa.index') }}">Reset filter</a><a class="btn" href="{{ route('admin.siswa.create') }}">Tambah siswa</a></div></div>
        @endif
        @include('admin.partials.pagination', ['paginator' => $siswa])
    </section>
@endsection
@push('scripts')
<script>
    document.getElementById('tahun_ajaran').addEventListener('change', function () {
        document.getElementById('filter-kelas').value = '';
        this.form.submit();
    });
</script>
@endpush
