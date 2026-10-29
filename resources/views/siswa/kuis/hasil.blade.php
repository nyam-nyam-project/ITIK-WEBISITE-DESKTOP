<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hasil Kuis - ITIK</title>

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

        /* LAYOUT HALAMAN */

        .columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 22px;
        }

        .section-header p {
            color: #777394;
            margin-top: 5px;
        }

        /* NILAI */

        .score-card {
            text-align: center;
            padding: 40px 30px;
        }

        .score-label {
            color: #777394;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .score-number {
            font-size: 84px;
            font-weight: bold;
            line-height: 1;
            color: #5d4be4;
            margin-bottom: 22px;
        }

        .score-number.gagal {
            color: #d93636;
        }

        .score-kkm {
            color: #777394;
            margin-top: 18px;
            font-size: 15px;
        }

        /* BADGE */

        .badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            white-space: nowrap;
            background: #e7f8ec;
            color: #20a653;
            font-weight: bold;
        }

        .badge.gagal {
            background: #fde8e8;
            color: #d93636;
        }

        .badge.kategori {
            background: #eeeaff;
            color: #5d4be4;
            font-weight: normal;
            padding: 7px 13px;
            font-size: 13px;
        }

        /* INFO */

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 16px 0;
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
            font-size: 15px;
        }

        .info-value {
            font-size: 17px;
            text-align: right;
        }

        .info-value.strong {
            font-weight: bold;
        }

        /* REMEDIAL */

        .remedial-compare {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .compare-box {
            background: #f5f4fc;
            border-radius: 18px;
            padding: 20px;
            text-align: center;
        }

        .compare-box.after {
            background: #eeeaff;
        }

        .compare-number {
            font-size: 38px;
            font-weight: bold;
        }

        .compare-label {
            color: #777394;
            font-size: 14px;
            margin-top: 5px;
        }

        .remedial-result {
            padding: 16px 18px;
            border-radius: 16px;
            font-weight: bold;
            text-align: center;
            background: #e7f8ec;
            color: #20a653;
        }

        .remedial-result.gagal {
            background: #fff0d4;
            color: #d68a00;
        }

        /* BUTTON */

        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            background: #6553e8;
            color: white;
            padding: 14px 22px;
            border-radius: 14px;
            font-size: 16px;
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

        a:focus-visible {
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

            .score-number {
                font-size: 64px;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

@php
    $lulus = $hasil->nilai !== null
        && $kuis->kkm !== null
        && $hasil->nilai >= $kuis->kkm;
@endphp

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
                <a href="{{ route('siswa.kuis.index') }}" class="back-link">
                    ← Kembali ke daftar kuis
                </a>

                <h1>Hasil Kuis</h1>
                <p>{{ $kuis->judul }}</p>
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


        <div class="columns">

            <!-- KIRI: NILAI -->

            <div class="section-card score-card">

                <div class="score-label">Nilai kamu</div>

                <div class="score-number {{ $lulus ? '' : 'gagal' }}">
                    {{ $hasil->nilai }}
                </div>

                @if($lulus)
                    <span class="badge">Lulus</span>
                @else
                    <span class="badge gagal">Tidak Lulus</span>
                @endif

                <div class="score-kkm">
                    KKM: {{ $kuis->kkm ?? '-' }}
                </div>

            </div>


            <!-- KANAN: DETAIL -->

            <div class="stack">

                <div class="section-card">

                    <div class="section-header">
                        <h2>Detail Kuis</h2>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Judul</div>
                        <div class="info-value strong">{{ $kuis->judul }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Kategori</div>
                        <div class="info-value">
                            <span class="badge kategori">{{ $kuis->kategori }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Nilai</div>
                        <div class="info-value strong">{{ $hasil->nilai }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">KKM</div>
                        <div class="info-value">{{ $kuis->kkm ?? '-' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @if($lulus)
                                <span class="badge">Lulus</span>
                            @else
                                <span class="badge gagal">Tidak Lulus</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Percobaan</div>
                        <div class="info-value">ke-{{ $hasil->percobaan_ke }}</div>
                    </div>

                </div>


                @if($kuis->kategori === 'remedial' && $remedial)

                    <div class="section-card">

                        <div class="section-header">
                            <h2>Hasil Remedial</h2>
                            <p>Perbandingan nilai sebelum dan sesudah remedial</p>
                        </div>

                        <div class="remedial-compare">

                            <div class="compare-box">
                                <div class="compare-number">{{ $nilaiAsal ?? '-' }}</div>
                                <div class="compare-label">Nilai sebelum remedial</div>
                            </div>

                            <div class="compare-box after">
                                <div class="compare-number">{{ $hasil->nilai }}</div>
                                <div class="compare-label">Nilai remedial</div>
                            </div>

                        </div>

                        <div class="info-item">
                            <div class="info-label">KKM</div>
                            <div class="info-value">{{ $kuis->kkm ?? '-' }}</div>
                        </div>

                        <div class="remedial-result {{ $lulus ? '' : 'gagal' }}" style="margin-top: 20px;">
                            @if($lulus)
                                Lulus setelah remedial
                            @else
                                Belum Lulus setelah remedial
                            @endif
                        </div>

                    </div>

                @endif

            </div>

        </div>


        <div class="actions">

            <a href="{{ route('siswa.remedial.index') }}" class="btn">
                Lihat tugas remedial
            </a>

            <a href="{{ route('siswa.kuis.index') }}" class="btn outline">
                ← Kembali ke daftar kuis
            </a>

        </div>

    </main>

</div>

</body>
</html>