<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $materi->judul }} - ITIK</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
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

        .back-link {
            display: inline-block;
            text-decoration: none;
            color: #5d4be4;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .back-link:hover {
            text-decoration: underline;
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

        /* ALERT */

        .alert {
            padding: 15px 22px;
            border-radius: 16px;
            margin-bottom: 25px;
            font-size: 15px;
        }

        .alert.success {
            background: #e7f8ec;
            color: #20a653;
        }

        .alert.error {
            background: #fde8e8;
            color: #d93636;
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

        .section-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 22px;
        }

        .see-all {
            text-decoration: none;
            color: #5d4be4;
            border: 1px solid #ddd9f8;
            padding: 10px 15px;
            border-radius: 12px;
            white-space: nowrap;
            font-size: 15px;
        }

        .see-all:hover {
            background: #f5f3ff;
        }

        /* INFO MATERI */

        .info-item {
            padding: 15px 0;
            border-bottom: 1px solid #eeeef5;
        }

        .info-item:first-of-type {
            padding-top: 0;
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            color: #777394;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 17px;
            line-height: 1.5;
        }

        .info-value.judul {
            font-weight: bold;
        }

        /* BADGE */

        .badge {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            white-space: nowrap;
            background: #fff0d4;
            color: #d68a00;
        }

        .badge.selesai {
            background: #e7f8ec;
            color: #20a653;
        }

        /* ISI MATERI */

        .pdf-frame {
            width: 100%;
            height: 70vh;
            min-height: 500px;
            border: 1px solid #eeeef5;
            border-radius: 16px;
            background: #faf9ff;
        }

        .isi-teks {
            line-height: 1.7;
            font-size: 17px;
            max-width: 75ch;
            white-space: pre-line;
        }

        .empty {
            text-align: center;
            padding: 40px 20px;
            color: #777394;
        }

        .empty-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: #eeeaff;
            color: #604de7;
            font-size: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        /* BUTTON */

        .btn {
            display: inline-block;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: #6553e8;
            color: white;
            padding: 14px 22px;
            border-radius: 14px;
            font-size: 16px;
            width: 100%;
            text-align: center;
        }

        .btn:hover {
            background: #5948dd;
        }

        .btn.outline {
            background: white;
            color: #5d4be4;
            border: 1px solid #ddd9f8;
        }

        .btn.outline:hover {
            background: #f5f3ff;
        }

        .done-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #e7f8ec;
            color: #20a653;
            padding: 16px 18px;
            border-radius: 16px;
            font-weight: bold;
            line-height: 1.4;
        }

        .action-text {
            color: #777394;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
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

            <a href="{{ url('/siswa/materi') }}" class="active">
                ▣ &nbsp; Materi
            </a>

            <a href="{{ url('/siswa/kuis') }}">
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
                <a href="{{ route('siswa.materi.index') }}" class="back-link">
                    ← Kembali ke daftar materi
                </a>

                <h1>{{ $materi->judul }}</h1>
                <p>{{ $materi->bab ?? '-' }} • Kelas {{ $materi->kelas ?? '-' }}</p>
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


        <!-- NOTIFIKASI -->

        @if(session('success'))
            <div class="alert success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert error">
                {{ session('error') }}
            </div>
        @endif


        <div class="columns">

            <!-- KIRI: ISI MATERI -->

            <div class="stack">

                <div class="section-card">

                    <div class="section-header">
                        <h2>Isi Materi</h2>

                        @if($pdfUrl)
                            <a href="{{ $pdfUrl }}" target="_blank" class="see-all">
                                Buka PDF di tab baru ↗
                            </a>
                        @endif
                    </div>

                    @if($pdfUrl)

                        <iframe
                            class="pdf-frame"
                            src="{{ $pdfUrl }}#toolbar=1"
                            title="PDF {{ $materi->judul }}"
                        ></iframe>

                    @elseif($materi->isi_materi)

                        <p class="isi-teks">{{ $materi->isi_materi }}</p>

                    @else

                        <div class="empty">
                            <div class="empty-icon">▣</div>
                            File materi belum tersedia.
                        </div>

                    @endif

                </div>


                @if($materi->link_youtube)

                    <div class="section-card">

                        <div class="section-header">
                            <h2>Video Pembelajaran</h2>
                        </div>

                        <a href="{{ $materi->link_youtube }}" target="_blank" class="btn outline">
                            Tonton video pembelajaran ↗
                        </a>

                    </div>

                @endif

            </div>


            <!-- KANAN: INFORMASI & PROGRESS -->

            <div class="stack">

                <div class="section-card">

                    <div class="section-header">
                        <h2>Informasi Materi</h2>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Judul</div>
                        <div class="info-value judul">{{ $materi->judul }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Bab</div>
                        <div class="info-value">{{ $materi->bab ?? '-' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Kelas</div>
                        <div class="info-value">{{ $materi->kelas ?? '-' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Deskripsi</div>
                        <div class="info-value">{{ $materi->deskripsi ?? '-' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Status progress</div>
                        <div class="info-value">
                            @if($progress->status === 'selesai')
                                <span class="badge selesai">Selesai</span>
                            @else
                                <span class="badge">Sedang Belajar</span>
                            @endif
                        </div>
                    </div>

                </div>


                <div class="section-card">

                    @if($progress->status !== 'selesai')

                        <p class="action-text">
                            Sudah selesai membaca materi ini? Tandai agar progress belajarmu tercatat.
                        </p>

                        <form
                            action="{{ route('siswa.materi.selesai', $materi->id_materi) }}"
                            method="POST"
                        >
                            @csrf
                            <button type="submit" class="btn">
                                ✓ Tandai sudah selesai membaca
                            </button>
                        </form>

                    @else

                        <div class="done-box">
                            ✓ Materi sudah selesai dipelajari.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>