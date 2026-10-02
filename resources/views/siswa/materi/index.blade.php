<h1>Materi Pembelajaran</h1>

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

@if($materi->isEmpty())

    <p>Belum ada materi.</p>

@else

<table border="1" cellpadding="10" cellspacing="0">

    <thead>
        <tr>
            <th>No</th>
            <th>Judul Materi</th>
            <th>Bab</th>
            <th>Kelas</th>
            <th>Progress</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>

        @foreach($materi as $item)

            @php
                $progress = $item->progress->first();
            @endphp

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $item->judul }}
                </td>

                <td>
                    {{ $item->bab ?? '-' }}
                </td>

                <td>
                    {{ $item->kelas ?? '-' }}
                </td>

                <td>
                    @if(!$progress)
                        Belum Mulai
                    @elseif($progress->status === 'sedang belajar')
                        Sedang Belajar
                    @elseif($progress->status === 'selesai')
                        <strong style="color: green;">
                            Selesai
                        </strong>
                    @else
                        {{ $progress->status }}
                    @endif
                </td>

                <td>
                    <a href="{{ route('siswa.materi.show', $item->id_materi) }}">
                        Buka Materi
                    </a>
                </td>

            </tr>

        @endforeach

    </tbody>

</table>

@endif

<br>

<a href="{{ route('siswa.kuis.index') }}">
    Ke Daftar Kuis
</a>
