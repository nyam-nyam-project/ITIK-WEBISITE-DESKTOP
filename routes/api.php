<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruKuisController;
use App\Http\Controllers\Api\GuruMateriController;
use App\Http\Controllers\Api\GuruNilaiController;
use App\Http\Controllers\Api\GuruRemedialController;
use App\Http\Controllers\Api\GuruSoalController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\SiswaKuisController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:siswa')->group(function () {
        Route::get('/materi', [MateriController::class, 'index']);
        Route::get('/materi/{idMateri}', [MateriController::class, 'show']);
        Route::post('/materi/{idMateri}/selesai', [MateriController::class, 'complete']);

        Route::get('/kuis', [SiswaKuisController::class, 'index']);
        Route::get('/kuis/{idKuis}', [SiswaKuisController::class, 'show']);
        Route::post('/kuis/{idKuis}/mulai', [SiswaKuisController::class, 'start']);
        Route::post('/kuis/{idKuis}/jawaban', [SiswaKuisController::class, 'saveAnswer']);
        Route::post('/kuis/{idKuis}/submit', [SiswaKuisController::class, 'submit']);
        Route::get('/kuis/{idKuis}/hasil', [SiswaKuisController::class, 'result']);
    });

    Route::middleware('role:guru')
        ->prefix('guru')
        ->group(function () {
            Route::apiResource('materi', GuruMateriController::class)
                ->names('guru.materi');

            Route::apiResource('kuis', GuruKuisController::class)
                ->except(['create', 'edit'])
                ->parameters(['kuis' => 'kuis'])
                ->names('guru.kuis');
            Route::post('/kuis/{kuis}/publish', [GuruKuisController::class, 'publish'])
                ->name('guru.kuis.publish');

            Route::apiResource('kuis.soal', GuruSoalController::class)
                ->parameters([
                    'kuis' => 'kuis',
                    'soal' => 'soal',
                ])
                ->names('guru.kuis.soal');

            Route::get('/nilai', [GuruNilaiController::class, 'index'])
                ->name('guru.nilai.index');
            Route::get('/nilai/{mengerjakan}', [GuruNilaiController::class, 'show'])
                ->name('guru.nilai.show');
            Route::patch('/nilai/{mengerjakan}/validasi', [GuruNilaiController::class, 'validateScore'])
                ->name('guru.nilai.validasi');

            Route::get('/remedial', [GuruRemedialController::class, 'index'])
                ->name('guru.remedial.index');
            Route::post('/remedial', [GuruRemedialController::class, 'store'])
                ->name('guru.remedial.store');
            Route::get('/remedial/{remedial}/siswa', [GuruRemedialController::class, 'students'])
                ->name('guru.remedial.siswa');
            Route::post('/remedial/{remedial}/tugaskan', [GuruRemedialController::class, 'assign'])
                ->name('guru.remedial.tugaskan');
            Route::get('/remedial/{remedial}', [GuruRemedialController::class, 'show'])
                ->name('guru.remedial.show');
        });
});
