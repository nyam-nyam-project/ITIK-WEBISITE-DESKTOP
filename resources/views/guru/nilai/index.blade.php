<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Validasi Nilai - ITIK</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f4fc;
            color: #202044;
            min-height: 100vh;
            display: flex;
        }

        /* SIDEBAR */

        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #7160ef, #5948dd);
            color: white;
            padding: 30px 25px;
            flex-shrink: 0;
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

        /* ALERT */

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

        /* CARD */

        .card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .card-header h2 {
            font-size: 22px;
        }

        .card-header p {
            color: #777394;
            font-size: 14px;
            margin-top: 5px;
        }

        /* TABLE */

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        thead th {
            background: #f7f6ff;
            color: #5b5875;
            font-size: 13px;
            font-weight: bold;
            text-align: left;
            padding: 15px 14px;
            border-bottom: 1px solid #eeeef5;
            white-space: nowrap;
        }

        tbody td {
            padding: 16px 14px;
            border-bottom: 1px solid #f0eff6;
            font-size: 14px;
            color: #55526b;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #faf9ff;
        }

        .student-name {
            color: #29274a;
            font-weight: bold;
        }

        .quiz-name {
            color: #5b4ae3;
            font-weight: 600;
        }

        .category {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #eeeaff;
            color: #5b4ae3;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .category.remedial {
            background: #fff4d6;
            color: #a97900;
        }

        .score {
            color: #29274a;
            font-size: 16px;
        }

        .pass {
            color: #188650;
            font-weight: bold;
        }

        .fail {
            color: #d64545;
            font-weight: bold;
        }

        .waiting {
            color: #a97900;
            font-weight: bold;
            white-space: nowrap;
        }

        .validated {
            color: #188650;
            font-weight: bold;
            white-space: nowrap;
        }

        .btn-view {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 13px;
            border-radius: 10px;
            background: #eeeaff;
            color: #5b4ae3;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .btn-view:hover {
            background: #e2ddff;
        }

        /* EMPTY */

        .empty-card {
            background: white;
            border-radius: 20px;
            padding: 55px 30px;
            text-align: center;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .empty-icon {
            width: 65px;
            height: 65px;
            border-radius: 18px;
            background: #eeeaff;
            color: #6553e8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 28px;
        }

        .empty-card h3 {
            margin-bottom: 8px;
        }

        .empty-card p {
            color: #9996aa;
        }

        /* RESPONSIVE */

        @media(max-width:900px) {
            .sidebar {
                display: none;
            }

            .content {
                padding: 25px;
            }
        }

        @media(max-width:700px) {
            .profile-mini {
                display: none;
            }

            .topbar {
                margin-bottom: 25px;
            }

            .card {
                padding: 20px;
            }
        }

        @media(max-width:500px) {
            .content {
                padding: 20px 15px;
            }

            .title h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    {{-- SIDEBAR --}}

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


    {{-- CONTENT --}}

    <main class="content">

        <div class="topbar">

            <div class="title">

                <h1>Validasi Nilai Siswa</h1>

                <p>
                    Kelola dan validasi hasil kuis siswa
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


        {{-- ALERT SUCCESS --}}

        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- ALERT ERROR --}}

        @if(session('error'))

            <div class="alert alert-error">
                {{ session('error') }}
            </div>

        @endif


        @if($hasil->isEmpty())

            <div class="empty-card">

                <div class="empty-icon">
                    ◉
                </div>

                <h3>Belum Ada Hasil Kuis</h3>

                <p>
                    Belum ada hasil kuis siswa yang dapat divalidasi.
                </p>

            </div>

        @else

            <div class="card">

                <div class="card-header">

                    <div>

                        <h2>
                            Hasil Kuis Siswa
                        </h2>

                        <p>
                            Daftar hasil pengerjaan kuis siswa
                        </p>

                    </div>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>Siswa</th>

                                <th>Kuis</th>

                                <th>Kategori</th>

                                <th>Percobaan</th>

                                <th>Nilai</th>

                                <th>KKM</th>

                                <th>Kelulusan</th>

                                <th>Validasi</th>

                                <th>Tanggal</th>

                                <th>Aksi</th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($hasil as $item)

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>


                                    <td>

                                        <span class="student-name">
                                            {{ $item->siswa->nama_lengkap ?? '-' }}
                                        </span>

                                    </td>


                                    <td>

                                        <span class="quiz-name">
                                            {{ $item->kuis->judul ?? '-' }}
                                        </span>

                                    </td>


                                    <td>

                                        @if($item->kuis->kategori === 'remedial')

                                            <span class="category remedial">
                                                Remedial
                                            </span>

                                        @else

                                            <span class="category">
                                                {{ $item->kuis->kategori ?? '-' }}
                                            </span>

                                        @endif

                                    </td>


                                    <td>
                                        {{ $item->percobaan_ke }}
                                    </td>


                                    <td>

                                        <strong class="score">
                                            {{ $item->nilai ?? '-' }}
                                        </strong>

                                    </td>


                                    <td>
                                        {{ $item->kuis->kkm ?? '-' }}
                                    </td>


                                    <td>

                                        @if($item->nilai === null)

                                            -

                                        @elseif(
                                            $item->kuis->kkm !== null &&
                                            $item->nilai >= $item->kuis->kkm
                                        )

                                            <span class="pass">
                                                Lulus
                                            </span>

                                        @else

                                            <span class="fail">
                                                Tidak Lulus
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if($item->status_validasi === 'belum divalidasi')

                                            <span class="waiting">
                                                Menunggu Validasi
                                            </span>

                                        @elseif($item->status_validasi === 'sudah divalidasi')

                                            <span class="validated">
                                                Sudah Divalidasi
                                            </span>

                                        @else

                                            {{ $item->status_validasi ?? '-' }}

                                        @endif

                                    </td>


                                    <td>
                                        {{ $item->tanggal ?? '-' }}
                                    </td>


                                    <td>

                                        <a
                                            href="{{ route(
                                                'guru.nilai.show',
                                                $item->id_mengerjakan
                                            ) }}"
                                            class="btn-view"
                                        >
                                            Lihat Detail
                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        @endif

    </main>

</body>

</html>