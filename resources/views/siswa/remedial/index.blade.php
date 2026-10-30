<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tugas Remedial - ITIK</title>

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
            color: #d93636;
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
            background: #fff0d4;
            color: #d68a00;
        }

        .badge.selesai {
            background: #e7f8ec;
            color: #20a653;
        }

        .done-text {
            color: #20a653;
            font-weight: bold;
            font-size: 15px;
            white-space: nowrap;
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

        .btn {
            display: inline-block;
            text-decoration: none;
            background: white;
            color: #5d4be4;
            border: 1px solid #ddd9f8;
            padding: 14px 22px;
            border-radius: 14px;
            font-size: 16px;
            margin-top: 25px;
        }

        .btn:hover {
            background: #f5f3ff;
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

            .btn {
                display: block;
                text-align: center;
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

            <a href="{{ url('/siswa/nilai') }}">
                ◉ &nbsp; Nilai Saya
            </a>

            <a href="{{ url('/siswa/remedial') }}" class="active">
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
                <h1>Tugas Remedial</h1>
                <p>Perbaiki nilai kuis yang belum mencapai KKM</p>
            </div>

            <div class="topbar-right">

                <a href="{{ route('siswa.kuis.index') }}" class="see-all">
                    ← Daftar kuis
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


        <div class="section-card">

            <div class="section-header">
                <h2>Tugas Remedial Saya</h2>
                <p>{{ $tugas->count() }} tugas remedial</p>
            </div>


            @if($tugas->isEmpty())

                <div class="empty">
                    <div class="empty-icon">↻</div>
                    <h3>Tidak ada tugas remedial</h3>
                    <p>Tugas remedial akan muncul di sini jika nilaimu belum mencapai KKM.</p>
                </div>

            @else

                <div class="table-wrap">

                    <table>

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kuis Asal</th>
                                <th>Nilai Awal</th>
                                <th>KKM</th>
                                <th>Kuis Remedial</th>
                                <th>Tanggal Ditugaskan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($tugas as $item)

                                @php
                                    $kkm = $item->mengerjakanAsal->kuis->kkm;
                                @endphp

                                <tr>

                                    <td class="col-no">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        <span class="row-title">
                                            {{ $item->mengerjakanAsal->kuis->judul }}
                                        </span>
                                    </td>

                                    <td class="nilai">
                                        {{ $item->mengerjakanAsal->nilai }}
                                    </td>

                                    <td>
                                        {{ $kkm }}
                                    </td>

                                    <td>
                                        {{ $item->kuisRemedial->judul }}
                                    </td>

                                    <td class="col-tanggal">
                                        {{ $item->tanggal_ditugaskan }}
                                    </td>

                                    <td>

                                        @if($item->status === 'ditugaskan')

                                            <span class="badge">Belum Dikerjakan</span>

                                        @elseif($item->status === 'selesai')

                                            <span class="badge selesai">Selesai</span>

                                        @endif

                                    </td>

                                    <td>

                                        @if($item->status === 'ditugaskan')

                                            <a href="{{ route('siswa.remedial.kerjakan', $item->id_remedial_siswa) }}" class="btn-inline">
                                                Kerjakan Remedial
                                            </a>

                                        @elseif($item->status === 'selesai')

                                            <span class="done-text">✓ Sudah Selesai</span>

                                        @else

                                            {{ $item->status }}

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </div>


        <a href="{{ route('siswa.kuis.index') }}" class="btn">
            ← Kembali ke daftar kuis
        </a>

    </main>

</div>

</body>
</html>