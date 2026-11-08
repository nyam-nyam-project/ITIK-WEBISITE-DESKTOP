@extends('layouts.admin')
@section('title', 'Pengajaran')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Pengajaran</h1>
            <p>Atur penugasan guru untuk mengajar mata pelajaran pada kelas per tahun ajaran.</p>
        </div>
    </div>

    <section class="card">
        <form action="{{ route('admin.pengajaran.index') }}" method="GET" class="filter-form">
            <div class="field">
                <label for="tahun_ajaran">Tahun ajaran</label>
                <select id="tahun_ajaran" name="tahun_ajaran">
                    <option value="">Tahun aktif / semua jika belum ada</option>
                    @foreach($daftarTahun as $tahun)
                        <option value="{{ $tahun->id_tahun_ajaran }}" @selected($idTahun == $tahun->id_tahun_ajaran)>
                            {{ $tahun->nama }}{{ $tahun->is_aktif ? ' (aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="btn" type="submit">Tampilkan</button>
            <a class="btn btn-secondary" href="{{ route('admin.pengajaran.index') }}">Reset</a>
        </form>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2>Tambah Penugasan Pengajaran</h2>
                <p class="muted small">Tugaskan guru untuk mengajar mata pelajaran tertentu pada suatu kelas.</p>
            </div>
        </div>
        <form action="{{ route('admin.pengajaran.store') }}" method="POST">
            @csrf
            <div class="grid grid-2">
                <div class="field">
                    <label for="id_user">Guru Pengajar *</label>
                    <select id="id_user" name="id_user" required>
                        <option value="">-- Pilih Guru --</option>
                        @foreach($daftarGuru as $g)
                            <option value="{{ $g->id_user }}" @selected(old('id_user') === $g->id_user)>
                                {{ $g->nama_lengkap }} (NIP {{ $g->nomor_induk ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_user')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="mapel_kelas">Kelas &amp; Mata Pelajaran *</label>
                    <select id="mapel_kelas" name="mapel_kelas" required>
                        <option value="">-- Pilih Kelas &amp; Mata Pelajaran --</option>
                        @foreach($mapelKelas as $mk)
                            <option value="{{ $mk->id_kelas }}:{{ $mk->id_mapel }}" @selected(old('mapel_kelas') === "{$mk->id_kelas}:{$mk->id_mapel}")>
                                {{ $mk->nama_kelas }} — {{ $mk->kode_mapel }} - {{ $mk->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    <p class="help">Pilihan diambil dari mata pelajaran yang telah diatur pada kelas di tahun ajaran ini.</p>
                    @error('id_kelas')<p class="error">{{ $message }}</p>@enderror
                    @error('id_mapel')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button class="btn" type="submit">Tugaskan Pengajaran</button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="card-header">
            <h2>Daftar Pengajaran</h2>
            <span class="badge">{{ $pengajaran->count() }} penugasan</span>
        </div>
        @if($pengajaran->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Nama Guru</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Mata Pelajaran</th>
                            <th scope="col">Tahun Ajaran</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajaran as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-break">
                                    <strong>{{ $item->guru?->nama_lengkap ?? '—' }}</strong>
                                    <span class="muted small">{{ $item->guru?->email ?? 'NIP ' . ($item->guru?->nomor_induk ?? '—') }}</span>
                                </td>
                                <td><strong>{{ $item->kelas?->nama_kelas ?? '—' }}</strong></td>
                                <td>{{ $item->mapel?->nama_mapel ?? '—' }}</td>
                                <td class="nowrap">{{ $item->kelas?->tahunAjaran?->nama ?? '—' }}</td>
                                <td>
                                    <form action="{{ route('admin.pengajaran.destroy', $item->id_pengajaran) }}" method="POST" class="u-display-inline" data-confirm="Yakin ingin menghapus penugasan pengajaran ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-small">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                <h3>Belum ada penugasan pengajaran</h3>
                <p>Gunakan formulir di atas untuk menugaskan guru pada mata pelajaran dan kelas tertentu.</p>
            </div>
        @endif
    </section>
@endsection
