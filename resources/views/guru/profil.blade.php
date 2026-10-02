<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil Guru - ITIK</title>

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

        /* ================= PROFILE ================= */

        .profile-container {
            display: grid;
            grid-template-columns: 330px 1fr;
            gap: 25px;
        }

        .card {
            background: white;
            border-radius: 25px;
            padding: 30px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        /* PROFILE LEFT */

        .profile-card {
            text-align: center;
        }

        .avatar-large {
            width: 110px;
            height: 110px;

            margin: 5px auto 20px;

            border-radius: 50%;
            background: linear-gradient(135deg, #7160ef, #5948dd);

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 36px;
            font-weight: bold;
        }

        .profile-card h2 {
            font-size: 24px;
            margin-bottom: 7px;
        }

        .role {
            color: #777394;
            font-size: 15px;
            margin-bottom: 25px;
        }

        .role-badge {
            display: inline-block;

            background: #eeeaff;
            color: #5b4ae3;

            padding: 8px 18px;
            border-radius: 20px;

            font-size: 14px;
            font-weight: bold;
        }

        /* PROFILE RIGHT */

        .card-title {
            margin-bottom: 25px;
        }

        .card-title h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .card-title p {
            color: #777394;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            padding: 18px;
            background: #f8f7fd;

            border-radius: 16px;
        }

        .info-label {
            color: #777394;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .info-value {
            font-size: 16px;
            font-weight: bold;
            word-break: break-word;
        }

        /* ================= ACTION ================= */

        .actions {
            margin-top: 25px;

            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            text-decoration: none;
            border: none;

            padding: 12px 20px;
            border-radius: 12px;

            font-size: 15px;
            font-weight: bold;

            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-back {
            background: #f0eff8;
            color: #55526f;
        }

        .btn-back:hover {
            background: #e7e5f2;
        }

        .btn-logout {
            background: #ffe5e5;
            color: #d64545;
        }

        .btn-logout:hover {
            background: #ffd5d5;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 1000px) {

            .profile-container {
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
                align-items: flex-start;
            }

            .profile-mini {
                display: none;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

        }

        @media(max-width: 500px) {

            .actions {
                flex-direction: column;
            }

            .actions .btn,
            .actions form {
                width: 100%;
            }

            .actions form button {
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

            <a href="{{ route('dashboard') }}">
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

            <a href="{{ route('profil') }}" class="active">
                ◎ &nbsp; Profil
            </a>

        </nav>

    </aside>


    <!-- ================= CONTENT ================= -->

    <main class="content">

        <div class="topbar">

            <div class="title">

                <h1>Profil</h1>

                <p>
                    Informasi akun guru
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


        <!-- ================= PROFILE CONTENT ================= -->

        <div class="profile-container">

            <!-- PROFILE CARD -->

            <div class="card profile-card">

                <div class="avatar-large">

                    {{ strtoupper(substr(Auth::user()->nama_lengkap, 0, 2)) }}

                </div>

                <h2>
                    {{ Auth::user()->nama_lengkap }}
                </h2>

                <div class="role">
                    Guru Portal ITIK
                </div>

                <span class="role-badge">
                    {{ ucfirst(Auth::user()->role) }}
                </span>

            </div>


            <!-- INFORMATION -->

            <div class="card">

                <div class="card-title">

                    <h2>Informasi Akun</h2>

                    <p>
                        Data akun yang sedang digunakan
                    </p>

                </div>


                <div class="info-grid">

                    <div class="info-item">

                        <div class="info-label">
                            Nama Lengkap
                        </div>

                        <div class="info-value">
                            {{ Auth::user()->nama_lengkap }}
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">
                            Nomor Induk
                        </div>

                        <div class="info-value">
                            {{ Auth::user()->nomor_induk ?? '-' }}
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">
                            Email
                        </div>

                        <div class="info-value">
                            {{ Auth::user()->email }}
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">
                            Role
                        </div>

                        <div class="info-value">
                            {{ ucfirst(Auth::user()->role) }}
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">
                            Kelas
                        </div>

                        <div class="info-value">
                            {{ Auth::user()->kelas ?? '-' }}
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">
                            ID User
                        </div>

                        <div class="info-value">
                            {{ Auth::user()->id_user }}
                        </div>

                    </div>

                </div>


                <!-- ================= ACTION ================= -->

                <div class="actions">

                    <a
                        href="{{ route('dashboard') }}"
                        class="btn btn-back"
                    >
                        ← Kembali
                    </a>


                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="btn btn-logout"
                        >
                            ↪ &nbsp; Logout
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>