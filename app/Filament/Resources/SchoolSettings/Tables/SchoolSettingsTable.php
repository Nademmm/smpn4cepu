<?php

namespace App\Filament\Resources\SchoolSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('principal_photo_path')
                    ->label('Foto Kepsek')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->principal_photo_url),
                TextColumn::make('principal_name')
                    ->label('Nama Kepala Sekolah')
                    ->weight('bold')
                    ->description(fn ($record) => $record->principal_title),
                TextColumn::make('school_phone')
                    ->label('Telepon TU'),
                TextColumn::make('school_email')
                    ->label('Email Resmi'),
                IconColumn::make('announcement_bar_active')
                    ->label('Banner Beranda')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah Pengaturan'),
            ]);
    }
}
