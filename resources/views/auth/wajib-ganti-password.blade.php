<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password Sementara - ITIK</title>


    <link rel="stylesheet" href="{{ asset('css/auth/wajib-ganti-password.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}">
</head>
<body>

<div class="card-wrapper">
    <div class="header">
        <div class="icon-shield">🔒</div>
        <h1>Ganti Password Sementara</h1>
        <p>
            Admin memberikan password sementara untuk akun Anda.
            Demi keamanan, silakan buat password baru sebelum melanjutkan ke aplikasi.
        </p>
        <span class="user-pill">{{ Auth::user()->nama_lengkap }} ({{ ucfirst(Auth::user()->role) }})</span>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('password.wajib.update') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="password">Password Baru</label>
            <div class="input-wrapper">
                <span class="input-icon">🔑</span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Minimal 6 karakter"
                    required
                    autofocus
                >
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password Baru</label>
            <div class="input-wrapper">
                <span class="input-icon">✓</span>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Ulangi password baru"
                    required
                >
            </div>
        </div>

        <button type="submit" class="btn-submit">
            Simpan Password & Masuk
        </button>
    </form>

    <div class="footer-note">
        Ingin keluar dulu?
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</div>

</body>
</html>
