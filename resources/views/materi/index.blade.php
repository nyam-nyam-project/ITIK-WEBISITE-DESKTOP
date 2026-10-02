<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Materi - ITIK</title>

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

        /* ================= HEADER ACTION ================= */

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

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            background: #6553e8;
            color: white;

            text-decoration: none;

            padding: 13px 20px;
            border-radius: 13px;

            font-weight: bold;
        }

        .btn-add:hover {
            background: #5543d5;
        }

        /* ================= SEARCH ================= */

        .toolbar {
            background: white;
            padding: 18px;
            border-radius: 20px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 25px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .search {
            width: 350px;

            padding: 13px 16px;

            border: 1px solid #e3e1ef;
            border-radius: 12px;

            outline: none;
            font-size: 14px;
        }

        .search:focus {
            border-color: #7160ef;
        }

        /* ================= MATERI GRID ================= */

        .materi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .materi-card {
            background: white;
            border-radius: 23px;

            padding: 25px;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);

            transition: .2s;
        }

        .materi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(70,60,130,.10);
        }

        .materi-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            margin-bottom: 20px;
        }

        .materi-icon {
            width: 48px;
            height: 48px;

            border-radius: 14px;

            background: #eeeaff;
            color: #6553e8;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 21px;
        }

        .kelas {
            background: #eef8ff;
            color: #3883c7;

            padding: 7px 12px;
            border-radius: 20px;

            font-size: 13px;
            font-weight: bold;
        }

        .materi-card h3 {
            font-size: 19px;
            line-height: 1.4;

            margin-bottom: 8px;
        }

        .bab {
            color: #6553e8;
            font-size: 14px;
            font-weight: bold;

            margin-bottom: 12px;
        }

        .deskripsi {
            color: #777394;
            font-size: 14px;
            line-height: 1.6;

            min-height: 67px;
        }

        .materi-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-top: 20px;
            padding-top: 18px;

            border-top: 1px solid #eeeef5;
        }

        .materi-id {
            color: #aaa7bc;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            text-decoration: none;

            padding: 8px 12px;
            border-radius: 10px;

            font-size: 13px;
            font-weight: bold;
        }

        .btn-view {
            background: #eeeaff;
            color: #5b4ae3;
        }

        .btn-edit {
            background: #fff3dc;
            color: #c98200;
        }

        .btn-delete {
            background: #ffe5e5;
            color: #d64545;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-delete:hover {
            background: #ffd5d5;
        }

        /* ================= EMPTY ================= */

        .empty {
            background: white;
            border-radius: 23px;
            padding: 60px 30px;

            text-align: center;

            color: #777394;

            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .empty h3 {
            color: #202044;
            margin-bottom: 7px;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 1200px) {

            .materi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media(max-width: 850px) {

            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .materi-grid {
                grid-template-columns: 1fr;
            }

            .search {
                width: 100%;
            }

            .toolbar {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
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

                <h1>Materi</h1>

                <p>
                    Kelola materi pembelajaran TIK
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


        <!-- HEADER -->

        <div class="page-header">

            <div>

                <h2>Daftar Materi</h2>

                <p>
                    Materi pembelajaran yang tersedia
                </p>

            </div>


            <a href="{{ route('materi.create') }}" class="btn-add">
                + Tambah Materi
            </a>

        </div>


        <!-- SEARCH -->

        <div class="toolbar">

            <input
                type="text"
                id="searchMateri"
                class="search"
                placeholder="Cari materi..."
            >

            <span style="color:#777394; font-size:14px;">
                {{ $materi->count() }} materi tersedia
            </span>

        </div>


        <!-- MATERI -->

        @if($materi->count() > 0)

            <div class="materi-grid" id="materiGrid">

                @foreach($materi as $materi)

                    <div class="materi-card">

                        <div class="materi-top">

                            <div class="materi-icon">
                                ▣
                            </div>

                            <span class="kelas">
                                Kelas {{ $materi->kelas ?? '-' }}
                            </span>

                        </div>


                        <h3>
                            {{ $materi->judul }}
                        </h3>


                        <div class="bab">
                            {{ $materi->bab ?? 'Materi Pembelajaran' }}
                        </div>


                        <div class="deskripsi">

                            {{ $materi->deskripsi
                                ? Str::limit($materi->deskripsi, 100)
                                : 'Tidak ada deskripsi materi.'
                            }}

                        </div>


                        <div class="materi-footer">

                            <span class="materi-id">
                                ID: {{ $materi->id_materi }}
                            </span>


                            <div class="actions">

                                <a
                                    href="{{ route('materi.show', $materi->id_materi) }}"
                                    class="btn btn-view"
                                >
                                    Lihat
                                </a>

                                <a
                                    href="{{ route('materi.edit', $materi->id_materi) }}"
                                    class="btn btn-edit"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('materi.destroy', $materi->id_materi) }}"
                                    method="POST"
                                    style="display: inline;"
                                    onsubmit="return confirm('Yakin ingin menghapus materi ini?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-delete">
                                        Hapus
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="empty">

                <div class="empty-icon">
                    📚
                </div>

                <h3>
                    Belum ada materi
                </h3>

                <p>
                    Silakan tambahkan materi pembelajaran pertama.
                </p>

            </div>

        @endif

    </main>

</div>


<!-- ================= SEARCH SCRIPT ================= -->

<script>

    const searchInput = document.getElementById('searchMateri');

    searchInput.addEventListener('keyup', function () {

        const keyword = this.value.toLowerCase();

        const cards = document.querySelectorAll('.materi-card');

        cards.forEach(card => {

            const text = card.innerText.toLowerCase();

            if (text.includes(keyword)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }

        });

    });

</script>

</body>
</html>