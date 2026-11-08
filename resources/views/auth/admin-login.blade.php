<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Administrator - ITIK</title>


    <link rel="stylesheet" href="{{ asset('css/auth/admin-login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}">
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
                            data-password-toggle="password"
                            data-password-visible-icon="&#128274;"
                            data-password-hidden-icon="&#128065;"
                            data-password-visible-label="Sembunyikan password"
                            data-password-hidden-label="Tampilkan password"
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

    <script src="{{ asset('js/auth/password.js') }}" defer></script>

</body>
</html>
