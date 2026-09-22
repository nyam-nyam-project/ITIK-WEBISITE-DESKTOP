<button class="sidebar-toggle d-lg-none position-fixed" type="button"
	data-bs-toggle="offcanvas" data-bs-target="#guruSidebar" aria-controls="guruSidebar"
	aria-label="Buka menu navigasi">
	<i class="bi bi-list" aria-hidden="true"></i>
</button>

<aside class="offcanvas-lg offcanvas-start guru-sidebar" tabindex="-1" id="guruSidebar"
	aria-labelledby="guruSidebarLabel">
	<div class="offcanvas-header px-4 py-4">
		<h5 class="offcanvas-title brand-title" id="guruSidebarLabel"><span class="brand-mark"><i class="bi bi-laptop" aria-hidden="true"></i></span> Edukasi TIK</h5>
		<button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"
			aria-label="Tutup menu navigasi"></button>
	</div>

	<div class="offcanvas-body d-flex flex-column p-3">
		<nav class="nav nav-pills flex-column" aria-label="Navigasi guru">
			<a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
				<i class="bi bi-house-door" aria-hidden="true"></i>Beranda
			</a>
			<a class="nav-link {{ request()->is('materi*') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
				<i class="bi bi-journal-text" aria-hidden="true"></i>Materi
			</a>
			<a class="nav-link" href="{{ url('/dashboard') }}">
				<i class="bi bi-question-circle" aria-hidden="true"></i>Kuis
			</a>
		</nav>
	</div>
</aside>
