<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $materi->judul }} - Materi</title>

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
            flex-shrink: 0;
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

        /* ================= SHOW PAGE ================= */

        .page-header {
            margin-bottom: 25px;
        }

        .back {
            display: inline-block;
            margin-bottom: 18px;
            color: #5948dd;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }

        .back:hover {
            text-decoration: underline;
        }

        .page-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: #292929;
        }

        .page-header p {
            color: #777;
            font-size: 16px;
        }

        .materi-container {
            background: white;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        }

        .materi-info {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .badge {
            background: #eeeaff;
            color: #5948dd;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .materi-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
        }

        .deskripsi {
            color: #666;
            font-size: 16px;
            line-height: 1.7;
            padding-bottom: 30px;
        }

        .materi-section {
            margin-top: 5px;
            padding-top: 30px;
            border-top: 2px solid #eeeaff;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .section-icon {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eeeaff;
            border-radius: 10px;
            font-size: 20px;
        }

        .section-heading h3 {
            font-size: 21px;
            color: #292929;
        }

        .section-heading p {
            margin-top: 4px;
            font-size: 13px;
            color: #999;
        }

        .document-box {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px 20px;
            background: #fafaff;
            border: 1px solid #dedaf5;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(89, 72, 221, 0.05);
            transition: .2s ease;
        }

        .document-box:hover {
            border-color: #7160ef;
            box-shadow: 0 6px 18px rgba(89, 72, 221, 0.10);
        }

        .document-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            flex: 1;
        }

        .document-icon {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eeeaff;
            border-radius: 9px;
            font-size: 20px;
        }

        .document-text {
            min-width: 0;
        }

        .document-left strong {
            display: block;
            color: #333;
            font-size: 14px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .document-left span {
            display: block;
            margin-top: 5px;
            color: #999;
            font-size: 12px;
        }

        .open-document {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 10px 16px;
            background: #7160ef;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            transition: .2s ease;
        }

        .open-document:hover {
            background: #5948dd;
            color: white;
        }

        .empty-content {
            padding: 35px;
            text-align: center;
            background: #fafaff;
            border: 1px dashed #d8d4ef;
            border-radius: 12px;
            color: #999;
        }

        .youtube {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .youtube-title {
            font-size: 18px;
            margin-bottom: 12px;
            color: #333;
        }

        .youtube a {
            display: inline-block;
            background: #7160ef;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: bold;
            transition: .2s;
        }

        .youtube a:hover {
            background: #5948dd;
        }

        .footer-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 13px;
        }

        @media(max-width: 850px) {
            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }
        }

        @media (max-width: 700px) {
            .materi-container {
                padding: 22px;
            }

            .materi-title {
                font-size: 26px;
            }

            .document-box {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .open-document {
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
                <p>Kelola materi pembelajaran TIK</p>
            </div>

            <div class="profile-mini">

                <div class="avatar-small">
                    {{ strtoupper(substr(Auth::user()->nama_lengkap ?? 'G', 0, 2)) }}
                </div>

                <div>
                    <strong>
                        {{ Auth::user()->nama_lengkap ?? 'Guru' }}
                    </strong>

                    <br>

                    <small>
                        {{ ucfirst(Auth::user()->role ?? 'guru') }}
                    </small>
                </div>

            </div>

        </div>


        <!-- ================= DETAIL MATERI ================= -->

        <div class="page-header">

            <a href="{{ route('materi.index') }}" class="back">
                ← Kembali ke Materi
            </a>

            <h1>Lihat Materi</h1>
            <p>Detail materi pembelajaran</p>

        </div>


        <div class="materi-container">

            <div class="materi-info">

                <span class="badge">
                    Kelas {{ $materi->kelas ?? '-' }}
                </span>

                <span class="badge">
                    {{ $materi->bab ?? 'Materi Pembelajaran' }}
                </span>

            </div>


            <h2 class="materi-title">
                {{ $materi->judul }}
            </h2>


            @if($materi->deskripsi)

                <div class="deskripsi">
                    {{ $materi->deskripsi }}
                </div>

            @endif


            <div class="materi-section">

                <div class="section-heading">

                    <div class="section-icon">
                        📖
                    </div>

                    <div>
                        <h3>Isi Materi</h3>
                        <p>Dokumen pembelajaran materi</p>
                    </div>

                </div>


                @if($materi->isi_materi)

                    <div class="document-box">

                        <div class="document-left">

                            <div class="document-icon">
                                📄
                            </div>

                            <div class="document-text">

                                <strong>
                                    {{ basename($materi->isi_materi) }}
                                </strong>

                                <span>
                                    Dokumen materi pembelajaran
                                </span>

                            </div>

                        </div>


                        <a href="{{ asset('storage/' . $materi->isi_materi) }}"
                           target="_blank"
                           class="open-document">
                            Buka PDF ↗
                        </a>

                    </div>

                @else

                    <div class="empty-content">
                        📚 Belum ada isi materi.
                    </div>

                @endif

            </div>


            @if($materi->link_youtube)

                <div class="youtube">

                    <h3 class="youtube-title">
                        🎥 Video Pembelajaran
                    </h3>

                    <a href="{{ $materi->link_youtube }}" target="_blank">
                        ▶ Tonton di YouTube
                    </a>

                </div>

            @endif


            <div class="footer-info">
                ID Materi: {{ $materi->id_materi }}
            </div>

        </div>

    </main>

</div>

</body>
</html>
