<?php

namespace App\Console\Commands;

use App\Models\SystemBackup;
use App\Services\BackupRecoveryService;
use Illuminate\Console\Command;

class SystemRestoreCommand extends Command
{
    protected $signature = 'system:restore
        {backup? : ID numerik atau nama berkas ZIP backup}
        {--latest : Gunakan arsip backup berhasil terbaru secara otomatis}
        {--force : Jalankan pemulihan tanpa prompt konfirmasi interaktif}';

    protected $description = 'Pulihkan database SQLite dan file unggahan publik dari arsip backup terverifikasi';

    public function handle(BackupRecoveryService $service): int
    {
        $backupArg = $this->argument('backup');

        if ($this->option('latest') || empty($backupArg)) {
            $backup = SystemBackup::where('status', 'berhasil')->latest('created_at')->first();
        } else {
            $backup = is_numeric($backupArg)
                ? SystemBackup::find((int) $backupArg)
                : SystemBackup::where('filename', $backupArg)->first();
        }

        if (! $backup) {
            $this->error('Arsip backup yang dimaksud tidak ditemukan.');
            return self::FAILURE;
        }

        $this->warn("Akan memulihkan sistem dari: {$backup->filename} ({$backup->formatted_created_at}, {$backup->formatted_size})");

        if (! $this->option('force') && ! $this->confirm('Database dan berkas unggahan saat ini akan ditimpa. Lanjutkan pemulihan?', false)) {
            $this->info('Pemulihan dibatalkan.');
            return self::SUCCESS;
        }

        try {
            $result = $service->restoreBackup($backup);
            $this->info($result['message']);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal memulihkan sistem: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

