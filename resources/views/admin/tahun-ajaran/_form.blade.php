<p class="muted small">Kolom bertanda * wajib diisi.</p>
<div class="field">
    <label for="nama">Nama tahun ajaran *</label>
    <input id="nama" name="nama" type="text" value="{{ old('nama', $tahunAjaran->nama ?? '') }}" maxlength="20" pattern="[0-9]{4}/[0-9]{4}" placeholder="2026/2027" required aria-describedby="nama-help" aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}">
    <p class="help" id="nama-help">Gunakan format tahun/tahun, misalnya 2026/2027.</p>
    @error('nama')<p class="error">{{ $message }}</p>@enderror
</div>
<div class="grid grid-2">
    <div class="field"><label for="tanggal_mulai">Tanggal mulai</label><input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($tahunAjaran) ? $tahunAjaran->tanggal_mulai?->format('Y-m-d') : '') }}" aria-invalid="{{ $errors->has('tanggal_mulai') ? 'true' : 'false' }}">@error('tanggal_mulai')<p class="error">{{ $message }}</p>@enderror</div>
    <div class="field"><label for="tanggal_selesai">Tanggal selesai</label><input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($tahunAjaran) ? $tahunAjaran->tanggal_selesai?->format('Y-m-d') : '') }}" aria-describedby="tanggal-help" aria-invalid="{{ $errors->has('tanggal_selesai') ? 'true' : 'false' }}"><p class="help" id="tanggal-help">Tanggal selesai harus setelah tanggal mulai.</p>@error('tanggal_selesai')<p class="error">{{ $message }}</p>@enderror</div>
</div>
<div class="alert alert-info">Status aktif tidak diubah melalui formulir ini. Gunakan tindakan <strong>Aktifkan</strong> di daftar tahun ajaran setelah data disimpan.</div>
<div class="form-actions"><button class="btn" type="submit">{{ isset($tahunAjaran) ? 'Simpan perubahan' : 'Simpan tahun ajaran' }}</button><a class="btn btn-secondary" href="{{ route('admin.tahun-ajaran.index') }}">Batal</a></div>
