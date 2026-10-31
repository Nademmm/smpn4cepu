<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Enums\BookCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Section Informasi Utama Buku
                Section::make('Informasi Bibliografi Buku')
                    ->description('Kelola data identitas buku paket kurikulum merdeka, literasi, atau karya sekolah secara mandiri.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Lengkap Buku')
                                    ->placeholder('Masukkan judul buku...')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, ?string $operation) {
                                        if ($operation === 'create' && !empty($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label('Slug URL')
                                    ->placeholder('otomatis-dibuat-dari-judul')
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('author')
                                    ->label('Penulis / Pengarang')
                                    ->placeholder('Contoh: Tim Kemendikbudristek / Andrea Hirata')
                                    ->required(),

                                TextInput::make('publisher')
                                    ->label('Penerbit')
                                    ->placeholder('Contoh: Pusat Perbukuan / Balai Pustaka'),

                                Select::make('category')
                                    ->label('Kategori Koleksi')
                                    ->options(BookCategory::options())
                                    ->default(BookCategory::KELAS_7->value)
                                    ->required()
                                    ->searchable(),

                                TextInput::make('published_year')
                                    ->label('Tahun Terbit')
                                    ->numeric()
                                    ->placeholder('Contoh: 2024')
                                    ->default((int) date('Y')),

                                TextInput::make('isbn')
                                    ->label('Nomor ISBN / Barcode')
                                    ->placeholder('Contoh: 978-602-xxx-xxx-x')
                                    ->maxLength(30),

                                TextInput::make('page_count')
                                    ->label('Jumlah Halaman')
                                    ->numeric()
                                    ->placeholder('Contoh: 240'),

                                TextInput::make('language')
                                    ->label('Bahasa')
                                    ->default('id')
                                    ->placeholder('id / en'),

                                Textarea::make('description')
                                    ->label('Sinopsis / Deskripsi Buku')
                                    ->rows(4)
                                    ->placeholder('Tuliskan ringkasan isi buku atau ulasan materi di sini...')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // 2. Section Sampul & Berkas E-Book Digital
                Section::make('Sampul & Berkas Digital (E-Book)')
                    ->description('Unggah sampul dan berkas PDF buku langsung ke server sekolah melalui form admin.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('cover_path')
                                    ->label('Upload File Sampul Buku')
                                    ->image()
                                    ->directory('books/covers')
                                    ->disk('public')
                                    ->maxSize(5120)
                                    ->openable()
                                    ->downloadable()
                                    ->helperText('Format JPG, PNG, atau WebP (maks 5MB). Disarankan rasio vertikal 2:3 atau 3:4.'),

                                TextInput::make('cover_url')
                                    ->label('Atau Masukkan Tautan Cover Gambar (Opsional)')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->helperText('Gunakan field ini jika gambar sampul di-host pada penyimpanan cloud terpisah.'),

                                FileUpload::make('pdf_path')
                                    ->label('Upload Berkas E-Book PDF')
                                    ->directory('books/ebooks')
                                    ->disk('public')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(51200)
                                    ->openable()
                                    ->downloadable()
                                    ->helperText('Unggah berkas PDF materi/buku (maks 50MB) untuk dapat dibaca langsung oleh siswa di web.'),

                                TextInput::make('pdf_url')
                                    ->label('Atau Masukkan Tautan E-Book PDF (Opsional)')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->helperText('Gunakan field ini jika berkas PDF disimpan pada cloud drive atau tautan luar.'),
                            ]),
                    ]),

                // 3. Section Status & Ketersediaan Fisik
                Section::make('Status Sirkulasi & Ketersediaan')
                    ->description('Atur ketersediaan buku fisik di ruang perpustakaan sekolah dan status tayang.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('physical_stock')
                                    ->label('Stok Buku Fisik di Ruang Perpus')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Jumlah eksemplar fisik yang tersedia di rak perpustakaan SMPN 4 Cepu.')
                                    ->required(),

                                Toggle::make('is_featured')
                                    ->label('Buku Pilihan / Rekomendasi')
                                    ->helperText('Tampilkan sebagai koleksi unggulan di halaman depan perpustakaan digital.')
                                    ->default(false),

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->helperText('Aktifkan agar buku ini dapat dicari dan dibaca oleh warga sekolah.')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
