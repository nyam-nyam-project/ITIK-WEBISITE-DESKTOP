<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Siswa - ITIK</title>

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

        /* SIDEBAR */

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

        .menu a.active:hover {
            background: white;
        }

        /* CONTENT */

        .content {
            flex: 1;
            padding: 30px 45px;
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .title h1 {
            font-size: 34px;
            margin-bottom: 5px;
        }

        .title p {
            color: #777394;
            font-size: 17px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 10px 18px;
            border-radius: 40px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
            flex-shrink: 0;
        }

        .avatar {
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

        .profile small {
            color: #777394;
        }

        /* BANNER SAMBUTAN */

        .welcome {
            background: linear-gradient(135deg, #7160ef, #5948dd);
            color: white;
            border-radius: 25px;
            padding: 35px 40px;
            margin-bottom: 32px;
            box-shadow: 0 4px 18px rgba(70,60,130,.12);
        }

        .welcome h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .welcome p {
            opacity: .9;
            font-size: 17px;
            line-height: 1.6;
            max-width: 60ch;
        }

        /* STATISTIK (opsional) */

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
            margin-bottom: 32px;
        }

        .card {
            background: white;
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 14px;
            background: #eeeaff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #604de7;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 38px;
            font-weight: bold;
        }

        .stat-label {
            color: #777394;
            margin-top: 5px;
        }

        .section-header {
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 22px;
        }

        .section-header p {
            color: #777394;
            margin-top: 5px;
        }

        /* NOTIF REMEDIAL */

        .remedial-alert {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #fff0d4;
            color: #8a5a00;
            border-radius: 25px;
            padding: 24px 30px;
            margin-bottom: 32px;
        }

        .remedial-alert.aman {
            background: #e7f8ec;
            color: #20a653;
            padding: 18px 30px;
        }

        .remedial-icon {
            width: 50px;
            height: 50px;
            flex-shrink: 0;
            border-radius: 15px;
            background: rgba(255,255,255,.65);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .remedial-alert h3 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .remedial-alert p {
            font-size: 15px;
            line-height: 1.5;
        }

        .remedial-alert .btn-inline {
            margin-left: auto;
            background: #d68a00;
        }

        .remedial-alert .btn-inline:hover {
            background: #b87500;
        }

        /* MATERI & KUIS TERBARU */

        .columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 32px;
        }

        .section-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(70,60,130,.06);
        }

        .section-card .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .see-all {
            text-decoration: none;
            color: #5d4be4;
            border: 1px solid #ddd9f8;
            padding: 10px 15px;
            border-radius: 12px;
            white-space: nowrap;
        }

        .see-all:hover {
            background: #f5f3ff;
        }

        .item {
            padding: 17px 0;
            border-bottom: 1px solid #eeeef5;
        }

        .item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .item-title {
            font-weight: bold;
            font-size: 17px;
        }

        .item-sub {
            color: #777394;
            margin-top: 5px;
            font-size: 14px;
        }

        .badge {
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            white-space: nowrap;
            background: #eeeaff;
            color: #7a74a3;
        }

        .badge.belajar {
            background: #fff0d4;
            color: #d68a00;
        }

        .badge.selesai {
            background: #e7f8ec;
            color: #20a653;
        }

        .btn-inline {
            display: inline-block;
            text-decoration: none;
            background: #6553e8;
            color: white;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 14px;
            white-space: nowrap;
        }

        .btn-inline:hover {
            background: #5948dd;
        }

        .empty-small {
            color: #777394;
            padding: 20px 0 5px;
        }

        a:focus-visible {
            outline: 3px solid #b9aefc;
            outline-offset: 2px;
        }

        @media(max-width: 1100px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .columns {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 700px) {
            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .title h1 {
                font-size: 28px;
            }

            .welcome {
                padding: 28px 24px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .remedial-alert {
                flex-direction: column;
                align-items: flex-start;
            }

            .remedial-alert .btn-inline {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">
            <div class="logo-icon">IT</div>

            <div>
                <h2>ITIK</h2>
                <span>Portal Siswa</span>
            </div>
        </div>

        <div class="menu-title">
            Menu Utama
        </div>

        <nav class="menu">

            <a href="{{ route('siswa.dashboard') }}" class="active">
                ▦ &nbsp; Dashboard
            </a>

            <a href="{{ url('/siswa/materi') }}">
                ▣ &nbsp; Materi
            </a>

            <a href="{{ url('/siswa/kuis') }}">
                ☑ &nbsp; Kuis
            </a>

            <a href="{{ url('/siswa/nilai') }}">
                ◉ &nbsp; Nilai Saya
            </a>

            <a href="{{ url('/siswa/remedial') }}">
                ↻ &nbsp; Remedial
            </a>

            <a href="{{ route('profil') }}">
                ◎ &nbsp; Profil
            </a>

        </nav>

    </aside>


    <!-- CONTENT -->

    <main class="content">

        <div class="topbar">

            <div class="title">
                <h1>Dashboard</h1>
                <p>Ringkasan aktivitas belajarmu</p>
            </div>

            <div class="profile">

                <div class="avatar">
                    {{ strtoupper(substr(Auth::user()->nama_lengkap, 0, 2)) }}
                </div>

                <div>
                    <strong>{{ Auth::user()->nama_lengkap }}</strong>
                    <br>
                    <small>{{ ucfirst(Auth::user()->role) }}</small>
                </div>

            </div>

        </div>


        <!-- SAMBUTAN -->

        <div class="welcome">
            <h2>Halo, {{ Auth::user()->nama_lengkap }}!</h2>
            <p>
                Selamat datang di portal belajar TIK. Pelajari materi, kerjakan kuis,
                dan pantau nilaimu dari sini.
            </p>
        </div>


        <!--
            REMEDIAL: tampil kalau controller mengirim $remedialAktif
            (koleksi tugas remedial yang statusnya 'ditugaskan').
        -->
        @isset($remedialAktif)

            @if($remedialAktif->isNotEmpty())

                <div class="remedial-alert">

                    <div class="remedial-icon">↻</div>

                    <div>
                        <h3>Kamu punya {{ $remedialAktif->count() }} tugas remedial</h3>
                        <p>
                            @foreach($remedialAktif->take(3) as $r)
                                {{ $r->kuisRemedial->judul ?? '-' }}@if(! $loop->last), @endif
                            @endforeach
                            @if($remedialAktif->count() > 3)
                                dan {{ $remedialAktif->count() - 3 }} lainnya
                            @endif
                        </p>
                    </div>

                    <a href="{{ route('siswa.remedial.index') }}" class="btn-inline">
                        Kerjakan remedial
                    </a>

                </div>

            @else

                <div class="remedial-alert aman">

                    <div class="remedial-icon">✓</div>

                    <div>
                        <h3>Tidak ada tugas remedial</h3>
                    </div>

                </div>

            @endif

        @endisset


        <!--
            STATISTIK (opsional): baris ini hanya tampil kalau controller
            mengirim variabel $totalMateri, $materiSelesai, $kuisTersedia,
            $rataNilai, dan $remedialBelum. Tanpa itu, halaman tetap jalan.
        -->
        @isset($totalMateri)

            <div class="stats">

                <div class="card">
                    <div class="stat-icon">▣</div>
                    <div class="stat-number">{{ $materiSelesai ?? 0 }}/{{ $totalMateri }}</div>
                    <div class="stat-label">Materi selesai</div>
                </div>

                <div class="card">
                    <div class="stat-icon">☑</div>
                    <div class="stat-number">{{ $kuisTersedia ?? 0 }}</div>
                    <div class="stat-label">Kuis tersedia</div>
                </div>

                <div class="card">
                    <div class="stat-icon">▥</div>
                    <div class="stat-number">{{ isset($rataNilai) && $rataNilai ? round($rataNilai) : 0 }}</div>
                    <div class="stat-label">Rata-rata nilai</div>
                </div>

                <div class="card">
                    <div class="stat-icon">↻</div>
                    <div class="stat-number">{{ $remedialBelum ?? 0 }}</div>
                    <div class="stat-label">Remedial belum dikerjakan</div>
                </div>

            </div>

        @endisset


        <!-- MATERI & KUIS TERBARU -->

        @if(isset($materiTerbaru) || isset($kuisTerbaru))

            <div class="columns">

                @isset($materiTerbaru)

                    <div class="section-card">

                        <div class="section-header">

                            <div>
                                <h2>Materi Terbaru</h2>
                                <p>Materi yang baru ditambahkan guru</p>
                            </div>

                            <a href="{{ route('siswa.materi.index') }}" class="see-all">
                                Lihat semua
                            </a>

                        </div>

                        @forelse($materiTerbaru as $materi)

                            @php
                                $progress = $materi->progress->first();
                            @endphp

                            <div class="item">

                                <div class="item-row">

                                    <div>
                                        <a href="{{ route('siswa.materi.show', $materi->id_materi) }}"
                                           class="item-title" style="color: inherit; text-decoration: none;">
                                            {{ $materi->judul }}
                                        </a>

                                        <div class="item-sub">
                                            {{ $materi->bab ?? '-' }} • Kelas {{ $materi->kelas ?? '-' }}
                                        </div>
                                    </div>

                                    @if(!$progress)
                                        <span class="badge">Belum Mulai</span>
                                    @elseif($progress->status === 'selesai')
                                        <span class="badge selesai">Selesai</span>
                                    @else
                                        <span class="badge belajar">Sedang Belajar</span>
                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="empty-small">Belum ada materi.</div>

                        @endforelse

                    </div>

                @endisset


                @isset($kuisTerbaru)

                    <div class="section-card">

                        <div class="section-header">

                            <div>
                                <h2>Kuis Terbaru</h2>
                                <p>Kuis yang bisa kamu kerjakan</p>
                            </div>

                            <a href="{{ route('siswa.kuis.index') }}" class="see-all">
                                Lihat semua
                            </a>

                        </div>

                        @forelse($kuisTerbaru as $kuis)

                            <div class="item">

                                <div class="item-row">

                                    <div>
                                        <div class="item-title">{{ $kuis->judul }}</div>

                                        <div class="item-sub">
                                            {{ $kuis->kategori }} • {{ $kuis->alokasi_waktu }} menit • KKM {{ $kuis->kkm }}
                                        </div>
                                    </div>

                                    <a href="{{ route('siswa.kuis.mulai', $kuis->id_kuis) }}" class="btn-inline">
                                        Mulai
                                    </a>

                                </div>

                            </div>

                        @empty

                            <div class="empty-small">Belum ada kuis yang tersedia.</div>

                        @endforelse

                    </div>

                @endisset

            </div>

        @endif


    </main>

</div>

</body>
</html>