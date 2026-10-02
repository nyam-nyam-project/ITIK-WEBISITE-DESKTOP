<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - ITIK</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: #f5f4fc;
            color: #202044;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 25px;
        }

        /* =========================
           LOGIN WRAPPER
        ========================= */

        .login-wrapper {
            width: 100%;
            max-width: 1000px;
            min-height: 600px;

            background: white;
            border-radius: 28px;

            overflow: hidden;

            display: grid;
            grid-template-columns: 1fr 1fr;

            box-shadow: 0 15px 50px rgba(70, 60, 130, .12);
        }

        /* =========================
           BAGIAN KIRI
        ========================= */

        .login-left {
            background: linear-gradient(
                180deg,
                #7160ef,
                #5948dd
            );

            color: white;

            padding: 55px;

            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;

            margin-bottom: 55px;
        }

        .logo-icon {
            width: 58px;
            height: 58px;

            border: 1px solid rgba(255, 255, 255, .4);
            border-radius: 16px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 23px;
            font-weight: bold;
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
            font-size: 38px;
            line-height: 1.2;

            margin-bottom: 18px;
        }

        .welcome p {
            font-size: 16px;
            line-height: 1.7;

            color: rgba(255, 255, 255, .85);

            max-width: 390px;
        }

        .decoration {
            margin-top: 45px;

            display: flex;
            gap: 8px;
        }

        .decoration span {
            width: 35px;
            height: 6px;

            border-radius: 20px;

            background: rgba(255, 255, 255, .35);
        }

        .decoration span:first-child {
            width: 65px;
            background: white;
        }

        /* =========================
           BAGIAN KANAN
        ========================= */

        .login-right {
            padding: 55px;

            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            margin-bottom: 35px;
        }

        .login-title h2 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .login-title p {
            color: #777394;
            font-size: 15px;
        }

        /* =========================
           ALERT
        ========================= */

        .alert {
            padding: 14px 16px;

            border-radius: 12px;

            margin-bottom: 20px;

            font-size: 14px;
        }

        .alert-error {
            background: #fff0f0;
            color: #d64545;

            border: 1px solid #ffd0d0;
        }

        /* =========================
           FORM
        ========================= */

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;

            margin-bottom: 9px;

            font-size: 14px;
            font-weight: bold;

            color: #393653;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 16px;
            top: 50%;

            transform: translateY(-50%);

            color: #9996aa;

            font-size: 16px;

            pointer-events: none;
        }

        .form-control {
            width: 100%;

            padding: 14px 16px 14px 45px;

            border: 1px solid #e6e4ef;

            border-radius: 13px;

            background: #faf9ff;

            color: #29274a;

            font-size: 14px;

            outline: none;

            transition: .2s;
        }

        .form-control:focus {
            border-color: #6553e8;

            background: white;

            box-shadow: 0 0 0 4px rgba(101, 83, 232, .08);
        }

        .form-control::placeholder {
            color: #aaa7b8;
        }

        /* =========================
           PASSWORD
        ========================= */

        .password-input {
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;

            right: 15px;
            top: 50%;

            transform: translateY(-50%);

            border: none;
            background: transparent;

            color: #9996aa;

            font-size: 17px;

            cursor: pointer;

            padding: 4px;

            display: flex;
            align-items: center;
            justify-content: center;

            transition: .2s;
        }

        .toggle-password:hover {
            color: #6553e8;
        }

        /* =========================
           BUTTON LOGIN
        ========================= */

        .btn-login {
            width: 100%;

            padding: 15px;

            border: none;
            border-radius: 13px;

            background: #6553e8;
            color: white;

            font-size: 15px;
            font-weight: bold;

            cursor: pointer;

            transition: .2s;

            margin-top: 5px;
        }

        .btn-login:hover {
            background: #5543d5;

            transform: translateY(-1px);

            box-shadow: 0 8px 20px rgba(101, 83, 232, .2);
        }

        /* =========================
           FOOTER
        ========================= */

        .login-footer {
            text-align: center;

            margin-top: 28px;

            color: #aaa7b8;

            font-size: 12px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 800px) {

            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
            }

            .login-left {
                padding: 40px;
            }

            .welcome h1 {
                font-size: 30px;
            }

            .welcome p {
                font-size: 14px;
            }

            .decoration {
                display: none;
            }

            .login-right {
                padding: 40px;
            }
        }

        @media (max-width: 500px) {

            body {
                padding: 15px;
            }

            .login-wrapper {
                border-radius: 20px;
            }

            .login-left {
                padding: 30px;
            }

            .login-right {
                padding: 30px 25px;
            }

            .logo {
                margin-bottom: 35px;
            }

            .welcome h1 {
                font-size: 27px;
            }

            .login-title h2 {
                font-size: 26px;
            }
        }
    </style>
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


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            ✉
                        </span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Masukkan email"
                            value="{{ old('email') }}"
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
                            onclick="togglePassword()"
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


            <div class="login-footer">
                © {{ date('Y') }} ITIK — Media Pembelajaran
            </div>

        </div>

    </div>


    <!-- =========================
         JAVASCRIPT PASSWORD
    ========================== -->

    <script>

        function togglePassword() {

            const password =
                document.getElementById('password');

            const button =
                document.querySelector('.toggle-password');


            if (password.type === 'password') {

                password.type = 'text';

                button.textContent = '🙈';

                button.setAttribute(
                    'aria-label',
                    'Sembunyikan password'
                );

            } else {

                password.type = 'password';

                button.textContent = '👁';

                button.setAttribute(
                    'aria-label',
                    'Tampilkan password'
                );

            }

        }

    </script>

</body>

</html>