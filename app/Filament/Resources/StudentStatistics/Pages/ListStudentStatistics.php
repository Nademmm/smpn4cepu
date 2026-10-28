<?php

namespace App\Filament\Resources\StudentStatistics\Pages;

use App\Filament\Resources\StudentStatistics\StudentStatisticResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListStudentStatistics extends ListRecords
{
    protected static string $resource = StudentStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncFromStudents')
                ->label('Hitung Ulang Otomatis dari Data Siswa')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('info')
                ->action(function (): void {
                    User::syncAllRombelStatistics('2026/2027');

                    Notification::make()
                        ->title('Statistik Rombel Berhasil Disinkronkan')
                        ->body('Seluruh jumlah siswa Putra (L), Putri (P), dan Total per Rombel telah dihitung ulang dari Data Peserta Didik aktif.')
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Tambah Rombel Manual'),
        ];
    }
}
