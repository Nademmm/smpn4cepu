<?php

namespace App\Filament\Resources\SystemBackups\Pages;

use App\Filament\Resources\SystemBackups\SystemBackupResource;
use App\Filament\Resources\SystemBackups\Widgets\BackupOverviewWidget;
use App\Services\BackupRecoveryService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSystemBackups extends ListRecords
{
    protected static string $resource = SystemBackupResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BackupOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Buat Backup Sekarang (Database + Unggahan)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mulai Proses Backup Sistem Lengkap?')
                ->modalDescription('Proses ini akan mengompresi database SQLite beserta seluruh berkas unggahan publik (PDF materi pelajaran, foto galeri, cover buku perpustakaan, dan berkas sekolah) ke dalam satu berkas arsip ZIP utuh.')
                ->modalSubmitActionLabel('Mulai Backup')
                ->action(function (BackupRecoveryService $service): void {
                    try {
                        $backup = $service->createBackup();

                        Notification::make()
                            ->title('Backup Sistem Berhasil')
                            ->body("Berkas backup {$backup->filename} ({$backup->formatted_size}) berhasil dibuat dan tersimpan aman.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Membuat Backup')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('recoveryGuide')
                ->label('Prosedur Pemulihan (SOP)')
                ->icon('heroicon-o-book-open')
                ->color('gray')
                ->modalHeading('SOP Prosedur Pemulihan Data Bencana (Disaster Recovery)')
                ->modalDescription('Panduan resmi langkah pemulihan sistem jika terjadi kerusakan server, kehilangan data, atau migrasi hosting:')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup Panduan')
                ->modalContent(view('filament.pages.recovery-sop-modal')),
        ];
    }
}

