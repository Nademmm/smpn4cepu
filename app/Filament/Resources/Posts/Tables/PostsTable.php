<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('Foto Sampul')
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => $record->featured_image_url),
                TextColumn::make('title')
                    ->label('Judul Berita')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->searchable(),
                TextColumn::make('author.name')
                    ->label('Penulis')
                    ->searchable(),
                TextColumn::make('event_date')
                    ->label('Tgl Agenda')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_published')
                    ->label('Tayang')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Waktu Publikasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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

