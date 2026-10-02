<h1>Remedial</h1>

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

<h2>Pilih Kuis Asal</h2>

@if($remedials->isEmpty())

    <p>Belum ada kuis yang dapat digunakan untuk remedial.</p>

@else

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>KKM</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($remedials as $item)

                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $item->kuisAsal->judul ?? '-' }}</td>

                    <td>{{ $item->kuisAsal->kategori ?? '-' }}</td>

                    <td>{{ $item->kuisAsal->kkm ?? '-' }}</td>

                    <td>
                        <a href="{{ route('guru.remedial.siswa', $item->id_remedial) }}">
                            Lihat Siswa
                        </a>
                    </td>
                </tr>

            @endforeach
        </tbody>
    </table>

@endif
