<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kuis - ITIK</title>

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
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 22px;
        }

        .page-header p {
            color: #777394;
            margin-top: 5px;
        }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            background: #6553e8;
            color: white;

            text-decoration: none;

            padding: 13px 20px;
            border-radius: 13px;

            font-weight: bold;
        }

        .btn-add:hover {
            background: #5543d5;
        }

        /* ================= ALERT ================= */

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #e9f9ef;
            color: #25834a;
            border: 1px solid #c9efd7;
        }

        .alert-error {
            background: #fff0f0;
            color: #d64545;
            border: 1px solid #ffd0d0;
        }

        /* ================= TABLE CARD ================= */

        .table-card {
            background: white;
            border-radius: 23px;
            padding: 25px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);

            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        thead {
            background: #f7f6fc;
        }

        th {
            text-align: left;
            padding: 15px 14px;

            color: #777394;
            font-size: 13px;
            font-weight: bold;

            border-bottom: 1px solid #eeeef5;
        }

        td {
            padding: 17px 14px;

            font-size: 14px;
            color: #44435f;

            border-bottom: 1px solid #eeeef5;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #faf9ff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .id-kuis {
            color: #aaa7bc;
            font-size: 12px;
            font-weight: bold;
        }

        .judul-kuis {
            color: #202044;
            font-weight: bold;
        }

        .kategori {
            display: inline-block;

            background: #eeeaff;
            color: #5b4ae3;

            padding: 7px 11px;
            border-radius: 20px;

            font-size: 12px;
            font-weight: bold;
        }

        .kkm {
            font-weight: bold;
            color: #202044;
        }

        .waktu {
            color: #777394;
        }

        /* ================= STATUS ================= */

        .status {
            display: inline-block;

            padding: 7px 11px;
            border-radius: 20px;

            font-size: 12px;
            font-weight: bold;
        }

        .status-published,
        .status-publikasi,
        .status-publish,
        .status-aktif {
            background: #e9f9ef;
            color: #25834a;
        }

        .status-draft {
            background: #fff3dc;
            color: #c98200;
        }

        /* ================= ACTION ================= */

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            text-decoration: none;

            padding: 8px 12px;
            border-radius: 10px;

            font-size: 12px;
            font-weight: bold;

            border: none;
            cursor: pointer;
        }

        .btn-view {
            background: #eeeaff;
            color: #5b4ae3;
        }

        .btn-edit {
            background: #fff3dc;
            color: #c98200;
        }

        .btn-archive {
            background: #ffe5e5;
            color: #d64545;
        }

        .btn-view:hover {
            background: #e2ddff;
        }

        .btn-edit:hover {
            background: #ffe8bf;
        }

        .btn-archive:hover {
            background: #ffd5d5;
        }

        /* ================= EMPTY ================= */

        .empty {
            background: white;
            border-radius: 23px;
            padding: 60px 30px;

            text-align: center;
            color: #777394;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .empty h3 {
            color: #202044;
            margin-bottom: 7px;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 850px) {

            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .page-header {
                gap: 15px;
            }

            .table-card {
                padding: 15px;
            }
        }

        @media(max-width: 600px) {

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
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

                <h1>Kuis</h1>

                <p>
                    Kelola kuis dan soal pembelajaran TIK
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


        <!-- ================= HEADER ================= -->

        <div class="page-header">

            <div>

                <h2>Data Kuis</h2>

                <p>
                    Daftar kuis yang tersedia
                </p>

            </div>


            <a
                href="{{ route('kuis.create') }}"
                class="btn-add"
            >
                + Tambah Kuis
            </a>

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


        <!-- ================= DATA ================= -->

        @if ($kuis->count() > 0)

            <div class="table-card">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Materi</th>
                            <th>KKM</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach ($kuis as $item)

                            <tr>

                                <td>
                                    <span class="id-kuis">
                                        {{ $item->id_kuis }}
                                    </span>
                                </td>


                                <td>
                                    <div class="judul-kuis">
                                        {{ $item->judul }}
                                    </div>
                                </td>


                                <td>

                                    <span class="kategori">
                                        {{ $item->kategori }}
                                    </span>

                                </td>


                                <td>

                                    @forelse ($item->materi as $materi)

                                        {{ $materi->judul }}

                                        @if (!$loop->last)
                                            <br>
                                        @endif

                                    @empty

                                        -

                                    @endforelse

                                </td>


                                <td>

                                    <span class="kkm">
                                        {{ $item->kkm ?? '-' }}
                                    </span>

                                </td>


                                <td>

                                    <span class="waktu">
                                        {{ $item->alokasi_waktu ?? '-' }} menit
                                    </span>

                                </td>


                                <td>

                                    <span class="status
                                        status-{{ strtolower(str_replace(' ', '-', $item->status_publikasi)) }}">

                                        {{ $item->status_publikasi }}

                                    </span>

                                </td>


                                <td>

                                    <div class="actions">

                                        <a
                                            href="{{ route('kuis.show', $item->id_kuis) }}"
                                            class="btn btn-view"
                                        >
                                            Lihat
                                        </a>


                                        <a
                                            href="{{ route('kuis.edit', $item->id_kuis) }}"
                                            class="btn btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <form
                                            action="{{ route('kuis.destroy', $item->id_kuis) }}"
                                            method="POST"
                                            style="display:inline;"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-archive"
                                                onclick="return confirm('Arsipkan kuis ini?')"
                                            >
                                                Arsipkan
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty">

                <div class="empty-icon">
                    📝
                </div>

                <h3>
                    Belum ada data kuis
                </h3>

                <p>
                    Silakan tambahkan kuis pembelajaran pertama.
                </p>

            </div>

        @endif


    </main>

</div>

</body>

</html>