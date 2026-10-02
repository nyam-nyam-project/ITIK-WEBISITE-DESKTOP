<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ITIK')</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f6f7fb;
            color: #333;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* =========================
           SIDEBAR
        ========================= */
        .sidebar {
            width: 390px;
            min-height: 100vh;
            flex-shrink: 0;
            color: white;
            padding: 40px 36px;
            background: linear-gradient(180deg, #7160ef 0%, #5c4be0 100%);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 82px;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            border: 1px solid rgba(255,255,255,.45);
            border-radius: 23px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 29px;
            font-weight: 500;
        }

        .brand-text h1 {
            font-size: 32px;
            line-height: 1;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .brand-text p {
            font-size: 20px;
            line-height: 1;
            color: rgba(255,255,255,.92);
        }

        .menu-title {
            font-size: 20px;
            font-weight: 600;
            color: rgba(255,255,255,.75);
            margin-bottom: 20px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu a {
            min-height: 74px;
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 0 27px;
            border-radius: 21px;
            color: white;
            text-decoration: none;
            font-size: 25px;
            transition: .2s ease;
        }

        .menu a:hover {
            background: rgba(255,255,255,.12);
        }

        .menu a.active {
            background: white;
            color: #5948dd;
        }

        .menu-icon {
            width: 25px;
            min-width: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        /* =========================
           CONTENT
        ========================= */
        .content {
            flex: 1;
            min-width: 0;
            padding: 40px 64px;
        }

        .topbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 35px;
        }

        .profile-mini {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 14px 20px 14px 16px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,.05);
        }

        .profile-mini strong {
            font-size: 18px;
        }

        .profile-mini small {
            display: block;
            margin-top: 4px;
            color: #888;
            font-size: 15px;
        }

        .avatar-small {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #6652e9;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        @media (max-width: 1200px) {
            .sidebar {
                width: 320px;
                padding: 35px 28px;
            }

            .brand {
                margin-bottom: 60px;
            }

            .brand-icon {
                width: 68px;
                height: 68px;
            }

            .brand-text h1 {
                font-size: 28px;
            }

            .brand-text p {
                font-size: 18px;
            }

            .menu a {
                min-height: 62px;
                font-size: 21px;
                border-radius: 17px;
            }

            .content {
                padding: 35px 40px;
            }
        }

        @media (max-width: 700px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
                padding: 24px 20px;
            }

            .brand {
                margin-bottom: 30px;
            }

            .menu {
                flex-direction: row;
                overflow-x: auto;
                gap: 6px;
            }

            .menu a {
                min-width: max-content;
                min-height: 52px;
                padding: 0 17px;
                font-size: 17px;
                border-radius: 13px;
            }

            .menu-title {
                font-size: 18px;
                margin-bottom: 14px;
            }

            .content {
                padding: 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="layout">

        <aside class="sidebar">

            <div class="brand">
                <div class="brand-icon">IT</div>

                <div class="brand-text">
                    <h1>ITIK</h1>
                    <p>Portal Guru</p>
                </div>
            </div>

            <div class="menu-title">Menu Utama</div>

            <nav class="menu">

                <a href="{{ route('dashboard') }}"
                   class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="menu-icon">▦</span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('materi.index') }}"
                   class="{{ request()->routeIs('materi.*') ? 'active' : '' }}">
                    <span class="menu-icon">□</span>
                    <span>Materi</span>
                </a>

                <a href="{{ route('kuis.index') }}"
                   class="{{ request()->routeIs('kuis.*') ? 'active' : '' }}">
                    <span class="menu-icon">☑</span>
                    <span>Kuis &amp; Soal</span>
                </a>

                <a href="{{ route('guru.nilai.index') }}"
                   class="{{ request()->routeIs('guru.nilai.*') ? 'active' : '' }}">
                    <span class="menu-icon">◎</span>
                    <span>Nilai</span>
                </a>

                <a href="{{ route('profil') }}"
                   class="{{ request()->routeIs('profil') ? 'active' : '' }}">
                    <span class="menu-icon">◉</span>
                    <span>Profil</span>
                </a>

            </nav>
        </aside>

        <main class="content">

            <div class="topbar">
                <div class="profile-mini">
                    <div>
                        <strong>{{ Auth::user()->nama_lengkap ?? 'Guru' }}</strong>
                        <small>{{ Auth::user()->role ?? 'Guru' }}</small>
                    </div>

                    <div class="avatar-small">
                        {{ strtoupper(substr(Auth::user()->nama_lengkap ?? 'G', 0, 1)) }}
                    </div>
                </div>
            </div>

            @yield('content')

        </main>
    </div>

    @stack('scripts')
</body>
</html>
