<?php

namespace App\Filament\Resources\SystemBackups\Tables;

use App\Models\SystemBackup;
use App\Models\User;
use App\Services\BackupRecoveryService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemBackupsTable
{
    public static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('filename')
                    ->label('Nama Berkas Backup (.zip)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-archive-box')
                    ->copyable()
                    ->copyMessage('Nama berkas berhasil disalin!'),

                TextColumn::make('formatted_size')
                    ->label('Ukuran Arsip')
                    ->badge()
                    ->color('info')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('size_bytes', $direction)),

                TextColumn::make('scope')
                    ->label('Cakupan Backup')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('status')
                    ->label('Status Backup')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'berhasil' => 'Berhasil (Utuh)',
                        'gagal' => 'Gagal',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'berhasil' => 'success',
                        default => 'danger',
                    }),

                TextColumn::make('formatted_created_at')
                    ->label('Waktu Pembuatan')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('created_at', $direction)),

                TextColumn::make('test_status')
                    ->label('Hasil Uji Pemulihan')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'lolos' => 'Lolos Uji Integritas',
                        'gagal' => 'Gagal Uji',
                        default => 'Belum Diuji',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'lolos' => 'success',
                        'gagal' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('test_notes')
                    ->label('Catatan Hasil Pengujian')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Unduh ZIP')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (SystemBackup $record): ?StreamedResponse {
                        if (! $record->fileExists()) {
                            Notification::make()
                                ->title('Berkas Fisik Tidak Ditemukan')
                                ->body("Berkas {$record->filename} tidak ditemukan pada storage server.")
                                ->danger()
                                ->send();
                            return null;
                        }

                        /** @var FilesystemAdapter $storage */
                        $storage = Storage::disk($record->disk);

                        return $storage->download($record->filepath, $record->filename);
                    }),

                Action::make('testRecovery')
                    ->label('Uji Pemulihan')
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Jalankan Simulasi Uji Pemulihan?')
                    ->modalDescription('Sistem akan mengekstrak arsip ke folder isolasi sementara, memverifikasi integritas database SQLite via PRAGMA, serta memeriksa kelengkapan berkas unggahan publik tanpa mengganggu data aktif.')
                    ->modalSubmitActionLabel('Mulai Uji Integritas')
                    ->action(function (SystemBackup $record, BackupRecoveryService $service): void {
                        $result = $service->testRecovery($record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Uji Pemulihan Berhasil')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Uji Pemulihan Gagal')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),

                DeleteAction::make()
                    ->label('Hapus')
                    ->before(function (SystemBackup $record): void {
                        if ($record->fileExists()) {
                            Storage::disk($record->disk)->delete($record->filepath);
                        }
                    }),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->before(function ($records): void {
                        foreach ($records as $record) {
                            if ($record->fileExists()) {
                                Storage::disk($record->disk)->delete($record->filepath);
                            }
                        }
                    }),
            ]);
    }
}

