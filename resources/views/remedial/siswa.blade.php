<!DOCTYPE html>
<html>
<head>
    <title>Siswa Remedial</title>
</head>
<body>

    <h1>Siswa yang Mendapat Remedial</h1>

    <h2>
        Kuis Remedial:
        {{ $remedial->kuisRemedial->judul }}
    </h2>

    <p>
        Kuis Asal:
        {{ $kuisAsal->judul }}
    </p>

    <p>
        KKM:
        {{ $kuisAsal->kkm }}
    </p>

    @if(session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    @if(session('error'))
        <p style="color: red;">
            {{ session('error') }}
        </p>
    @endif

    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($hasilSiswa->isEmpty())

        <p>
            Tidak ada siswa yang memiliki nilai di bawah KKM.
        </p>

    @else

        <form
            action="{{ route('guru.remedial.tugaskan', $remedial->id_remedial) }}"
            method="POST"
        >

            @csrf

            <table border="1" cellpadding="10" cellspacing="0">

                <thead>
                    <tr>
                        <th>Pilih</th>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Nilai</th>
                        <th>KKM</th>
                        <th>Percobaan</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($hasilSiswa as $hasil)

                        <tr>

                            <td>
                                @if(in_array(
                                    $hasil->id_mengerjakan,
                                    $sudahDitugaskan
                                ))

                                    Sudah Ditugaskan

                                @else

                                    <input
                                        type="checkbox"
                                        name="id_mengerjakan[]"
                                        value="{{ $hasil->id_mengerjakan }}"
                                    >

                                @endif
                            </td>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $hasil->siswa->nama_lengkap ?? '-' }}
                            </td>

                            <td>
                                {{ $hasil->nilai }}
                            </td>

                            <td>
                                {{ $kuisAsal->kkm }}
                            </td>

                            <td>
                                {{ $hasil->percobaan_ke }}
                            </td>

                            <td>

                                @if(in_array(
                                    $hasil->id_mengerjakan,
                                    $sudahDitugaskan
                                ))

                                    Sudah mendapat remedial

                                @else

                                    Belum ditugaskan

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <br>

            <button type="submit">
                Berikan Remedial kepada Siswa Terpilih
            </button>

        </form>

    @endif

    <br>

    <a href="{{ route('guru.remedial.index') }}">
        ← Kembali ke Daftar Remedial
    </a>

</body>
</html>
