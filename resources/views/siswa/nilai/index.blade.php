<h1>Hasil Nilai Saya</h1>

@if($hasil->isEmpty())

    <p>Belum ada hasil kuis.</p>

@else

    <table border="1" cellpadding="10" cellspacing="0">

        <thead>
            <tr>
                <th>No</th>
                <th>Kuis</th>
                <th>Kategori</th>
                <th>Percobaan</th>
                <th>Nilai</th>
                <th>KKM</th>
                <th>Kelulusan</th>
                <th>Validasi</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>

            @foreach($hasil as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>
                        {{ $item->kuis->judul ?? '-' }}
                    </td>

                    <td>
                        {{ $item->kuis->kategori ?? '-' }}
                    </td>

                    <td>
                        {{ $item->percobaan_ke }}
                    </td>

                    <td>
                        {{ $item->nilai ?? '-' }}
                    </td>

                    <td>
                        {{ $item->kuis->kkm ?? '-' }}
                    </td>

                    {{-- STATUS KELULUSAN --}}
                    <td>

                        @if($item->nilai === null)

                            -

                        @elseif(
                            $item->kuis->kkm !== null &&
                            $item->nilai >= $item->kuis->kkm
                        )

                            Lulus

                        @else

                            Tidak Lulus

                        @endif

                    </td>

                    {{-- STATUS VALIDASI --}}
                    <td>

                        @if($item->status_validasi === 'belum divalidasi')

                            Menunggu Validasi Guru

                        @elseif($item->status_validasi === 'sudah divalidasi')

                            Sudah Divalidasi

                        @else

                            {{ $item->status_validasi ?? '-' }}

                        @endif

                    </td>

                    <td>
                        {{ $item->tanggal ?? '-' }}
                    </td>

                    <td>

                        <a href="{{ route(
                            'siswa.kuis.hasil',
                            $item->id_kuis
                        ) }}">
                            Lihat Detail
                        </a>

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

@endif
