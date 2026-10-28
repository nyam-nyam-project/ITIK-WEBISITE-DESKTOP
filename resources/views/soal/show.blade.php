<h1>Detail Soal</h1>

<h2>{{ $kuis->judul }}</h2>

<hr>

<p>
    <strong>Tipe soal:</strong>
    {{ $soal->tipe_soal === 'essay' ? 'Essay / Jawaban Teks' : 'Pilihan Ganda' }}
</p>

<p>
    <strong>Pertanyaan:</strong>
</p>

<p>
    {{ $soal->pertanyaan }}
</p>

@if ($soal->gambar_pertanyaan)
    <img
        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($soal->gambar_pertanyaan) }}"
        alt="Gambar pertanyaan"
        style="max-width: 100%; max-height: 320px; object-fit: contain;"
    >
@endif

<hr>

@if ($soal->tipe_soal === 'essay')

    <p>Jawaban berupa teks bebas dan tidak diberi nilai otomatis.</p>

@else

    <p>
        <strong>Pilihan Jawaban:</strong>
    </p>

    @foreach (['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'] as $huruf => $opsi)
        @php
            $teksOpsi = $soal->{'opsi_' . $opsi};
            $gambarOpsi = $soal->{'gambar_opsi_' . $opsi};
        @endphp

        @if ($teksOpsi || $gambarOpsi)
            <p>{{ $huruf }}. {{ $teksOpsi }}</p>

            @if ($gambarOpsi)
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($gambarOpsi) }}"
                    alt="Gambar opsi {{ $huruf }}"
                    style="max-width: 280px; max-height: 200px; object-fit: contain;"
                >
            @endif
        @endif
    @endforeach

    <p>
        <strong>Jawaban Benar:</strong>
        {{ $soal->jawaban }}
    </p>

@endif

<p>
    <strong>Bobot:</strong>
    {{ $soal->bobot ?? 0 }}
</p>

<p>
    <strong>Tingkat Kesulitan:</strong>
    {{ $soal->tingkat_kesulitan ?? '-' }}
</p>

<hr>

<a href="{{ route('kuis.soal.edit', [$kuis->id_kuis, $soal->id_soal]) }}">
    Edit Soal
</a>

<br><br>

<a href="{{ route('kuis.soal.index', $kuis->id_kuis) }}">
    ← Kembali ke Daftar Soal
</a>
