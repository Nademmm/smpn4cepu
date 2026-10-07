<?php

namespace App\Filament\Resources\LibraryBooks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LibraryBooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->disk('public'),
                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->description(fn ($record) => $record->author . ' · ' . $record->publisher)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->searchable(),
                TextColumn::make('shelf_location')
                    ->label('Lokasi Rak')
                    ->description(fn ($record) => $record->call_number ? 'No. Panggil: ' . $record->call_number : null)
                    ->searchable(),
                TextColumn::make('available_stock')
                    ->label('Stok Pinjam')
                    ->formatStateUsing(fn ($record) => $record->available_stock . ' / ' . $record->total_stock . ' Eks')
                    ->badge()
                    ->color(fn ($record) => $record->available_stock > 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('publication_year')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
