<?php
/**
 * Setup & Deployment Helper untuk SMP Negeri 4 Cepu di Hostinger
 * Penggunaan: Akses https://domain-anda.sch.id/hostinger_setup.php?key=CEPU2026
 */
define('SETUP_SECURITY_KEY', 'CEPU2026');

// Keamanan: Cek kunci otorisasi
if (!isset($_GET['key']) || $_GET['key'] !== SETUP_SECURITY_KEY) {
    http_response_code(403);
    die('<h2 style="font-family: sans-serif; color: #dc2626; text-align: center; margin-top: 50px;">Akses Ditolak: Kunci Otorisasi Tidak Valid.</h2>');
}

$startTime = microtime(true);
$logs = [];

function logMsg($msg, $type = 'info') {
    global $logs;
    $logs[] = ['msg' => $msg, 'type' => $type];
}

// 1. Muat Bootstrap Laravel
$basePath = realpath(__DIR__ . '/..');
if (!file_exists($basePath . '/vendor/autoload.php')) {
    // Coba path alternatif jika folder ditaruh sejajar dengan public_html
    $basePath = realpath(__DIR__ . '/../laravel_app');
}

if (!file_exists($basePath . '/vendor/autoload.php')) {
    die('<h2 style="font-family: sans-serif; color: #dc2626; text-align: center; margin-top: 50px;">Error: Direktori vendor/autoload.php tidak ditemukan di ' . htmlspecialchars((string) $basePath) . '</h2>');
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

// Tindakan Hapus Skrip Mandiri
if (isset($_POST['action']) && $_POST['action'] === 'delete_self') {
    @unlink(__FILE__);
    die('<div style="font-family: sans-serif; text-align: center; padding: 50px;"><h2 style="color: #059669;">✅ Berkas hostinger_setup.php Berhasil Dihapus!</h2><p>Website Anda kini aman dari akses konfigurasi ulang. <br><br><a href="/" style="display: inline-block; background: #1F5FBF; color: #fff; padding: 10px 20px; border-radius: 9999px; text-decoration: none; font-weight: bold;">Menuju Halaman Utama</a></p></div>');
}

// 2. Cek & Generate APP_KEY jika belum ada
try {
    if (empty(config('app.key'))) {
        Artisan::call('key:generate', ['--force' => true]);
        logMsg('Kunci enkripsi aplikasi (APP_KEY) berhasil digenerate.', 'success');
    } else {
        logMsg('APP_KEY sudah aktif dan valid.', 'info');
    }
} catch (\Throwable $e) {
    logMsg('Peringatan APP_KEY: ' . $e->getMessage(), 'warning');
}

// 3. Salin Aset Media Bawaan ke Storage
try {
    $storagePublic = storage_path('app/public');
    File::ensureDirectoryExists($storagePublic . '/staff');
    File::ensureDirectoryExists($storagePublic . '/facilities');
    File::ensureDirectoryExists($storagePublic . '/principal');
    File::ensureDirectoryExists($storagePublic . '/assets');

    if (File::exists(public_path('images/staff'))) {
        File::copyDirectory(public_path('images/staff'), $storagePublic . '/staff');
    }
    if (File::exists(public_path('images/facilities'))) {
        File::copyDirectory(public_path('images/facilities'), $storagePublic . '/facilities');
    }
    if (File::exists(public_path('images/principal'))) {
        File::copyDirectory(public_path('images/principal'), $storagePublic . '/principal');
    }
    if (File::exists(public_path('images/assets'))) {
        File::copyDirectory(public_path('images/assets'), $storagePublic . '/assets');
    }
    logMsg('Aset media gambar (guru, fasilitas, kepala sekolah) berhasil disinkronkan ke storage.', 'success');
} catch (\Throwable $e) {
    logMsg('Sinkronisasi aset media: ' . $e->getMessage(), 'warning');
}

// 4. Hubungkan Symlink Storage
try {
    Artisan::call('storage:link');
    logMsg('Tautan publik storage (storage:link) berhasil dihubungkan.', 'success');
} catch (\Throwable $e) {
    logMsg('Storage link: ' . $e->getMessage(), 'warning');
}

// 5. Jalankan Migrasi Basis Data & Auto-Seeder
try {
    Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = trim(Artisan::output());
    logMsg('Migrasi basis data berhasil dijalankan: ' . ($migrateOutput ?: 'Tabel basis data mutakhir.'), 'success');
} catch (\Throwable $e) {
    logMsg('Galat Migrasi Database: ' . $e->getMessage(), 'error');
}

// 6. Optimalisasi Caching Produksi
try {
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('icons:cache');
    Artisan::call('filament:cache-components');
    logMsg('Seluruh cache produksi (config, route, view, icons, filament) berhasil diaktifkan.', 'success');
} catch (\Throwable $e) {
    logMsg('Optimalisasi cache: ' . $e->getMessage(), 'warning');
}

$elapsed = round(microtime(true) - $startTime, 2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inisialisasi Sukses - SMP Negeri 4 Cepu</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #F1EBDA; color: #1B2A4A; margin: 0; padding: 24px; }
        .card { max-width: 680px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 32px; border: 2px solid #FFE3A0; }
        h1 { color: #1F5FBF; margin-top: 0; font-size: 22px; display: flex; align-items: center; gap: 8px; }
        .log-item { padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; font-size: 14px; font-weight: 500; }
        .log-success { background: #ECFDF5; color: #065F46; border-left: 4px solid #10B981; }
        .log-info { background: #EFF6FF; color: #1E40AF; border-left: 4px solid #3B82F6; }
        .log-warning { background: #FFFBEB; color: #92400E; border-left: 4px solid #F59E0B; }
        .log-error { background: #FEF2F2; color: #991B1B; border-left: 4px solid #EF4444; }
        .actions { margin-top: 24px; padding-top: 20px; border-top: 1px solid #E5E7EB; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { padding: 10px 20px; border-radius: 9999px; font-weight: 700; font-size: 14px; text-decoration: none; cursor: pointer; border: none; display: inline-flex; align-items: center; justify-content: center; }
        .btn-primary { background: #1F5FBF; color: #fff; }
        .btn-danger { background: #DC2626; color: #fff; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Setup Hostinger SMP Negeri 4 Cepu Selesai!</h1>
        <p style="font-size: 14px; color: #4B5563; margin-bottom: 20px;">
            Inisialisasi sistem berhasil diselesaikan dalam <strong><?= $elapsed ?> detik</strong>.
        </p>

        <?php foreach ($logs as $log): ?>
            <div class="log-item log-<?= $log['type'] ?>">
                <?= htmlspecialchars($log['msg']) ?>
            </div>
        <?php endforeach; ?>

        <div class="actions">
            <a href="/" class="btn btn-primary" target="_blank">Buka Website Utama &rarr;</a>
            <a href="/admin" class="btn btn-primary" style="background: #F5B700; color: #1B2A4A;" target="_blank">Buka Panel Admin</a>
            <form method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas setup ini demi keamanan?');">
                <input type="hidden" name="action" value="delete_self">
                <button type="submit" class="btn btn-danger">🗑️ Hapus Berkas Setup Ini</button>
            </form>
        </div>
    </div>
</body>
</html>
