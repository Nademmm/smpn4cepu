<?php

namespace App\Filament\Resources\SchoolDocuments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SchoolDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Dokumen')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('category')
                    ->label('Kategori Dokumen')
                    ->options([
                        'Akademik' => 'Akademik',
                        'Kesiswaan' => 'Kesiswaan',
                        'Formulir' => 'Formulir & Administrasi',
                        'Perpustakaan' => 'Perpustakaan',
                        'Tata Tertib' => 'Tata Tertib & Kode Etik',
                        'Umum' => 'Umum',
                    ])
                    ->default('Umum')
                    ->required(),

                TextInput::make('display_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi Singkat Dokumen')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('file_path')
                    ->label('Berkas Dokumen (PDF / DOCX)')
                    ->disk('public')
                    ->directory('documents')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(15360) // Maksimal 15 MB
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('file_size')
                    ->label('Ukuran File (Contoh: 1.2 MB)')
                    ->placeholder('Misal: 1.2 MB')
                    ->maxLength(50),

                TextInput::make('file_extension')
                    ->label('Ekstensi File (Contoh: pdf)')
                    ->default('pdf')
                    ->maxLength(10),

                Toggle::make('is_active')
                    ->label('Aktif dan Tampil di Web')
                    ->default(true),
            ]);
    }
}
