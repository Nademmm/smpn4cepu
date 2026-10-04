<?php

namespace App\Filament\Resources\StudentStatistics\Tables;

use Filament\Actions\BulkActionGroup;
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
                    ->searchable(),
                TextColumn::make('grade_level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('class_name')
                    ->searchable(),
                TextColumn::make('male_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('female_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
