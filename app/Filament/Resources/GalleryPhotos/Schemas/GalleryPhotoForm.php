<?php

namespace App\Filament\Resources\GalleryPhotos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Dokumentasi / Foto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('tag')
                    ->label('Kategori / Tag Kegiatan')
                    ->options([
                        'Lingkungan Sekolah' => 'Lingkungan Sekolah & Fasilitas',
                        'Kesiswaan' => 'Kesiswaan & Ekstrakurikuler',
                        'Pembelajaran' => 'Pembelajaran di Kelas / Laboratorium',
                        'Warga Sekolah' => 'Guru, Karyawan & Warga Sekolah',
                        'Agenda' => 'Agenda, Upacara & Lomba',
                    ])
                    ->default('Kegiatan')
                    ->required(),

                TextInput::make('display_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Textarea::make('caption')
                    ->label('Keterangan / Cerita Foto')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('photo_path')
                    ->label('Berkas Foto')
                    ->disk('public')
                    ->directory('gallery')
                    ->image()
                    ->imageEditor()
                    ->maxSize(10240) // 10 MB
                    ->required()
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Tampilkan di Halaman Galeri Publik')
                    ->default(true),
            ]);
    }
}
