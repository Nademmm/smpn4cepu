<?php

namespace App\Filament\Resources\BookReservations\Pages;

use App\Filament\Resources\BookReservations\BookReservationResource;
use App\Models\BookReservation;
use App\Services\LibraryLoanService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ListBookReservations extends ListRecords
{
    protected static string $resource = BookReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Aksi Cepat Scan Barcode / Input Kode Tiket di Meja Sirkulasi
            Action::make('quickScan')
                ->label('Scan Barcode / Cek Tiket')
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->modalHeading('Pindai Barcode / Verifikasi Tiket Sirkulasi')
                ->modalDescription('Gunakan pemindai barcode USB/kamera atau ketikkan kode tiket reservasi (contoh: LIB-2026-ABC12).')
                ->modalSubmitActionLabel('Proses Sirkulasi')
                ->form([
                    TextInput::make('code')
                        ->label('Kode Tiket / Hasil Scan Barcode')
                        ->placeholder('Arahkan scanner atau ketik LIB-2026-...')
                        ->autofocus()
                        ->required()
                        ->helperText('Otomatis mendeteksi peminjam dan status ketersediaan buku.'),

                    Radio::make('circulation_action')
                        ->label('Tindakan Sirkulasi yang Diinginkan')
                        ->options([
                            'auto' => 'Deteksi Otomatis (Jika menunggu: serahkan; Jika dipinjam: kembalikan)',
                            'hand_over' => 'Serahkan Buku ke Siswa (Status Menjadi Dipinjam)',
                            'return' => 'Terima Pengembalian Buku (Kembalikan ke Rak)',
                            'renew' => 'Perpanjang Masa Pinjam (+7 Hari)',
                            'view_only' => 'Hanya Lihat Data & Status Tiket',
                        ])
                        ->default('auto')
                        ->required(),
                ])
                ->action(function (array $data, LibraryLoanService $loanService) {
                    $ticket = $loanService->findByCode($data['code'] ?? '');

                    if (! $ticket) {
                        Notification::make()
                            ->title('Tiket Tidak Ditemukan')
                            ->body('Tidak ada data tiket sirkulasi dengan kode: ' . htmlspecialchars($data['code'] ?? ''))
                            ->danger()
                            ->send();
                        return;
                    }

                    $actionType = $data['circulation_action'] ?? 'auto';
                    $staffId = Auth::id();

                    try {
                        if ($actionType === 'auto') {
                            if ($ticket->status === BookReservation::STATUS_PENDING) {
                                $loanService->handOver($ticket, $staffId);
                                Notification::make()
                                    ->title('Buku Berhasil Diserahkan')
                                    ->body("Buku \"{$ticket->book?->title}\" telah diserahkan kepada {$ticket->borrower_name} ({$ticket->borrower_class}). Tenggat: " . $ticket->fresh()->due_date->format('d M Y'))
                                    ->success()
                                    ->send();
                            } elseif ($ticket->status === BookReservation::STATUS_BORROWED) {
                                $loanService->receiveReturn($ticket, $staffId);
                                Notification::make()
                                    ->title('Buku Berhasil Dikembalikan')
                                    ->body("Buku \"{$ticket->book?->title}\" dari peminjam {$ticket->borrower_name} telah diterima kembali. Stok fisik di katalog bertambah 1.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title("Status Tiket: {$ticket->status_label}")
                                    ->body("Tiket {$ticket->reservation_code} saat ini berstatus \"{$ticket->status_label}\". Tidak ada aksi otomatis yang dijalankan.")
                                    ->info()
                                    ->send();
                            }
                        } elseif ($actionType === 'hand_over') {
                            $loanService->handOver($ticket, $staffId);
                            Notification::make()
                                ->title('Buku Berhasil Diserahkan')
                                ->body("Tiket {$ticket->reservation_code} kini berstatus Sedang Dipinjam.")
                                ->success()
                                ->send();
                        } elseif ($actionType === 'return') {
                            $loanService->receiveReturn($ticket, $staffId);
                            Notification::make()
                                ->title('Buku Berhasil Dikembalikan')
                                ->body("Buku fisik tiket {$ticket->reservation_code} telah masuk kembali ke inventaris perpustakaan.")
                                ->success()
                                ->send();
                        } elseif ($actionType === 'renew') {
                            $loanService->renew($ticket);
                            Notification::make()
                                ->title('Masa Pinjam Diperpanjang')
                                ->body("Tenggat pengembalian tiket {$ticket->reservation_code} berhasil diperpanjang hingga " . $ticket->fresh()->due_date->format('d M Y'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title("Tiket {$ticket->reservation_code}")
                                ->body("Peminjam: {$ticket->borrower_name} ({$ticket->borrower_class}) | Buku: {$ticket->book?->title} | Status: {$ticket->status_label}")
                                ->info()
                                ->send();
                        }
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('Gagal Memproses Sirkulasi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn (): int => BookReservation::query()->count()),

            'pending' => Tab::make('Menunggu Pengambilan')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookReservation::STATUS_PENDING))
                ->badge(fn (): int => BookReservation::where('status', BookReservation::STATUS_PENDING)->count())
                ->badgeColor('warning'),

            'borrowed' => Tab::make('Sedang Dipinjam')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookReservation::STATUS_BORROWED))
                ->badge(fn (): int => BookReservation::where('status', BookReservation::STATUS_BORROWED)->count())
                ->badgeColor('info'),

            'overdue' => Tab::make('Terlambat')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->overdue())
                ->badge(fn (): int => BookReservation::overdue()->count())
                ->badgeColor('danger'),

            'returned' => Tab::make('Selesai Kembali')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookReservation::STATUS_RETURNED))
                ->badge(fn (): int => BookReservation::where('status', BookReservation::STATUS_RETURNED)->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make('Batal / Kadaluarsa')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', [BookReservation::STATUS_CANCELLED, BookReservation::STATUS_EXPIRED]))
                ->badge(fn (): int => BookReservation::whereIn('status', [BookReservation::STATUS_CANCELLED, BookReservation::STATUS_EXPIRED])->count())
                ->badgeColor('gray'),
        ];
    }
}
