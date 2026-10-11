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
                    ->label('Kode Tiket Reservasi')
                    ->disabled()
                    ->required(),

                Select::make('book_id')
                    ->label('Buku yang Dipesan')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('borrower_name')
                    ->label('Nama Lengkap Siswa')
                    ->required()
                    ->maxLength(100),

                TextInput::make('borrower_class')
                    ->label('Kelas / Rombel')
                    ->required()
                    ->maxLength(50),

                DatePicker::make('pickup_date')
                    ->label('Tanggal Rencana Ambil')
                    ->required(),

                Select::make('status')
                    ->label('Status Reservasi / Sirkulasi')
                    ->options([
                        'menunggu' => 'Menunggu Pengambilan',
                        'diambil' => 'Buku Telah Diambil (Dipinjam)',
                        'selesai' => 'Buku Selesai Dikembalikan',
                        'dibatalkan' => 'Reservasi Dibatalkan / Kadaluarsa',
                    ])
                    ->default('menunggu')
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan Staf Perpustakaan')
                    ->placeholder('Kondisi buku, catatan denda, atau tanggal pengembalian fisik...')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
