<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Guru - ITIK</title>

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

        /* CONTENT */

        .content {
            flex: 1;
            padding: 30px 45px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* STATISTIC */

        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 22px;
            margin-bottom: 32px;
        }

        .card {
            background: white;
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 14px;
            background: #eeeaff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #604de7;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 38px;
            font-weight: bold;
        }

        .stat-label {
            color: #777394;
            margin-top: 5px;
        }

        /* LOWER CONTENT */

        .columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 22px;
        }

        .section-header p {
            color: #777394;
            margin-top: 5px;
        }

        .see-all {
            text-decoration: none;
            color: #5d4be4;
            border: 1px solid #ddd9f8;
            padding: 10px 15px;
            border-radius: 12px;
        }

        .item {
            padding: 17px 0;
            border-bottom: 1px solid #eeeef5;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .item-title {
            font-weight: bold;
            font-size: 17px;
        }

        .item-sub {
            color: #777394;
            margin-top: 5px;
            font-size: 14px;
        }

        .badge {
            padding: 7px 13px;
            border-radius: 20px;
            background: #e7f8ec;
            color: #20a653;
            font-size: 13px;
            white-space: nowrap;
        }

        .badge.draft {
            background: #fff0d4;
            color: #d68a00;
        }

        @media(max-width: 1100px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

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

            .stats {
                grid-template-columns: 1fr;
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

    <a href="{{ route('dashboard') }}" class="active">
        ▦ &nbsp; Dashboard
    </a>

    <a href="{{ route('materi.index') }}">
        ▣ &nbsp; Materi
    </a>

    <a href="{{ route('kuis.index') }}">
        ☑ &nbsp; Kuis & Soal
    </a>

    <a href="{{ route('guru.nilai.index') }}">
        ◉ &nbsp; Nilai
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
                <h1>Dashboard</h1>
                <p>Ringkasan aktivitas pembelajaran TIK</p>
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


        <!-- STATISTIK -->

        <div class="stats">

            <div class="card">
                <div class="stat-icon">♙</div>

                <div class="stat-number">
                    {{ $jumlahSiswa }}
                </div>

                <div class="stat-label">
                    Siswa
                </div>
            </div>


            <div class="card">
                <div class="stat-icon">▣</div>

                <div class="stat-number">
                    {{ $jumlahMateri }}
                </div>

                <div class="stat-label">
                    Materi
                </div>
            </div>


            <div class="card">
                <div class="stat-icon">☑</div>

                <div class="stat-number">
                    {{ $jumlahKuis }}
                </div>

                <div class="stat-label">
                    Kuis
                </div>
            </div>


            <div class="card">
                <div class="stat-icon">?</div>

                <div class="stat-number">
                    {{ $jumlahSoal }}
                </div>

                <div class="stat-label">
                    Soal
                </div>
            </div>


            <div class="card">
                <div class="stat-icon">▥</div>

                <div class="stat-number">
                    {{ $rataNilai ? round($rataNilai) : 0 }}
                </div>

                <div class="stat-label">
                    Rata-rata Nilai
                </div>
            </div>

        </div>


        <!-- DATA TERBARU -->

        <div class="columns">

            <!-- KUIS -->

            <div class="section-card">

                <div class="section-header">

                    <div>
                        <h2>Kuis Terbaru</h2>
                        <p>5 kuis yang terakhir dibuat</p>
                    </div>

                    <a href="{{ route('kuis.index') }}" class="see-all">
                        Lihat semua
                    </a>

                </div>


                @foreach($kuisTerbaru as $kuis)

                    <div class="item">

                        <div class="item-row">

                            <div>

                                <div class="item-title">
                                    {{ $kuis->judul }}
                                </div>

                                <div class="item-sub">
                                    {{ $kuis->kategori }}
                                </div>

                            </div>

                            @if($kuis->status_publikasi === 'draft')

                                <span class="badge draft">
                                    Draft
                                </span>

                            @else

                                <span class="badge">
                                    Terbit
                                </span>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>


            <!-- MATERI -->

            <div class="section-card">

                <div class="section-header">

                    <div>
                        <h2>Materi Terbaru</h2>
                        <p>5 materi yang terakhir ditambahkan</p>
                    </div>

                    <a href="{{ route('materi.index') }}" class="see-all">
                        Lihat semua
                    </a>

                </div>


                @foreach($materiTerbaru as $materi)

                    <div class="item">

                        <div class="item-title">
                            {{ $materi->judul }}
                        </div>

                        <div class="item-sub">
                            {{ $materi->bab ?? '-' }}
                            •
                            Kelas {{ $materi->kelas ?? '-' }}
                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    </main>

</div>

</body>
</html>