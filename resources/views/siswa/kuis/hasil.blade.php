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
        <td>{{ $kuis->kategori_label }}</td>
    </tr>

    <tr>
        <th>Tingkat</th>
        <td>{{ $kuis->tingkat_ujian_label }}</td>
    </tr>

    <tr>
        <th>Durasi pengerjaan</th>
        <td>
            @if ($durasiPengerjaan === null)
                -
            @else
                {{ intdiv($durasiPengerjaan, 60) }} menit {{ $durasiPengerjaan % 60 }} detik
            @endif
        </td>
    </tr>

    <tr>
        <th>Nilai</th>
        <td>
            @if ($hasil->nilai === null)
                <strong>Jawaban essay diterima; tidak ada nilai otomatis.</strong>
            @else
                <strong>{{ $hasil->nilai }}</strong>
                @if ($adaSoalEssay)
                    <p>Nilai ini dihitung dari soal pilihan ganda; jawaban essay tidak dinilai otomatis.</p>
                @endif
            @endif
        </td>
    </tr>

    <tr>
        <th>KKM</th>
        <td>{{ $kuis->kkm ?? '-' }}</td>
    </tr>

    <tr>
        <th>Status</th>
        <td>
            @if ($hasil->nilai === null)

                <strong>Tidak dinilai otomatis</strong>

            @elseif(
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

@if ($canRetry)
    <form action="{{ route('siswa.kuis.mulai', $kuis->id_kuis) }}" method="GET">
        <button type="submit">Ulangi Kuis</button>
    </form>
@elseif ($hasil->nilai !== null && $kuis->kkm !== null && $hasil->nilai >= $kuis->kkm)
    <p>Kamu telah lulus KKM. Kuis tidak perlu diulang.</p>
@elseif ($kuis->maks_percobaan !== null && $percobaanTerpakai >= $kuis->maks_percobaan)
    <p>Batas maksimal percobaan kuis telah tercapai. Hubungi guru untuk remedial.</p>
@endif

<br>

<a href="{{ route('siswa.remedial.index') }}">
    Lihat Tugas Remedial
</a>

<br><br>

<a href="{{ route('siswa.kuis.index') }}">
    ← Kembali ke Daftar Kuis
</a>
