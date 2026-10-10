<?php

namespace App\Filament\Resources\SchoolFacilities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolFacilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto Sarana')
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => $record->photo_url),
                TextColumn::make('name')
                    ->label('Nama Fasilitas')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('display_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('display_order', 'asc')
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

