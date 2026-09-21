<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PpdbController;
use App\Http\Controllers\AdminPpdbController;

/*
|--------------------------------------------------------------------------
| Web Routes - PPDB Frontend Consumer
|--------------------------------------------------------------------------
|
| Laravel operates as an API consumer for the FastAPI backend.
|
*/

// ==========================================
// 1. Publik / Beranda & Portal Siswa
// ==========================================
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Autentikasi Calon Siswa (Publik)
Route::controller(AuthController::class)->group(function () {
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->name('register.submit');
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.submit');
});

// Rute Terproteksi Calon Siswa (check.api.token)
Route::middleware(['check.api.token'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/ppdb/upload-payment', [PpdbController::class, 'uploadPayment'])->name('ppdb.upload-payment');
    Route::get('/ppdb/form', [PpdbController::class, 'showForm'])->name('ppdb.form');
    Route::post('/ppdb/form', [PpdbController::class, 'submitForm'])->name('ppdb.form.submit');
    Route::get('/ppdb/test-card', [PpdbController::class, 'showTestCard'])->name('ppdb.test-card');
    Route::get('/ppdb/upload-berkas', [PpdbController::class, 'showUpload'])->name('ppdb.upload');
});

// ==========================================
// 2. Modul Admin PPDB (SIAKAD Staff)
// ==========================================
Route::prefix('admin')->group(function () {
    // Admin Auth (Publik)
    Route::get('/login', [AdminPpdbController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminPpdbController::class, 'login'])->name('admin.login.submit');

    // Admin Protected Routes (check.admin.token)
    Route::middleware(['check.admin.token'])->group(function () {
        Route::post('/logout', [AdminPpdbController::class, 'logout'])->name('admin.logout');
        
        // Dashboard Utama
        Route::get('/ppdb', [AdminPpdbController::class, 'index'])->name('admin.ppdb.index');
        Route::get('/ppdb/dashboard', [AdminPpdbController::class, 'index'])->name('admin.ppdb.dashboard');
        
        // Modul Data Siswa
        Route::get('/ppdb/students', [AdminPpdbController::class, 'students'])->name('admin.ppdb.students');
        
        // Modul Data User Akun
        Route::get('/ppdb/users', [AdminPpdbController::class, 'users'])->name('admin.ppdb.users');
        Route::post('/ppdb/users', [AdminPpdbController::class, 'storeUser'])->name('admin.ppdb.users.store');
        Route::get('/ppdb/users/template', [AdminPpdbController::class, 'downloadUserTemplate'])->name('admin.ppdb.users.template');
        Route::post('/ppdb/users/import', [AdminPpdbController::class, 'importUsers'])->name('admin.ppdb.users.import');
        Route::put('/ppdb/users/{id}', [AdminPpdbController::class, 'updateUser'])->name('admin.ppdb.users.update');
        Route::delete('/ppdb/users/{id}', [AdminPpdbController::class, 'destroyUser'])->name('admin.ppdb.users.destroy');
        
        // Modul Data Berkas
        Route::get('/ppdb/documents', [AdminPpdbController::class, 'documents'])->name('admin.ppdb.documents');
        
        // Modul Verifikasi Pembayaran
        Route::get('/ppdb/payments', [AdminPpdbController::class, 'payments'])->name('admin.ppdb.payments');
        
        // Ekspor & Dossier
        Route::get('/ppdb/export', [AdminPpdbController::class, 'export'])->name('admin.ppdb.export');
        Route::get('/ppdb/registrations/{id}', [AdminPpdbController::class, 'show'])->name('admin.ppdb.show');
        
        // Form Edit & Update Data Pendaftar
        Route::get('/ppdb/registrations/{id}/edit', [AdminPpdbController::class, 'edit'])->name('admin.ppdb.edit');
        Route::put('/ppdb/registrations/{id}', [AdminPpdbController::class, 'update'])->name('admin.ppdb.update');

        // Hapus Data Pendaftar
        Route::delete('/ppdb/registrations/{id}', [AdminPpdbController::class, 'destroy'])->name('admin.ppdb.destroy');
        
        // Verifikasi Pembayaran (Accept / Reject)
        Route::post('/ppdb/registrations/{id}/verify-payment', [AdminPpdbController::class, 'verifyPayment'])->name('admin.ppdb.verify-payment');
        
        // Penerimaan Calon Siswa & Migrasi Data Otomatis ke SIAKAD
        Route::post('/ppdb/registrations/{id}/accept', [AdminPpdbController::class, 'acceptStudent'])->name('admin.ppdb.accept');
    });
});
