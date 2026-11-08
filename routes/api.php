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
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:siswa')->prefix('siswa')->group(function (): void {
        Route::get('/materi', [SiswaController::class, 'materi']);
        Route::get('/materi/{materi}', [SiswaController::class, 'showMateri']);
        Route::post('/materi/{materi}/selesai', [SiswaController::class, 'selesaikanMateri']);

        Route::get('/kuis', [SiswaController::class, 'kuis']);
        Route::post('/kuis/{kuis}/mulai', [SiswaController::class, 'mulaiKuis']);
        Route::get('/kuis/{kuis}', [SiswaController::class, 'detailKuis']);
        Route::put('/kuis/{kuis}/jawaban', [SiswaController::class, 'simpanJawaban']);
        Route::post('/kuis/{kuis}/submit', [SiswaController::class, 'submitKuis']);
        Route::get('/nilai', [SiswaController::class, 'nilai']);
        Route::get('/remedial', [SiswaController::class, 'remedial']);
    });

    Route::middleware('role:guru,operator')->prefix('guru')->group(function (): void {
        Route::apiResource('materi', GuruMateriController::class);
        Route::apiResource('kuis', GuruKuisController::class);
        Route::post('/kuis/{kuis}/soal', [GuruKuisController::class, 'storeSoal']);
        Route::put('/kuis/{kuis}/soal/{soal}', [GuruKuisController::class, 'updateSoal']);
        Route::delete('/kuis/{kuis}/soal/{soal}', [GuruKuisController::class, 'destroySoal']);
    });

    Route::middleware('role:guru')->prefix('guru')->group(function (): void {
        Route::post('/kuis/{kuis}/publish', [GuruKuisController::class, 'publish']);
        Route::get('/nilai', [GuruNilaiController::class, 'index']);
        Route::get('/nilai/{mengerjakan}', [GuruNilaiController::class, 'show']);
        Route::patch('/nilai/{mengerjakan}/validasi', [GuruNilaiController::class, 'validasi']);
        Route::get('/remedial', [GuruRemedialController::class, 'index']);
        Route::post('/remedial', [GuruRemedialController::class, 'store']);
        Route::get('/remedial/{remedial}/siswa', [GuruRemedialController::class, 'siswa']);
        Route::get('/remedial/{remedial}', [GuruRemedialController::class, 'show']);
        Route::post('/remedial/{remedial}/tugaskan', [GuruRemedialController::class, 'tugaskan']);
    });
});
