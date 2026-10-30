<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nilai Saya - ITIK</title>

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

        /* CARD */

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
            vertical-align: middle;
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

        .nilai {
            font-weight: bold;
            font-size: 18px;
        }

        .muted {
            color: #777394;
        }

        .col-tanggal {
            white-space: nowrap;
            color: #777394;
            font-size: 15px;
        }

        /* BADGE */

        .badge {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            white-space: nowrap;
            background: #eeeaff;
            color: #5d4be4;
        }

        .badge.lulus {
            background: #e7f8ec;
            color: #20a653;
        }

        .badge.gagal {
            background: #fde8e8;
            color: #d93636;
        }

        .badge.menunggu {
            background: #fff0d4;
            color: #d68a00;
        }

        /* BUTTON */

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

        /* EMPTY STATE */

        .empty {
            text-align: center;
            padding: 50px 20px;
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

        a:focus-visible {
            outline: 3px solid #b9aefc;
            outline-offset: 2px;
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
                <span>Portal Siswa</span>
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

            <a href="{{ url('/siswa/kuis') }}">
                ☑ &nbsp; Kuis
            </a>

            <a href="{{ url('/siswa/nilai') }}" class="active">
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
                <h1>Nilai Saya</h1>
                <p>Hasil kuis yang sudah kamu kerjakan</p>
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


        <div class="section-card">

            <div class="section-header">
                <h2>Riwayat Nilai</h2>
                <p>{{ $hasil->count() }} hasil kuis</p>
            </div>


            @if($hasil->isEmpty())

                <div class="empty">
                    <div class="empty-icon">◉</div>
                    <h3>Belum ada hasil kuis</h3>
                    <p>Nilaimu akan muncul di sini setelah kamu mengumpulkan kuis.</p>
                </div>

            @else

                <div class="table-wrap">

                    <table>

                        <thead>
                            <tr>
                                <th>No</th>
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

                                    <td class="col-no">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        <span class="row-title">
                                            {{ $item->kuis->judul ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        @if(isset($item->kuis->kategori))
                                            <span class="badge">{{ $item->kuis->kategori }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        {{ $item->percobaan_ke }}
                                    </td>

                                    <td class="nilai">
                                        {{ $item->nilai ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->kuis->kkm ?? '-' }}
                                    </td>

                                    {{-- STATUS KELULUSAN --}}
                                    <td>

                                        @if($item->nilai === null)

                                            <span class="muted">-</span>

                                        @elseif(
                                            $item->kuis->kkm !== null &&
                                            $item->nilai >= $item->kuis->kkm
                                        )

                                            <span class="badge lulus">Lulus</span>

                                        @else

                                            <span class="badge gagal">Tidak Lulus</span>

                                        @endif

                                    </td>

                                    {{-- STATUS VALIDASI --}}
                                    <td>

                                        @if($item->status_validasi === 'belum divalidasi')

                                            <span class="badge menunggu">Menunggu Validasi Guru</span>

                                        @elseif($item->status_validasi === 'sudah divalidasi')

                                            <span class="badge lulus">Sudah Divalidasi</span>

                                        @else

                                            {{ $item->status_validasi ?? '-' }}

                                        @endif

                                    </td>

                                    <td class="col-tanggal">
                                        {{ $item->tanggal ?? '-' }}
                                    </td>

                                    <td>

                                        <a href="{{ route(
                                            'siswa.kuis.hasil',
                                            $item->id_kuis
                                        ) }}" class="btn-inline">
                                            Lihat Detail
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