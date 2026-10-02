<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\KuisController;
use App\Http\Controllers\SoalController;
use App\Http\Controllers\SiswaKuisController;
use App\Http\Controllers\ValidasiNilaiController;
use App\Http\Controllers\RemedialSiswaController;
use App\Http\Controllers\RemedialController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\OperatorDashboardController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');
Route::get('/profil', function () {
    return view('guru.profil');
})->middleware('auth')->name('profil');


// =====================================================
// UMUM - GUEST
// =====================================================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

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


// =====================================================
// OPERATOR
// =====================================================

// Route::get('/operator/dashboard', [OperatorDashboardController::class, 'index'])
//     ->middleware(['auth', 'role:operator'])
//     ->name('operator.dashboard');

// =====================================================
// GURU DAN OPERATOR
// =====================================================

Route::middleware(['auth', 'role:guru,operator'])->group(function () {

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

        Route::get('/dashboard', function () {
            return view('siswa.dashboard');
        })->name('dashboard');

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
