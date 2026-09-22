@extends('guru.layouts.app')

@section('container')
    <section class="dashboard-page" aria-labelledby="dashboard-title">
        <header class="dashboard-header">
            <div class="dashboard-heading">
                <div class="profile-avatar" aria-hidden="true">SD</div>
                <div>
                    <div class="eyebrow">Dashboard</div>
                    <div class="profile-name">Sari Dewi, S.Pd</div>
                </div>
            </div>
            <button class="notification-button" type="button" aria-label="Lihat notifikasi">
                <i class="bi bi-bell" aria-hidden="true"></i>
                <span class="notification-dot" aria-hidden="true"></span>
            </button>
        </header>

        <div class="welcome-copy">
            <h1 id="dashboard-title">Pantau perkembangan siswa Anda</h1>
            <p>Guru Pengampu TIK</p>
        </div>

        <div class="stats-grid" aria-label="Ringkasan dashboard">
            <article class="stat-card">
                <strong>5</strong>
                <span>Siswa</span>
            </article>
            <article class="stat-card">
                <strong>4</strong>
                <span>Kuis</span>
            </article>
            <article class="stat-card">
                <strong>2</strong>
                <span>Perlu Validasi</span>
            </article>
        </div>

        <section class="management-section" aria-labelledby="management-title">
            <h2 id="management-title">Kelola</h2>
            <div class="management-list">
                <a class="management-item" href="{{ url('/dashboard') }}">
                    <span class="action-icon action-icon-green"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span>
                    <span class="action-copy"><strong>Validasi Nilai Siswa</strong><small>2 nilai menunggu validasi</small></span>
                    <i class="bi bi-chevron-right action-arrow" aria-hidden="true"></i>
                </a>
                <a class="management-item" href="{{ url('/dashboard') }}">
                    <span class="action-icon action-icon-red"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i></span>
                    <span class="action-copy"><strong>Kelola Materi</strong><small>4 materi PDF tersedia</small></span>
                    <i class="bi bi-chevron-right action-arrow" aria-hidden="true"></i>
                </a>
                <a class="management-item" href="{{ url('/dashboard') }}">
                    <span class="action-icon action-icon-orange"><i class="bi bi-question-circle" aria-hidden="true"></i></span>
                    <span class="action-copy"><strong>Kelola Kuis</strong><small>4 kuis dapat diedit atau dihapus</small></span>
                    <i class="bi bi-chevron-right action-arrow" aria-hidden="true"></i>
                </a>
            </div>
        </section>
    </section>
@endsection
