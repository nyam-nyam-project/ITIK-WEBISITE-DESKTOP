<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil - ITIK</title>

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

        .btn-password {
            background: #eeeaff;
            color: #5b4ae3;
        }

        .btn-password:hover {
            background: #e0d9fc;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7160ef, #5948dd);
            color: white;
        }

        .btn-primary:hover {
            opacity: .95;
        }

        .alert-success {
            background: #e8f7ee;
            color: #1b7a43;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: #ffe5e5;
            color: #c93434;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 16px;
            border-left: 4px solid #d64545;
        }

        /* ================= MODAL GANTI PASSWORD ================= */

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(32, 32, 68, 0.55);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.22);
            animation: modalFadeIn .2s ease-out;
        }

        @keyframes modalFadeIn {
            from { transform: scale(0.94); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .modal-header h3 {
            font-size: 20px;
            color: #202044;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 26px;
            color: #777394;
            cursor: pointer;
            line-height: 1;
            padding: 4px;
        }

        .btn-close:hover {
            color: #202044;
        }

        .modal-form .form-group {
            margin-bottom: 16px;
        }

        .modal-form label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #3b3955;
        }

        .modal-form .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #dfddec;
            border-radius: 12px;
            font-size: 14px;
            background: #faf9ff;
            transition: all .2s;
        }

        .modal-form .form-control:focus {
            outline: none;
            border-color: #7160ef;
            background: white;
            box-shadow: 0 0 0 3px rgba(113, 96, 239, .15);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 24px;
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
                <span>{{ Auth::user()->role === 'siswa' ? 'Portal Siswa' : 'Portal Guru' }}</span>
            </div>

        </div>

        <div class="menu-title">
            Menu Utama
        </div>

        @if(Auth::user()->role === 'siswa')

        <nav class="menu">

            <a href="{{ route('siswa.dashboard') }}">
                ▦ &nbsp; Dashboard
            </a>

            <a href="{{ route('siswa.materi.index') }}">
                ▣ &nbsp; Materi
            </a>

            <a href="{{ route('siswa.kuis.index') }}">
                ☑ &nbsp; Kuis
            </a>

            <a href="{{ route('siswa.nilai') }}">
                ◉ &nbsp; Nilai Saya
            </a>

            <a href="{{ route('siswa.remedial.index') }}">
                ↻ &nbsp; Remedial
            </a>

            <a href="{{ route('profil') }}" class="active">
                ◎ &nbsp; Profil
            </a>

        </nav>

        @else

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

        @endif

    </aside>


    <!-- ================= CONTENT ================= -->

    <main class="content">

        <div class="topbar">

            <div class="title">

                <h1>Profil</h1>

                <p>
                    Informasi akun {{ Auth::user()->role }}
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
                    {{ ucfirst(Auth::user()->role) }} Portal ITIK
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

                @if(session('success'))
                    <div class="alert-success">
                        ✓ {{ session('success') }}
                    </div>
                @endif


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

                    <button
                        type="button"
                        class="btn btn-password"
                        onclick="bukaModalPassword()"
                    >
                        🔑 &nbsp; Ganti Password
                    </button>

                    <a
                        href="{{ Auth::user()->role === 'siswa' ? route('siswa.dashboard') : route('dashboard') }}"
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

    <!-- ================= MODAL GANTI PASSWORD ================= -->
    <div id="modalPassword" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Ganti Password</h3>
                <button type="button" class="btn-close" onclick="tutupModalPassword()">&times;</button>
            </div>

            @if ($errors->has('password_lama') || $errors->has('password_baru'))
                <div class="alert-error">
                    {{ $errors->first('password_lama') ?: $errors->first('password_baru') }}
                </div>
            @endif

            <form action="{{ route('profil.password') }}" method="POST" class="modal-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="password_lama">Password Saat Ini</label>
                    <input
                        type="password"
                        id="password_lama"
                        name="password_lama"
                        class="form-control"
                        placeholder="Masukkan password saat ini"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <input
                        type="password"
                        id="password_baru"
                        name="password_baru"
                        class="form-control"
                        placeholder="Minimal 6 karakter"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_baru_confirmation">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        id="password_baru_confirmation"
                        name="password_baru_confirmation"
                        class="form-control"
                        placeholder="Ulangi password baru"
                        required
                    >
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-back" onclick="tutupModalPassword()">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalPassword() {
            document.getElementById('modalPassword').classList.add('active');
        }

        function tutupModalPassword() {
            document.getElementById('modalPassword').classList.remove('active');
        }

        document.getElementById('modalPassword').addEventListener('click', function(e) {
            if (e.target === this) {
                tutupModalPassword();
            }
        });

        @if ($errors->has('password_lama') || $errors->has('password_baru'))
            bukaModalPassword();
        @endif
    </script>

</body>
</html>