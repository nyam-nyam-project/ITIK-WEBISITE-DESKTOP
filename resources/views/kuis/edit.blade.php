<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Kuis - ITIK</title>

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

        .alert-error {
            background: #fff0f0;
            color: #d64545;
            border: 1px solid #ffd0d0;
        }

        .error-list {
            margin: 8px 0 0 20px;
        }

        /* ================= FORM CARD ================= */

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

        select.form-control {
            cursor: pointer;
        }

        /* ================= GRID ================= */

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        /* ================= MATERI ================= */

        .materi-section,
        .waktu-section {
            padding: 22px;
            background: #faf9ff;
            border: 1px solid #eeeef5;
            border-radius: 16px;
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 17px;
            margin-bottom: 5px;
        }

        .section-description {
            color: #777394;
            font-size: 13px;
            margin-bottom: 15px;
        }

        select[multiple] {
            min-height: 150px;
        }

        .helper {
            display: block;
            margin-top: 7px;
            color: #9996aa;
            font-size: 12px;
            line-height: 1.5;
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


    <!-- CONTENT -->
    <main class="content">

        <!-- TOPBAR -->
        <div class="topbar">

            <div class="title">

                <h1>Edit Kuis</h1>

                <p>
                    Perbarui informasi kuis
                </p>

            </div>


            <div class="profile-mini">

                <div class="avatar-small">
                    {{ strtoupper(substr(Auth::user()->nama_lengkap, 0, 2)) }}
                </div>

                <div>
                    <strong>{{ Auth::user()->nama_lengkap }}</strong>

                    <br>

                    <small>
                        {{ ucfirst(Auth::user()->role) }}
                    </small>
                </div>

            </div>

        </div>


        <!-- PAGE HEADER -->
        <div class="page-header">

            <div>

                <h2>
                    Edit Kuis
                </h2>

                <p>
                    Ubah informasi kuis sesuai kebutuhan.
                </p>

            </div>

        </div>


        <!-- VALIDATION ERROR -->
        @if ($errors->any())

            <div class="alert alert-error">

                <strong>Terjadi kesalahan:</strong>

                <ul class="error-list">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <!-- FORM -->
        <div class="form-card">

            <form
                action="{{ route('kuis.update', $kuis->id_kuis) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


                <!-- JUDUL -->
                <div class="form-group">

                    <label for="judul">
                        Judul <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        class="form-control"
                        value="{{ old('judul', $kuis->judul) }}"
                        required
                    >

                </div>


                <!-- DESKRIPSI -->
                <div class="form-group">

                    <label for="deskripsi">
                        Deskripsi
                    </label>

                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        class="form-control"
                        rows="5"
                    >{{ old('deskripsi', $kuis->deskripsi) }}</textarea>

                </div>


                <!-- KATEGORI -->
                <div class="form-group">

                    <label for="kategori">
                        Kategori <span class="required">*</span>
                    </label>

                    <select
                        name="kategori"
                        id="kategori"
                        class="form-control"
                        required
                    >

                        <option value="post-test"
                            {{ old('kategori', $kuis->kategori) === 'post-test' ? 'selected' : '' }}>
                            Post-test
                        </option>

                        <option value="pass-test"
                            {{ old('kategori', $kuis->kategori) === 'pass-test' ? 'selected' : '' }}>
                            Pass-test
                        </option>

                        <option value="kuis harian"
                            {{ old('kategori', $kuis->kategori) === 'kuis harian' ? 'selected' : '' }}>
                            Kuis Harian
                        </option>

                        <option value="ulangan"
                            {{ old('kategori', $kuis->kategori) === 'ulangan' ? 'selected' : '' }}>
                            Ulangan
                        </option>

                        <option value="remedial"
                            {{ old('kategori', $kuis->kategori) === 'remedial' ? 'selected' : '' }}>
                            Remedial
                        </option>

                    </select>

                </div>


                <!-- MATERI -->
                <div
                    id="materi-container"
                    class="materi-section"
                >

                    <h3 class="section-title">
                        Materi
                    </h3>

                    <p class="section-description">
                        Pilih materi yang berkaitan dengan kuis.
                    </p>

                    <select
                        name="materi[]"
                        class="form-control"
                        multiple
                    >

                        @foreach ($materi as $itemMateri)

                           <option
    value="{{ $itemMateri->id_materi }}"
    {{ in_array(
        $itemMateri->id_materi,
        old('materi', $materiTerpilih)
    ) ? 'selected' : '' }}
>
    {{ $itemMateri->judul }}

    @if ($itemMateri->bab)
        - {{ $itemMateri->bab }}
    @endif
</option>

                        @endforeach

                    </select>

                    <small class="helper">
                        Gunakan Ctrl + klik untuk memilih beberapa materi.
                    </small>

                </div>


                <!-- ALOKASI & KKM -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="alokasi_waktu">
                            Alokasi Waktu (menit)
                        </label>

                        <input
                            type="number"
                            id="alokasi_waktu"
                            name="alokasi_waktu"
                            class="form-control"
                            value="{{ old('alokasi_waktu', $kuis->alokasi_waktu) }}"
                            min="1"
                        >

                    </div>


                    <div class="form-group">

                        <label for="kkm">
                            KKM
                        </label>

                        <input
                            type="number"
                            id="kkm"
                            name="kkm"
                            class="form-control"
                            value="{{ old('kkm', $kuis->kkm) }}"
                            min="0"
                            max="100"
                            step="0.01"
                        >

                    </div>

                </div>


                <!-- WAKTU ULANGAN -->
                <div
                    id="waktu-container"
                    class="waktu-section"
                >

                    <h3 class="section-title">
                        Jadwal Ulangan
                    </h3>

                    <p class="section-description">
                        Atur waktu mulai dan selesai apabila kuis merupakan ulangan.
                    </p>


                    <div class="form-row">

                        <div class="form-group">

                            <label for="waktu_mulai">
                                Waktu Mulai
                            </label>

                            <input
                                type="datetime-local"
                                id="waktu_mulai"
                                name="waktu_mulai"
                                class="form-control"
                                value="{{ old(
                                    'waktu_mulai',
                                    $kuis->waktu_mulai?->format('Y-m-d\TH:i')
                                ) }}"
                            >

                        </div>


                        <div class="form-group">

                            <label for="waktu_selesai">
                                Waktu Selesai
                            </label>

                            <input
                                type="datetime-local"
                                id="waktu_selesai"
                                name="waktu_selesai"
                                class="form-control"
                                value="{{ old(
                                    'waktu_selesai',
                                    $kuis->waktu_selesai?->format('Y-m-d\TH:i')
                                ) }}"
                            >

                        </div>

                    </div>

                </div>


                <!-- ACTION -->
                <div class="form-actions">

                    <a
                        href="{{ route('kuis.index') }}"
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


<script>

    const kategori = document.getElementById('kategori');
    const materiContainer = document.getElementById('materi-container');
    const waktuContainer = document.getElementById('waktu-container');

    function updateForm() {

        if (kategori.value === 'ulangan') {

            materiContainer.style.display = 'block';
            waktuContainer.style.display = 'block';

        } else if (
            kategori.value === 'post-test' ||
            kategori.value === 'pass-test' ||
            kategori.value === 'kuis harian'
        ) {

            materiContainer.style.display = 'block';
            waktuContainer.style.display = 'none';

        } else {

            materiContainer.style.display = 'none';
            waktuContainer.style.display = 'none';

        }

    }

    kategori.addEventListener('change', updateForm);

    updateForm();

</script>

</body>
</html>