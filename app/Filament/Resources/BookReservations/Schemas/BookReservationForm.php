<?php

namespace App\Filament\Resources\BookReservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reservation_code')
                    ->label('Kode Tiket Reservasi / Barcode')
                    ->disabled()
                    ->required(),

                Select::make('book_id')
                    ->label('Buku yang Dipinjam')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('borrower_name')
                    ->label('Nama Lengkap Siswa')
                    ->required()
                    ->maxLength(100),

                TextInput::make('borrower_nisn')
                    ->label('NISN Siswa')
                    ->maxLength(20),

                TextInput::make('borrower_class')
                    ->label('Kelas / Rombel')
                    ->required()
                    ->maxLength(50),

                DatePicker::make('pickup_date')
                    ->label('Tanggal Rencana Ambil')
                    ->required(),

                DatePicker::make('due_date')
                    ->label('Tenggat Pengembalian')
                    ->helperText('Batas waktu buku harus dikembalikan ke perpustakaan (default 7 hari).'),

                Select::make('status')
                    ->label('Status Reservasi / Sirkulasi')
                    ->options([
                        'menunggu' => 'Menunggu Pengambilan',
                        'diambil' => 'Buku Telah Diambil (Sedang Dipinjam)',
                        'selesai' => 'Buku Selesai Dikembalikan',
                        'dibatalkan' => 'Reservasi Dibatalkan / Kadaluarsa',
                    ])
                    ->default('menunggu')
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan Staf Perpustakaan')
                    ->placeholder('Kondisi buku saat dipinjam, nomor seri fisik, catatan denda, dsb...')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
