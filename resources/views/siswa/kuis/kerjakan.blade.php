<meta name="csrf-token" content="{{ csrf_token() }}">

<h1>{{ $kuis->judul }}</h1>

<p>
    Kategori:
    {{ $kuis->kategori_label }}
</p>

<p>
    Tingkat: {{ $kuis->tingkat_ujian_label }}
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
            $jawabanTeksimpan =
                $draftJawaban[$item->id_soal]->jawaban_text ?? '';
        @endphp

        <div style="margin-bottom: 30px;">

            <p>
                <strong>
                    {{ $index + 1 }}.
                    {{ $item->pertanyaan }}
                </strong>
            </p>

            @if ($item->gambar_pertanyaan)
                <div>
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->gambar_pertanyaan) }}"
                        alt="Gambar pertanyaan {{ $index + 1 }}"
                        style="max-width: 100%; max-height: 320px; object-fit: contain;"
                    >
                </div>
            @endif

            @if ($item->tipe_soal === 'essay')
                <label for="jawaban-{{ $item->id_soal }}">Jawaban teks</label>
                <textarea
                    id="jawaban-{{ $item->id_soal }}"
                    data-soal="{{ $item->id_soal }}"
                    rows="6"
                    maxlength="10000"
                    style="display: block; width: 100%; max-width: 800px;"
                >{{ $jawabanTeksimpan }}</textarea>
            @else
                @foreach (['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'] as $huruf => $opsi)
                    @php
                        $teksOpsi = $item->{'opsi_' . $opsi};
                        $gambarOpsi = $item->{'gambar_opsi_' . $opsi};
                    @endphp

                    @if ($teksOpsi || $gambarOpsi)
                        <label style="display: block; margin-top: 10px;">
                            <input
                                type="radio"
                                name="jawaban[{{ $item->id_soal }}]"
                                value="{{ $huruf }}"
                                data-soal="{{ $item->id_soal }}"
                                @checked($jawabanTersimpan === $huruf)
                            >
                            {{ $huruf }}. {{ $teksOpsi }}
                            @if ($gambarOpsi)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($gambarOpsi) }}"
                                    alt="Gambar opsi {{ $huruf }}"
                                    style="display: block; max-width: 280px; max-height: 200px; object-fit: contain; margin-left: 25px;"
                                >
                            @endif
                        </label>
                    @endif
                @endforeach
            @endif

            <small id="status-{{ $item->id_soal }}" aria-live="polite"></small>

        </div>

    @endforeach

</form>

<form
    action="{{ route('siswa.kuis.submit', $kuis->id_kuis) }}"
    method="POST"
    id="submitKuis">
    @csrf

    <button type="submit">
        Kumpulkan Kuis
    </button>
</form>



<script>

//DraftJawaban
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

document.querySelectorAll('input[type="radio"]').forEach(function (radio) {
    radio.addEventListener('change', () => queueAnswerSave(radio));
});

document.querySelectorAll('textarea[data-soal]').forEach(function (textarea) {
    textarea.addEventListener('input', () => queueAnswerSave(textarea));
});

const saveTimers = new Map();
const pendingSaves = new Map();
const saveUrl = @json(route('siswa.kuis.jawaban', $kuis->id_kuis));

function saveAnswer(field) {
    if (!csrfToken) {
        return Promise.reject(new Error('CSRF token tidak ditemukan.'));
    }

    const idSoal = field.dataset.soal;
    const payload = {
        id_soal: idSoal,
        ...(field.type === 'radio'
            ? { jawaban: field.value }
            : { jawaban_text: field.value })
    };
    const status = document.getElementById(`status-${idSoal}`);
    status.textContent = 'Menyimpan...';

    let request = fetch(saveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Jawaban gagal disimpan.');
        }
        status.textContent = 'Tersimpan';
    })
    .catch(error => {
        status.textContent = 'Gagal menyimpan jawaban. Coba lagi.';
        console.error('Gagal menyimpan jawaban:', error);
        throw error;
    })
    .finally(() => {
        if (pendingSaves.get(idSoal) === request) {
            pendingSaves.delete(idSoal);
        }
    });

    pendingSaves.set(idSoal, request);
    return request;
}

function queueAnswerSave(field) {
    const idSoal = field.dataset.soal;
    clearTimeout(saveTimers.get(idSoal));
    saveTimers.set(idSoal, setTimeout(() => {
        saveAnswer(field).catch(() => {});
    }, 400));
}

let waktuHabis = false;
document.getElementById('submitKuis').addEventListener('submit', async function (event) {
    if (!waktuHabis && !confirm('Yakin ingin mengumpulkan kuis? Jawaban tidak dapat diubah lagi.')) {
        event.preventDefault();
        return;
    }

    event.preventDefault();

    const changedAnswers = [
        ...document.querySelectorAll('input[type="radio"]:checked, textarea[data-soal]')
    ];

    changedAnswers.forEach(field => {
        const idSoal = field.dataset.soal;
        clearTimeout(saveTimers.get(idSoal));
    });

    try {
        await Promise.allSettled([...pendingSaves.values()]);
        await Promise.all(changedAnswers.map(field => saveAnswer(field)));
        this.submit();
    } catch (error) {
        alert('Jawaban belum tersimpan. Periksa koneksi lalu coba kumpulkan kembali.');
    }
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
            waktuHabis = true;
            document.getElementById('submitKuis').requestSubmit();

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
