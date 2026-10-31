<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\KuisController;
use App\Http\Controllers\SoalController;
use App\Http\Controllers\SiswaKuisController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\ValidasiNilaiController;
use App\Http\Controllers\RemedialSiswaController;
use App\Http\Controllers\RemedialController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\RekapNilaiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->role === 'siswa') {
        return redirect()->route('siswa.dashboard');
    }

    return app(DashboardController::class)->index();
})->middleware('auth')->name('dashboard');
Route::get('/profil', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.profil');
    }

    return view('guru.profil');
})->middleware('auth')->name('profil');

Route::middleware('auth')->group(function () {
    Route::get('/password/wajib', [PasswordController::class, 'formWajibGanti'])->name('password.wajib');
    Route::post('/password/wajib', [PasswordController::class, 'simpanWajibGanti'])->name('password.wajib.update');
    Route::put('/profil/password', [PasswordController::class, 'updateProfilPassword'])->name('profil.password');
});


// =====================================================
// UMUM - GUEST
// =====================================================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])
    ->name('admin.login');

Route::post('/admin/login', [AuthController::class, 'adminLogin'])
    ->name('admin.login.process');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


// =====================================================
// GURU
// =====================================================

Route::middleware(['auth', 'role:guru'])->group(function () {

    Route::patch('/kuis/{kuis}/publish', [KuisController::class, 'publish'])
        ->name('kuis.publish');

});


// =====================================================
// GURU - NILAI & REMEDIAL
// =====================================================

Route::middleware(['auth', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {

        // -------------------------
        // VALIDASI NILAI
        // -------------------------

        Route::get('/nilai', [ValidasiNilaiController::class, 'index'])
            ->name('nilai.index');

        Route::get('/nilai/{mengerjakan}', [ValidasiNilaiController::class, 'show'])
            ->name('nilai.show');

        Route::patch('/nilai/{mengerjakan}/validasi', [ValidasiNilaiController::class, 'validasi'])
            ->name('nilai.validasi');


        // -------------------------
        // REMEDIAL
        // -------------------------

        Route::get('/remedial', [RemedialController::class, 'index'])
            ->name('remedial.index');

        Route::get('/remedial/create', [RemedialController::class, 'create'])
            ->name('remedial.create');

        Route::post('/remedial', [RemedialController::class, 'store'])
            ->name('remedial.store');

        Route::get('/remedial/{id}/siswa', [RemedialController::class, 'siswa'])
            ->name('remedial.siswa');

        Route::post('/remedial/{id}/tugaskan', [RemedialController::class, 'tugaskan'])
            ->name('remedial.tugaskan');

        Route::get('/remedial/{id}', [RemedialController::class, 'show'])
            ->name('remedial.show');
    });


Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::view('/profil', 'admin.profil')->name('profil');
        Route::patch('/tahun-ajaran/{tahunAjaran}/aktifkan', [TahunAjaranController::class, 'aktifkan'])
            ->name('tahun-ajaran.aktifkan');
        Route::resource('tahun-ajaran', TahunAjaranController::class)
            ->parameters(['tahun-ajaran' => 'tahunAjaran'])
            ->except(['show', 'destroy']);
        Route::resource('mapel', MapelController::class)->except(['show', 'destroy']);
        Route::post('/kelas/{kelas}/anggota', [KelasController::class, 'tambahAnggota'])
            ->name('kelas.anggota.store');
        Route::resource('kelas', KelasController::class)
            ->parameters(['kelas' => 'kelas'])->except('destroy');
        Route::patch('/siswa/{siswa}/nonaktifkan', [SiswaController::class, 'nonaktifkan'])
            ->name('siswa.nonaktifkan');
        Route::patch('/siswa/{siswa}/aktifkan', [SiswaController::class, 'aktifkan'])
            ->name('siswa.aktifkan');
        Route::resource('siswa', SiswaController::class)->except(['show', 'destroy']);
        Route::patch('/guru/{guru}/nonaktifkan', [GuruController::class, 'nonaktifkan'])
            ->name('guru.nonaktifkan');
        Route::patch('/guru/{guru}/aktifkan', [GuruController::class, 'aktifkan'])
            ->name('guru.aktifkan');
        Route::resource('guru', GuruController::class)->except(['show', 'destroy']);
        Route::get('/rekap', [RekapNilaiController::class, 'index'])->name('rekap.index');
    });

Route::middleware(['auth', 'role:guru'])->group(function () {

    Route::resource('materi', MateriController::class);

    Route::resource('kuis', KuisController::class);

    Route::resource('kuis.soal', SoalController::class)
        ->parameters([
            'kuis' => 'kuis',
            'soal' => 'soal'
        ]);
});


// =====================================================
// SISWA
// =====================================================

Route::middleware(['auth', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {

        Route::get('/dashboard', [SiswaDashboardController::class, 'index'])
            ->name('dashboard');

        // -------------------------
        // MATERI
        // -------------------------

        Route::get('/materi', [ProgressController::class, 'index'])
            ->name('materi.index');

            Route::get('/materi/{idMateri}', [ProgressController::class, 'show'])
            ->name('materi.show');

        Route::post('/materi/{idMateri}/selesai', [ProgressController::class, 'selesai'])
            ->name('materi.selesai');

        // -------------------------
        // KUIS
        // -------------------------

        Route::get('/kuis', [SiswaKuisController::class, 'index'])
            ->name('kuis.index');

        Route::get('/kuis/{kuis}/mulai', [SiswaKuisController::class, 'mulai'])
            ->name('kuis.mulai');

        Route::get('/kuis/{kuis}/kerjakan', [SiswaKuisController::class, 'kerjakan'])
            ->name('kuis.kerjakan');

        Route::post('/kuis/{kuis}/jawaban', [SiswaKuisController::class, 'simpanJawaban'])
            ->name('kuis.jawaban');

        Route::post('/kuis/{kuis}/submit', [SiswaKuisController::class, 'submit'])
            ->name('kuis.submit');

        Route::get('/kuis/{kuis}/hasil', [SiswaKuisController::class, 'hasil'])
            ->name('kuis.hasil');


        // -------------------------
        // NILAI
        // -------------------------

        Route::get('/nilai', [SiswaKuisController::class, 'daftarNilai'])
            ->name('nilai');


        // -------------------------
        // REMEDIAL SISWA
        // -------------------------

        Route::get('/remedial', [RemedialSiswaController::class, 'index'])
            ->name('remedial.index');

        Route::get('/remedial/{id}/kerjakan', [RemedialSiswaController::class, 'kerjakan'])
            ->name('remedial.kerjakan');
    });
