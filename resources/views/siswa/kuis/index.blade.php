<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Kuis - ITIK</title>

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

        .title h1 {
            font-size: 34px;
            margin-bottom: 5px;
        }

        .title p {
            color: #777394;
            font-size: 17px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
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

        /* CARD */

        .section-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
            margin-bottom: 25px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
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
            white-space: nowrap;
            background: white;
        }

        .see-all:hover {
            background: #f5f3ff;
        }

        /* KUIS GRID */

        .kuis-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .kuis-item {
            border: 1px solid #eeeef5;
            border-radius: 20px;
            padding: 24px;
            display: flex;
            flex-direction: column;
        }

        .kuis-item h3 {
            font-size: 19px;
            line-height: 1.35;
            margin-bottom: 12px;
        }

        .badge {
            display: inline-block;
            align-self: flex-start;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            white-space: nowrap;
            background: #eeeaff;
            color: #5d4be4;
            margin-bottom: 20px;
        }

        .kuis-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 16px 0;
            border-top: 1px solid #eeeef5;
            border-bottom: 1px solid #eeeef5;
            margin-bottom: 22px;
            margin-top: auto;
        }

        .meta-number {
            font-size: 22px;
            font-weight: bold;
        }

        .meta-label {
            color: #777394;
            font-size: 13px;
            margin-top: 3px;
        }

        .btn {
            display: block;
            width: 100%;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
            background: #6553e8;
            color: white;
            padding: 13px 20px;
            border-radius: 14px;
            font-size: 16px;
        }

        .btn:hover {
            background: #5948dd;
        }

        /* TABLE */

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            color: #777394;
            font-size: 14px;
            font-weight: normal;
            padding: 12px 14px;
            border-bottom: 1px solid #eeeef5;
            white-space: nowrap;
        }

        tbody td {
            padding: 17px 14px;
            border-bottom: 1px solid #eeeef5;
            font-size: 16px;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #faf9ff;
        }

        .col-no {
            width: 60px;
            color: #777394;
        }

        .row-title {
            font-weight: bold;
            font-size: 17px;
        }

        .btn-inline {
            display: inline-block;
            text-decoration: none;
            background: #6553e8;
            color: white;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 14px;
            white-space: nowrap;
        }

        .btn-inline:hover {
            background: #5948dd;
        }

        .badge.inline {
            margin-bottom: 0;
        }

        /* EMPTY STATE */

        .empty {
            text-align: center;
            padding: 40px 20px;
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

        .empty h3 {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .empty p {
            color: #777394;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #b9aefc;
            outline-offset: 2px;
        }

        @media(max-width: 1300px) {
            .kuis-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 1100px) {
            .topbar {
                flex-wrap: wrap;
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

            .topbar-right {
                flex-direction: column;
                align-items: flex-start;
            }

            .kuis-grid {
                grid-template-columns: 1fr;
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
                <h1>Daftar Kuis</h1>
                <p>Kerjakan kuis yang tersedia</p>
            </div>

            <div class="topbar-right">

                <a href="{{ route('siswa.nilai') }}" class="see-all">
                    Lihat nilai saya
                </a>

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

        </div>


        <!-- NOTIFIKASI -->

        @if (session('error'))
            <div class="alert error">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert success">
                {{ session('success') }}
            </div>
        @endif


        <!-- KUIS TERSEDIA -->

        <div class="section-card">

            <div class="section-header">

                <div>
                    <h2>Kuis Tersedia</h2>
                    <p>{{ $kuis->count() }} kuis bisa dikerjakan</p>
                </div>

            </div>


            @if ($kuis->count() === 0)

                <div class="empty">
                    <div class="empty-icon">☑</div>
                    <h3>Belum ada kuis</h3>
                    <p>Belum ada kuis yang tersedia.</p>
                </div>

            @else

                <div class="kuis-grid">

                    @foreach ($kuis as $item)

                        <div class="kuis-item">

                            <h3>{{ $item->judul }}</h3>

                            <span class="badge">{{ $item->kategori }}</span>

                            <div class="kuis-meta">

                                <div>
                                    <div class="meta-number">{{ $item->alokasi_waktu }}</div>
                                    <div class="meta-label">Menit</div>
                                </div>

                                <div>
                                    <div class="meta-number">{{ $item->kkm }}</div>
                                    <div class="meta-label">KKM</div>
                                </div>

                                <div>
                                    <div class="meta-number">{{ $item->soal()->count() }}</div>
                                    <div class="meta-label">Soal</div>
                                </div>

                            </div>

                            <form action="{{ route('siswa.kuis.mulai', $item->id_kuis) }}" method="GET">
                                <button type="submit" class="btn">Mulai</button>
                            </form>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>


        <!-- KUIS REMEDIAL -->

        <div class="section-card">

            <div class="section-header">

                <div>
                    <h2>Kuis Remedial</h2>
                    <p>Tugas perbaikan nilai untukmu</p>
                </div>

            </div>


            @if($remedial->isEmpty())

                <div class="empty">
                    <div class="empty-icon">↻</div>
                    <h3>Tidak ada tugas remedial</h3>
                    <p>Remedial akan muncul di sini jika nilaimu belum mencapai KKM.</p>
                </div>

            @else

                <div class="table-wrap">

                    <table>

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($remedial as $item)

                                <tr>

                                    <td class="col-no">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        <span class="row-title">
                                            {{ $item->kuisRemedial->judul }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge inline">
                                            {{ $item->kuisRemedial->kategori }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ route('siswa.kuis.mulai', $item->kuisRemedial->id_kuis) }}" class="btn-inline">
                                            Kerjakan Remedial
                                        </a>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </div>

    </main>

</div>

</body>
</html>