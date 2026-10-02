<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Soal - ITIK</title>

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

        /* ================= HEADER ================= */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 28px;
            margin-bottom: 7px;
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
            border: none;

            text-decoration: none;

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

        .btn-view {
            background: #eeeaff;
            color: #5b4ae3;
        }

        .btn-edit {
            background: #fff4d6;
            color: #a97900;
        }

        .btn-delete {
            background: #ffe5e5;
            color: #d64545;
        }

        .btn-delete:hover {
            background: #ffd5d5;
        }

        /* ================= ALERT ================= */

        .alert {
            padding: 15px 18px;
            border-radius: 13px;

            margin-bottom: 20px;

            font-size: 14px;
        }

        .alert-success {
            background: #e8f8ef;
            color: #188650;
            border: 1px solid #c9ecd9;
        }

        .alert-error {
            background: #fff0f0;
            color: #d64545;
            border: 1px solid #ffd0d0;
        }

        /* ================= QUIZ INFO ================= */

        .quiz-info {
            background: white;
            border-radius: 20px;

            padding: 24px 28px;

            margin-bottom: 22px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;
        }

        .quiz-info-left h3 {
            font-size: 21px;
            margin-bottom: 8px;
        }

        .quiz-info-left p {
            color: #777394;
            font-size: 14px;
        }

        .quiz-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .meta-badge {
            background: #f1efff;
            color: #5b4ae3;

            padding: 9px 13px;

            border-radius: 20px;

            font-size: 12px;
            font-weight: bold;
        }

        /* ================= SOAL CARD ================= */

        .soal-card {
            background: white;
            border-radius: 20px;

            padding: 28px;

            margin-bottom: 18px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .soal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            gap: 15px;

            margin-bottom: 20px;
        }

        .soal-number {
            width: 38px;
            height: 38px;

            border-radius: 11px;

            background: #eeeaff;
            color: #5b4ae3;

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: bold;
            flex-shrink: 0;
        }

        .question {
            flex: 1;

            font-size: 16px;
            line-height: 1.6;
            color: #29274a;
        }

        .difficulty {
            padding: 6px 11px;
            border-radius: 20px;

            background: #f3f2f8;
            color: #777394;

            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        /* ================= OPTIONS ================= */

        .options {
            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 10px;

            margin-bottom: 20px;
        }

        .option {
            padding: 12px 15px;

            background: #faf9ff;
            border: 1px solid #eeeef5;

            border-radius: 11px;

            color: #55526b;

            font-size: 14px;
            line-height: 1.5;
        }

        .option strong {
            color: #6553e8;
            margin-right: 5px;
        }

        .option.correct {
            background: #eaf8ef;
            border-color: #c9ecd9;
        }

        .option.correct strong {
            color: #188650;
        }

        /* ================= ANSWER ================= */

        .answer-info {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 12px 15px;

            background: #f7f6ff;

            border-radius: 11px;

            margin-bottom: 20px;

            font-size: 13px;
        }

        .answer-label {
            color: #777394;
        }

        .answer-value {
            color: #188650;
            font-weight: bold;
        }

        /* ================= ACTION ================= */

        .soal-actions {
            display: flex;
            justify-content: flex-end;

            gap: 8px;

            padding-top: 15px;

            border-top: 1px solid #f0eff6;
        }

        /* ================= EMPTY ================= */

        .empty-card {
            background: white;

            border-radius: 20px;

            padding: 55px 30px;

            text-align: center;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .empty-card h3 {
            margin-bottom: 8px;
        }

        .empty-card p {
            color: #9996aa;
            margin-bottom: 20px;
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

        @media(max-width: 700px) {

            .profile-mini {
                display: none;
            }

            .topbar {
                margin-bottom: 25px;
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

            .quiz-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .options {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 500px) {

            .content {
                padding: 20px 15px;
            }

            .soal-card {
                padding: 20px;
            }

            .soal-header {
                flex-wrap: wrap;
            }

            .soal-actions {
                justify-content: stretch;
            }

            .soal-actions .btn,
            .soal-actions form {
                flex: 1;
            }

            .soal-actions form button {
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

            <a
                href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                ▦ &nbsp; Dashboard
            </a>

            <a
                href="{{ route('materi.index') }}"
                class="{{ request()->routeIs('materi.*') ? 'active' : '' }}"
            >
                ▣ &nbsp; Materi
            </a>

            <a
                href="{{ route('kuis.index') }}"
                class="{{ request()->routeIs('kuis.*') ? 'active' : '' }}"
            >
                ☑ &nbsp; Kuis & Soal
            </a>

            <a
                href="{{ route('guru.nilai.index') }}"
                class="{{ request()->routeIs('guru.nilai.*') ? 'active' : '' }}"
            >
                ◉ &nbsp; Nilai
            </a>

            <a
                href="{{ route('profil') }}"
                class="{{ request()->routeIs('profil') ? 'active' : '' }}"
            >
                ◎ &nbsp; Profil
            </a>

        </nav>

    </aside>


    <!-- ================= CONTENT ================= -->

    <main class="content">


        <!-- TOPBAR -->

        <div class="topbar">

            <div class="title">

                <h1>Daftar Soal</h1>

                <p>
                    Kelola soal untuk kuis
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


        <!-- ================= PAGE HEADER ================= -->

        <div class="page-header">

            <div>

                <h2>
                    {{ $kuis->judul }}
                </h2>

                <p>
                    Kategori: {{ $kuis->kategori }}
                    &nbsp; • &nbsp;
                    {{ $soal->count() }} soal
                </p>

            </div>


            <div class="header-actions">

                <a
                    href="{{ route('kuis.soal.create', $kuis->id_kuis) }}"
                    class="btn btn-primary"
                >
                    + Tambah Soal
                </a>

                <a
                    href="{{ route('kuis.show', $kuis->id_kuis) }}"
                    class="btn btn-secondary"
                >
                    ← Kembali ke Kuis
                </a>

            </div>

        </div>


        <!-- ================= ALERT ================= -->

        @if (session('success'))

            <div class="alert alert-success">

                {{ session('success') }}

            </div>

        @endif


        @if (session('error'))

            <div class="alert alert-error">

                {{ session('error') }}

            </div>

        @endif


        <!-- ================= QUIZ INFO ================= -->

        <div class="quiz-info">

            <div class="quiz-info-left">

                <h3>
                    Daftar Pertanyaan
                </h3>

                <p>
                    Semua soal yang terdapat dalam kuis ini.
                </p>

            </div>


            <div class="quiz-meta">

                <span class="meta-badge">
                    {{ $soal->count() }} Soal
                </span>

                <span class="meta-badge">
                    {{ $kuis->kategori }}
                </span>

            </div>

        </div>


        <!-- ================= DAFTAR SOAL ================= -->

        @if ($soal->count() > 0)

            @foreach ($soal as $index => $item)

                <div class="soal-card">


                    <div class="soal-header">

                        <div class="soal-number">
                            {{ $index + 1 }}
                        </div>


                        <div class="question">

                            <strong>
                                {{ $item->pertanyaan }}
                            </strong>

                        </div>


                        <span class="difficulty">

                            {{ $item->tingkat_kesulitan ?? '-' }}

                        </span>

                    </div>


                    <!-- OPTIONS -->

                    <div class="options">


                        <div
                            class="option {{ $item->jawaban === 'A' ? 'correct' : '' }}"
                        >

                            <strong>A.</strong>

                            {{ $item->opsi_a }}

                        </div>


                        <div
                            class="option {{ $item->jawaban === 'B' ? 'correct' : '' }}"
                        >

                            <strong>B.</strong>

                            {{ $item->opsi_b }}

                        </div>


                        <div
                            class="option {{ $item->jawaban === 'C' ? 'correct' : '' }}"
                        >

                            <strong>C.</strong>

                            {{ $item->opsi_c }}

                        </div>


                        <div
                            class="option {{ $item->jawaban === 'D' ? 'correct' : '' }}"
                        >

                            <strong>D.</strong>

                            {{ $item->opsi_d }}

                        </div>


                        @if ($item->opsi_e)

                            <div
                                class="option {{ $item->jawaban === 'E' ? 'correct' : '' }}"
                            >

                                <strong>E.</strong>

                                {{ $item->opsi_e }}

                            </div>

                        @endif


                    </div>


                    <!-- ANSWER -->

                    <div class="answer-info">

                        <span class="answer-label">
                            Jawaban benar:
                        </span>

                        <span class="answer-value">
                            {{ $item->jawaban }}
                        </span>

                    </div>


                    <!-- ACTION -->

                    <div class="soal-actions">

                        <a
                            href="{{ route('kuis.soal.show', [$kuis->id_kuis, $item->id_soal]) }}"
                            class="btn btn-view"
                        >
                            Lihat
                        </a>


                        <a
                            href="{{ route('kuis.soal.edit', [$kuis->id_kuis, $item->id_soal]) }}"
                            class="btn btn-edit"
                        >
                            Edit
                        </a>


                        <form
                            action="{{ route('kuis.soal.destroy', [$kuis->id_kuis, $item->id_soal]) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus soal ini?');"
                        >

                            @csrf

                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-delete"
                            >
                                Hapus
                            </button>

                        </form>

                    </div>


                </div>

            @endforeach


        @else


            <!-- EMPTY -->

            <div class="empty-card">

                <h3>
                    Belum Ada Soal
                </h3>

                <p>
                    Belum ada soal untuk kuis ini.
                </p>

                <a
                    href="{{ route('kuis.soal.create', $kuis->id_kuis) }}"
                    class="btn btn-primary"
                >
                    + Tambah Soal
                </a>

            </div>


        @endif


    </main>

</div>

</body>
</html>