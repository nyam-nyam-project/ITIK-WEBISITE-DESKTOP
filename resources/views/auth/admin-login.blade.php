<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Administrator - ITIK</title>
    @include('partials.favicon')
    <script src="{{ asset('js/theme-init.js') }}"></script>
    <script src="{{ asset('js/aksesibilitas-init.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/admin-login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}">
</head>

<body>

    <div class="login-wrapper">

        <!-- BAGIAN KIRI -->
        <div class="login-left">

            <div class="admin-badge">
                @include('partials.icon', ['name' => 'shield']) PORTAL ADMINISTRATOR
            </div>

            <div class="logo">
                <div class="logo-icon">
                    <img src="{{ asset('images/logo-itik.png') }}" alt="Logo ITIK" class="logo-itik-img">
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
                <p>Masukkan username atau email dan password akun administrator.</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login.process') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="login">Username atau Email Administrator</label>
                    <div class="input-wrapper">
                        <span class="input-icon">@include('partials.icon', ['name' => 'mail'])</span>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            class="form-control"
                            placeholder="Username atau admin@sekolah.sch.id"
                            value="{{ old('login', old('email')) }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">@include('partials.icon', ['name' => 'lock'])</span>
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
                            data-password-visible-label="Sembunyikan password"
                            data-password-hidden-label="Tampilkan password"
                            aria-label="Tampilkan password"
                            aria-pressed="false"
                        >
                            <span data-password-icon="hidden">@include('partials.icon', ['name' => 'eye'])</span>
                            <span data-password-icon="visible" hidden>@include('partials.icon', ['name' => 'eye-off'])</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Masuk sebagai Administrator
                </button>
            </form>

            <!-- LINK KEMBALI KE LOGIN SISWA & GURU -->
            <div class="back-link-box">
                <x-tombol-kembali :fallback="route('login')" label="Kembali ke Login Siswa & Guru" class="btn-back-user" />
            </div>

        </div>

    </div>

    <script src="{{ asset('js/auth/password.js') }}" defer></script>

</body>
</html>
