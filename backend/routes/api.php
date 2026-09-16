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
    Route::post('pendaftaran/form', [\App\Http\Controllers\Api\PendaftaranController::class, 'simpanForm']);
    Route::get('dining/info', [\App\Http\Controllers\Api\DiningController::class, 'getInfo']);
    Route::post('staff/dining/input', [\App\Http\Controllers\Api\DiningController::class, 'recordMeal']);
    Route::get('staff/dining/logs', [\App\Http\Controllers\Api\DiningController::class, 'getStaffTodayLogs']);
    Route::get('asrama/info', [\App\Http\Controllers\Api\PendaftaranController::class, 'asramaInfo']);
    Route::post('asrama/ganti-kamar', [\App\Http\Controllers\Api\PendaftaranController::class, 'gantiKamar']);
    Route::post('staff/asrama/approve', [\App\Http\Controllers\Api\PendaftaranController::class, 'staffApproveAsrama']);

    // Data kelas untuk guru (SMP/SMA) — daftar kelas dan daftar siswa per kelas.
    Route::prefix('guru')->middleware('role:guru,admin,super_admin')->group(function () {
        Route::get('kelas', [GuruKelasController::class, 'index']);
        Route::get('kelas/{tingkat}', [GuruKelasController::class, 'show']);
    });
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('registrations', [\App\Http\Controllers\Api\RegistrationController::class, 'store']);
    Route::post('payments', [\App\Http\Controllers\Api\PaymentController::class, 'createTransaction']);
});

Route::post('webhooks/midtrans', [\App\Http\Controllers\Api\PaymentController::class, 'webhook']);
