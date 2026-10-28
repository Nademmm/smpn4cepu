<?php

namespace App\Filament\Resources\Students\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        $classOptions = [
            '7A' => 'Kelas 7A', '7B' => 'Kelas 7B', '7C' => 'Kelas 7C', '7D' => 'Kelas 7D', '7E' => 'Kelas 7E', '7F' => 'Kelas 7F', '7G' => 'Kelas 7G',
            '8A' => 'Kelas 8A', '8B' => 'Kelas 8B', '8C' => 'Kelas 8C', '8D' => 'Kelas 8D', '8E' => 'Kelas 8E', '8F' => 'Kelas 8F', '8G' => 'Kelas 8G',
            '9A' => 'Kelas 9A', '9B' => 'Kelas 9B', '9C' => 'Kelas 9C', '9D' => 'Kelas 9D', '9E' => 'Kelas 9E', '9F' => 'Kelas 9F',
        ];

        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('name')
                    ->label('Nama Lengkap Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('gender')
                    ->label('L/P')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'L' => 'Laki-Laki (L)',
                        'P' => 'Perempuan (P)',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'L' => 'info',
                        'P' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('student_class')
                    ->label('Rombel')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('birth_place')
                    ->label('Tempat, Tgl Lahir')
                    ->searchable()
                    ->formatStateUsing(function ($state, User $record): string {
                        $place = $record->birth_place ?: '-';
                        $date = $record->birth_date ? $record->birth_date->translatedFormat('d M Y') : '';
                        return $date ? "{$place}, {$date}" : $place;
                    }),

                TextColumn::make('quiz_attempts_count')
                    ->label('Kuis Dikerjakan')
                    ->badge()
                    ->color(fn ($state): string => ((int) $state) > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state): string => ((int) $state) . ' Kuis')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email Login')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('student_class', 'asc')
            ->filters([
                SelectFilter::make('student_class')
                    ->label('Filter Rombel Kelas')
                    ->options($classOptions)
                    ->searchable(),

                SelectFilter::make('gender')
                    ->label('Filter Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-Laki (Putra)',
                        'P' => 'Perempuan (Putri)',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit'),

                Action::make('resetPassword')
                    ->label('Reset Sandi')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Kata Sandi Siswa?')
                    ->modalDescription(fn (User $record): string => "Kata sandi untuk {$record->name} (NISN: {$record->nisn}) akan dikembalikan ke default: password123")
                    ->action(function (User $record): void {
                        $record->update([
                            'password' => Hash::make('password123', ['rounds' => 10]),
                        ]);

                        Notification::make()
                            ->title("Sandi {$record->name} berhasil direset ke: password123")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('moveClass')
                        ->label('Pindah Kelas / Rombel Massal')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('primary')
                        ->form([
                            Select::make('target_class')
                                ->label('Pilih Kelas / Rombel Tujuan')
                                ->options($classOptions)
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $targetClass = $data['target_class'];
                            $affectedClasses = [$targetClass];

                            foreach ($records as $student) {
                                if ($student->student_class) {
                                    $affectedClasses[] = $student->student_class;
                                }
                                $student->update(['student_class' => $targetClass]);
                            }

                            foreach (array_unique($affectedClasses) as $cls) {
                                User::syncSingleRombelStatistic((string) $cls);
                            }

                            Notification::make()
                                ->title($records->count() . " siswa berhasil dipindahkan ke Kelas {$targetClass}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulkResetPassword')
                        ->label('Reset Password ke Default (password123)')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $hashed = Hash::make('password123', ['rounds' => 10]);
                            User::whereIn('id', $records->pluck('id'))->update(['password' => $hashed]);

                            Notification::make()
                                ->title($records->count() . ' akun siswa berhasil direset sandinya ke: password123')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
