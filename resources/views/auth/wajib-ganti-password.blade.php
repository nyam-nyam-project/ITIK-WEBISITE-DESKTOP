<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password Sementara - ITIK</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f5f4fc;
            color: #202044;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .card-wrapper {
            width: 100%;
            max-width: 520px;
            background: white;
            border-radius: 28px;
            box-shadow: 0 15px 50px rgba(70, 60, 130, .12);
            padding: 45px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .icon-shield {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #7160ef, #5948dd);
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 18px;
            box-shadow: 0 8px 20px rgba(113, 96, 239, .25);
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            color: #202044;
        }

        .header p {
            color: #777394;
            font-size: 14px;
            line-height: 1.5;
        }

        .user-pill {
            display: inline-block;
            background: #f0eff8;
            color: #5b4ae3;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 10px;
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
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 13px 18px 13px 45px;
            border: 1px solid #dfddec;
            border-radius: 14px;
            font-size: 15px;
            transition: all .2s;
            background: #faf9ff;
        }

        .form-control:focus {
            outline: none;
            border-color: #7160ef;
            background: white;
            box-shadow: 0 0 0 3px rgba(113, 96, 239, .15);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #7160ef, #5948dd);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(113, 96, 239, .3);
            transition: transform .1s, box-shadow .2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(113, 96, 239, .38);
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

        .footer-note {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
        }

        .footer-note form {
            display: inline;
        }

        .footer-note button {
            background: none;
            border: none;
            color: #d64545;
            cursor: pointer;
            font-size: 13px;
            text-decoration: underline;
        }
    </style>
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

