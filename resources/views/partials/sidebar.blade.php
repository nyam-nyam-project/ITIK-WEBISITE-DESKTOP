
@php
    $role = Auth::user()->role;

    if ($role === 'siswa') {

        $label = 'Portal Siswa';

        $menu = [
            ['icon' => 'book-open', 'text' => 'Materi',     'url' => route('siswa.materi.index'),   'aktif' => request()->is('siswa/materi*')],
            ['icon' => 'check-square', 'text' => 'Kuis',       'url' => route('siswa.kuis.index'),     'aktif' => request()->is('siswa/kuis*')],
            ['icon' => 'chart', 'text' => 'Nilai Saya', 'url' => route('siswa.nilai'),          'aktif' => request()->is('siswa/nilai*')],
            ['icon' => 'rotate-ccw', 'text' => 'Remedial',   'url' => route('siswa.remedial.index'), 'aktif' => request()->is('siswa/remedial*')],
            ['icon' => 'user', 'text' => 'Profil',     'url' => route('profil'),               'aktif' => request()->is('profil')],
        ];

    } else {

        // guru dan operator
        $label = 'Portal Guru';

        $menu = [
            ['icon' => 'layout-grid', 'text' => 'Dashboard',    'url' => route('dashboard'),         'aktif' => request()->is('dashboard')],
            ['icon' => 'book-open', 'text' => 'Materi',       'url' => route('materi.index'),      'aktif' => request()->is('materi*')],
            ['icon' => 'check-square', 'text' => 'Kuis & Soal',  'url' => route('kuis.index'),        'aktif' => request()->is('kuis*')],
            ['icon' => 'chart', 'text' => 'Nilai',        'url' => route('guru.nilai.index'),  'aktif' => request()->is('guru/nilai*')],
            ['icon' => 'user', 'text' => 'Profil',       'url' => route('profil'),            'aktif' => request()->is('profil')],
        ];

    }
@endphp

<aside class="sidebar">

    <div class="logo">
        <div class="logo-icon">
            <img src="{{ asset('images/logo-itik.png') }}" alt="Logo ITIK" class="logo-itik-img">
        </div>

        <div>
            <h2>I-TIK</h2>
            <span>{{ $label }}</span>
        </div>
    </div>

    <div class="menu-title">
        Menu Utama
    </div>

    <nav class="menu">

        @foreach($menu as $item)
            <a href="{{ $item['url'] }}" class="{{ $item['aktif'] ? 'active' : '' }}">
                @include('partials.icon', ['name' => $item['icon']]) &nbsp; {{ $item['text'] }}
            </a>
        @endforeach

    </nav>

</aside>
