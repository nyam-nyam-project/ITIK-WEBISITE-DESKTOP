<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Kuis - ITIK</title>

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
            align-items: center;

            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 22px;
        }

        .page-header p {
            color: #777394;
            margin-top: 5px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            background: #eeeaff;
            color: #5b4ae3;

            text-decoration: none;

            padding: 13px 20px;
            border-radius: 13px;

            font-weight: bold;
        }

        .btn-back:hover {
            background: #e2ddff;
        }

        /* ================= FORM CARD ================= */

        .form-card {
            background: white;
            border-radius: 23px;
            padding: 30px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;

            color: #202044;
            font-size: 14px;
            font-weight: bold;

            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;

            border: 1px solid #e3e1ef;
            border-radius: 12px;

            padding: 13px 16px;

            outline: none;
            font-size: 14px;

            color: #202044;
            background: white;

            transition: .2s;
        }

        .form-control:focus {
            border-color: #7160ef;
            box-shadow: 0 0 0 3px rgba(113,96,239,.08);
        }

        textarea.form-control {
            min-height: 130px;
            resize: vertical;
            line-height: 1.6;
        }

        select.form-control {
            cursor: pointer;
        }

        /* MULTIPLE SELECT */

        select[multiple].form-control {
            min-height: 150px;
            padding: 10px;
        }

        select[multiple].form-control option {
            padding: 9px 10px;
            border-radius: 6px;
        }

        select[multiple].form-control option:checked {
            background: #6553e8;
            color: white;
        }

        .form-hint {
            margin-top: 7px;
            color: #aaa7bc;
            font-size: 12px;
        }

        /* ================= SPECIAL SECTION ================= */

        .special-section {
            background: #faf9ff;
            border: 1px solid #eeeef5;

            border-radius: 16px;

            padding: 22px;

            margin-bottom: 22px;
        }

        .special-section h3 {
            font-size: 17px;
            color: #202044;

            margin-bottom: 18px;
        }

        /* ================= ERROR ================= */

        .error-box {
            background: #fff0f0;
            border: 1px solid #ffd0d0;

            color: #d64545;

            padding: 15px 18px;
            border-radius: 12px;

            margin-bottom: 25px;
        }

        .error-box strong {
            display: block;
            margin-bottom: 8px;
        }

        .error-box ul {
            padding-left: 20px;
        }

        .error-box li {
            margin-bottom: 4px;
        }

        /* ================= BUTTON ================= */

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;

            margin-top: 30px;
            padding-top: 22px;

            border-top: 1px solid #eeeef5;
        }

        .btn {
            border: none;
            cursor: pointer;

            text-decoration: none;

            padding: 12px 20px;
            border-radius: 12px;

            font-size: 14px;
            font-weight: bold;

            transition: .2s;
        }

        .btn-cancel {
            background: #f1f0f7;
            color: #777394;
        }

        .btn-cancel:hover {
            background: #e5e4ed;
        }

        .btn-save {
            background: #6553e8;
            color: white;
        }

        .btn-save:hover {
            background: #5543d5;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 850px) {

            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .topbar {
                margin-bottom: 25px;
            }

            .form-card {
                padding: 22px;
            }
        }

        @media(max-width: 600px) {

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .form-footer {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                text-align: center;
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


    <!-- ================= CONTENT ================= -->

    <main class="content">


        <!-- TOPBAR -->

        <div class="topbar">

            <div class="title">

                <h1>Tambah Kuis</h1>

                <p>
                    Buat kuis pembelajaran baru
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


        <!-- ================= HEADER ================= -->

        <div class="page-header">

            <div>

                <h2>Form Kuis</h2>

                <p>
                    Isi informasi kuis yang ingin dibuat
                </p>

            </div>


            <a
                href="{{ route('kuis.index') }}"
                class="btn-back"
            >
                ← Kembali ke Kuis
            </a>

        </div>


        <!-- ================= FORM CARD ================= -->

        <div class="form-card">


            <!-- ERROR -->

            @if ($errors->any())

                <div class="error-box">

                    <strong>
                        Terdapat kesalahan:
                    </strong>

                    <ul>

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- FORM -->

            <form
                action="{{ route('kuis.store') }}"
                method="POST"
            >

                @csrf


                <!-- JUDUL -->

                <div class="form-group">

                    <label>
                        Judul
                    </label>

                    <input
                        type="text"
                        name="judul"
                        class="form-control"
                        value="{{ old('judul') }}"
                        placeholder="Masukkan judul kuis"
                        required
                    >

                </div>


                <!-- DESKRIPSI -->

                <div class="form-group">

                    <label>
                        Deskripsi
                    </label>

                    <textarea
                        name="deskripsi"
                        class="form-control"
                        placeholder="Masukkan deskripsi kuis"
                    >{{ old('deskripsi') }}</textarea>

                </div>


                <!-- KATEGORI -->

                <div class="form-group">

                    <label>
                        Kategori
                    </label>

                    <select
                        name="kategori"
                        id="kategori"
                        class="form-control"
                        required
                    >

                        <option
                            value=""
                            {{ old('kategori') ? '' : 'selected' }}
                        >
                            -- Pilih Kategori --
                        </option>

                        <option
                            value="post-test"
                            {{ old('kategori') === 'post-test' ? 'selected' : '' }}
                        >
                            Post-test
                        </option>

                        <option
                            value="pass-test"
                            {{ old('kategori') === 'pass-test' ? 'selected' : '' }}
                        >
                            Pass-test
                        </option>

                        <option
                            value="kuis harian"
                            {{ old('kategori') === 'kuis harian' ? 'selected' : '' }}
                        >
                            Kuis Harian
                        </option>

                        <option
                            value="ulangan"
                            {{ old('kategori') === 'ulangan' ? 'selected' : '' }}
                        >
                            Ulangan
                        </option>

                        <option
                            value="remedial"
                            {{ old('kategori') === 'remedial' ? 'selected' : '' }}
                        >
                            Remedial
                        </option>

                    </select>

                </div>


                <!-- MATERI -->

                <div
                    id="materi-container"
                    class="special-section"
                    style="display: none;"
                >

                    <div class="form-group">

                        <label>
                            Materi
                        </label>

                        <select
                            name="materi[]"
                            multiple
                            class="form-control"
                        >

                            @foreach ($materi as $materi)

                                <option
                                    value="{{ $materi->id_materi }}"
                                    {{ in_array($materi->id_materi, old('materi', [])) ? 'selected' : '' }}
                                >

                                    {{ $materi->judul }}

                                    @if ($materi->bab)
                                        - {{ $materi->bab }}
                                    @endif

                                </option>

                            @endforeach

                        </select>

                        <p class="form-hint">
                            Tekan Ctrl + klik untuk memilih lebih dari satu materi.
                        </p>

                    </div>

                </div>


                <!-- ALOKASI WAKTU -->

                <div class="form-group">

                    <label>
                        Alokasi Waktu (menit)
                    </label>

                    <input
                        type="number"
                        name="alokasi_waktu"
                        class="form-control"
                        value="{{ old('alokasi_waktu') }}"
                        min="1"
                        placeholder="Contoh: 60"
                    >

                </div>


                <!-- KKM -->

                <div class="form-group">

                    <label>
                        KKM
                    </label>

                    <input
                        type="number"
                        name="kkm"
                        class="form-control"
                        value="{{ old('kkm') }}"
                        min="0"
                        max="100"
                        step="0.01"
                        placeholder="Contoh: 75"
                    >

                </div>


                <!-- JADWAL ULANGAN -->

                <div
                    id="waktu-container"
                    class="special-section"
                    style="display: none;"
                >

                    <h3>
                        Jadwal Ulangan
                    </h3>


                    <div class="form-group">

                        <label>
                            Waktu Mulai
                        </label>

                        <input
                            type="datetime-local"
                            name="waktu_mulai"
                            class="form-control"
                            value="{{ old('waktu_mulai') }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Waktu Selesai
                        </label>

                        <input
                            type="datetime-local"
                            name="waktu_selesai"
                            class="form-control"
                            value="{{ old('waktu_selesai') }}"
                        >

                    </div>

                </div>


                <!-- REMEDIAL -->

                <div
                    id="field-remedial"
                    class="special-section"
                    style="display: none;"
                >

                    <div class="form-group">

                        <label>
                            Kuis Asal
                        </label>

                        <select
                            name="id_kuis_asal"
                            class="form-control"
                        >

                            <option value="">
                                -- Pilih Kuis Asal --
                            </option>

                            @foreach($kuisAsal as $item)

                                <option
                                    value="{{ $item->id_kuis }}"
                                    {{ old('id_kuis_asal') == $item->id_kuis ? 'selected' : '' }}
                                >

                                    {{ $item->judul }}
                                    ({{ $item->kategori }})

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                <!-- BUTTON -->

                <div class="form-footer">

                    <a
                        href="{{ route('kuis.index') }}"
                        class="btn btn-cancel"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Simpan Kuis
                    </button>

                </div>


            </form>

        </div>


    </main>

</div>


<!-- ================= JAVASCRIPT ================= -->

<script>

    const kategori = document.getElementById('kategori');

    const materiContainer =
        document.getElementById('materi-container');

    const waktuContainer =
        document.getElementById('waktu-container');

    const remedialContainer =
        document.getElementById('field-remedial');


    function updateForm() {

        const value = kategori.value;


        // Default

        materiContainer.style.display = 'none';

        waktuContainer.style.display = 'none';

        remedialContainer.style.display = 'none';


        // Post-test, pass-test, kuis harian

        if (
            value === 'post-test' ||
            value === 'pass-test' ||
            value === 'kuis harian'
        ) {

            materiContainer.style.display = 'block';

        }


        // Ulangan

        else if (value === 'ulangan') {

            materiContainer.style.display = 'block';

            waktuContainer.style.display = 'block';

        }


        // Remedial

        else if (value === 'remedial') {

            remedialContainer.style.display = 'block';

        }

    }


    kategori.addEventListener(
        'change',
        updateForm
    );


    updateForm();

</script>


</body>

</html>