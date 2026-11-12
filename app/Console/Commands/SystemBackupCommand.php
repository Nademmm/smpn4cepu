<?php

namespace App\Console\Commands;

use App\Services\BackupRecoveryService;
use Illuminate\Console\Command;

class SystemBackupCommand extends Command
{
    protected $signature = 'system:backup';

    protected $description = 'Jalankan backup lengkap sistem (Database SQLite dan seluruh berkas unggahan publik)';

    public function handle(BackupRecoveryService $service): int
    {
        $this->info('Memulai proses backup sistem lengkap...');

        try {
            $backup = $service->createBackup();
            $this->info("Backup berhasil dibuat: {$backup->filename} ({$backup->formatted_size})");
            $this->line("Cakupan: {$backup->scope}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal membuat backup: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
