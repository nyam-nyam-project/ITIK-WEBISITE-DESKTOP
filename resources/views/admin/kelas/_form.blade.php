@php
    $sedangEdit = isset($kelas);
    $tahunDefault = $daftarTahun->firstWhere('is_aktif', true)?->id_tahun_ajaran ?? '';
    $pilihanMapel = $daftarMapel->concat($sedangEdit ? $kelas->mapel : collect())->unique('id_mapel')->sortBy('nama_mapel');
    $mapelDipilih = $errors->any() ? old('mapel', []) : ($mapelTerpilih ?? []);
@endphp
<p class="muted small">Kolom bertanda * wajib diisi.</p>
<div class="grid grid-2">
    <div class="field"><label for="nama_kelas">Nama kelas *</label><input id="nama_kelas" name="nama_kelas" type="text" value="{{ old('nama_kelas', $kelas->nama_kelas ?? '') }}" maxlength="50" placeholder="X TKJ 1" required aria-invalid="{{ $errors->has('nama_kelas') ? 'true' : 'false' }}">@error('nama_kelas')<p class="error">{{ $message }}</p>@enderror</div>
    <div class="field"><label for="tingkat">Tingkat *</label><select id="tingkat" name="tingkat" required aria-invalid="{{ $errors->has('tingkat') ? 'true' : 'false' }}"><option value="">Pilih tingkat</option>@foreach(['X', 'XI', 'XII'] as $tingkat)<option value="{{ $tingkat }}" @selected(old('tingkat', $kelas->tingkat ?? '') === $tingkat)>{{ $tingkat }}</option>@endforeach</select>@error('tingkat')<p class="error">{{ $message }}</p>@enderror</div>
</div>
<div class="field">
    <label for="tahun_kelas">Tahun ajaran *</label>
    @if($sedangEdit)
        <input id="tahun_kelas" type="text" value="{{ $daftarTahun->firstWhere('id_tahun_ajaran', $kelas->id_tahun_ajaran)?->nama ?? $kelas->id_tahun_ajaran }}" readonly aria-describedby="tahun-help">
        <input type="hidden" name="id_tahun_ajaran" value="{{ $kelas->id_tahun_ajaran }}">
        <p class="help" id="tahun-help">Tahun ajaran dikunci agar riwayat kelas tidak berpindah. Untuk tahun baru, buat kelas baru.</p>
    @else
        <select id="tahun_kelas" name="id_tahun_ajaran" required aria-invalid="{{ $errors->has('id_tahun_ajaran') ? 'true' : 'false' }}"><option value="">Pilih tahun ajaran</option>@foreach($daftarTahun as $tahun)<option value="{{ $tahun->id_tahun_ajaran }}" @selected(old('id_tahun_ajaran', $tahunDefault) == $tahun->id_tahun_ajaran)>{{ $tahun->nama }}{{ $tahun->is_aktif ? ' (aktif)' : '' }}</option>@endforeach</select>
        @if($daftarTahun->isEmpty())<p class="help">Belum ada tahun ajaran. <a href="{{ route('admin.tahun-ajaran.create') }}">Tambahkan tahun ajaran terlebih dahulu.</a></p>@endif
    @endif
    @error('id_tahun_ajaran')<p class="error">{{ $message }}</p>@enderror
</div>
<fieldset><legend>Mata pelajaran kelas</legend><p class="muted small">Pilih mata pelajaran yang digunakan. Mata pelajaran nonaktif yang sudah terpasang tetap ditampilkan agar tidak terlepas tanpa sengaja.</p>
    <div class="check-grid">
        @forelse($pilihanMapel as $pelajaran)
            <label class="check" for="mapel-{{ $pelajaran->id_mapel }}"><input id="mapel-{{ $pelajaran->id_mapel }}" type="checkbox" name="mapel[]" value="{{ $pelajaran->id_mapel }}" @checked(in_array($pelajaran->id_mapel, (array) $mapelDipilih, true))><span>{{ $pelajaran->nama_mapel }}<small>{{ $pelajaran->kode_mapel }}{{ $pelajaran->is_aktif ? '' : ' · Nonaktif' }}</small></span></label>
        @empty
            <p class="muted">Belum ada mata pelajaran tersedia. <a href="{{ route('admin.mapel.create') }}">Tambah mata pelajaran</a></p>
        @endforelse
    </div>
    @error('mapel')<p class="error">{{ $message }}</p>@enderror
    @foreach($errors->get('mapel.*') as $pesanMapel)@foreach($pesanMapel as $pesan)<p class="error">{{ $pesan }}</p>@endforeach @endforeach
</fieldset>
<div class="field"><input type="hidden" name="is_aktif" value="0"><label class="check" for="is_aktif"><input id="is_aktif" name="is_aktif" type="checkbox" value="1" @checked(old('is_aktif', $kelas->is_aktif ?? true))><span>Kelas aktif<small>Menonaktifkan kelas tidak menghapus anggota atau riwayat nilai.</small></span></label>@error('is_aktif')<p class="error">{{ $message }}</p>@enderror</div>
<div class="form-actions"><button class="btn" type="submit" @disabled(!$sedangEdit && $daftarTahun->isEmpty())>{{ $sedangEdit ? 'Simpan perubahan' : 'Simpan kelas' }}</button><a class="btn btn-secondary" href="{{ $sedangEdit ? route('admin.kelas.show', $kelas) : route('admin.kelas.index') }}">Batal</a></div>
