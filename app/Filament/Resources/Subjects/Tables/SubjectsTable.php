<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Mapel')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('teachers.name')
                    ->label('Guru Pengampu & Rombel')
                    ->getStateUsing(function ($record): array {
                        return $record->teachers->map(function ($teacher): string {
                            $classes = \App\Models\User::parsePivotArray($teacher->pivot?->allowed_classes);
                            $grades = \App\Models\User::parsePivotArray($teacher->pivot?->allowed_grades);

                            if (! empty($classes)) {
                                return $teacher->name . ' (' . implode(', ', $classes) . ')';
                            }

                            if (! empty($grades)) {
                                return $teacher->name . ' (Kelas ' . implode(', ', $grades) . ')';
                            }

                            return $teacher->name;
                        })->all();
                    })
                    ->badge()
                    ->color('info')
                    ->placeholder('Belum diatur'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

