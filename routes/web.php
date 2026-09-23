<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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
    Route::post('/register', 'register')->name('register.submit')->middleware('throttle:3,1');
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.submit')->middleware('throttle:5,1');
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
    Route::post('/ppdb/upload-berkas', [PpdbController::class, 'submitUpload'])->name('ppdb.upload.submit');
});

// ==========================================
// 2. Modul Admin PPDB (SIAKAD Staff)
// ==========================================
Route::prefix('admin')->group(function () {
    // Admin Auth (Publik)
    Route::get('/login', [AdminPpdbController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminPpdbController::class, 'login'])->name('admin.login.submit')->middleware('throttle:5,1');

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
        
        // Satu-Klik Sinkronisasi ke Master Data API (One-Click Bulk Sync)
        Route::post('/ppdb/sync', [AdminPpdbController::class, 'syncAllToApi'])->name('admin.ppdb.sync');
    });
});

// ============================================================
// 3. Helper Rute Deployment cPanel / Production
// ============================================================

// 1. Generate Application Key (php artisan key:generate --force)
Route::get('/generate-key', function () {
    try {
        Artisan::call('key:generate', ['--force' => true]);
        $output = Artisan::output();

        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.3);'>
            <div style='display:flex;align-items:center;gap:10px;margin-bottom:15px;'>
                <span style='background:#10b981;color:#fff;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:bold;'>SUCCESS</span>
                <h2 style='margin:0;font-size:20px;color:#38bdf8;'>✓ Application Key Berhasil Di-generate</h2>
            </div>
            <p style='color:#94a3b8;font-size:14px;margin-bottom:12px;'>Kunci enkripsi aplikasi telah diperbarui di file <code>.env</code> server cPanel Anda.</p>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#34d399;font-size:13px;overflow-x:auto;border:1px solid #334155;'>" . htmlspecialchars($output ?: 'Application key set successfully.') . "</pre>
            <div style='margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;'>
                <a href='" . url('/migrate') . "' style='padding:8px 16px;background:#0284c7;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600;'>Lanjut: Jalankan Migrate &rarr;</a>
                <a href='" . url('/') . "' style='padding:8px 16px;background:#334155;color:#f1f5f9;text-decoration:none;border-radius:8px;font-size:13px;'>&larr; Beranda</a>
            </div>
        </div>");
    } catch (\Throwable $e) {
        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;'>
            <h2 style='margin:0 0 15px 0;font-size:20px;color:#ef4444;'>✗ Gagal Generate Key</h2>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#fca5a5;font-size:13px;overflow-x:auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>
        </div>", 500);
    }
});

// 2. Database Migration (php artisan migrate --force)
Route::get('/migrate', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.3);'>
            <div style='display:flex;align-items:center;gap:10px;margin-bottom:15px;'>
                <span style='background:#10b981;color:#fff;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:bold;'>SUCCESS</span>
                <h2 style='margin:0;font-size:20px;color:#38bdf8;'>✓ Database Migration Berhasil Dijalankan</h2>
            </div>
            <p style='color:#94a3b8;font-size:14px;margin-bottom:12px;'>Seluruh tabel database PPDB telah dibuat / dimigrasikan ke MySQL cPanel.</p>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#34d399;font-size:13px;overflow-x:auto;border:1px solid #334155;'>" . htmlspecialchars($output ?: 'Nothing to migrate.') . "</pre>
            <div style='margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;'>
                <a href='" . url('/migrate-seed') . "' style='padding:8px 16px;background:#f59e0b;color:#1e293b;text-decoration:none;border-radius:8px;font-size:13px;font-weight:700;'>Jalankan Seeder (AdminSeeder) &rarr;</a>
                <a href='" . url('/storage-link') . "' style='padding:8px 16px;background:#0284c7;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600;'>Storage Link &rarr;</a>
                <a href='" . url('/clear-cache') . "' style='padding:8px 16px;background:#334155;color:#f1f5f9;text-decoration:none;border-radius:8px;font-size:13px;'>Clear Cache &rarr;</a>
                <a href='" . url('/') . "' style='padding:8px 16px;background:#334155;color:#f1f5f9;text-decoration:none;border-radius:8px;font-size:13px;'>&larr; Beranda</a>
            </div>
        </div>");
    } catch (\Throwable $e) {
        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;'>
            <h2 style='margin:0 0 15px 0;font-size:20px;color:#ef4444;'>✗ Database Migration Gagal</h2>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#fca5a5;font-size:13px;overflow-x:auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>
            <p style='color:#94a3b8;font-size:13px;margin-top:12px;'>Pastikan konfigurasi <code>DB_DATABASE</code>, <code>DB_USERNAME</code>, dan <code>DB_PASSWORD</code> di file <code>.env</code> cPanel sudah sesuai dengan database MySQL yang telah Anda buat di cPanel.</p>
        </div>", 500);
    }
});

// 3. Migrate + Seeder Otomatis (php artisan migrate --force && php artisan db:seed --force)
Route::get('/migrate-seed', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();

        Artisan::call('db:seed', ['--force' => true]);
        $seedOutput = Artisan::output();

        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.3);'>
            <div style='display:flex;align-items:center;gap:10px;margin-bottom:15px;'>
                <span style='background:#10b981;color:#fff;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:bold;'>SUCCESS</span>
                <h2 style='margin:0;font-size:20px;color:#38bdf8;'>✓ Migrate &amp; Seeder Berhasil Dijalankan</h2>
            </div>
            <p style='color:#94a3b8;font-size:13px;margin-bottom:6px;'>Hasil Migration:</p>
            <pre style='background:#1e293b;padding:12px;border-radius:8px;color:#34d399;font-size:12px;overflow-x:auto;border:1px solid #334155;margin-bottom:15px;'>" . htmlspecialchars($migrateOutput ?: 'Nothing to migrate.') . "</pre>
            <p style='color:#94a3b8;font-size:13px;margin-bottom:6px;'>Hasil Seeder (Termasuk Akun Admin PPDB):</p>
            <pre style='background:#1e293b;padding:12px;border-radius:8px;color:#fbbf24;font-size:12px;overflow-x:auto;border:1px solid #334155;'>" . htmlspecialchars($seedOutput ?: 'Seeding completed.') . "</pre>
            <div style='margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;'>
                <a href='" . url('/admin/login') . "' style='padding:8px 16px;background:#0284c7;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600;'>Buka Login Admin &rarr;</a>
                <a href='" . url('/') . "' style='padding:8px 16px;background:#334155;color:#f1f5f9;text-decoration:none;border-radius:8px;font-size:13px;'>&larr; Beranda</a>
            </div>
        </div>");
    } catch (\Throwable $e) {
        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;'>
            <h2 style='margin:0 0 15px 0;font-size:20px;color:#ef4444;'>✗ Migrate &amp; Seeder Gagal</h2>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#fca5a5;font-size:13px;overflow-x:auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>
        </div>", 500);
    }
});

// 4. Storage Link (php artisan storage:link + PHP native symlink fallback & aliases)
$storageLinkHandler = function () {
    $target = storage_path('app/public');
    $link = public_path('storage');
    $messages = [];
    $isSuccess = true;

    try {
        // Pastikan direktori target storage/app/public ada
        if (!file_exists($target)) {
            @mkdir($target, 0755, true);
            $messages[] = "Direktori target storage/app/public berhasil dibuat.";
        }

        // Cek apakah public/storage sudah ada
        if (file_exists($link) || is_link($link)) {
            $messages[] = "Folder atau symlink [public/storage] sudah ada dan aktif.";
        } else {
            // Coba jalankan perintah artisan terlebih dahulu
            try {
                Artisan::call('storage:link');
                $output = trim(Artisan::output());
                if (!empty($output)) {
                    $messages[] = "Artisan: " . $output;
                }
            } catch (\Throwable $ex) {
                $messages[] = "Artisan storage:link error: " . $ex->getMessage();
            }

            // Jika masih belum terbentuk, coba native symlink (umum pada shared hosting/cPanel)
            if (!file_exists($link) && !is_link($link)) {
                if (function_exists('symlink')) {
                    if (@symlink($target, $link)) {
                        $messages[] = "Native PHP symlink berhasil dibuat: {$link} -> {$target}";
                    } else {
                        $isSuccess = false;
                        $messages[] = "Gagal membuat native symlink. Periksa hak akses / permission folder hosting Anda.";
                    }
                } else {
                    $isSuccess = false;
                    $messages[] = "Fungsi symlink() dinonaktifkan di konfigurasi PHP hosting ini.";
                }
            }
        }

        $allOutput = implode("\n", $messages);
        $badge = $isSuccess ? 'SUCCESS' : 'INFO';
        $badgeBg = $isSuccess ? '#10b981' : '#f59e0b';
        $title = $isSuccess ? '✓ Storage Symlink Berhasil Diproses' : 'ℹ Info Storage Symlink';

        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.3);'>
            <div style='display:flex;align-items:center;gap:10px;margin-bottom:15px;'>
                <span style='background:{$badgeBg};color:#fff;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:bold;'>{$badge}</span>
                <h2 style='margin:0;font-size:20px;color:#38bdf8;'>{$title}</h2>
            </div>
            <p style='color:#94a3b8;font-size:14px;margin-bottom:12px;'>Target: <code>{$target}</code> &rarr; Link: <code>{$link}</code></p>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#34d399;font-size:13px;overflow-x:auto;border:1px solid #334155;'>" . htmlspecialchars($allOutput ?: 'The [public/storage] directory has been linked.') . "</pre>
            <div style='margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;'>
                <a href='" . url('/') . "' style='padding:8px 16px;background:#334155;color:#f1f5f9;text-decoration:none;border-radius:8px;font-size:13px;'>&larr; Beranda</a>
                <a href='" . url('/clear-cache') . "' style='padding:8px 16px;background:#0284c7;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600;'>Clear Cache &rarr;</a>
                <a href='" . url('/admin/login') . "' style='padding:8px 16px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;'>Login Admin</a>
            </div>
        </div>");
    } catch (\Throwable $e) {
        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;'>
            <h2 style='margin:0 0 15px 0;font-size:20px;color:#ef4444;'>✗ Storage Link Gagal</h2>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#fca5a5;font-size:13px;overflow-x:auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>
            <div style='margin-top:20px;'>
                <a href='" . url('/') . "' style='padding:8px 16px;background:#334155;color:#f1f5f9;text-decoration:none;border-radius:8px;font-size:13px;'>&larr; Beranda</a>
            </div>
        </div>", 500);
    }
};

Route::get('/storage-link', $storageLinkHandler)->name('storage.link');
Route::get('/link-storage', $storageLinkHandler);
Route::get('/linkstorage', $storageLinkHandler);
Route::get('/storage/link', $storageLinkHandler);

// 5. Clear Cache & Optimize (php artisan optimize:clear)
Route::get('/clear-cache', function () {
    try {
        Artisan::call('optimize:clear');
        $output = Artisan::output();

        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.3);'>
            <div style='display:flex;align-items:center;gap:10px;margin-bottom:15px;'>
                <span style='background:#10b981;color:#fff;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:bold;'>SUCCESS</span>
                <h2 style='margin:0;font-size:20px;color:#38bdf8;'>✓ Seluruh Cache Berhasil Dibersihkan</h2>
            </div>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#34d399;font-size:13px;overflow-x:auto;border:1px solid #334155;'>" . htmlspecialchars($output ?: 'Compiled views, config, and route caches cleared.') . "</pre>
            <div style='margin-top:20px;'>
                <a href='" . url('/') . "' style='padding:8px 16px;background:#334155;color:#f1f5f9;text-decoration:none;border-radius:8px;font-size:13px;'>&larr; Beranda</a>
            </div>
        </div>");
    } catch (\Throwable $e) {
        return response("
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:700px;margin:50px auto;padding:25px;background:#0f172a;color:#f8fafc;border-radius:16px;'>
            <h2 style='margin:0 0 15px 0;font-size:20px;color:#ef4444;'>✗ Gagal Clear Cache</h2>
            <pre style='background:#1e293b;padding:15px;border-radius:10px;color:#fca5a5;font-size:13px;overflow-x:auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>
        </div>", 500);
    }
});

