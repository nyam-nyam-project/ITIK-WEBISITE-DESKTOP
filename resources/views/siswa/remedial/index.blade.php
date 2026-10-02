<!DOCTYPE html>
<html>
<head>
    <title>Tugas Remedial</title>
</head>
<body>

    <h1>Tugas Remedial Saya</h1>

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

    @if($tugas->isEmpty())

        <p>Tidak ada tugas remedial.</p>

    @else

    <table border="1" cellpadding="10" cellspacing="0">

        <thead>
            <tr>
                <th>No</th>
                <th>Kuis Asal</th>
                <th>Nilai Awal</th>
                <th>KKM</th>
                <th>Kuis Remedial</th>
                <th>Tanggal Ditugaskan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>

            @foreach($tugas as $item)

                @php
                    $kkm = $item->mengerjakanAsal->kuis->kkm;
                @endphp

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>
                        {{ $item->mengerjakanAsal->kuis->judul }}
                    </td>

                    <td>
                        {{ $item->mengerjakanAsal->nilai }}
                    </td>

                    <td>
                        {{ $kkm }}
                    </td>

                    <td>
                        {{ $item->kuisRemedial->judul }}
                    </td>

                    <td>
                        {{ $item->tanggal_ditugaskan }}
                    </td>

                    <td>

                        @if($item->status === 'ditugaskan')

                            <span style="color: orange;">
                                Belum Dikerjakan
                            </span>

                        @elseif($item->status === 'selesai')

                            <span style="color: green;">
                                Selesai
                            </span>

                        @endif

                    </td>

                    <td>

                        @if($item->status === 'ditugaskan')

                            <a href="{{ route('siswa.remedial.kerjakan', $item->id_remedial_siswa) }}">
                                Kerjakan Remedial
                            </a>

                        @elseif($item->status === 'selesai')

                            <strong>Sudah Selesai</strong>

                        @else

                            {{ $item->status }}

                        @endif

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

    @endif

    <br>

    <a href="{{ route('siswa.kuis.index') }}">
        ← Kembali ke Daftar Kuis
    </a>

</body>
</html>
