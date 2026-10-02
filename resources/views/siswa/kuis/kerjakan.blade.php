<meta name="csrf-token" content="{{ csrf_token() }}">

<h1>{{ $kuis->judul }}</h1>

<p>
    Kategori:
    {{ $kuis->kategori }}
</p>

<p>
    Waktu:
    {{ $kuis->alokasi_waktu }} menit
</p>

<div>
    Waktu tersisa:
    <strong id="timer">00:00</strong>
</div>

<hr>

<h3>Soal</h3>

<form id="formKuis">

    @foreach ($soal as $index => $item)

        @php
            $jawabanTersimpan =
                $draftJawaban[$item->id_soal]->jawaban_dipilih ?? null;
        @endphp

        <div style="margin-bottom: 30px;">

            <p>
                <strong>
                    {{ $index + 1 }}.
                    {{ $item->pertanyaan }}
                </strong>
            </p>

            <label>
                <input
                    type="radio"
                    name="jawaban[{{ $item->id_soal }}]"
                    value="A"
                    data-soal="{{ $item->id_soal }}"
                    @checked($jawabanTersimpan === 'A')
                >
                A. {{ $item->opsi_a }}
            </label>

            <br>

            <label>
                <input
                    type="radio"
                    name="jawaban[{{ $item->id_soal }}]"
                    value="B"
                    data-soal="{{ $item->id_soal }}"
                    @checked($jawabanTersimpan === 'B')
                >
                B. {{ $item->opsi_b }}
            </label>

            <br>

            <label>
                <input
                    type="radio"
                    name="jawaban[{{ $item->id_soal }}]"
                    value="C"
                    data-soal="{{ $item->id_soal }}"
                    @checked($jawabanTersimpan === 'C')
                >
                C. {{ $item->opsi_c }}
            </label>

            <br>

            <label>
                <input
                    type="radio"
                    name="jawaban[{{ $item->id_soal }}]"
                    value="D"
                    data-soal="{{ $item->id_soal }}"
                    @checked($jawabanTersimpan === 'D')
                >
                D. {{ $item->opsi_d }}
            </label>

            @if ($item->opsi_e)
                <br>

                <label>
                    <input
                        type="radio"
                        name="jawaban[{{ $item->id_soal }}]"
                        value="E"
                        data-soal="{{ $item->id_soal }}"
                        @checked($jawabanTersimpan === 'E')
                    >
                    E. {{ $item->opsi_e }}
                </label>
            @endif

        </div>

    @endforeach

</form>

<form
    action="{{ route('siswa.kuis.submit', $kuis->id_kuis) }}"
    method="POST"
    onsubmit="return confirm('Yakin ingin mengumpulkan kuis? Jawaban tidak dapat diubah lagi.');">
    @csrf

    <button type="submit">
        Kumpulkan Kuis
    </button>
</form>



<script>

//DraftJawaban
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

document.querySelectorAll('input[type="radio"]').forEach(function (radio) {

    radio.addEventListener('change', function () {

        const idSoal = this.dataset.soal;
        const jawaban = this.value;

        if (!csrfToken) {
            console.error('CSRF token tidak ditemukan. Tambahkan meta csrf-token di view atau layout.');
            return;
        }

        fetch(
            "{{ route('siswa.kuis.jawaban', $kuis->id_kuis) }}",
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    id_soal: idSoal,
                    jawaban: jawaban
                })
            }
        )
        .then(response => response.json())
        .then(data => {

            if (data.success) {
                console.log('Jawaban tersimpan:', idSoal, jawaban);
            } else {
                console.error(data.message);
            }

        })
        .catch(error => {
            console.error('Gagal menyimpan jawaban:', error);
        });

    });

});

//Timer
    const batasWaktu = new Date(
        "{{ $batasWaktu->toIso8601String() }}"
    ).getTime();

    const timerElement = document.getElementById('timer');

    function updateTimer() {

        const sekarang = new Date().getTime();

        const sisa = batasWaktu - sekarang;

        if (sisa <= 0) {
            timerElement.innerHTML = "00:00";

            // Submit otomatis
            document.getElementById('form-kuis').submit();

            return;
        }

        const totalDetik = Math.floor(sisa / 1000);

        const menit = Math.floor(totalDetik / 60);
        const detik = totalDetik % 60;

        timerElement.innerHTML =
            String(menit).padStart(2, '0') +
            ':' +
            String(detik).padStart(2, '0');
    }

    updateTimer();

    setInterval(updateTimer, 1000);
</script>
