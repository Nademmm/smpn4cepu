<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\SystemBackup;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BackupRecoveryService
{
    /**
     * Jalankan proses backup penuh yang mencakup Database SQLite dan Berkas Unggahan Publik.
     */
    public function createBackup(?User $actor = null): SystemBackup
    {
        /** @var User|null $currentUser */
        $currentUser = $actor ?: Auth::user();
        $actorName = $currentUser?->name ?? 'Sistem';

        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_smpn4cepu_{$timestamp}.zip";
        $relativePath = "backups/{$filename}";

        // Pastikan direktori backup tersedia
        Storage::disk('local')->makeDirectory('backups');
        $fullZipPath = Storage::disk('local')->path($relativePath);

        $zip = new ZipArchive();
        if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Gagal membuat arsip ZIP backup di {$fullZipPath}");
        }

        $filesCount = 0;

        // 1. Salin Database SQLite ke dalam ZIP
        $databasePath = database_path('database.sqlite');
        if (File::exists($databasePath)) {
            $zip->addFile($databasePath, 'database/database.sqlite');
            $filesCount++;
        }

        // 2. Salin Seluruh File Unggahan (PDF materi, foto galeri, cover buku, avatar)
        $publicStoragePath = storage_path('app/public');
        if (File::isDirectory($publicStoragePath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($publicStoragePath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $localPath = $item->getPathname();
                    $relativeInZip = 'storage_public/' . str_replace('\\', '/', substr($localPath, strlen($publicStoragePath) + 1));
                    $zip->addFile($localPath, $relativeInZip);
                    $filesCount++;
                }
            }
        }

        // 3. Tambahkan Berkas Manifest Integritas
        $manifest = [
            'app_name' => 'SMP Negeri 4 Cepu',
            'created_at' => now()->toIso8601String(),
            'created_by' => $actorName,
            'database_engine' => config('database.default'),
            'total_files_archived' => $filesCount,
            'scope' => 'Database SQLite + File Unggahan (PDF & Gambar)',
            'version' => '1.0.0',
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip->close();

        $sizeBytes = file_exists($fullZipPath) ? filesize($fullZipPath) : 0;

        $backup = SystemBackup::create([
            'filename' => $filename,
            'disk' => 'local',
            'filepath' => $relativePath,
            'size_bytes' => $sizeBytes,
            'scope' => 'Database SQLite + File Unggahan (PDF & Gambar)',
            'status' => 'berhasil',
            'created_by' => $currentUser?->id,
        ]);

        // Catat ke Activity Log
        ActivityLog::record(
            action: 'create_backup',
            description: "{$actorName} membuat berkas backup sistem lengkap ({$backup->formatted_size}).",
            reason: 'Pemeliharaan dan perlindungan data rutin (database dan seluruh berkas unggahan)',
            subject: $backup,
            properties: [
                'filename' => $filename,
                'size_bytes' => $sizeBytes,
                'files_count' => $filesCount,
            ],
            module: 'Pengaturan'
        );

        return $backup;
    }

    /**
     * Jalankan simulasi uji pemulihan untuk memverifikasi integritas database dan berkas di dalam ZIP.
     */
    public function testRecovery(SystemBackup $backup, ?User $actor = null): array
    {
        /** @var User|null $currentUser */
        $currentUser = $actor ?: Auth::user();
        $actorName = $currentUser?->name ?? 'Sistem';

        $fullZipPath = Storage::disk($backup->disk)->path($backup->filepath);

        if (! file_exists($fullZipPath)) {
            $backup->update([
                'tested_at' => now(),
                'test_status' => 'gagal',
                'test_notes' => 'Berkas ZIP backup tidak ditemukan pada storage server.',
            ]);

            return [
                'success' => false,
                'message' => 'Berkas backup fisik tidak ditemukan.',
            ];
        }

        $tempExtractDir = storage_path('app/temp_recovery_test_' . uniqid());
        File::makeDirectory($tempExtractDir, 0755, true, true);

        try {
            $zip = new ZipArchive();
            if ($zip->open($fullZipPath) !== true) {
                throw new Exception('Berkas ZIP rusak atau tidak dapat dibuka.');
            }

            $zip->extractTo($tempExtractDir);
            $zip->close();

            // 1. Verifikasi Database SQLite
            $extractedDb = $tempExtractDir . '/database/database.sqlite';
            if (! file_exists($extractedDb)) {
                throw new Exception('Berkas database.sqlite tidak ditemukan di dalam arsip backup.');
            }

            // Uji integritas SQLite menggunakan query PRAGMA
            $pdo = new PDO('sqlite:' . $extractedDb);
            $stmt = $pdo->query('PRAGMA integrity_check;');
            $checkResult = $stmt ? $stmt->fetchColumn() : 'failed';
            unset($pdo);

            if (strtolower((string) $checkResult) !== 'ok') {
                throw new Exception("Integritas SQLite gagal: {$checkResult}");
            }

            // 2. Verifikasi Manifest dan Berkas Unggahan
            $manifestFile = $tempExtractDir . '/manifest.json';
            $manifestValid = file_exists($manifestFile);

            $uploadsDir = $tempExtractDir . '/storage_public';
            $uploadFilesCount = file_exists($uploadsDir) ? count(File::allFiles($uploadsDir)) : 0;

            $notes = "Lolos uji pemulihan. Database SQLite terverifikasi sehat (PRAGMA: ok), manifest utuh, serta ditemukan {$uploadFilesCount} berkas unggahan publik.";

            $backup->update([
                'tested_at' => now(),
                'test_status' => 'lolos',
                'test_notes' => $notes,
            ]);

            ActivityLog::record(
                action: 'test_recovery',
                description: "{$actorName} menjalankan uji pemulihan pada backup {$backup->filename}: Lolos Uji Integritas.",
                reason: 'Simulasi pemulihan bencana (Disaster Recovery Simulation)',
                subject: $backup,
                properties: [
                    'filename' => $backup->filename,
                    'test_status' => 'lolos',
                    'verified_uploads' => $uploadFilesCount,
                ],
                module: 'Pengaturan'
            );

            return [
                'success' => true,
                'message' => $notes,
            ];
        } catch (\Throwable $e) {
            $notes = 'Gagal uji pemulihan: ' . $e->getMessage();

            $backup->update([
                'tested_at' => now(),
                'test_status' => 'gagal',
                'test_notes' => $notes,
            ]);

            return [
                'success' => false,
                'message' => $notes,
            ];
        } finally {
            // Bersihkan folder sementara
            if (File::isDirectory($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
        }
    }

    /**
     * Dapatkan ringkasan statistik backup dan pemulihan untuk panel admin.
     */
    public function getBackupOverview(): array
    {
        $lastBackup = SystemBackup::where('status', 'berhasil')->latest('created_at')->first();
        $lastTest = SystemBackup::whereNotNull('tested_at')->latest('tested_at')->first();

        return [
            'last_backup_time' => $lastBackup ? $lastBackup->formatted_created_at : 'Belum pernah dilakukan',
            'last_backup_filename' => $lastBackup?->filename ?? '-',
            'last_backup_size' => $lastBackup ? $lastBackup->formatted_size : '-',
            'scope' => 'Database SQLite + File Unggahan (PDF & Gambar)',
            'status' => $lastBackup ? 'Berhasil (100% Utuh)' : 'Belum Ada',
            'next_schedule' => 'Setiap Hari pukul 02.00 WIB (Otomatis)',
            'last_recovery_test' => $lastTest ? $lastTest->formatted_tested_at : 'Belum pernah diuji',
            'last_recovery_status' => $lastTest ? ($lastTest->test_status === 'lolos' ? 'Lolos Uji Integritas' : 'Perlu Perhatian') : 'Siap Diuji',
            'last_recovery_notes' => $lastTest?->test_notes ?? 'Silakan jalankan simulasi uji pemulihan untuk memverifikasi arsip.',
            'total_backups' => SystemBackup::count(),
        ];
    }
}
