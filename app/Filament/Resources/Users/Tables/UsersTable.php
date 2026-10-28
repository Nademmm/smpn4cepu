<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('student_class')
                    ->label('Kelas / Rombel')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('gender')
                    ->label('L/P')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'L' => 'info',
                        'P' => 'success',
                        default => 'gray',
                    })
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('roles.name')
                    ->label('Peran / Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'guru' => 'success',
                        'siswa' => 'primary',
                        'staf_perpus' => 'warning',
                        'panitia_pilketos' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('roles')
                    ->label('Filter Peran / Role')
                    ->relationship('roles', 'name')
                    ->preload(),

                SelectFilter::make('student_class')
                    ->label('Filter Kelas / Rombel')
                    ->options([
                        '7A' => 'Kelas 7A', '7B' => 'Kelas 7B', '7C' => 'Kelas 7C', '7D' => 'Kelas 7D', '7E' => 'Kelas 7E', '7F' => 'Kelas 7F', '7G' => 'Kelas 7G',
                        '8A' => 'Kelas 8A', '8B' => 'Kelas 8B', '8C' => 'Kelas 8C', '8D' => 'Kelas 8D', '8E' => 'Kelas 8E', '8F' => 'Kelas 8F', '8G' => 'Kelas 8G',
                        '9A' => 'Kelas 9A', '9B' => 'Kelas 9B', '9C' => 'Kelas 9C', '9D' => 'Kelas 9D', '9E' => 'Kelas 9E', '9F' => 'Kelas 9F',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn ($record): bool => (int) $record->id === (int) auth()->id()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
