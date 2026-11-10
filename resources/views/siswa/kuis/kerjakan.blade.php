<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $kuis->judul }} - ITIK</title>


    <link rel="stylesheet" href="{{ asset('css/siswa/kuis/kerjakan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}">
</head>

<body>

<!-- MODAL AWAL: MULAI UJIAN & KUNCI LAYAR PENUH -->
<div id="fullscreenModal" class="fullscreen-modal-overlay">
    <div class="fullscreen-modal-card">
        <div class="fullscreen-modal-icon">🔒</div>
        <h2>Ujian Moda Layar Penuh (CBT)</h2>
        <p>
            Untuk menjaga integritas dan ketertiban ujian, Anda wajib mengaktifkan mode layar penuh.
            Segala bentuk perpindahan tab, jendela, atau keluar dari layar penuh akan dicatat sebagai pelanggaran.
        </p>
        <p class="fullscreen-modal-sub">
            Maksimal toleransi: <strong>{{ \App\Http\Controllers\SiswaKuisController::BATAS_PELANGGARAN }} kali</strong> pelanggaran sebelum ujian dikumpulkan otomatis.
        </p>
        <button type="button" id="btnMulaiLayarPenuh" class="btn-fullscreen-start">
            Mulai Ujian &amp; Kunci Layar Penuh
        </button>
    </div>
</div>

<!-- OVERLAY MERAH: PERINGATAN PELANGGARAN -->
<div id="pelanggaranOverlay" class="pelanggaran-overlay is-cbt-hidden" role="alert" aria-live="assertive">
    <div class="pelanggaran-card">
        <div class="pelanggaran-icon">⚠️</div>
        <h2 id="pelanggaranPesan" class="pelanggaran-text">
            ⚠️ PERINGATAN (0/{{ \App\Http\Controllers\SiswaKuisController::BATAS_PELANGGARAN }}): Anda terdeteksi keluar dari layar penuh atau berpindah jendela. Segera kembali ke mode layar penuh.
        </h2>
        <button type="button" id="btnKembaliLayarPenuh" class="btn-warning-action">
            Kembali ke Layar Penuh
        </button>
    </div>
</div>

<div class="layout is-cbt-hidden" id="cbtLayout">

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

        </div>


        <div class="columns">

            <!-- KIRI: SOAL -->

            <div class="section-card">

                <form id="formKuis"
                    data-jawaban-url="{{ route('siswa.kuis.jawaban', $kuis->id_kuis) }}"
                    data-pelanggaran-url="{{ route('siswa.kuis.pelanggaran', $kuis->id_kuis) }}"
                    data-hasil-url="{{ route('siswa.kuis.hasil', $kuis->id_kuis) }}"
                    data-batas="{{ \App\Http\Controllers\SiswaKuisController::BATAS_PELANGGARAN }}">

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
                    <div class="timer" id="timer" data-batas-waktu="{{ $batasWaktu->toIso8601String() }}">00:00</div>

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
                        method="POST">
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


<script defer src="{{ asset('js/siswa/kuis/kerjakan.js') }}"></script>

</body>
</html>
