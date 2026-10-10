<?php

namespace App\Filament\Resources\StudentStatistics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentStatisticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->searchable(),
                TextColumn::make('grade_level')
                    ->label('Tingkat')
                    ->formatStateUsing(fn ($state) => 'Kelas ' . $state)
                    ->sortable(),
                TextColumn::make('class_name')
                    ->label('Rombel')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('male_count')
                    ->label('Laki-laki')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('female_count')
                    ->label('Perempuan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_count')
                    ->label('Total Siswa')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('academic_year', 'desc')
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

