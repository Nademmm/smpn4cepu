<?php

namespace App\Filament\Resources\StaffMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StaffMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => $record->photo_url),
                TextColumn::make('name')
                    ->label('Nama Pendidik / Tendik')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('nip')
                    ->label('NIP / Identitas')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('display_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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

