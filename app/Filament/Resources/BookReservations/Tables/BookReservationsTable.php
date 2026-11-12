<?php

namespace App\Filament\Resources\BookReservations\Tables;

use App\Models\BookReservation;
use App\Services\LibraryLoanService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
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
                    ->limit(32)
                    ->wrap(),

                TextColumn::make('pickup_date')
                    ->label('Rencana Ambil')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Tenggat Kembali')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable()
                    ->description(fn (BookReservation $record): ?string => $record->is_overdue
                        ? "Terlambat {$record->days_late} hari"
                        : ($record->renew_count > 0 ? "Perpanjangan: {$record->renew_count}x" : null)
                    )
                    ->color(fn (BookReservation $record): ?string => $record->is_overdue ? 'danger' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (BookReservation $record): string => $record->status_color)
                    ->formatStateUsing(fn (BookReservation $record): string => $record->status_label)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // 1. Lihat Tiket & QR Code
                Action::make('viewQrCode')
                    ->label('Tiket & QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->modalHeading(fn (BookReservation $record) => "Tiket Sirkulasi: {$record->reservation_code}")
                    ->modalDescription('Pindai QR Code di bawah ini menggunakan kamera sirkulasi perpustakaan.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (BookReservation $record) => new HtmlString(
                        '<div style="text-align: center; padding: 1.25rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">' .
                        '<div style="margin-bottom: 0.75rem;">' .
                        '<h3 style="font-weight: 800; color: #0f172a; font-size: 1.1rem; margin: 0 0 0.25rem 0;">' . htmlspecialchars($record->book?->title ?? 'Buku Perpustakaan', ENT_QUOTES, 'UTF-8') . '</h3>' .
                        '<p style="color: #64748b; font-size: 0.85rem; margin: 0;">Peminjam: <strong>' . htmlspecialchars($record->borrower_name, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($record->borrower_class, ENT_QUOTES, 'UTF-8') . ')</strong></p>' .
                        '</div>' .
                        '<div style="background: #ffffff; padding: 1rem; border-radius: 12px; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; width: 220px; height: 220px;">' .
                        $record->qr_svg .
                        '</div>' .
                        '<div style="font-family: monospace; font-weight: 800; font-size: 1.1rem; color: #1e293b; letter-spacing: 0.05em; margin-bottom: 0.75rem;">' .
                        htmlspecialchars($record->reservation_code, ENT_QUOTES, 'UTF-8') .
                        '</div>' .
                        '<div style="display: flex; justify-content: center; gap: 1rem; font-size: 0.8rem; color: #475569; margin-bottom: 0.75rem;">' .
                        '<div><strong>Tgl Ambil:</strong> ' . ($record->pickup_date ? $record->pickup_date->format('d M Y') : '-') . '</div>' .
                        '<div><strong>Tenggat:</strong> ' . ($record->due_date ? $record->due_date->format('d M Y') : '7 hari setelah diambil') . '</div>' .
                        '</div>' .
                        '<div><a href="' . route('library.ticket', $record->reservation_code) . '" target="_blank" style="color: #0284c7; font-weight: 700; font-size: 0.8rem; text-decoration: underline;">Buka Halaman Tiket Siap Cetak &rarr;</a></div>' .
                        '</div>'
                    )),

                // 2. Aksi Serahkan Buku ke Siswa
                Action::make('markAsTaken')
                    ->label('Serahkan Buku')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BookReservation $record) => $record->status === BookReservation::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penyerahan Buku Fisik')
                    ->modalDescription('Pastikan siswa telah menerima eksemplar fisik buku. Masa peminjaman (7 hari) akan mulai dihitung sejak hari ini.')
                    ->action(function (BookReservation $record, LibraryLoanService $loanService) {
                        try {
                            $loanService->handOver($record, Auth::id());
                            Notification::make()
                                ->title('Buku Berhasil Diserahkan')
                                ->body("Tiket {$record->reservation_code} kini Sedang Dipinjam. Tenggat pengembalian: " . $record->fresh()->due_date->format('d M Y'))
                                ->success()
                                ->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Gagal Menyerahkan Buku')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 3. Aksi Terima Pengembalian Buku
                Action::make('markAsReturned')
                    ->label('Terima Kembali')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('info')
                    ->visible(fn (BookReservation $record) => $record->status === BookReservation::STATUS_BORROWED)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengembalian Buku Fisik')
                    ->modalDescription('Pastikan buku telah dikembalikan dalam kondisi baik ke meja sirkulasi. Stok fisik di katalog akan otomatis bertambah 1.')
                    ->action(function (BookReservation $record, LibraryLoanService $loanService) {
                        try {
                            $loanService->receiveReturn($record, Auth::id());
                            Notification::make()
                                ->title('Buku Berhasil Dikembalikan')
                                ->body("Buku fisik dari tiket {$record->reservation_code} telah kembali ke perpustakaan. Stok siap dipinjam kembali.")
                                ->success()
                                ->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Gagal Menerima Pengembalian')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 4. Aksi Perpanjang Masa Pinjam
                Action::make('renewLoan')
                    ->label('Perpanjang (+7 Hari)')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn (BookReservation $record) => $record->status === BookReservation::STATUS_BORROWED && ! $record->is_overdue && $record->renew_count < 1)
                    ->requiresConfirmation()
                    ->modalHeading('Perpanjang Masa Peminjaman')
                    ->modalDescription('Masa peminjaman buku akan ditambah 7 hari dari tenggat sebelumnya. Maksimal perpanjangan 1 kali.')
                    ->action(function (BookReservation $record, LibraryLoanService $loanService) {
                        try {
                            $loanService->renew($record);
                            Notification::make()
                                ->title('Masa Pinjam Diperpanjang')
                                ->body("Tenggat pengembalian tiket {$record->reservation_code} kini jatuh pada " . $record->fresh()->due_date->format('d M Y'))
                                ->success()
                                ->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Gagal Memperpanjang')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 5. Aksi Batalkan Tiket (Khusus status menunggu)
                Action::make('cancelReservation')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (BookReservation $record) => $record->status === BookReservation::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Tiket Reservasi')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan tiket ini? Eksemplar buku yang ditahan akan otomatis dilepaskan kembali ke katalog.')
                    ->action(function (BookReservation $record, LibraryLoanService $loanService) {
                        try {
                            $loanService->cancel($record, 'Dibatalkan oleh staf perpustakaan');
                            Notification::make()
                                ->title('Tiket Berhasil Dibatalkan')
                                ->body("Tiket {$record->reservation_code} telah dibatalkan. Stok buku fisik telah dikembalikan.")
                                ->success()
                                ->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Gagal Membatalkan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('borrower_class')
                    ->label('Filter Kelas / Rombel Siswa')
                    ->options(function () {
                        return BookReservation::query()
                            ->whereNotNull('borrower_class')
                            ->where('borrower_class', '!=', '')
                            ->distinct()
                            ->orderBy('borrower_class')
                            ->pluck('borrower_class', 'borrower_class')
                            ->toArray();
                    })
                    ->searchable(),

                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Filter Status Sirkulasi')
                    ->options([
                        BookReservation::STATUS_PENDING => 'Menunggu Pengambilan',
                        BookReservation::STATUS_BORROWED => 'Sedang Dipinjam',
                        BookReservation::STATUS_RETURNED => 'Sudah Dikembalikan',
                        BookReservation::STATUS_CANCELLED => 'Dibatalkan',
                        BookReservation::STATUS_EXPIRED => 'Kadaluarsa',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
