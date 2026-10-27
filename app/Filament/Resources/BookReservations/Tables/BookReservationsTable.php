<?php

namespace App\Filament\Resources\BookReservations\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation_code')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('borrower_name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('borrower_class')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('book.title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->limit(35)
                    ->wrap(),

                TextColumn::make('pickup_date')
                    ->label('Rencana Ambil')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'diambil' => 'success',
                        'selesai' => 'gray',
                        'dibatalkan' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'menunggu' => 'Menunggu',
                        'diambil' => 'Sedang Dipinjam',
                        'selesai' => 'Selesai Kembali',
                        'dibatalkan' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('markAsTaken')
                    ->label('Buku Diambil')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'menunggu')
                    ->action(function ($record) {
                        if ($record->status === 'menunggu') {
                            $record->update(['status' => 'diambil']);
                        }
                    }),

                Action::make('markAsReturned')
                    ->label('Buku Kembali')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn ($record) => $record->status === 'diambil')
                    ->action(function ($record) {
                        if ($record->status === 'diambil') {
                            $record->update(['status' => 'selesai']);
                        }
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
