<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Siswa Baru')
                ->icon('heroicon-o-user-plus'),

            Action::make('syncStatistics')
                ->label('Sinkronkan Rekap Rombel')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function (): void {
                    $synced = User::syncAllRombelStatistics('2026/2027');

                    Notification::make()
                        ->title("Berhasil menyinkronkan statistik {$synced} rombel dari data siswa aktif")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Siswa')
                ->badge(fn (): int => User::whereNotNull('nisn')->where('student_class', 'not like', 'Kelas %')->count()),

            'grade_7' => Tab::make('Kelas VII (7A-7G)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('student_class', 'like', '7%'))
                ->badge(fn (): int => User::where('student_class', 'like', '7%')->count()),

            'grade_8' => Tab::make('Kelas VIII (8A-8G)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('student_class', 'like', '8%'))
                ->badge(fn (): int => User::where('student_class', 'like', '8%')->count()),

            'grade_9' => Tab::make('Kelas IX (9A-9F)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('student_class', 'like', '9%'))
                ->badge(fn (): int => User::where('student_class', 'like', '9%')->count()),

            'male' => Tab::make('Putra (L)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', 'L'))
                ->badge(fn (): int => User::whereNotNull('nisn')->where('student_class', 'not like', 'Kelas %')->where('gender', 'L')->count()),

            'female' => Tab::make('Putri (P)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', 'P'))
                ->badge(fn (): int => User::whereNotNull('nisn')->where('student_class', 'not like', 'Kelas %')->where('gender', 'P')->count()),
        ];
    }
}
