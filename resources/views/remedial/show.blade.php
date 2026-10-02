<h1>Detail Remedial</h1>

<h3>{{ $remedial->kuisRemedial->judul }}</h3>

<p>
    <strong>Kuis Asal:</strong>
    {{ $remedial->kuisAsal->judul }}
</p>

<p>
    <strong>KKM:</strong>
    {{ $remedial->kuisAsal->kkm ?? '-' }}
</p>

<hr>

<h3>Hasil Siswa</h3>

<table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>Siswa</th>
            <th>Nilai Asal</th>
            <th>KKM</th>
            <th>Nilai Remedial</th>
            <th>Status Tugas</th>
            <th>Hasil Remedial</th>
        </tr>
    </thead>

    <tbody>

        @forelse($remedial->remedialSiswa as $index => $item)

            @php
                $nilaiAsal = $item->mengerjakanAsal?->nilai;
                $nilaiRemedial = $item->hasilRemedial?->nilai;
                $kkm = $remedial->kuisRemedial->kkm;
            @endphp

            <tr>

                <td>
                    {{ $index + 1 }}
                </td>

                <td>
                    {{ $item->mengerjakanAsal?->siswa?->nama_lengkap ?? '-' }}
                </td>

                <td>
                    {{ $nilaiAsal ?? '-' }}
                </td>

                <td>
                    {{ $kkm ?? '-' }}
                </td>

                <td>
                    {{ $nilaiRemedial ?? '-' }}
                </td>

                <td>
                    {{ ucfirst($item->status) }}
                </td>

                <td>

                    @if($nilaiRemedial === null)

                        <strong>Belum Mengerjakan</strong>

                    @elseif($kkm !== null && $nilaiRemedial >= $kkm)

                        <strong>Lulus</strong>

                    @else

                        <strong>Belum Lulus</strong>

                    @endif

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7">
                    Belum ada siswa yang diberi tugas remedial.
                </td>
            </tr>

        @endforelse

    </tbody>
</table>

<br>

<a href="{{ route('guru.remedial.index') }}">
    ← Kembali ke Daftar Remedial
</a>
