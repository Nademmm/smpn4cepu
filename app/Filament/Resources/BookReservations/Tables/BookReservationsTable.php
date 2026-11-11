<?php

namespace App\Filament\Resources\BookReservations\Tables;

use App\Models\BookReservation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                    ->copyable()
                    ->description(fn (BookReservation $record): ?string => $record->borrower_nisn ? "NISN: {$record->borrower_nisn}" : null),

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

                TextColumn::make('due_date')
                    ->label('Tenggat Kembali')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'diambil', 'dipinjam' => 'info',
                        'selesai', 'dikembalikan' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'menunggu' => 'Menunggu Pengambilan',
                        'diambil', 'dipinjam' => 'Sedang Dipinjam',
                        'selesai', 'dikembalikan' => 'Selesai Kembali',
                        'dibatalkan' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // 1. Lihat Tiket & Barcode
                Action::make('viewBarcode')
                    ->label('Tiket & Barcode')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->modalHeading(fn (BookReservation $record) => "Tiket Peminjaman: {$record->reservation_code}")
                    ->modalDescription('Tunjukkan atau scan barcode di bawah ini untuk verifikasi peminjaman fisik.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (BookReservation $record) => new HtmlString(
                        '<div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">' .
                        '<div style="margin-bottom: 1rem;">' .
                        '<h3 style="font-weight: 800; color: #0f172a; font-size: 1.1rem; margin: 0 0 0.25rem 0;">' . htmlspecialchars($record->book?->title ?? 'Buku Perpustakaan', ENT_QUOTES, 'UTF-8') . '</h3>' .
                        '<p style="color: #64748b; font-size: 0.85rem; margin: 0;">Peminjam: <strong>' . htmlspecialchars($record->borrower_name, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($record->borrower_class, ENT_QUOTES, 'UTF-8') . ')</strong></p>' .
                        '</div>' .
                        '<div style="background: #ffffff; padding: 1rem; border-radius: 8px; border: 1px solid #cbd5e1; display: inline-block; margin-bottom: 1rem; max-width: 100%;">' .
                        $record->barcode_svg .
                        '</div>' .
                        '<div style="display: flex; justify-content: center; gap: 1rem; font-size: 0.8rem; color: #475569;">' .
                        '<div><strong>Tgl Ambil:</strong> ' . ($record->pickup_date ? $record->pickup_date->format('d M Y') : '-') . '</div>' .
                        '<div><strong>Tenggat:</strong> ' . ($record->due_date ? $record->due_date->format('d M Y') : '7 hari setelah diambil') . '</div>' .
                        '</div>' .
                        '</div>'
                    )),

                // 2. Aksi Konfirmasi Pengambilan (Serahkan Buku ke Siswa)
                Action::make('markAsTaken')
                    ->label('Buku Diserahkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BookReservation $record) => $record->status === 'menunggu')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penyerahan Buku')
                    ->modalDescription('Pastikan siswa telah menerima buku fisik. Status peminjaman akan diubah ke "Sedang Dipinjam" dan stok fisik buku di katalog akan otomatis dikurangi 1.')
                    ->action(function (BookReservation $record) {
                        $record->update([
                            'status' => 'diambil',
                            'borrowed_at' => now(),
                            'due_date' => $record->due_date ?? now()->addDays(7),
                        ]);

                        Notification::make()
                            ->title('Buku Berhasil Diserahkan')
                            ->body("Status tiket {$record->reservation_code} kini Sedang Dipinjam. Stok buku otomatis diperbarui.")
                            ->success()
                            ->send();
                    }),

                // 3. Aksi Terima Pengembalian Buku
                Action::make('markAsReturned')
                    ->label('Terima Pengembalian')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('gray')
                    ->visible(fn (BookReservation $record) => in_array($record->status, ['diambil', 'dipinjam'], true))
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengembalian Buku')
                    ->modalDescription('Pastikan buku telah dikembalikan dalam kondisi baik. Status akan diubah menjadi "Selesai Kembali" dan stok fisik buku akan otomatis dikembalikan.')
                    ->action(function (BookReservation $record) {
                        $record->update([
                            'status' => 'selesai',
                            'returned_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Buku Berhasil Dikembalikan')
                            ->body("Buku dari tiket {$record->reservation_code} telah dikembalikan. Stok fisik kembali bertambah.")
                            ->success()
                            ->send();
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
