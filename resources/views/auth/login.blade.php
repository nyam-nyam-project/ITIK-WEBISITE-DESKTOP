<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - ITIK</title>


    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}">
</head>

<body>

    <div class="login-wrapper">

        <!-- =========================
             BAGIAN KIRI
        ========================== -->

        <div class="login-left">

            <div class="logo">

                <div class="logo-icon">
                    IT
                </div>

                <div class="logo-text">
                    <h2>ITIK</h2>
                    <span>Media Pembelajaran</span>
                </div>

            </div>

            <div class="welcome">

                <h1>
                    Selamat Datang<br>
                    Kembali 👋
                </h1>

                <p>
                    Masuk ke akun ITIK untuk mengakses
                    materi, kuis, nilai, dan berbagai
                    aktivitas pembelajaran.
                </p>

            </div>

            <div class="decoration">
                <span></span>
                <span></span>
                <span></span>
            </div>

        </div>


        <!-- =========================
             BAGIAN KANAN
        ========================== -->

        <div class="login-right">

            <div class="login-title">

                <h2>Login</h2>

                <p>
                    Silakan masuk menggunakan akun kamu.
                </p>

            </div>


            <!-- ERROR LOGIN -->

            @if ($errors->any())

                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>

            @endif


            <form
                action="{{ route('login.process') }}"
                method="POST"
            >

                @csrf


                <!-- IDENTITAS (NISN / NIP) -->

                <div class="form-group">

                    <label for="nomor_induk">
                        NISN / NIP
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            👤
                        </span>

                        <input
                            type="text"
                            id="nomor_induk"
                            name="nomor_induk"
                            class="form-control"
                            placeholder="Masukkan NISN (Siswa) atau NIP (Guru)"
                            value="{{ old('nomor_induk', old('login', old('email'))) }}"
                            required
                            autofocus
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔒
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control password-input"
                            placeholder="Masukkan password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-password-toggle="password"
                            data-password-visible-icon="&#128584;"
                            data-password-hidden-icon="&#128065;"
                            data-password-visible-label="Sembunyikan password"
                            data-password-hidden-label="Tampilkan password"
                            aria-label="Tampilkan password"
                        >
                            👁
                        </button>

                    </div>

                </div>


                <!-- BUTTON LOGIN -->

                <button
                    type="submit"
                    class="btn-login"
                >
                    Masuk ke ITIK
                </button>

            </form>


            <!-- AKUN DEMO -->

            <div class="demo-box">

                <div class="demo-title">
                    ℹ️ Akun Demo
                </div>

                <div class="demo-row">
                    <span class="demo-role">Guru (NIP)</span>
                    <span class="demo-email">198001</span>
                    <span class="demo-pass">password123</span>
                </div>

                <div class="demo-row">
                    <span class="demo-role">Siswa (NISN)</span>
                    <span class="demo-email">202401</span>
                    <span class="demo-pass">password123</span>
                </div>

            </div>

            <!-- LINK MENUJU LOGIN ADMIN -->
            <div class="admin-portal-box">
                <a href="{{ route('admin.login') }}" class="btn-admin-portal">
                    🛡️ &nbsp; Login sebagai Administrator
                </a>
            </div>


            <div class="login-footer">
                © {{ date('Y') }} ITIK — Media Pembelajaran
            </div>

        </div>

    </div>


    <!-- =========================
         JAVASCRIPT PASSWORD
    ========================== -->

    <script src="{{ asset('js/auth/password.js') }}" defer></script>

</body>

</html>
