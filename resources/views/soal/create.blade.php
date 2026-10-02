<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Soal - ITIK</title>

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

        /* ================= HEADER ================= */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 28px;
            margin-bottom: 7px;
        }

        .page-header p {
            color: #777394;
            font-size: 15px;
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

        .alert-error {
            background: #fff0f0;
            color: #d64545;
            border: 1px solid #ffd0d0;
        }

        /* ================= FORM CARD ================= */

        .form-card {
            background: white;
            border-radius: 20px;

            padding: 30px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .form-card h3 {
            font-size: 21px;
            margin-bottom: 25px;
        }

        /* ================= FORM ================= */

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;

            margin-bottom: 9px;

            font-size: 14px;
            font-weight: bold;

            color: #29274a;
        }

        .required {
            color: #d64545;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;

            padding: 12px 14px;

            border: 1px solid #deddec;
            border-radius: 11px;

            background: #fff;

            color: #29274a;

            font-size: 14px;

            outline: none;

            transition: .2s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #6553e8;
            box-shadow: 0 0 0 3px rgba(101,83,232,.08);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.6;
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
            font-size: 21px;
            margin-bottom: 8px;
        }

        .quiz-info p {
            color: #777394;
            font-size: 14px;
        }

        /* ================= OPTIONS ================= */

        .section-title {
            font-size: 17px;
            font-weight: bold;

            margin: 28px 0 18px;

            color: #29274a;
        }

        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 18px;
        }

        .option-label {
            display: flex !important;
            align-items: center;
            gap: 8px;
        }

        .option-badge {
            width: 30px;
            height: 30px;

            border-radius: 9px;

            background: #eeeaff;
            color: #5b4ae3;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            font-weight: bold;
        }

        .optional {
            font-size: 12px;
            color: #9996aa;
            font-weight: normal;
        }

        /* ================= TWO COLUMN ================= */

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 18px;
        }

        /* ================= HELP ================= */

        .form-help {
            display: block;

            margin-top: 7px;

            color: #9996aa;

            font-size: 12px;
        }

        /* ================= ACTION ================= */

        .form-actions {
            display: flex;
            justify-content: flex-end;

            gap: 10px;

            margin-top: 30px;
            padding-top: 22px;

            border-top: 1px solid #f0eff6;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 900px) {

            .sidebar {
                display: none;
            }

            .content {
                padding: 25px;
            }
        }

        @media(max-width: 700px) {

            .profile-mini {
                display: none;
            }

            .topbar {
                margin-bottom: 25px;
            }

            .options-grid,
            .two-column {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 22px;
            }
        }

        @media(max-width: 500px) {

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

            <a
                href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                ▦ &nbsp; Dashboard
            </a>

            <a
                href="{{ route('materi.index') }}"
                class="{{ request()->routeIs('materi.*') ? 'active' : '' }}"
            >
                ▣ &nbsp; Materi
            </a>

            <a
                href="{{ route('kuis.index') }}"
                class="{{ request()->routeIs('kuis.*') ? 'active' : '' }}"
            >
                ☑ &nbsp; Kuis & Soal
            </a>

            <a
                href="{{ route('guru.nilai.index') }}"
                class="{{ request()->routeIs('guru.nilai.*') ? 'active' : '' }}"
            >
                ◉ &nbsp; Nilai
            </a>

            <a
                href="{{ route('profil') }}"
                class="{{ request()->routeIs('profil') ? 'active' : '' }}"
            >
                ◎ &nbsp; Profil
            </a>

        </nav>

    </aside>


    <!-- ================= CONTENT ================= -->

    <main class="content">


        <!-- TOPBAR -->

        <div class="topbar">

            <div class="title">

                <h1>Tambah Soal</h1>

                <p>
                    Tambahkan soal baru ke dalam kuis
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


        <!-- ================= PAGE HEADER ================= -->

        <div class="page-header">

            <div>

                <h2>
                    Tambah Soal
                </h2>

                <p>
                    Buat pertanyaan dan tentukan jawaban yang benar.
                </p>

            </div>


            <div class="header-actions">

                <a
                    href="{{ route('kuis.soal.index', $kuis->id_kuis) }}"
                    class="btn btn-secondary"
                >
                    ← Kembali ke Daftar Soal
                </a>

            </div>

        </div>


        <!-- ================= QUIZ INFO ================= -->

        <div class="quiz-info">

            <h3>
                {{ $kuis->judul }}
            </h3>

            <p>
                Kategori: {{ $kuis->kategori }}
                &nbsp; • &nbsp;
                ID Kuis: {{ $kuis->id_kuis }}
            </p>

        </div>


        <!-- ================= ERROR ================= -->

        @if ($errors->any())

            <div class="alert alert-error">

                <strong>
                    Terdapat kesalahan:
                </strong>

                <ul style="margin-top: 8px; padding-left: 20px;">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        @if (session('error'))

            <div class="alert alert-error">
                {{ session('error') }}
            </div>

        @endif


        <!-- ================= FORM ================= -->

        <div class="form-card">

            <h3>
                Informasi Soal
            </h3>


            <form
                action="{{ route('kuis.soal.store', $kuis->id_kuis) }}"
                method="POST"
            >

                @csrf


                <!-- PERTANYAAN -->

                <div class="form-group">

                    <label>
                        Pertanyaan
                        <span class="required">*</span>
                    </label>

                    <textarea
                        name="pertanyaan"
                        required
                        placeholder="Tulis pertanyaan soal..."
                    >{{ old('pertanyaan') }}</textarea>

                </div>


                <!-- PILIHAN JAWABAN -->

                <div class="section-title">
                    Pilihan Jawaban
                </div>


                <div class="options-grid">


                    <!-- A -->

                    <div class="form-group">

                        <label class="option-label">

                            <span class="option-badge">
                                A
                            </span>

                            Opsi A
                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            name="opsi_a"
                            value="{{ old('opsi_a') }}"
                            placeholder="Masukkan opsi A"
                            required
                        >

                    </div>


                    <!-- B -->

                    <div class="form-group">

                        <label class="option-label">

                            <span class="option-badge">
                                B
                            </span>

                            Opsi B
                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            name="opsi_b"
                            value="{{ old('opsi_b') }}"
                            placeholder="Masukkan opsi B"
                            required
                        >

                    </div>


                    <!-- C -->

                    <div class="form-group">

                        <label class="option-label">

                            <span class="option-badge">
                                C
                            </span>

                            Opsi C
                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            name="opsi_c"
                            value="{{ old('opsi_c') }}"
                            placeholder="Masukkan opsi C"
                            required
                        >

                    </div>


                    <!-- D -->

                    <div class="form-group">

                        <label class="option-label">

                            <span class="option-badge">
                                D
                            </span>

                            Opsi D
                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            name="opsi_d"
                            value="{{ old('opsi_d') }}"
                            placeholder="Masukkan opsi D"
                            required
                        >

                    </div>


                    <!-- E -->

                    <div class="form-group">

                        <label class="option-label">

                            <span class="option-badge">
                                E
                            </span>

                            Opsi E

                            <span class="optional">
                                (Opsional)
                            </span>

                        </label>

                        <input
                            type="text"
                            name="opsi_e"
                            value="{{ old('opsi_e') }}"
                            placeholder="Masukkan opsi E jika diperlukan"
                        >

                    </div>


                </div>


                <!-- JAWABAN & BOBOT -->

                <div class="section-title">
                    Pengaturan Soal
                </div>


                <div class="two-column">


                    <!-- JAWABAN BENAR -->

                    <div class="form-group">

                        <label>
                            Jawaban Benar
                            <span class="required">*</span>
                        </label>

                        <select name="jawaban" required>

                            <option value="">
                                -- Pilih Jawaban --
                            </option>

                            <option
                                value="A"
                                {{ old('jawaban') == 'A' ? 'selected' : '' }}
                            >
                                A
                            </option>

                            <option
                                value="B"
                                {{ old('jawaban') == 'B' ? 'selected' : '' }}
                            >
                                B
                            </option>

                            <option
                                value="C"
                                {{ old('jawaban') == 'C' ? 'selected' : '' }}
                            >
                                C
                            </option>

                            <option
                                value="D"
                                {{ old('jawaban') == 'D' ? 'selected' : '' }}
                            >
                                D
                            </option>

                            <option
                                value="E"
                                {{ old('jawaban') == 'E' ? 'selected' : '' }}
                            >
                                E
                            </option>

                        </select>

                    </div>


                    <!-- BOBOT -->

                    <div class="form-group">

                        <label>
                            Bobot Nilai (%)
                            <span class="required">*</span>
                        </label>

                        <input
                            type="number"
                            name="bobot"
                            value="{{ old('bobot') }}"
                            min="0.01"
                            max="100"
                            step="0.01"
                            placeholder="Contoh: 10"
                            required
                        >

                        <small class="form-help">
                            Total bobot semua soal tidak boleh lebih dari 100%.
                        </small>

                    </div>


                </div>


                <!-- TINGKAT KESULITAN -->

                <div class="form-group">

                    <label>
                        Tingkat Kesulitan
                    </label>

                    <select name="tingkat_kesulitan">

                        <option value="">
                            -- Pilih Tingkat Kesulitan --
                        </option>

                        <option
                            value="mudah"
                            {{ old('tingkat_kesulitan') == 'mudah' ? 'selected' : '' }}
                        >
                            Mudah
                        </option>

                        <option
                            value="sedang"
                            {{ old('tingkat_kesulitan') == 'sedang' ? 'selected' : '' }}
                        >
                            Sedang
                        </option>

                        <option
                            value="sulit"
                            {{ old('tingkat_kesulitan') == 'sulit' ? 'selected' : '' }}
                        >
                            Sulit
                        </option>

                    </select>

                </div>


                <!-- ================= ACTION ================= -->

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
                        Simpan Soal
                    </button>

                </div>


            </form>

        </div>


    </main>

</div>

</body>

</html>