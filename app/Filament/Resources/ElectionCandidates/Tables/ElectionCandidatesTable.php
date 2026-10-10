<?php

namespace App\Filament\Resources\ElectionCandidates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ElectionCandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate_number')
                    ->label('No. Urut')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                ImageColumn::make('photo_path')
                    ->label('Foto Paslon')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => $record->photo_url),
                TextColumn::make('candidate_name')
                    ->label('Calon Ketua OSIS')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('vice_candidate_name')
                    ->label('Calon Wakil Ketua OSIS')
                    ->searchable(),
                TextColumn::make('total_votes_cached')
                    ->label('Total Suara')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('candidate_number', 'asc')
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

