<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ITIK') | ITIK</title>
    @include('partials.styles')
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#main-content">Langsung ke konten</a>

    @php
        $user = auth()->user();
        $role = $user?->role ?? 'siswa';

        if ($role === 'admin') {
            $brandUrl = route('admin.dashboard');
            $brandSub = 'Administrasi sekolah';
            $caption = 'RUANG ADMIN';
            $sidebarNote = 'Kelola data sekolah tanpa menghapus riwayat pembelajaran.';
            $eyebrow = 'Portal administrasi';
            $profileUrl = route('admin.profil');
            $profileRole = 'Administrator ITIK';
            $footerText = 'ITIK · Administrasi sekolah dan riwayat pembelajaran';
            $menuItems = [
                ['admin.dashboard', 'admin.dashboard', 'Dasbor', 'M3 10 12 3l9 7M5 9v12h14V9M9 21v-7h6v7'],
                ['admin.tahun-ajaran.index', 'admin.tahun-ajaran.*', 'Tahun ajaran', 'M4 5h16v16H4zM8 3v4M16 3v4M4 11h16'],
                ['admin.mapel.index', 'admin.mapel.*', 'Mata pelajaran', 'M12 5v16M3 4l9 1 9-1v15l-9 2-9-2z'],
                ['admin.kelas.index', 'admin.kelas.*', 'Kelas', 'M3 21h18M5 21V5h14v16M9 9h1M14 9h1M9 13h1M14 13h1M10 21v-4h4v4'],
                ['admin.siswa.index', 'admin.siswa.*', 'Siswa', 'M12 3 2 8l10 5 10-5-10-5M6 10v7c4 3 8 3 12 0v-7M22 8v9'],
                ['admin.guru.index', 'admin.guru.*', 'Guru', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M17 4h5v11h-4'],
                ['admin.rekap.index', 'admin.rekap.*', 'Rekap nilai', 'M4 3v18h17M8 17v-5M13 17V7M18 17v-8'],
                ['admin.profil', 'admin.profil', 'Profil saya', 'M20 21v-2a7 7 0 0 0-14 0v2M13 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8'],
            ];
        } elseif ($role === 'guru') {
            $brandUrl = route('dashboard');
            $brandSub = 'Portal Guru';
            $caption = 'PORTAL GURU';
            $sidebarNote = 'Kelola materi, kuis, dan pantau perkembangan siswa.';
            $eyebrow = 'Portal Guru';
            $profileUrl = route('profil');
            $profileRole = 'Guru ITIK';
            $footerText = 'ITIK · Portal Guru — Media Pembelajaran TIK';
            $menuItems = [
                ['dashboard', 'dashboard', 'Dashboard', 'M3 10 12 3l9 7M5 9v12h14V9M9 21v-7h6v7'],
                ['materi.index', 'materi.*', 'Materi', 'M4 4h16v16H4zM9 9h6M9 12h6M9 15h4'],
                ['kuis.index', 'kuis.*', 'Kuis & Soal', 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2'],
                ['guru.nilai.index', 'guru.nilai.*', 'Nilai', 'M4 3v18h17M8 17v-5M13 17V7M18 17v-8'],
                ['profil', 'profil', 'Profil Saya', 'M20 21v-2a7 7 0 0 0-14 0v2M13 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8'],
            ];
        } else {
            $brandUrl = route('siswa.dashboard');
            $brandSub = 'Portal Siswa';
            $caption = 'PORTAL SISWA';
            $sidebarNote = 'Pelajari materi dan kerjakan kuis dengan semangat!';
            $eyebrow = 'Portal Siswa';
            $profileUrl = route('profil');
            $profileRole = 'Siswa ITIK';
            $footerText = 'ITIK · Portal Siswa — Media Pembelajaran TIK';
            $menuItems = [
                ['siswa.dashboard', 'siswa.dashboard', 'Dashboard', 'M3 10 12 3l9 7M5 9v12h14V9M9 21v-7h6v7'],
                ['siswa.materi.index', 'siswa.materi.*', 'Materi', 'M4 4h16v16H4zM9 9h6M9 12h6M9 15h4'],
                ['siswa.kuis.index', 'siswa.kuis.*', 'Kuis', 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2'],
                ['siswa.nilai', 'siswa.nilai*', 'Nilai Saya', 'M4 3v18h17M8 17v-5M13 17V7M18 17v-8'],
                ['siswa.remedial.index', 'siswa.remedial.*', 'Remedial', 'M4 4v7l8 4 8-4V4M4 11v7l8 4 8-4v-7'],
                ['profil', 'profil', 'Profil Saya', 'M20 21v-2a7 7 0 0 0-14 0v2M13 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8'],
            ];
        }
    @endphp

    <div class="layout">
        <aside class="sidebar" id="app-sidebar">
            <div class="sidebar-inner">
                <div class="brand-row">
                    <a class="brand" href="{{ $brandUrl }}" aria-label="ITIK, {{ $brandSub }}">
                        <span class="brand-mark" aria-hidden="true">IT</span>
                        <span><strong>ITIK</strong><small>{{ $brandSub }}</small></span>
                    </a>
                    <button type="button" class="nav-toggle" id="nav-toggle" aria-controls="app-navigation" aria-expanded="false">Menu</button>
                </div>

                <div class="sidebar-content" id="app-navigation">
                    <p class="menu-caption">{{ $caption }}</p>

                    <nav class="menu" aria-label="Navigasi {{ strtolower($caption) }}">
                        @foreach($menuItems as [$namaRoute, $polaRoute, $labelMenu, $ikonMenu])
                            <a href="{{ route($namaRoute) }}" class="{{ request()->routeIs($polaRoute) ? 'active' : '' }}" @if(request()->routeIs($polaRoute)) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $ikonMenu }}" /></svg>
                                <span>{{ $labelMenu }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <p class="sidebar-note">{{ $sidebarNote }}</p>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout">Keluar dari akun</button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="content" id="main-content" tabindex="-1">
            <header class="topbar">
                <span class="eyebrow">{{ $eyebrow }}</span>
                <a class="profile-mini" href="{{ $profileUrl }}">
                    <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user?->nama_lengkap ?? 'U', 0, 1)) }}</span>
                    <span><strong>{{ $user?->nama_lengkap ?? 'Pengguna' }}</strong><small>{{ $profileRole }}</small></span>
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
                    <strong>Terjadi kesalahan. Periksa kembali isian berikut:</strong>
                    <ul>
                        @foreach($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <footer class="footer">{{ $footerText }}</footer>
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
