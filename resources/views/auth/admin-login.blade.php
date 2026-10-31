<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Administrator - ITIK</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f0eff8;
            color: #202044;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: white;
            border-radius: 28px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            box-shadow: 0 15px 50px rgba(45, 35, 95, .15);
        }

        /* BAGIAN KIRI */
        .login-left {
            background: linear-gradient(180deg, #3d348b, #201b4d);
            color: white;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 25px;
            width: fit-content;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 45px;
        }

        .logo-icon {
            width: 58px;
            height: 58px;
            border: 1px solid rgba(255, 255, 255, .4);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        .logo-text h2 {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .logo-text span {
            font-size: 13px;
            opacity: .8;
        }

        .welcome h1 {
            font-size: 34px;
            line-height: 1.25;
            margin-bottom: 15px;
        }

        .welcome p {
            font-size: 15px;
            line-height: 1.6;
            opacity: .9;
        }

        /* BAGIAN KANAN */
        .login-right {
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            margin-bottom: 30px;
        }

        .login-title h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #202044;
        }

        .login-title p {
            color: #777394;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #3b3955;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9c98b3;
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 13px 18px 13px 45px;
            border: 1px solid #dfddec;
            border-radius: 14px;
            font-size: 15px;
            background: #faf9ff;
            transition: all .2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3d348b;
            background: white;
            box-shadow: 0 0 0 3px rgba(61, 52, 139, .15);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #777394;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3d348b, #252055);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(61, 52, 139, .3);
            transition: transform .1s, box-shadow .2s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(61, 52, 139, .4);
        }

        .alert-error {
            background: #ffe5e5;
            color: #c93434;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            border-left: 4px solid #d64545;
        }

        /* AKUN DEMO */
        .demo-box {
            background: #f8f7fd;
            border: 1px dashed #cfcae8;
            border-radius: 14px;
            padding: 14px 18px;
            margin-top: 25px;
        }

        .demo-title {
            font-size: 12px;
            font-weight: bold;
            color: #4a3f9e;
            margin-bottom: 8px;
        }

        .demo-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .demo-role {
            font-weight: bold;
            color: #3d348b;
        }

        .demo-email {
            color: #55516e;
        }

        .demo-pass {
            font-family: Consolas, monospace;
            background: #eceaf9;
            color: #4a3f9e;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
        }

        /* BACK LINK */
        .back-link-box {
            margin-top: 20px;
            text-align: center;
        }

        .btn-back-user {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #7160ef;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: color .2s;
        }

        .btn-back-user:hover {
            color: #4738be;
            text-decoration: underline;
        }

        @media (max-width: 800px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
            }

            .login-left {
                padding: 35px;
            }

            .login-right {
                padding: 35px;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <!-- BAGIAN KIRI -->
        <div class="login-left">

            <div class="admin-badge">
                🛡️ PORTAL ADMINISTRATOR
            </div>

            <div class="logo">
                <div class="logo-icon">
                    ⚙️
                </div>
                <div class="logo-text">
                    <h2>ITIK Admin</h2>
                    <span>Panel Pengelola Sekolah</span>
                </div>
            </div>

            <div class="welcome">
                <h1>
                    Kelola Sekolah<br>
                    Lebih Praktis
                </h1>
                <p>
                    Akses kontrol untuk manajemen akun siswa, guru, kelas, mata pelajaran,
                    dan rekapitulasi nilai sekolah.
                </p>
            </div>

        </div>

        <!-- BAGIAN KANAN -->
        <div class="login-right">

            <div class="login-title">
                <h2>Login Admin</h2>
                <p>Masukkan email dan password akun administrator.</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login.process') }}" method="POST">
                @csrf

                <!-- EMAIL -->
                <div class="form-group">
                    <label for="email">Email Administrator</label>
                    <div class="input-wrapper">
                        <span class="input-icon">✉</span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="admin@sekolah.sch.id"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password admin"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                            aria-label="Tampilkan password"
                        >
                            👁
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Masuk sebagai Administrator
                </button>
            </form>

            <!-- AKUN DEMO ADMINISTRATOR -->
            <div class="demo-box">
                <div class="demo-title">
                    ℹ️ Akun Demo Administrator
                </div>
                <div class="demo-row">
                    <span class="demo-role">Admin</span>
                    <span class="demo-email">andi@sekolah.sch.id</span>
                    <span class="demo-pass">123456</span>
                </div>
            </div>

            <!-- LINK KEMBALI KE LOGIN SISWA & GURU -->
            <div class="back-link-box">
                <a href="{{ route('login') }}" class="btn-back-user">
                    ← Kembali ke Login Siswa & Guru
                </a>
            </div>

        </div>

    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const button = document.querySelector('.toggle-password');

            if (password.type === 'password') {
                password.type = 'text';
                button.textContent = '🔒';
            } else {
                password.type = 'password';
                button.textContent = '👁';
            }
        }
    </script>

</body>
</html>

