{{-- resources/views/partials/sidebar.blade.php --}}

@php
    $role = Auth::user()->role;

    if ($role === 'siswa') {

        $label = 'Portal Siswa';

        $menu = [
            ['icon' => '▣', 'text' => 'Materi',     'url' => route('siswa.materi.index'),   'aktif' => request()->is('siswa/materi*')],
            ['icon' => '☑', 'text' => 'Kuis',       'url' => route('siswa.kuis.index'),     'aktif' => request()->is('siswa/kuis*')],
            ['icon' => '◉', 'text' => 'Nilai Saya', 'url' => route('siswa.nilai'),          'aktif' => request()->is('siswa/nilai*')],
            ['icon' => '↻', 'text' => 'Remedial',   'url' => route('siswa.remedial.index'), 'aktif' => request()->is('siswa/remedial*')],
            ['icon' => '◎', 'text' => 'Profil',     'url' => route('profil'),               'aktif' => request()->is('profil')],
        ];

    } else {

        // guru dan operator
        $label = 'Portal Guru';

        $menu = [
            ['icon' => '▦', 'text' => 'Dashboard',    'url' => route('dashboard'),         'aktif' => request()->is('dashboard')],
            ['icon' => '▣', 'text' => 'Materi',       'url' => route('materi.index'),      'aktif' => request()->is('materi*')],
            ['icon' => '☑', 'text' => 'Kuis & Soal',  'url' => route('kuis.index'),        'aktif' => request()->is('kuis*')],
            ['icon' => '◉', 'text' => 'Nilai',        'url' => route('guru.nilai.index'),  'aktif' => request()->is('guru/nilai*')],
            ['icon' => '◎', 'text' => 'Profil',       'url' => route('profil'),            'aktif' => request()->is('profil')],
        ];

    }
@endphp

<aside class="sidebar">

    <div class="logo">
        <div class="logo-icon">IT</div>

        <div>
            <h2>ITIK</h2>
            <span>{{ $label }}</span>
        </div>
    </div>

    <div class="menu-title">
        Menu Utama
    </div>

    <nav class="menu">

        @foreach($menu as $item)
            <a href="{{ $item['url'] }}" class="{{ $item['aktif'] ? 'active' : '' }}">
                {{ $item['icon'] }} &nbsp; {{ $item['text'] }}
            </a>
        @endforeach

    </nav>

</aside>
