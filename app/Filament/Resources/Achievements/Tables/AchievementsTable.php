<?php

namespace App\Filament\Resources\Achievements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AchievementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto Piala')
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => $record->photo_url),
                TextColumn::make('title')
                    ->label('Nama Prestasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('rank')
                    ->label('Peringkat')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Nasional', 'Internasional' => 'danger',
                        'Provinsi' => 'success',
                        'Karesidenan' => 'info',
                        default => 'primary',
                    }),
                TextColumn::make('winner_name')
                    ->label('Peraih / Tim')
                    ->searchable(),
                TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('display_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('event_date', 'desc')
            ->filters([
                SelectFilter::make('level')
                    ->label('Tingkat')
                    ->options([
                        'Kecamatan' => 'Kecamatan',
                        'Kabupaten' => 'Kabupaten',
                        'Karesidenan' => 'Karesidenan',
                        'Provinsi' => 'Provinsi',
                        'Nasional' => 'Nasional',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Akademik' => 'Akademik',
                        'Olahraga' => 'Olahraga',
                        'Kesenian' => 'Kesenian',
                        'Lingkungan Hidup' => 'Lingkungan Hidup',
                        'Keagamaan' => 'Keagamaan',
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
