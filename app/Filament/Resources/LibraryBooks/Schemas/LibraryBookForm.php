<?php

namespace App\Filament\Resources\LibraryBooks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LibraryBookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Lengkap Buku')
                    ->required()
                    ->placeholder('Masukkan judul buku...')
                    ->columnSpanFull(),

                TextInput::make('isbn')
                    ->label('Nomor ISBN / Barcode')
                    ->placeholder('978-602-xxx-xxx-x'),

                TextInput::make('author')
                    ->label('Pengarang / Penulis')
                    ->required()
                    ->placeholder('Nama penulis atau tim penyusun'),

                TextInput::make('publisher')
                    ->label('Penerbit')
                    ->required()
                    ->placeholder('Nama penerbit resmi'),

                Select::make('category')
                    ->label('Kategori Koleksi')
                    ->options([
                        'Buku Pelajaran' => 'Buku Pelajaran',
                        'Sains & Teknologi' => 'Sains & Teknologi',
                        'Teknologi' => 'Teknologi',
                        'Fiksi & Sastra' => 'Fiksi & Sastra',
                        'Sejarah & Sosial' => 'Sejarah & Sosial',
                        'Bahasa & Kamus' => 'Bahasa & Kamus',
                        'Agama & Budi Pekerti' => 'Agama & Budi Pekerti',
                        'Ensiklopedia & Referensi' => 'Ensiklopedia & Referensi',
                        'Karya Ilmiah & Majalah' => 'Karya Ilmiah & Majalah',
                    ])
                    ->searchable()
                    ->required(),

                TextInput::make('publication_year')
                    ->label('Tahun Terbit')
                    ->required()
                    ->numeric()
                    ->default((int) date('Y')),

                TextInput::make('page_count')
                    ->label('Jumlah Halaman')
                    ->numeric()
                    ->placeholder('Misal: 240'),

                TextInput::make('language')
                    ->label('Bahasa')
                    ->default('Bahasa Indonesia'),

                TextInput::make('shelf_location')
                    ->label('Lokasi Rak Fisik')
                    ->required()
                    ->placeholder('Contoh: Rak B-02 / Lantai 1'),

                TextInput::make('call_number')
                    ->label('Nomor Panggil (Call Number / DDC)')
                    ->placeholder('Contoh: 500 IRY i'),

                TextInput::make('total_stock')
                    ->label('Total Eksemplar Fisik')
                    ->required()
                    ->numeric()
                    ->default(1),

                TextInput::make('available_stock')
                    ->label('Eksemplar Siap Pinjam')
                    ->required()
                    ->numeric()
                    ->default(1),

                FileUpload::make('cover_image')
                    ->label('Foto Sampul Buku')
                    ->image()
                    ->directory('library/covers')
                    ->disk('public'),

                FileUpload::make('digital_file_path')
                    ->label('Berkas E-Book Digital (Opsional PDF)')
                    ->directory('library/ebooks')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf']),

                Textarea::make('synopsis')
                    ->label('Sinopsis / Ringkasan Buku')
                    ->rows(4)
                    ->placeholder('Tuliskan ringkasan cerita atau ulasan materi buku di sini...')
                    ->columnSpanFull(),
            ]);
    }
}
