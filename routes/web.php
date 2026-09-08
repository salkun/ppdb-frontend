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
        
        // Dashboard & Monitoring Pendaftar
        Route::get('/ppdb', [AdminPpdbController::class, 'index'])->name('admin.ppdb.index');
        Route::get('/ppdb/registrations/{id}', [AdminPpdbController::class, 'show'])->name('admin.ppdb.show');
        
        // Verifikasi Pembayaran (Accept / Reject)
        Route::post('/ppdb/registrations/{id}/verify-payment', [AdminPpdbController::class, 'verifyPayment'])->name('admin.ppdb.verify-payment');
        
        // Penerimaan Calon Siswa & Migrasi Data Otomatis ke SIAKAD
        Route::post('/ppdb/registrations/{id}/accept', [AdminPpdbController::class, 'acceptStudent'])->name('admin.ppdb.accept');
    });
});
