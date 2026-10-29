<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $kuis->judul }} - ITIK</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: #f5f4fc;
            color: #202044;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #7160ef, #5948dd);
            color: white;
            padding: 30px 25px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 60px;
        }

        .logo-icon {
            width: 55px;
            height: 55px;
            border: 1px solid rgba(255,255,255,.4);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
        }

        .logo h2 {
            font-size: 22px;
        }

        .logo span {
            font-size: 14px;
            opacity: .85;
        }

        .menu-title {
            font-size: 14px;
            opacity: .7;
            margin-bottom: 15px;
        }

        .menu a {
            display: block;
            text-decoration: none;
            color: white;
            padding: 15px 18px;
            border-radius: 15px;
            margin-bottom: 8px;
            font-size: 17px;
        }

        .menu a.active {
            background: white;
            color: #5b4ae3;
        }

        .menu a:hover {
            background: rgba(255,255,255,.15);
        }

        .menu a.active:hover {
            background: white;
        }

        /* CONTENT */

        .content {
            flex: 1;
            padding: 30px 45px;
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .title h1 {
            font-size: 34px;
            margin-bottom: 5px;
        }

        .title p {
            color: #777394;
            font-size: 17px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 10px 18px;
            border-radius: 40px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
            flex-shrink: 0;
        }

        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #6553e8;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .profile small {
            color: #777394;
        }

        /* LAYOUT HALAMAN */

        .columns {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            align-items: start;
        }

        .stack {
            display: grid;
            gap: 25px;
        }

        .side {
            position: sticky;
            top: 20px;
        }

        .section-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        /* SOAL */

        .soal-item {
            scroll-margin-top: 20px;
        }

        .soal-head {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }

        .soal-no {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 12px;
            background: #eeeaff;
            color: #604de7;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .soal-text {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.5;
            padding-top: 6px;
        }

        .opsi-list {
            display: grid;
            gap: 10px;
            padding-left: 56px;
        }

        .opsi {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 18px;
            border: 1px solid #e3e0f5;
            border-radius: 16px;
            cursor: pointer;
            line-height: 1.5;
            font-size: 16px;
        }

        .opsi:hover {
            background: #faf9ff;
        }

        .opsi input {
            margin-top: 4px;
            accent-color: #6553e8;
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        .opsi:has(input:checked) {
            border-color: #6553e8;
            background: #eeeaff;
        }

        .opsi:has(input:focus-visible) {
            outline: 3px solid #b9aefc;
            outline-offset: 2px;
        }

        .opsi-huruf {
            font-weight: bold;
            color: #5d4be4;
        }

        .soal-divider {
            border: none;
            border-top: 1px solid #eeeef5;
            margin: 30px 0;
        }

        /* PANEL KANAN */

        .timer-label {
            color: #777394;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .timer {
            font-size: 48px;
            font-weight: bold;
            color: #5d4be4;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        .timer.warning {
            color: #d93636;
        }

        .panel-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid #eeeef5;
        }

        .meta-value {
            font-size: 17px;
            font-weight: bold;
        }

        .meta-label {
            color: #777394;
            font-size: 13px;
            margin-top: 3px;
        }

        .nav-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .nav-sub {
            color: #777394;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }

        .nav-grid a {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 42px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 15px;
            background: #f5f4fc;
            color: #777394;
            border: 1px solid #e3e0f5;
        }

        .nav-grid a.answered {
            background: #6553e8;
            border-color: #6553e8;
            color: white;
        }

        .btn {
            display: block;
            width: 100%;
            border: none;
            cursor: pointer;
            background: #6553e8;
            color: white;
            padding: 15px 20px;
            border-radius: 14px;
            font-size: 16px;
        }

        .btn:hover {
            background: #5948dd;
        }

        .submit-note {
            color: #777394;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .empty {
            text-align: center;
            padding: 40px 20px;
            color: #777394;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #b9aefc;
            outline-offset: 2px;
        }

        @media(max-width: 1100px) {
            .columns {
                grid-template-columns: 1fr;
            }

            .side {
                position: static;
                order: -1;
            }
        }

        @media(max-width: 700px) {
            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .title h1 {
                font-size: 28px;
            }

            .section-card {
                padding: 22px;
            }

            .opsi-list {
                padding-left: 0;
            }
        }
    </style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">
            <div class="logo-icon">IT</div>

            <div>
                <h2>ITIK</h2>
                <span>Portal Guru</span>
            </div>
        </div>

        <div class="menu-title">
            Menu Utama
        </div>

        <nav class="menu">

            <a href="{{ route('siswa.dashboard') }}">
                ▦ &nbsp; Dashboard
            </a>

            <a href="{{ url('/siswa/materi') }}">
                ▣ &nbsp; Materi
            </a>

            <a href="{{ url('/siswa/kuis') }}" class="active">
                ☑ &nbsp; Kuis
            </a>

            <a href="{{ url('/siswa/nilai') }}">
                ◉ &nbsp; Nilai Saya
            </a>

            <a href="{{ url('/siswa/remedial') }}">
                ↻ &nbsp; Remedial
            </a>

            <a href="{{ route('profil') }}">
                ◎ &nbsp; Profil
            </a>

        </nav>

    </aside>


    <!-- CONTENT -->

    <main class="content">

        <div class="topbar">

            <div class="title">
                <h1>{{ $kuis->judul }}</h1>
                <p>{{ $kuis->kategori }} • {{ $soal->count() }} soal</p>
            </div>

            <div class="profile">

                <div class="avatar">
                    {{ strtoupper(substr(Auth::user()->nama_lengkap, 0, 2)) }}
                </div>

                <div>
                    <strong>{{ Auth::user()->nama_lengkap }}</strong>
                    <br>
                    <small>{{ ucfirst(Auth::user()->role) }}</small>
                </div>

            </div>

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


        <div class="columns">

            <!-- KIRI: SOAL -->

            <div class="section-card">

                <form id="formKuis">

                    @forelse ($soal as $index => $item)

                        @php
                            $jawabanTersimpan =
                                $draftJawaban[$item->id_soal]->jawaban_dipilih ?? null;
                        @endphp

                        <div class="soal-item" id="soal-{{ $item->id_soal }}">

                            <div class="soal-head">
                                <div class="soal-no">{{ $index + 1 }}</div>
                                <div class="soal-text">{{ $item->pertanyaan }}</div>
                            </div>

                            <div class="opsi-list">

                                <label class="opsi">
                                    <input
                                        type="radio"
                                        name="jawaban[{{ $item->id_soal }}]"
                                        value="A"
                                        data-soal="{{ $item->id_soal }}"
                                        @checked($jawabanTersimpan === 'A')
                                    >
                                    <span><span class="opsi-huruf">A.</span> {{ $item->opsi_a }}</span>
                                </label>

                                <label class="opsi">
                                    <input
                                        type="radio"
                                        name="jawaban[{{ $item->id_soal }}]"
                                        value="B"
                                        data-soal="{{ $item->id_soal }}"
                                        @checked($jawabanTersimpan === 'B')
                                    >
                                    <span><span class="opsi-huruf">B.</span> {{ $item->opsi_b }}</span>
                                </label>

                                <label class="opsi">
                                    <input
                                        type="radio"
                                        name="jawaban[{{ $item->id_soal }}]"
                                        value="C"
                                        data-soal="{{ $item->id_soal }}"
                                        @checked($jawabanTersimpan === 'C')
                                    >
                                    <span><span class="opsi-huruf">C.</span> {{ $item->opsi_c }}</span>
                                </label>

                                <label class="opsi">
                                    <input
                                        type="radio"
                                        name="jawaban[{{ $item->id_soal }}]"
                                        value="D"
                                        data-soal="{{ $item->id_soal }}"
                                        @checked($jawabanTersimpan === 'D')
                                    >
                                    <span><span class="opsi-huruf">D.</span> {{ $item->opsi_d }}</span>
                                </label>

                                @if ($item->opsi_e)
                                    <label class="opsi">
                                        <input
                                            type="radio"
                                            name="jawaban[{{ $item->id_soal }}]"
                                            value="E"
                                            data-soal="{{ $item->id_soal }}"
                                            @checked($jawabanTersimpan === 'E')
                                        >
                                        <span><span class="opsi-huruf">E.</span> {{ $item->opsi_e }}</span>
                                    </label>
                                @endif

                            </div>

                        </div>

                        @if (! $loop->last)
                            <hr class="soal-divider">
                        @endif

                    @empty

                        <div class="empty">Belum ada soal pada kuis ini.</div>

                    @endforelse

                </form>

            </div>


            <!-- KANAN: TIMER, NAVIGASI, KUMPULKAN -->

            <div class="stack side">

                <div class="section-card">

                    <div class="timer-label">Waktu tersisa</div>
                    <div class="timer" id="timer">00:00</div>

                    <div class="panel-meta">

                        <div>
                            <div class="meta-value">{{ $kuis->alokasi_waktu }} menit</div>
                            <div class="meta-label">Alokasi waktu</div>
                        </div>

                        <div>
                            <div class="meta-value">{{ $kuis->kategori }}</div>
                            <div class="meta-label">Kategori</div>
                        </div>

                    </div>

                </div>


                <div class="section-card">

                    <div class="nav-title">Daftar soal</div>
                    <div class="nav-sub">
                        <span id="jumlahTerjawab">0</span> dari {{ $soal->count() }} soal terjawab
                    </div>

                    <div class="nav-grid">
                        @foreach ($soal as $index => $item)
                            <a href="#soal-{{ $item->id_soal }}" data-nav="{{ $item->id_soal }}">
                                {{ $index + 1 }}
                            </a>
                        @endforeach
                    </div>

                </div>


                <div class="section-card">

                    <p class="submit-note">
                        Jawabanmu tersimpan otomatis. Kumpulkan kuis setelah semua soal selesai dikerjakan.
                    </p>

                    <form
                        id="form-kuis"
                        action="{{ route('siswa.kuis.submit', $kuis->id_kuis) }}"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin mengumpulkan kuis? Jawaban tidak dapat diubah lagi.');">
                        @csrf

                        <button type="submit" class="btn">
                            Kumpulkan kuis
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </main>

</div>


<script>

//DraftJawaban
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Penanda soal terjawab di daftar soal
function perbaruiNavigasi() {

    const terjawab = new Set();

    document.querySelectorAll('input[type="radio"]:checked').forEach(function (radio) {
        terjawab.add(radio.dataset.soal);
    });

    document.querySelectorAll('[data-nav]').forEach(function (link) {
        link.classList.toggle('answered', terjawab.has(link.dataset.nav));
    });

    document.getElementById('jumlahTerjawab').textContent = terjawab.size;
}

perbaruiNavigasi();

document.querySelectorAll('input[type="radio"]').forEach(function (radio) {
    radio.addEventListener('change', () => queueAnswerSave(radio));
});

document.querySelectorAll('textarea[data-soal]').forEach(function (textarea) {
    textarea.addEventListener('input', () => queueAnswerSave(textarea));
});

        perbaruiNavigasi();

        const idSoal = this.dataset.soal;
        const jawaban = this.value;

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
    let sudahSubmit = false;

    function updateTimer() {

        const sekarang = new Date().getTime();

        const sisa = batasWaktu - sekarang;

        if (sisa <= 0) {
            timerElement.innerHTML = "00:00";
            timerElement.classList.add('warning');

            // Submit otomatis (hanya sekali)
            if (!sudahSubmit) {
                sudahSubmit = true;
                document.getElementById('form-kuis').submit();
            }

            return;
        }

        const totalDetik = Math.floor(sisa / 1000);

        const menit = Math.floor(totalDetik / 60);
        const detik = totalDetik % 60;

        timerElement.innerHTML =
            String(menit).padStart(2, '0') +
            ':' +
            String(detik).padStart(2, '0');

        // Peringatan saat sisa waktu kurang dari 1 menit
        timerElement.classList.toggle('warning', totalDetik < 60);
    }

    updateTimer();

    setInterval(updateTimer, 1000);
</script>

</body>
</html>