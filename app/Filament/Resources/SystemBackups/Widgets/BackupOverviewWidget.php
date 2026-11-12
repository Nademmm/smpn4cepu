<?php

namespace App\Filament\Resources\SystemBackups\Widgets;

use App\Services\BackupRecoveryService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BackupOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        /** @var BackupRecoveryService $service */
        $service = app(BackupRecoveryService::class);
        $overview = $service->getBackupOverview();

        return [
            Stat::make('Waktu Backup Terakhir', $overview['last_backup_time'])
                ->description('Ukuran: ' . $overview['last_backup_size'])
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),

            Stat::make('Cakupan Backup', 'Database + File Unggahan')
                ->description('SQLite, PDF Materi, & Foto')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Status Backup', $overview['status'])
                ->description('Arsip utuh dan terlindungi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Jadwal Berikutnya', 'Pukul 02.00 WIB')
                ->description('Otomatis harian')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Uji Pemulihan Terakhir', $overview['last_recovery_status'])
                ->description($overview['last_recovery_test'])
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('primary'),
        ];
    }
}
