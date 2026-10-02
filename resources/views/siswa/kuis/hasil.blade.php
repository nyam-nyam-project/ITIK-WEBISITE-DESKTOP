<h1>Hasil Kuis</h1>

@if(session('success'))
    <p style="color: green;">
        {{ session('success') }}
    </p>
@endif

<h3>{{ $kuis->judul }}</h3>

<table border="1" cellpadding="10" cellspacing="0">

    <tr>
        <th>Kategori</th>
        <td>{{ $kuis->kategori }}</td>
    </tr>

    <tr>
        <th>Nilai</th>
        <td>
            <strong>{{ $hasil->nilai }}</strong>
        </td>
    </tr>

    <tr>
        <th>KKM</th>
        <td>{{ $kuis->kkm ?? '-' }}</td>
    </tr>

    <tr>
        <th>Status</th>
        <td>
            @if(
                $hasil->nilai !== null &&
                $kuis->kkm !== null &&
                $hasil->nilai >= $kuis->kkm
            )

                <strong>Lulus</strong>

            @else

                <strong>Tidak Lulus</strong>

            @endif
        </td>
    </tr>

    <tr>
        <th>Percobaan</th>
        <td>{{ $hasil->percobaan_ke }}</td>
    </tr>

</table>

@if($kuis->kategori === 'remedial' && $remedial)

    <br>

    <h3>Hasil Remedial</h3>

    <table border="1" cellpadding="10" cellspacing="0">

        <tr>
            <th>Nilai Sebelum Remedial</th>
            <td>
                {{ $nilaiAsal ?? '-' }}
            </td>
        </tr>

        <tr>
            <th>Nilai Remedial</th>
            <td>
                <strong>{{ $hasil->nilai }}</strong>
            </td>
        </tr>

        <tr>
            <th>KKM</th>
            <td>
                {{ $kuis->kkm ?? '-' }}
            </td>
        </tr>

        <tr>
            <th>Hasil Remedial</th>
            <td>

                @if(
                    $hasil->nilai !== null &&
                    $kuis->kkm !== null &&
                    $hasil->nilai >= $kuis->kkm
                )

                    <strong>Lulus setelah remedial</strong>

                @else

                    <strong>Belum Lulus setelah remedial</strong>

                @endif

            </td>
        </tr>

    </table>

@endif

<br>

<a href="{{ route('siswa.remedial.index') }}">
    Lihat Tugas Remedial
</a>

<br><br>

<a href="{{ route('siswa.kuis.index') }}">
    ← Kembali ke Daftar Kuis
</a>
