<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | ITIK</title>
    @include('admin.partials.styles')
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#main-content">Langsung ke konten</a>
    <div class="layout">
        <aside class="sidebar" id="admin-sidebar">
            <div class="sidebar-inner">
                <div class="brand-row">
                    <a class="brand" href="{{ route('admin.dashboard') }}" aria-label="ITIK, dasbor admin">
                        <span class="brand-mark" aria-hidden="true">IT</span>
                        <span><strong>ITIK</strong><small>Administrasi sekolah</small></span>
                    </a>
                    <button type="button" class="nav-toggle" id="nav-toggle" aria-controls="admin-navigation" aria-expanded="false">Menu</button>
                </div>
                <div class="sidebar-content" id="admin-navigation">
                    <p class="menu-caption">RUANG ADMIN</p>
                    @php
                        $menuAdmin = [
                            ['admin.dashboard', 'admin.dashboard', 'Dasbor', 'M3 10 12 3l9 7M5 9v12h14V9M9 21v-7h6v7'],
                            ['admin.tahun-ajaran.index', 'admin.tahun-ajaran.*', 'Tahun ajaran', 'M4 5h16v16H4zM8 3v4M16 3v4M4 11h16'],
                            ['admin.mapel.index', 'admin.mapel.*', 'Mata pelajaran', 'M12 5v16M3 4l9 1 9-1v15l-9 2-9-2z'],
                            ['admin.kelas.index', 'admin.kelas.*', 'Kelas', 'M3 21h18M5 21V5h14v16M9 9h1M14 9h1M9 13h1M14 13h1M10 21v-4h4v4'],
                            ['admin.siswa.index', 'admin.siswa.*', 'Siswa', 'M12 3 2 8l10 5 10-5-10-5M6 10v7c4 3 8 3 12 0v-7M22 8v9'],
                            ['admin.guru.index', 'admin.guru.*', 'Guru', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M17 4h5v11h-4'],
                            ['admin.rekap.index', 'admin.rekap.*', 'Rekap nilai', 'M4 3v18h17M8 17v-5M13 17V7M18 17v-8'],
                            ['admin.profil', 'admin.profil', 'Profil saya', 'M20 21v-2a7 7 0 0 0-14 0v2M13 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8'],
                        ];
                    @endphp
                    <nav class="menu" aria-label="Navigasi admin">
                        @foreach($menuAdmin as [$namaRoute, $polaRoute, $labelMenu, $ikonMenu])
                            <a href="{{ route($namaRoute) }}" class="{{ request()->routeIs($polaRoute) ? 'active' : '' }}" @if(request()->routeIs($polaRoute)) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $ikonMenu }}" /></svg>
                                <span>{{ $labelMenu }}</span>
                            </a>
                        @endforeach
                    </nav>
                    <p class="sidebar-note">Kelola data sekolah tanpa menghapus riwayat pembelajaran.</p>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout">Keluar dari akun</button>
                    </form>
                </div>
            </div>
        </aside>
        <main class="content" id="main-content" tabindex="-1">
            <header class="topbar">
                <span class="eyebrow">Portal administrasi</span>
                <a class="profile-mini" href="{{ route('admin.profil') }}">
                    <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()?->nama_lengkap ?? 'A', 0, 1)) }}</span>
                    <span><strong>{{ auth()->user()?->nama_lengkap ?? 'Administrator' }}</strong><small>Administrator ITIK</small></span>
                </a>
            </header>
            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error" role="alert">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error" role="alert">
                    <strong>Data belum dapat disimpan. Periksa kembali isian berikut:</strong>
                    <ul>@foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
            <footer class="footer">ITIK · Administrasi sekolah dan riwayat pembelajaran</footer>
        </main>
    </div>
    <script>
        document.documentElement.classList.add('js');
        const sidebar = document.getElementById('admin-sidebar');
        const navToggle = document.getElementById('nav-toggle');
        navToggle.addEventListener('click', function () {
            const open = sidebar.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', String(open));
            navToggle.textContent = open ? 'Tutup menu' : 'Menu';
        });
        document.addEventListener('submit', function (event) {
            const message = event.target.getAttribute('data-confirm');
            if (message && !window.confirm(message)) event.preventDefault();
        });
    </script>
    @stack('scripts')
</body>
</html>
