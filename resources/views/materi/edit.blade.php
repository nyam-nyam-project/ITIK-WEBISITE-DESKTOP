<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Materi - ITIK</title>

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

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            background: #eeeaff;
            color: #5b4ae3;

            text-decoration: none;

            padding: 13px 20px;
            border-radius: 13px;

            font-weight: bold;
        }

        .btn-back:hover {
            background: #e2ddff;
        }


        /* ================= FORM ================= */

        .form-card {
            background: white;
            border-radius: 23px;
            padding: 30px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);

            max-width: 100%;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;

            color: #202044;
            font-size: 14px;
            font-weight: bold;

            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;

            border: 1px solid #e3e1ef;
            border-radius: 12px;

            padding: 13px 16px;

            outline: none;
            font-size: 14px;

            color: #202044;
            background: white;

            transition: .2s;
        }

        .form-control:focus {
            border-color: #7160ef;
            box-shadow: 0 0 0 3px rgba(113,96,239,.08);
        }

        textarea.form-control {
            min-height: 140px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-hint {
            color: #aaa7bc;
            font-size: 12px;
            margin-top: 7px;
        }


        /* ================= ERROR ================= */

        .error-box {
            background: #fff0f0;
            border: 1px solid #ffd0d0;

            color: #d64545;

            padding: 15px 18px;
            border-radius: 12px;

            margin-bottom: 25px;
        }

        .error-box strong {
            display: block;
            margin-bottom: 8px;
        }

        .error-box ul {
            padding-left: 20px;
        }

        .error-box li {
            margin-bottom: 4px;
        }


        /* ================= BUTTON ================= */

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;

            margin-top: 30px;
            padding-top: 22px;

            border-top: 1px solid #eeeef5;
        }

        .btn {
            border: none;
            cursor: pointer;

            text-decoration: none;

            padding: 12px 20px;
            border-radius: 12px;

            font-size: 14px;
            font-weight: bold;

            transition: .2s;
        }

        .btn-cancel {
            background: #f1f0f7;
            color: #777394;
        }

        .btn-cancel:hover {
            background: #e5e4ed;
        }

        .btn-update {
            background: #6553e8;
            color: white;
        }

        .btn-update:hover {
            background: #5543d5;
        }


        /* ================= RESPONSIVE ================= */

        @media(max-width: 850px) {

            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .topbar {
                margin-bottom: 25px;
            }

            .page-header {
                gap: 15px;
            }

            .form-card {
                padding: 22px;
            }
        }

        @media(max-width: 600px) {

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .form-footer {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                text-align: center;
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

                <h1>Edit Materi</h1>

                <p>
                    Perbarui informasi materi pembelajaran
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

                <h2>Edit Materi</h2>

                <p>
                    Ubah informasi materi yang sudah tersedia
                </p>

            </div>


            <a
                href="{{ route('materi.index') }}"
                class="btn-back"
            >
                ← Kembali ke Materi
            </a>

        </div>


        <!-- ================= FORM CARD ================= -->

        <div class="form-card">


            <!-- ERROR -->

            @if($errors->any())

                <div class="error-box">

                    <strong>
                        Terjadi kesalahan:
                    </strong>

                    <ul>

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- FORM -->

            <form
                action="{{ route('materi.update', $materi->id_materi) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


                <!-- JUDUL -->

                <div class="form-group">

                    <label>
                        Judul
                    </label>

                    <input
                        type="text"
                        name="judul"
                        class="form-control"
                        value="{{ old('judul', $materi->judul) }}"
                        placeholder="Masukkan judul materi"
                    >

                </div>


                <!-- BAB -->

                <div class="form-group">

                    <label>
                        Bab
                    </label>

                    <input
                        type="text"
                        name="bab"
                        class="form-control"
                        value="{{ old('bab', $materi->bab) }}"
                        placeholder="Contoh: Bab 1"
                    >

                </div>


                <!-- KELAS -->

                <div class="form-group">

                    <label>
                        Kelas
                    </label>

                    <input
                        type="text"
                        name="kelas"
                        class="form-control"
                        value="{{ old('kelas', $materi->kelas) }}"
                        placeholder="Contoh: X RPL 1"
                    >

                </div>


                <!-- DESKRIPSI -->

                <div class="form-group">

                    <label>
                        Deskripsi
                    </label>

                    <textarea
                        name="deskripsi"
                        class="form-control"
                        placeholder="Masukkan deskripsi materi"
                    >{{ old('deskripsi', $materi->deskripsi) }}</textarea>

                </div>


                <!-- ISI MATERI -->

                <div class="form-group">

                    <label>
                        Isi Materi / Path PDF
                    </label>

                    <input
                        type="text"
                        name="isi_materi"
                        class="form-control"
                        value="{{ old('isi_materi', $materi->isi_materi) }}"
                        placeholder="Masukkan path PDF materi"
                    >

                    <div class="form-hint">
                        Masukkan path file PDF materi jika tersedia.
                    </div>

                </div>


                <!-- YOUTUBE -->

                <div class="form-group">

                    <label>
                        Link YouTube
                    </label>

                    <input
                        type="url"
                        name="link_youtube"
                        class="form-control"
                        value="{{ old('link_youtube', $materi->link_youtube) }}"
                        placeholder="https://www.youtube.com/watch?v=..."
                    >

                </div>


                <!-- BUTTON -->

                <div class="form-footer">

                    <a
                        href="{{ route('materi.index') }}"
                        class="btn btn-cancel"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn btn-update"
                    >
                        Update Materi
                    </button>

                </div>


            </form>

        </div>


    </main>

</div>

</body>

</html>