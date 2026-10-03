<?php

use App\Http\Controllers\Api\ApplicantController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AsramaStaffController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatAdminController;
use App\Http\Controllers\Api\ChatSessionController;
use App\Http\Controllers\Api\GuruKelasController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PendaftaranController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SLAPUR (Siswa)
|--------------------------------------------------------------------------
*/

Route::prefix('chatbot')->group(function () {
    Route::post('message', [\App\Http\Controllers\Api\ChatbotCoreController::class, 'message']);
    Route::get('history', [\App\Http\Controllers\Api\ChatbotController::class, 'history']);
    Route::post('clear', [\App\Http\Controllers\Api\ChatbotController::class, 'clear']);
});

Route::prefix('v2/chat')->group(function () {
    Route::post('session', [ChatSessionController::class, 'createSession']);
    Route::post('message', [ChatSessionController::class, 'sendMessage']);
    Route::get('session/{sessionId}/history', [ChatSessionController::class, 'getHistory']);
});

Route::post('applicants', [ApplicantController::class, 'store']);
Route::get('invoices/status/{nomorPendaftaran}', [InvoiceController::class, 'checkStatus']);

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
    Route::post('user/update-profile-photo', [AuthController::class, 'updateProfilePhoto']);
    Route::get('pendaftaran/status', [\App\Http\Controllers\Api\PendaftaranController::class, 'status']);
    Route::get('user/biodata', [\App\Http\Controllers\Api\PendaftaranController::class, 'biodata']);
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
        
        // Tugas Guru
        Route::get('tugas', [\App\Http\Controllers\Api\AssignmentController::class, 'indexGuru']);
        Route::post('tugas', [\App\Http\Controllers\Api\AssignmentController::class, 'storeGuru']);
        Route::get('tugas/{id}/submissions', [\App\Http\Controllers\Api\AssignmentController::class, 'submissions']);
        
        // Absensi Guru
        Route::post('absensi', [\App\Http\Controllers\Api\AttendanceController::class, 'storeGuru']);
        Route::get('absensi', [\App\Http\Controllers\Api\AttendanceController::class, 'getGuruByDate']);
    });

    Route::prefix('siswa')->middleware('role:siswa,admin,super_admin')->group(function () {
        Route::get('absensi', [\App\Http\Controllers\Api\AttendanceController::class, 'getSiswa']);
        Route::get('tugas', [\App\Http\Controllers\Api\AssignmentController::class, 'indexSiswa']);
        Route::post('tugas/{id}/submit', [\App\Http\Controllers\Api\AssignmentController::class, 'submitSiswa']);
    });

    // --- Staff Asrama Routes (AsramaStaffController) ---
    Route::middleware('role:staff,admin,super_admin')->prefix('staff/payments')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\PaymentController::class, 'getPendingPayments']);
        Route::post('/{id}/verify', [\App\Http\Controllers\Api\PaymentController::class, 'verifyPayment']);
    });

    // --- Staff Asrama Routes (AsramaStaffController) ---
    Route::middleware('role:staff_asrama,super_admin')->prefix('staff/asrama')->group(function () {
        Route::get('/dashboard', [AsramaStaffController::class, 'dashboard']);
        Route::get('/kamar', [AsramaStaffController::class, 'listKamar']);
        Route::post('/kamar', [AsramaStaffController::class, 'storeKamar']);
        Route::put('/kamar/{id}', [AsramaStaffController::class, 'updateKamar']);
        Route::delete('/kamar/{id}', [AsramaStaffController::class, 'deleteKamar']);
        
        Route::get('/persetujuan', [AsramaStaffController::class, 'listPersetujuan']);
        Route::post('/approve', [PendaftaranController::class, 'staffApproveAsrama']); // Reuse existing method
        
        Route::get('/penghuni', [AsramaStaffController::class, 'listPenghuni']);
        Route::post('/mutasi', [AsramaStaffController::class, 'mutasiSiswa']);
    });

    // --- Administrasi (Staff) Routes ---
    Route::post('v1/auth/login', [\App\Http\Controllers\Api\AdministrasiController::class, 'login']);
    Route::middleware(['auth:sanctum', 'role:staff'])->prefix('v1/staff')->group(function () {
        Route::post('auth/logout', [\App\Http\Controllers\Api\AdministrasiController::class, 'logout']);
        Route::get('dashboard', [\App\Http\Controllers\Api\AdministrasiController::class, 'dashboard']);
        
        Route::get('students', [\App\Http\Controllers\Api\AdministrasiController::class, 'getSiswa']);
        Route::get('students/{id}', [\App\Http\Controllers\Api\AdministrasiController::class, 'getSiswaDetail']);
        Route::match(['put', 'patch'], 'siswa/{id}', [\App\Http\Controllers\Api\AdministrasiController::class, 'updateSiswa']);
        
        Route::get('dokumen', [\App\Http\Controllers\Api\AdministrasiController::class, 'getDokumen']);
        Route::get('dokumen/{id}', [\App\Http\Controllers\Api\AdministrasiController::class, 'getDokumenDetail']);
        Route::patch('dokumen/{id}/verifikasi', [\App\Http\Controllers\Api\AdministrasiController::class, 'verifikasiDokumen']);
        
        Route::get('pembayaran', [\App\Http\Controllers\Api\AdministrasiController::class, 'getPembayaran']);
        Route::get('pembayaran/{id}', [\App\Http\Controllers\Api\AdministrasiController::class, 'getPembayaranDetail']);
        Route::patch('pembayaran/{id}/verifikasi', [\App\Http\Controllers\Api\AdministrasiController::class, 'verifikasiPembayaran']);
        
        Route::get('tagihan', [\App\Http\Controllers\Api\AdministrasiController::class, 'getTagihan']);
        Route::post('tagihan', [\App\Http\Controllers\Api\AdministrasiController::class, 'buatTagihan']);
        
        Route::get('laporan/{type}', [\App\Http\Controllers\Api\AdministrasiController::class, 'laporan']);
    });

    // --- Admin Dasbor & Handoff ---
    Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {
        Route::get('applicants', [AdminDashboardController::class, 'getApplicants']);
        Route::get('transactions', [AdminDashboardController::class, 'getTransactions']);
        Route::get('transactions/export', [AdminDashboardController::class, 'exportPayments']);
        Route::post('transactions/{id}/mark', [AdminDashboardController::class, 'manualMarkPayment']);
        
        Route::get('handoffs', [ChatAdminController::class, 'getHandoffs']);
        Route::get('handoffs/{sessionId}/history', [ChatAdminController::class, 'getChatLog']);
        Route::post('handoffs/{sessionId}/reply', [ChatAdminController::class, 'replyHandoff']);
        
        Route::get('faqs', [ChatAdminController::class, 'getFAQs']);
        Route::post('faqs', [ChatAdminController::class, 'storeFAQ']);
    });

    // --- Kafetaria Routes ---
    Route::group(['prefix' => 'kafetaria'], function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\KafetariaController::class, 'dashboardData']);
        Route::get('/menus', [\App\Http\Controllers\Api\KafetariaController::class, 'getMenus']);
        Route::post('/menus', [\App\Http\Controllers\Api\KafetariaController::class, 'storeMenu']);
        Route::put('/menus/{id}', [\App\Http\Controllers\Api\KafetariaController::class, 'updateMenu']);
        Route::delete('/menus/{id}', [\App\Http\Controllers\Api\KafetariaController::class, 'destroyMenu']);
        
        Route::get('/today', [\App\Http\Controllers\Api\KafetariaController::class, 'todayMenu']);
        Route::post('/scan', [\App\Http\Controllers\Api\KafetariaController::class, 'scanQrCode']);
        Route::get('/laporan', [\App\Http\Controllers\Api\KafetariaController::class, 'report']);
    });
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('registrations', [\App\Http\Controllers\Api\RegistrationController::class, 'store']);
    Route::post('payments', [\App\Http\Controllers\Api\PaymentController::class, 'createTransaction']);
});

Route::post('webhooks/midtrans', [\App\Http\Controllers\Api\PaymentController::class, 'webhook']);
