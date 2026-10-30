@extends('layouts.admin')
@section('title', 'Rekap nilai')
@section('content')
    <div class="page-heading"><div><h1>Rekap nilai</h1><p>Ringkasan nilai per kelas, termasuk keanggotaan historis siswa.</p></div></div>
    <section class="card">
        <form action="{{ route('admin.rekap.index') }}" method="GET" class="filter-form">
            <div class="field"><label for="tahun_ajaran">Tahun ajaran</label><select id="tahun_ajaran" name="tahun_ajaran"><option value="">Tahun aktif / semua jika belum ada</option>@foreach($daftarTahun as $tahun)<option value="{{ $tahun->id_tahun_ajaran }}" @selected($idTahun == $tahun->id_tahun_ajaran)>{{ $tahun->nama }}{{ $tahun->is_aktif ? ' (aktif)' : '' }}</option>@endforeach</select></div>
            <div class="field"><label for="filter-mapel">Mata pelajaran</label><select id="filter-mapel" name="mapel"><option value="">Semua mata pelajaran</option>@foreach($daftarMapel as $pelajaran)<option value="{{ $pelajaran->id_mapel }}" @selected($idMapel == $pelajaran->id_mapel)>{{ $pelajaran->nama_mapel }}</option>@endforeach</select></div>
            <div class="field"><label for="filter-kuis">Kuis</label><select id="filter-kuis" name="kuis"><option value="">Agregat semua kuis dalam filter</option>@foreach($daftarKuis as $kuis)<option value="{{ $kuis->id_kuis }}" @selected($idKuis == $kuis->id_kuis)>{{ $kuis->judul }}</option>@endforeach</select></div>
            <button class="btn" type="submit">Tampilkan</button><a class="btn btn-secondary" href="{{ route('admin.rekap.index') }}">Reset</a>
        </form>
        <p class="help">Ubah tahun atau mata pelajaran untuk memuat ulang pilihan kuis yang sesuai.</p>
    </section>
    <div class="alert alert-info">
        @if($kuisTerpilih)
            <strong>Kuis: {{ $kuisTerpilih->judul }} · KKM {{ number_format((float) ($kuisTerpilih->kkm ?? 75), 2, ',', '.') }}</strong>
            <p>Nilai siswa adalah nilai terbaik dari seluruh percobaan final pada kuis ini. Klasifikasi menggunakan KKM kuis tersebut.</p>
        @else
            <strong>Rekap agregat · KKM acuan 75</strong>
            <p>Ambil nilai terbaik per siswa pada setiap kuis, lalu hitung rata-rata antar kuis dengan bobot yang sama. Nilai <strong>tidak dibobot berdasarkan jumlah percobaan</strong>. Angka 75 merupakan acuan agregat, bukan KKM masing-masing kuis.</p>
        @endif
        <p class="small">Nilai percobaan final sesuai filter tetap dihitung meskipun kuis telah diarsipkan. Rata-rata kelas menggunakan siswa yang sudah dinilai. Siswa belum dinilai tidak dianggap bernilai nol atau masuk kelompok di bawah KKM; akun nonaktif tetap tercakup dalam riwayat kelas. Jika KKM kuis belum diisi, digunakan acuan 75.</p>
    </div>
    @forelse($rekap as $hasil)
        <section class="card">
            <div class="card-header">
                <div><h2>{{ $hasil['kelas']->nama_kelas }}</h2><p class="muted small">Tingkat {{ $hasil['kelas']->tingkat }} · {{ $hasil['kelas']->tahunAjaran?->nama ?? 'Tahun ajaran belum tersedia' }}</p></div>
                <div class="actions"><span class="badge">{{ $kuisTerpilih ? 'KKM kuis' : 'KKM acuan agregat' }} {{ number_format((float) $hasil['kkm'], 2, ',', '.') }}</span><a class="btn btn-secondary btn-small" href="{{ route('admin.kelas.show', $hasil['kelas']) }}">Lihat anggota</a></div>
            </div>
            <div class="rekap-stats">
                <div class="rekap-stat"><span>Anggota historis</span><strong>{{ $hasil['jumlah_anggota'] }}</strong></div>
                <div class="rekap-stat"><span>Sudah dinilai</span><strong>{{ $hasil['jumlah_dinilai'] }}</strong></div>
                <div class="rekap-stat"><span>Belum dinilai</span><strong>{{ $hasil['belum_dinilai'] }}</strong></div>
                <div class="rekap-stat"><span>Rata-rata kelas</span><strong>{{ $hasil['rata_rata'] === null ? '—' : number_format((float) $hasil['rata_rata'], 2, ',', '.') }}</strong></div>
                <div class="rekap-stat"><span>Nilai tertinggi</span><strong>{{ $hasil['tertinggi'] === null ? '—' : number_format((float) $hasil['tertinggi'], 2, ',', '.') }}</strong></div>
                <div class="rekap-stat"><span>Nilai terendah</span><strong>{{ $hasil['terendah'] === null ? '—' : number_format((float) $hasil['terendah'], 2, ',', '.') }}</strong></div>
                <div class="rekap-stat"><span>Kuat / mencapai acuan</span><strong>{{ $hasil['jumlah_kuat'] }}</strong></div>
                <div class="rekap-stat"><span>Di bawah acuan / perlu pendampingan</span><strong>{{ $hasil['jumlah_lemah'] }}</strong></div>
            </div>
            @if($hasil['jumlah_dinilai'] > 0)
                <div class="grid grid-2">
                    <div><h3>Siswa dengan nilai tertinggi</h3><p class="muted small text-break">{{ $hasil['siswa_tertinggi']->pluck('nama_lengkap')->implode(', ') }}</p></div>
                    <div><h3>Siswa dengan nilai terendah</h3><p class="muted small text-break">{{ $hasil['siswa_terendah']->pluck('nama_lengkap')->implode(', ') }}</p></div>
                </div>
                <details><summary>Rincian nilai {{ $hasil['jumlah_dinilai'] }} siswa</summary><div class="details-body">
                    <p class="help">Semua siswa dengan nilai yang sama ditampilkan pada kelompok tertinggi atau terendah. Jumlah kuis menunjukkan kuis yang telah menghasilkan nilai final untuk siswa tersebut.</p>
                    <div class="table-wrap"><table><thead><tr><th scope="col">No.</th><th scope="col">NISN</th><th scope="col">Nama siswa</th><th scope="col" class="number">{{ $kuisTerpilih ? 'Nilai terbaik' : 'Rata-rata nilai terbaik' }}</th><th scope="col" class="number">Jumlah kuis</th><th scope="col">Posisi terhadap acuan</th></tr></thead><tbody>
                        @foreach($hasil['siswa'] as $baris)
                            <tr><td>{{ $loop->iteration }}</td><td class="nowrap">{{ $baris->nomor_induk ?? '—' }}</td><td class="text-break"><strong>{{ $baris->nama_lengkap }}</strong></td><td class="number">{{ number_format((float) $baris->nilai_akhir, 2, ',', '.') }}</td><td class="number">{{ $baris->jumlah_kuis }}</td><td><span class="badge {{ (float) $baris->nilai_akhir >= (float) $hasil['kkm'] ? 'badge-success' : 'badge-warning' }}">{{ (float) $baris->nilai_akhir >= (float) $hasil['kkm'] ? 'Mencapai acuan' : 'Di bawah acuan' }}</span></td></tr>
                        @endforeach
                    </tbody></table></div>
                </div></details>
            @else
                <div class="empty"><h3>Belum ada nilai final</h3><p>{{ $hasil['jumlah_anggota'] > 0 ? 'Anggota kelas belum memiliki nilai final pada kuis yang sesuai filter. Ubah filter untuk memeriksa periode atau kuis lainnya.' : 'Kelas belum memiliki anggota. Tambahkan siswa melalui halaman kelas.' }}</p></div>
            @endif
        </section>
    @empty
        <section class="card"><div class="empty"><h2>Belum ada kelas untuk direkap</h2><p>Pilih tahun ajaran lain atau buat kelas dan tambahkan anggotanya terlebih dahulu.</p><a class="btn btn-secondary" href="{{ route('admin.kelas.index') }}">Kelola kelas</a></div></section>
    @endforelse
@endsection
@push('scripts')
<script>
    ['tahun_ajaran', 'filter-mapel'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            document.getElementById('filter-kuis').value = '';
            this.form.submit();
        });
    });
</script>
@endpush
