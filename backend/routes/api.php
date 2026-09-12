<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruKelasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SLAPUR (Siswa)
|--------------------------------------------------------------------------
*/

Route::prefix('chatbot')->group(function () {
    Route::post('message', [\App\Http\Controllers\Api\ChatbotController::class, 'message']);
    Route::get('history', [\App\Http\Controllers\Api\ChatbotController::class, 'history']);
    Route::post('clear', [\App\Http\Controllers\Api\ChatbotController::class, 'clear']);
});

Route::get('subjects', [\App\Http\Controllers\Api\SubjectController::class, 'index']);

Route::get('pendaftaran/kurikulum', [\App\Http\Controllers\Api\PendaftaranController::class, 'kurikulum']);
Route::get('pendaftaran/kamar', [\App\Http\Controllers\Api\PendaftaranController::class, 'kamar']);

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login-guru', [AuthController::class, 'loginGuru']);
    Route::post('login-staff', [AuthController::class, 'loginStaff']);
    Route::post('register-guru', [AuthController::class, 'registerGuru']);
    Route::post('forgot-password-guru', [AuthController::class, 'forgotPasswordGuru']);
    Route::post('reset-password-guru', [AuthController::class, 'resetPasswordGuru']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('pendaftaran/status', [\App\Http\Controllers\Api\PendaftaranController::class, 'status']);
    Route::get('pendaftaran/dokumen', [\App\Http\Controllers\Api\PendaftaranController::class, 'dokumen']);
    Route::post('pendaftaran/kamar', [\App\Http\Controllers\Api\PendaftaranController::class, 'pilihKamar']);
    Route::post('pendaftaran/upload-bukti-pembayaran', [\App\Http\Controllers\Api\PendaftaranController::class, 'uploadBuktiPembayaran']);
    Route::post('pendaftaran/upload-dokumen', [\App\Http\Controllers\Api\PendaftaranController::class, 'uploadDokumen']);

    // Data kelas untuk guru (SMP/SMA) — daftar kelas dan daftar siswa per kelas.
    Route::prefix('guru')->group(function () {
        Route::get('kelas', [GuruKelasController::class, 'index']);
        Route::get('kelas/{tingkat}', [GuruKelasController::class, 'show']);
    });
});
