<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Soal - ITIK</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f4fc;
            color: #202044;
            min-height: 100vh;
        }

        .app {
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
            min-width: 0;
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
            gap: 20px;
            margin-bottom: 22px;
        }

        .page-header h2 {
            font-size: 24px;
            margin-bottom: 7px;
        }

        .page-header p {
            color: #777394;
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        /* ================= BUTTON ================= */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 12px;
            border: none;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .btn-primary {
            background: #6553e8;
            color: white;
        }

        .btn-primary:hover {
            background: #5543d5;
        }

        .btn-secondary {
            background: #eeeaff;
            color: #5b4ae3;
        }

        .btn-secondary:hover {
            background: #e2ddff;
        }

        /* ================= ALERT ================= */

        .alert {
            padding: 15px 18px;
            border-radius: 13px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #e8f8ef;
            color: #188650;
            border: 1px solid #c9ecd9;
        }

        .alert-error {
            background: #fff0f0;
            color: #d64545;
            border: 1px solid #ffd0d0;
        }

        .error-list {
            margin: 8px 0 0 20px;
        }

        /* ================= QUIZ INFO ================= */

        .quiz-info {
            background: white;
            border-radius: 20px;
            padding: 24px 28px;
            margin-bottom: 22px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .quiz-info h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .quiz-info p {
            color: #777394;
            font-size: 14px;
        }

        /* ================= FORM ================= */

        .form-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #29274a;
            margin-bottom: 8px;
        }

        .required {
            color: #d64545;
        }

        .form-control {
            width: 100%;
            border: 1px solid #e4e2ee;
            background: #faf9ff;
            border-radius: 12px;
            padding: 13px 15px;
            font-size: 14px;
            color: #29274a;
            outline: none;
            transition: .2s;
        }

        .form-control:focus {
            border-color: #6553e8;
            background: white;
            box-shadow: 0 0 0 3px rgba(101,83,232,.08);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 130px;
            line-height: 1.6;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .helper {
            display: block;
            margin-top: 7px;
            color: #9996aa;
            font-size: 12px;
            line-height: 1.5;
        }

        /* ================= OPTION ================= */

        .option-title {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .option-badge {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #eeeaff;
            color: #5b4ae3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* ================= ACTIONS ================= */

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 22px;
            margin-top: 8px;
            border-top: 1px solid #f0eff6;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width:900px) {
            .sidebar {
                display: none;
            }

            .content {
                padding: 25px;
            }
        }

        @media(max-width:700px) {
            .profile-mini {
                display: none;
            }

            .topbar {
                margin-bottom: 25px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                flex: 1;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width:500px) {
            .content {
                padding: 20px 15px;
            }

            .form-card {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="logo">
            <div class="logo-icon">IT</div>

            <div>
                <h2>ITIK</h2>
                <span>Learning System</span>
            </div>
        </div>

        <div class="menu-title">
            MENU
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


    <!-- CONTENT -->
    <main class="content">

        <!-- TOPBAR -->
        <div class="topbar">

            <div class="title">
                <h1>Edit Soal</h1>
                <p>Perbarui pertanyaan dan jawaban soal</p>
            </div>

            <div class="profile-mini">

                <div class="avatar-small">
                    {{ strtoupper(substr(Auth::user()->nama_lengkap, 0, 2)) }}
                </div>

                <div>
                    <strong>{{ Auth::user()->nama_lengkap }}</strong>
                    <br>
                    <small>{{ ucfirst(Auth::user()->role) }}</small>
                </div>

            </div>

        </div>


        <!-- PAGE HEADER -->
        <div class="page-header">

            <div>
                <h2>Edit Soal</h2>
                <p>Ubah informasi soal pada kuis ini.</p>
            </div>

            <div class="header-actions">

                <a href="{{ route('kuis.soal.index', $kuis->id_kuis) }}"
                   class="btn btn-secondary">
                    ← Kembali ke Daftar Soal
                </a>

            </div>

        </div>


        <!-- ERROR VALIDATION -->
        @if ($errors->any())

            <div class="alert alert-error">

                <strong>Terjadi kesalahan:</strong>

                <ul class="error-list">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        <!-- SESSION ERROR -->
        @if (session('error'))

            <div class="alert alert-error">
                {{ session('error') }}
            </div>

        @endif


        <!-- QUIZ INFO -->
        <div class="quiz-info">

            <h3>{{ $kuis->judul }}</h3>

            <p>
                ID Kuis: {{ $kuis->id_kuis }}
                &nbsp; • &nbsp;
                Soal: {{ $soal->id_soal }}
            </p>

        </div>


        <!-- FORM -->
        <div class="form-card">

            <form
                action="{{ route('kuis.soal.update', [$kuis->id_kuis, $soal->id_soal]) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


                <!-- PERTANYAAN -->
                <div class="form-group">

                    <label for="pertanyaan">
                        Pertanyaan <span class="required">*</span>
                    </label>

                    <textarea
                        id="pertanyaan"
                        name="pertanyaan"
                        class="form-control"
                        rows="5"
                        required
                    >{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>

                </div>


                <!-- OPSI A & B -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="opsi_a">
                            <span class="option-title">
                                <span class="option-badge">A</span>
                                Opsi A
                            </span>
                        </label>

                        <input
                            type="text"
                            id="opsi_a"
                            name="opsi_a"
                            class="form-control"
                            value="{{ old('opsi_a', $soal->opsi_a) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="opsi_b">
                            <span class="option-title">
                                <span class="option-badge">B</span>
                                Opsi B
                            </span>
                        </label>

                        <input
                            type="text"
                            id="opsi_b"
                            name="opsi_b"
                            class="form-control"
                            value="{{ old('opsi_b', $soal->opsi_b) }}"
                            required
                        >

                    </div>

                </div>


                <!-- OPSI C & D -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="opsi_c">
                            <span class="option-title">
                                <span class="option-badge">C</span>
                                Opsi C
                            </span>
                        </label>

                        <input
                            type="text"
                            id="opsi_c"
                            name="opsi_c"
                            class="form-control"
                            value="{{ old('opsi_c', $soal->opsi_c) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="opsi_d">
                            <span class="option-title">
                                <span class="option-badge">D</span>
                                Opsi D
                            </span>
                        </label>

                        <input
                            type="text"
                            id="opsi_d"
                            name="opsi_d"
                            class="form-control"
                            value="{{ old('opsi_d', $soal->opsi_d) }}"
                            required
                        >

                    </div>

                </div>


                <!-- OPSI E -->
                <div class="form-group">

                    <label for="opsi_e">
                        <span class="option-title">
                            <span class="option-badge">E</span>
                            Opsi E
                        </span>
                    </label>

                    <input
                        type="text"
                        id="opsi_e"
                        name="opsi_e"
                        class="form-control"
                        value="{{ old('opsi_e', $soal->opsi_e) }}"
                    >

                    <small class="helper">
                        Opsi E bersifat opsional.
                    </small>

                </div>


                <!-- JAWABAN + BOBOT -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="jawaban">
                            Jawaban Benar <span class="required">*</span>
                        </label>

                        <select
                            id="jawaban"
                            name="jawaban"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Jawaban --
                            </option>

                            <option value="A"
                                {{ old('jawaban', $soal->jawaban) == 'A' ? 'selected' : '' }}>
                                A
                            </option>

                            <option value="B"
                                {{ old('jawaban', $soal->jawaban) == 'B' ? 'selected' : '' }}>
                                B
                            </option>

                            <option value="C"
                                {{ old('jawaban', $soal->jawaban) == 'C' ? 'selected' : '' }}>
                                C
                            </option>

                            <option value="D"
                                {{ old('jawaban', $soal->jawaban) == 'D' ? 'selected' : '' }}>
                                D
                            </option>

                            <option value="E"
                                {{ old('jawaban', $soal->jawaban) == 'E' ? 'selected' : '' }}>
                                E
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="bobot">
                            Bobot Nilai (%) <span class="required">*</span>
                        </label>

                        <input
                            type="number"
                            id="bobot"
                            name="bobot"
                            class="form-control"
                            value="{{ old('bobot', $soal->bobot) }}"
                            min="0.01"
                            max="100"
                            step="0.01"
                            required
                        >

                        <small class="helper">
                            Total bobot semua soal dalam kuis tidak boleh lebih dari 100%.
                        </small>

                    </div>

                </div>


                <!-- TINGKAT KESULITAN -->
                <div class="form-group">

                    <label for="tingkat_kesulitan">
                        Tingkat Kesulitan
                    </label>

                    <select
                        id="tingkat_kesulitan"
                        name="tingkat_kesulitan"
                        class="form-control"
                    >

                        <option value="">
                            -- Pilih Tingkat Kesulitan --
                        </option>

                        <option value="mudah"
                            {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'mudah' ? 'selected' : '' }}>
                            Mudah
                        </option>

                        <option value="sedang"
                            {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'sedang' ? 'selected' : '' }}>
                            Sedang
                        </option>

                        <option value="sulit"
                            {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'sulit' ? 'selected' : '' }}>
                            Sulit
                        </option>

                    </select>

                </div>


                <!-- ACTION -->
                <div class="form-actions">

                    <a
                        href="{{ route('kuis.soal.index', $kuis->id_kuis) }}"
                        class="btn btn-secondary"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>