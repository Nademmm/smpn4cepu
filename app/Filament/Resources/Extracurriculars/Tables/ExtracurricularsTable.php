<?php

namespace App\Filament\Resources\Extracurriculars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExtracurricularsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->getStateUsing(fn ($record) => $record->photo_url),
                TextColumn::make('name')
                    ->label('Nama Ekstrakurikuler')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Wajib' => 'danger',
                        'Olahraga' => 'warning',
                        'Kesenian' => 'info',
                        'Kemanusiaan' => 'danger',
                        'Keagamaan' => 'success',
                        'Sains & Teknologi' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('leader_name')
                    ->label('Pembina / Pelatih')
                    ->searchable(),
                TextColumn::make('schedule_day')
                    ->label('Jadwal'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('display_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('display_order', 'asc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options([
                        'Wajib' => 'Wajib',
                        'Olahraga' => 'Olahraga',
                        'Kesenian' => 'Kesenian',
                        'Kemanusiaan' => 'Kemanusiaan',
                        'Keagamaan' => 'Keagamaan',
                        'Sains & Teknologi' => 'Sains & Teknologi',
                    ]),
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
