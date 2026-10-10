<?php

namespace App\Filament\Resources\SchoolFacilities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SchoolFacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Fasilitas / Sarana')
                    ->placeholder('Contoh: Laboratorium Komputer Multimedia')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi Sarana & Spesifikasi')
                    ->placeholder('Jelaskan kegunaan dan kelengkapan fasilitas ini...')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('photo_path')
                    ->label('Dokumentasi Foto Fasilitas')
                    ->image()
                    ->imageEditor()
                    ->directory('facilities')
                    ->disk('public')
                    ->maxSize(5120)
                    ->columnSpanFull(),

                TextInput::make('display_order')
                    ->label('Nomor Urut Tampilan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Angka urutan tampil di halaman fasilitas'),
            ]);
    }
}
