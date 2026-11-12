<?php

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\ActivityLog;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearOldLogs')
                ->label('Bersihkan Log Lama (>90 Hari)')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Bersihkan Log Riwayat Lama?')
                ->modalDescription('Seluruh catatan aktivitas yang berumur lebih dari 90 hari akan dihapus permanen.')
                ->action(function (): void {
                    $deleted = ActivityLog::where('created_at', '<', now()->subDays(90))->delete();

                    Notification::make()
                        ->title("Berhasil menghapus {$deleted} baris log lama")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Log')
                ->badge(fn (): int => ActivityLog::count()),

            'kesiswaan' => Tab::make('Kesiswaan & Siswa')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('module', 'Kesiswaan'))
                ->badge(fn (): int => ActivityLog::where('module', 'Kesiswaan')->count()),

            'tahun_ajaran' => Tab::make('Tahun Ajaran')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('module', 'Tahun Ajaran'))
                ->badge(fn (): int => ActivityLog::where('module', 'Tahun Ajaran')->count()),

            'operator' => Tab::make('Aktivitas Operator')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('actor_role', 'operator_sekolah'))
                ->badge(fn (): int => ActivityLog::where('actor_role', 'operator_sekolah')->count()),
        ];
    }
}
