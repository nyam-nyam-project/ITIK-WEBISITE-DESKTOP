<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruKuisController;
use App\Http\Controllers\Api\GuruMateriController;
use App\Http\Controllers\Api\GuruNilaiController;
use App\Http\Controllers\Api\GuruRemedialController;
use App\Http\Controllers\Api\SiswaController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'updateProfile']);
    Route::post('/me/photo', [AuthController::class, 'updatePhoto']);
    Route::delete('/me/photo', [AuthController::class, 'deletePhoto']);
    Route::put('/me/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:siswa')->prefix('siswa')->group(function (): void {
        Route::get('/materi', [SiswaController::class, 'materi']);
        Route::get('/materi/{materi}', [SiswaController::class, 'showMateri']);
        Route::post('/materi/{materi}/selesai', [SiswaController::class, 'selesaikanMateri']);

        Route::get('/kuis', [SiswaController::class, 'kuis']);
        Route::get('/nilai', [SiswaController::class, 'nilai']);
        Route::get('/remedial', [SiswaController::class, 'remedial']);
        Route::get('/kuis/{kuis}', [SiswaController::class, 'detailKuis']);
        Route::post('/kuis/{kuis}/mulai', [SiswaController::class, 'mulaiKuis']);
        Route::put('/kuis/{kuis}/jawaban', [SiswaController::class, 'simpanJawaban']);
        Route::post('/kuis/{kuis}/submit', [SiswaController::class, 'submitKuis']);
    });

    Route::middleware('role:guru')->prefix('guru')->group(function (): void {
        Route::get('/mapel', [GuruMateriController::class, 'mapel'])
            ->name('guru.mapel.index');
        Route::apiResource('materi', GuruMateriController::class)
            ->names('guru.materi');
        Route::apiResource('kuis', GuruKuisController::class)
            ->parameters(['kuis' => 'kuis'])
            ->names('guru.kuis');

        Route::post('/kuis/{kuis}/soal', [GuruKuisController::class, 'storeSoal'])
            ->name('guru.kuis.soal.store');
        Route::put('/kuis/{kuis}/soal/{soal}', [GuruKuisController::class, 'updateSoal'])
            ->name('guru.kuis.soal.update');
        Route::delete('/kuis/{kuis}/soal/{soal}', [GuruKuisController::class, 'destroySoal'])
            ->name('guru.kuis.soal.destroy');

        Route::post('/kuis/{kuis}/publish', [GuruKuisController::class, 'publish'])
            ->name('guru.kuis.publish');

        Route::get('/nilai', [GuruNilaiController::class, 'index'])
            ->name('guru.nilai.index');
        Route::get('/nilai/{mengerjakan}', [GuruNilaiController::class, 'show'])
            ->name('guru.nilai.show');
        Route::patch('/nilai/{mengerjakan}/validasi', [GuruNilaiController::class, 'validasi'])
            ->name('guru.nilai.validasi');

        Route::get('/remedial', [GuruRemedialController::class, 'index'])
            ->name('guru.remedial.index');
        Route::post('/remedial', [GuruRemedialController::class, 'store'])
            ->name('guru.remedial.store');
        Route::get('/remedial/{remedial}/siswa', [GuruRemedialController::class, 'siswa'])
            ->name('guru.remedial.siswa');
        Route::post('/remedial/{remedial}/tugaskan', [GuruRemedialController::class, 'tugaskan'])
            ->name('guru.remedial.tugaskan');
        Route::get('/remedial/{remedial}', [GuruRemedialController::class, 'show'])
            ->name('guru.remedial.show');
    });
});
