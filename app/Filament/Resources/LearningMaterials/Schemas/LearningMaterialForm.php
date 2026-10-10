<?php

namespace App\Filament\Resources\LearningMaterials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LearningMaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('grade_level')
                    ->label('Tingkat Kelas')
                    ->options([
                        7 => 'Kelas VII (Tujuh)',
                        8 => 'Kelas VIII (Delapan)',
                        9 => 'Kelas IX (Sembilan)',
                    ])
                    ->required(),

                TextInput::make('title')
                    ->label('Judul Materi Pembelajaran')
                    ->required()
                    ->placeholder('Contoh: Struktur dan Fungsi Tumbuhan Bagian 1')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label('Slug URL (Otomatis)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Dipergunakan untuk tautan materi pembelajaran di halaman web.')
                    ->columnSpanFull(),

                Textarea::make('summary')
                    ->label('Ringkasan Singkat Materi')
                    ->required()
                    ->rows(3)
                    ->placeholder('Rangkuman kompetensi dasar atau materi yang dipelajari...')
                    ->columnSpanFull(),

                RichEditor::make('content')
                    ->label('Isi Lengkap Materi / Panduan Belajar')
                    ->placeholder('Tuliskan materi ajar lengkap atau penjelasan bagi siswa di sini...')
                    ->columnSpanFull(),

                FileUpload::make('attachment_path')
                    ->label('Berkas Lampiran / Bahan Ajar Digital')
                    ->directory('materials')
                    ->disk('public')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip',
                    ])
                    ->helperText('Pilih berkas dari komputer (format PDF, DOCX, PPTX, XLSX, atau ZIP).')
                    ->columnSpanFull(),

                TextInput::make('external_url')
                    ->label('Tautan Video / Referensi Eksternal (Opsional)')
                    ->url()
                    ->placeholder('https://youtube.com/watch?v=... atau https://drive.google.com/...')
                    ->helperText('Masukkan link jika materi berupa video pembelajaran atau Google Drive publik.')
                    ->columnSpanFull(),

                Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]);
    }
}

