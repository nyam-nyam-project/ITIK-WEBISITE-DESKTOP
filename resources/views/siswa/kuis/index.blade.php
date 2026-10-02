<a href="{{ route('siswa.nilai') }}">
    Lihat Nilai Saya
</a>

<h1>Daftar Kuis</h1>

@if (session('error'))
    <div style="color: red;">
        {{ session('error') }}
    </div>
@endif

@if (session('success'))
    <div style="color: green;">
        {{ session('success') }}
    </div>
@endif

@if ($kuis->count() === 0)

    <p>Belum ada kuis yang tersedia.</p>

@else

    @foreach ($kuis as $item)

        <div style="margin-bottom: 20px;">

            <h3>{{ $item->judul }}</h3>

            <p>
                Kategori:
                {{ $item->kategori }}
            </p>

            <p>
                Waktu:
                {{ $item->alokasi_waktu }} menit
            </p>

            <p>
                KKM:
                {{ $item->kkm }}
            </p>

            <p>
                Jumlah soal:
                {{ $item->soal()->count() }}
            </p>

            <form action="{{ route('siswa.kuis.mulai', $item->id_kuis) }}" method="GET">
                <button type="submit">Mulai</button>
            </form>

        </div>

        <hr>

    @endforeach

@endif

<br>
<hr>

<h2>Kuis Remedial</h2>

@if($remedial->isEmpty())

    <p>Tidak ada tugas remedial.</p>

@else

    <table border="1" cellpadding="10" cellspacing="0">

        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>

            @foreach($remedial as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>
                        {{ $item->kuisRemedial->judul }}
                    </td>

                    <td>
                        {{ $item->kuisRemedial->kategori }}
                    </td>

                    <td>
                        <a href="{{ route('siswa.kuis.mulai', $item->kuisRemedial->id_kuis) }}">
                            Kerjakan Remedial
                        </a>
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

@endif
