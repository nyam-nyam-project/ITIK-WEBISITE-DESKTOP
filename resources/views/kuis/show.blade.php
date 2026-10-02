<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Kuis - ITIK</title>

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

        /* ================= SIDEBAR ================= */

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

        /* ================= CONTENT ================= */

        .content {
            flex: 1;
            padding: 30px 45px;
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .title h1 {
            font-size: 34px;
            margin-bottom: 5px;
        }

        .title p {
            color: #777394;
            font-size: 17px;
        }

        .profile-mini {
            display: flex;
            align-items: center;
            gap: 12px;

            background: white;
            padding: 10px 18px;
            border-radius: 40px;

            box-shadow: 0 3px 15px rgba(0,0,0,.04);
        }

        .avatar-small {
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

        .profile-mini small {
            color: #777394;
        }

        /* ================= PAGE HEADER ================= */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 28px;
            margin-bottom: 6px;
        }

        .page-header p {
            color: #777394;
            font-size: 15px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        /* ================= BUTTON ================= */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 12px 18px;
            border-radius: 12px;

            text-decoration: none;
            border: none;

            font-size: 14px;
            font-weight: bold;

            cursor: pointer;
            transition: .2s;
        }

        .btn-primary {
            background: #6553e8;
            color: white;
        }

        .btn-primary:hover {
            background: #5543d5;
        }

        .btn-secondary {
            background: #eeeaff;
            color: #5b4ae3;
        }

        .btn-secondary:hover {
            background: #e2ddff;
        }

        .btn-success {
            background: #20a464;
            color: white;
        }

        .btn-success:hover {
            background: #178a53;
        }

        /* ================= ERROR ================= */

        .alert-danger {
            background: #fff0f0;
            border: 1px solid #ffd0d0;

            color: #d64545;

            padding: 16px 18px;
            border-radius: 13px;

            margin-bottom: 25px;
        }

        .alert-danger strong {
            display: block;
            margin-bottom: 8px;
        }

        .alert-danger ul {
            padding-left: 20px;
        }

        .alert-danger li {
            margin-bottom: 4px;
        }

        /* ================= INFO CARD ================= */

        .info-card {
            background: white;
            border-radius: 22px;

            padding: 28px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);

            margin-bottom: 22px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 35px;
        }

        .info-item {
            padding-bottom: 17px;
            border-bottom: 1px solid #f0eff6;
        }

        .info-item.full {
            grid-column: 1 / -1;
        }

        .info-label {
            color: #8b88a0;
            font-size: 13px;

            margin-bottom: 6px;
        }

        .info-value {
            color: #202044;
            font-size: 15px;
            line-height: 1.6;
        }

        .info-value strong {
            font-size: 16px;
        }

        /* ================= STATUS ================= */

        .status {
            display: inline-flex;
            align-items: center;

            padding: 6px 12px;
            border-radius: 20px;

            font-size: 12px;
            font-weight: bold;
        }

        .status-draft {
            background: #fff4d6;
            color: #a97900;
        }

        .status-published {
            background: #e5f8ed;
            color: #188650;
        }

        /* ================= SECTION ================= */

        .section-card {
            background: white;
            border-radius: 22px;

            padding: 28px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);

            margin-bottom: 22px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 20px;
        }

        .section-header h3 {
            font-size: 20px;
        }

        .section-header span {
            color: #777394;
            font-size: 13px;
        }

        /* ================= MATERI ================= */

        .materi-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .materi-item {
            background: #faf9ff;
            border: 1px solid #eeeef5;

            border-radius: 12px;

            padding: 14px 16px;

            color: #45435e;
            font-size: 14px;
        }

        .materi-item strong {
            color: #6553e8;
            margin-right: 6px;
        }

        .empty-text {
            color: #aaa7bc;
            font-size: 14px;
        }

        /* ================= SOAL ================= */

        .question-list {
            padding-left: 22px;
            margin-bottom: 22px;
        }

        .question-list li {
            padding: 12px 5px;
            border-bottom: 1px solid #f0eff6;

            color: #45435e;
            font-size: 14px;
            line-height: 1.6;
        }

        .question-list li:last-child {
            border-bottom: none;
        }

        .question-list li::marker {
            color: #6553e8;
            font-weight: bold;
        }

        .question-count {
            background: #eeeaff;
            color: #5b4ae3;

            padding: 7px 12px;
            border-radius: 20px;

            font-size: 12px;
            font-weight: bold;
        }

        /* ================= PUBLISH ================= */

        .publish-box {
            background: #f7f6ff;
            border: 1px solid #e9e6ff;

            border-radius: 15px;

            padding: 18px;

            margin-top: 20px;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .publish-box p {
            color: #777394;
            font-size: 13px;
            line-height: 1.5;
        }

        .publish-box strong {
            display: block;
            color: #202044;
            margin-bottom: 4px;
        }

        /* ================= BOTTOM ACTION ================= */

        .bottom-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-top: 25px;
        }

        .right-actions {
            display: flex;
            gap: 10px;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 900px) {

            .sidebar {
                display: none;
            }

            .content {
                padding: 25px;
            }
        }

        @media(max-width: 650px) {

            .topbar {
                align-items: flex-start;
            }

            .profile-mini {
                display: none;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                flex: 1;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .info-item.full {
                grid-column: auto;
            }

            .publish-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .bottom-actions {
                flex-direction: column-reverse;
                gap: 12px;
                align-items: stretch;
            }

            .right-actions {
                flex-direction: column;
            }

            .bottom-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>


<body>

<div class="layout">


    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar">

        <div class="logo">

            <div class="logo-icon">
                IT
            </div>

            <div>
                <h2>ITIK</h2>
                <span>Portal Guru</span>
            </div>

        </div>


        <div class="menu-title">
            Menu Utama
        </div>


        <nav class="menu">

            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                ▦ &nbsp; Dashboard
            </a>

            <a href="{{ route('materi.index') }}"
               class="{{ request()->routeIs('materi.*') ? 'active' : '' }}">
                ▣ &nbsp; Materi
            </a>

            <a href="{{ route('kuis.index') }}"
               class="{{ request()->routeIs('kuis.*') ? 'active' : '' }}">
                ☑ &nbsp; Kuis & Soal
            </a>

            <a href="{{ route('guru.nilai.index') }}"
               class="{{ request()->routeIs('guru.nilai.*') ? 'active' : '' }}">
                ◉ &nbsp; Nilai
            </a>

            <a href="{{ route('profil') }}"
               class="{{ request()->routeIs('profil') ? 'active' : '' }}">
                ◎ &nbsp; Profil
            </a>

        </nav>

    </aside>


    <!-- ================= CONTENT ================= -->

    <main class="content">


        <!-- TOPBAR -->

        <div class="topbar">

            <div class="title">

                <h1>Detail Kuis</h1>

                <p>
                    Informasi dan pengelolaan kuis
                </p>

            </div>


            <div class="profile-mini">

                <div class="avatar-small">

                    {{ strtoupper(substr(Auth::user()->nama_lengkap, 0, 2)) }}

                </div>

                <div>

                    <strong>
                        {{ Auth::user()->nama_lengkap }}
                    </strong>

                    <br>

                    <small>
                        {{ ucfirst(Auth::user()->role) }}
                    </small>

                </div>

            </div>

        </div>


        <!-- ERROR -->

        @if ($errors->any())

            <div class="alert-danger">

                <strong>
                    Kuis belum dapat dipublikasikan:
                </strong>

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h2>
                    {{ $kuis->judul }}
                </h2>

                <p>
                    Detail informasi kuis
                </p>

            </div>


            <div class="header-actions">

                <a
                    href="{{ route('kuis.edit', $kuis->id_kuis) }}"
                    class="btn btn-secondary"
                >
                    ✎ Edit
                </a>

                <a
                    href="{{ route('kuis.index') }}"
                    class="btn btn-secondary"
                >
                    ← Kembali
                </a>

            </div>

        </div>


        <!-- ================= INFORMASI KUIS ================= -->

        <div class="info-card">

            <div class="info-grid">


                <!-- ID -->

                <div class="info-item">

                    <div class="info-label">
                        ID Kuis
                    </div>

                    <div class="info-value">

                        <strong>
                            {{ $kuis->id_kuis }}
                        </strong>

                    </div>

                </div>


                <!-- KATEGORI -->

                <div class="info-item">

                    <div class="info-label">
                        Kategori
                    </div>

                    <div class="info-value">
                        {{ $kuis->kategori }}
                    </div>

                </div>


                <!-- DESKRIPSI -->

                <div class="info-item full">

                    <div class="info-label">
                        Deskripsi
                    </div>

                    <div class="info-value">
                        {{ $kuis->deskripsi ?? '-' }}
                    </div>

                </div>


                <!-- KKM -->

                <div class="info-item">

                    <div class="info-label">
                        KKM
                    </div>

                    <div class="info-value">

                        {{ $kuis->kkm ?? '-' }}

                    </div>

                </div>


                <!-- WAKTU -->

                <div class="info-item">

                    <div class="info-label">
                        Alokasi Waktu
                    </div>

                    <div class="info-value">

                        {{ $kuis->alokasi_waktu ?? '-' }} menit

                    </div>

                </div>


                <!-- STATUS -->

                <div class="info-item">

                    <div class="info-label">
                        Status
                    </div>

                    <div class="info-value">

                        @if ($kuis->status_publikasi === 'draft')

                            <span class="status status-draft">
                                Draft
                            </span>

                        @else

                            <span class="status status-published">
                                {{ $kuis->status_publikasi }}
                            </span>

                        @endif

                    </div>

                </div>


                <!-- JADWAL ULANGAN -->

                @if ($kuis->kategori === 'ulangan')

                    <div class="info-item">

                        <div class="info-label">
                            Waktu Mulai
                        </div>

                        <div class="info-value">

                            {{ $kuis->waktu_mulai?->format('d-m-Y H:i') ?? '-' }}

                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">
                            Waktu Selesai
                        </div>

                        <div class="info-value">

                            {{ $kuis->waktu_selesai?->format('d-m-Y H:i') ?? '-' }}

                        </div>

                    </div>

                @endif

            </div>


            <!-- PUBLISH -->

            @if ($kuis->status_publikasi === 'draft' && $kuis->is_aktif)

                @if (Auth::user()->role === 'guru')

                    <div class="publish-box">

                        <div>

                            <strong>
                                Kuis masih dalam status draft
                            </strong>

                            <p>
                                Publikasikan kuis agar dapat digunakan sesuai pengaturan yang telah dibuat.
                            </p>

                        </div>


                        <form
                            action="{{ route('kuis.publish', $kuis->id_kuis) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin mempublikasikan kuis ini?');"
                        >

                            @csrf

                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn btn-success"
                            >
                                ✓ Publikasikan Kuis
                            </button>

                        </form>

                    </div>

                @endif

            @endif

        </div>


        <!-- ================= MATERI ================= -->

        <div class="section-card">

            <div class="section-header">

                <h3>
                    Materi
                </h3>

            </div>


            <div class="materi-list">

                @forelse ($kuis->materi as $materi)

                    <div class="materi-item">

                        <strong>•</strong>

                        {{ $materi->judul }}

                        @if ($materi->bab)

                            <span>
                                ({{ $materi->bab }})
                            </span>

                        @endif

                    </div>

                @empty

                    <p class="empty-text">
                        Tidak ada materi terkait.
                    </p>

                @endforelse

            </div>

        </div>


        <!-- ================= SOAL ================= -->

        <div class="section-card">

            <div class="section-header">

                <h3>
                    Soal
                </h3>

                <span class="question-count">

                    {{ $kuis->soal->count() }} soal

                </span>

            </div>


            @if ($kuis->soal->count() > 0)

                <ol class="question-list">

                    @foreach ($kuis->soal as $soal)

                        <li>
                            {{ $soal->pertanyaan }}
                        </li>

                    @endforeach

                </ol>

            @else

                <p class="empty-text">
                    Belum ada soal.
                </p>

            @endif


            <a
                href="{{ route('kuis.soal.index', $kuis->id_kuis) }}"
                class="btn btn-primary"
            >
                ⚙ Kelola Soal
            </a>

        </div>

    </main>

</div>

</body>
</html>