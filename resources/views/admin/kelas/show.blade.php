@extends('layouts.admin')
@section('title', 'Detail kelas')
@section('content')
    <div class="page-heading"><div><h1>{{ $kelas->nama_kelas }}</h1><p>Tingkat {{ $kelas->tingkat }} · Tahun ajaran {{ $kelas->tahunAjaran?->nama ?? '—' }} · {{ $kelas->is_aktif ? 'Kelas aktif' : 'Kelas nonaktif' }}</p></div><div class="actions"><a class="btn btn-secondary" href="{{ route('admin.kelas.index', ['tahun_ajaran' => $kelas->id_tahun_ajaran]) }}">Kembali</a><a class="btn" href="{{ route('admin.kelas.edit', $kelas) }}">Ubah kelas</a></div></div>
    <div class="alert alert-info"><strong>Keanggotaan historis tetap disimpan.</strong><p>Tidak ada tindakan mengeluarkan anggota karena hal itu dapat menghilangkan siswa dari rekap kelas lama. Jika siswa keluar sekolah, nonaktifkan akunnya melalui halaman siswa. Untuk tahun ajaran berikutnya, buat kelas dan keanggotaan baru.</p></div>
    <section class="card"><h2>Mata pelajaran kelas</h2><div class="tags">@forelse($kelas->mapel as $pelajaran)<span class="badge {{ $pelajaran->is_aktif ? '' : 'badge-muted' }}">{{ $pelajaran->nama_mapel }}{{ $pelajaran->is_aktif ? '' : ' (nonaktif)' }}</span>@empty<p class="muted">Belum ada mata pelajaran. Gunakan Ubah kelas untuk mengaturnya.</p>@endforelse</div></section>
    <section class="card">
        <div class="card-header"><div><h2>Anggota kelas</h2><p class="muted small">Termasuk akun nonaktif untuk menjaga rekap historis.</p></div><span class="badge">{{ $anggota->count() }} siswa</span></div>
        @if($anggota->isNotEmpty())
            <form action="{{ route('admin.kelas.pindah-anggota', $kelas) }}" method="POST" id="form-pindah-anggota">
                @csrf
                <div class="table-wrap"><table><thead><tr><th scope="col"><input type="checkbox" id="check-semua-anggota" title="Pilih semua siswa" aria-label="Pilih semua siswa"></th><th scope="col">No.</th><th scope="col">NISN</th><th scope="col">Nama siswa</th><th scope="col">Status akun</th><th scope="col">Tindakan</th></tr></thead><tbody>
                    @foreach($anggota as $siswaAnggota)
                        <tr><td><input type="checkbox" name="id_user[]" value="{{ $siswaAnggota->id_user }}" class="check-siswa" aria-label="Pilih {{ $siswaAnggota->nama_lengkap }}" @checked(in_array($siswaAnggota->id_user, (array) old('id_user', []), true))></td><td>{{ $loop->iteration }}</td><td class="nowrap">{{ $siswaAnggota->nomor_induk ?? '—' }}</td><td class="text-break"><strong>{{ $siswaAnggota->nama_lengkap }}</strong><span class="muted small">{{ $siswaAnggota->email ?? 'Email belum diisi' }}</span></td><td><span class="badge {{ $siswaAnggota->status === 'aktif' ? 'badge-success' : 'badge-muted' }}">{{ $siswaAnggota->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td><td><a class="btn btn-secondary btn-small" href="{{ route('admin.siswa.edit', $siswaAnggota) }}" aria-label="Ubah akun {{ $siswaAnggota->nama_lengkap }}">Ubah akun</a></td></tr>
                    @endforeach
                </tbody></table></div>
                @error('id_user')<p class="error">{{ $message }}</p>@enderror
                @error('id_kelas_tujuan')<p class="error">{{ $message }}</p>@enderror
                @if($daftarKelasTujuan->isNotEmpty())
                    <div class="form-actions">
                        <div class="field u-margin-bottom-0">
                            <label for="id_kelas_tujuan">Pindahkan anggota terpilih ke kelas lain:</label>
                            <select id="id_kelas_tujuan" name="id_kelas_tujuan" required>
                                <option value="">-- Pilih Kelas Tujuan --</option>
                                @foreach($daftarKelasTujuan as $kt)
                                    <option value="{{ $kt->id_kelas }}" @selected(old('id_kelas_tujuan') === $kt->id_kelas)>
                                        {{ $kt->nama_kelas }} (Tingkat {{ $kt->tingkat }}{{ $kt->is_aktif ? '' : ' - nonaktif' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-secondary" type="submit" id="btn-submit-pindah">
                            Pindahkan siswa terpilih
                        </button>
                        <p class="muted small" id="jumlah-siswa-pindah" aria-live="polite">0 siswa dipilih</p>
                    </div>
                @else
                    <p class="muted small u-margin-top-14px">Tidak ada kelas tujuan lain di tahun ajaran ini untuk memindahkan siswa.</p>
                @endif
            </form>
        @else
            <div class="empty"><h3>Belum ada anggota kelas</h3><p>Pilih siswa tersedia pada formulir di bawah untuk menambah anggota pertama.</p></div>
        @endif
    </section>
    <section class="card">
        <div class="card-header"><div><h2>Tambah anggota</h2><p class="muted small">Pilih satu atau lebih siswa aktif. Anggota yang sudah tersimpan tidak akan dihapus.</p></div><span class="badge">{{ $siswaTersedia->count() }} siswa tersedia</span></div>
        @if($siswaTersedia->isNotEmpty())
            <form action="{{ route('admin.kelas.anggota.store', $kelas) }}" method="POST" id="anggota-form">
                @csrf
                <div class="field" id="pencarian-anggota" hidden><label for="cari-anggota">Cari siswa tersedia</label><input id="cari-anggota" type="search" placeholder="Nama atau NISN" autocomplete="off"><p class="help">Pencarian hanya menyaring daftar; pilihan yang sudah dicentang tetap tersimpan.</p></div>
                <fieldset><legend>Siswa yang akan ditambahkan *</legend><div class="check-grid check-list" id="daftar-anggota">
                    @foreach($siswaTersedia as $calon)
                        <label class="check" for="anggota-{{ $calon->id_user }}"><input id="anggota-{{ $calon->id_user }}" type="checkbox" name="id_user[]" value="{{ $calon->id_user }}" @checked(in_array($calon->id_user, (array) old('id_user', []), true))><span>{{ $calon->nama_lengkap }}<small>NISN {{ $calon->nomor_induk ?? '—' }}</small></span></label>
                    @endforeach
                </div>@error('id_user')<p class="error">{{ $message }}</p>@enderror
                @foreach($errors->get('id_user.*') as $pesanAnggota)@foreach($pesanAnggota as $pesan)<p class="error">{{ $pesan }}</p>@endforeach @endforeach
                </fieldset>
                <p class="muted small" id="jumlah-pilihan" aria-live="polite"></p>
                <p class="muted small" id="anggota-tidak-ditemukan" hidden>Tidak ada siswa yang cocok dengan pencarian.</p>
                <div class="form-actions"><button class="btn" type="submit">Tambahkan anggota terpilih</button></div>
            </form>
        @else
            <div class="empty"><h3>Tidak ada siswa tersedia</h3><p>Semua siswa yang tersedia sudah menjadi anggota atau belum ada akun siswa aktif yang dapat ditambahkan.</p><a class="btn btn-secondary" href="{{ route('admin.siswa.create') }}">Tambah akun siswa</a></div>
        @endif
    </section>
@endsection
@push('scripts')
<script src="{{ asset('js/admin/kelas.js') }}" defer></script>
@endpush
