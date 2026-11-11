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

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CirculationCameraWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Pemindaian QR Code Langsung via Kamera
            Action::make('cameraScan')
                ->label('Scan QR Code Tiket')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->modalHeading('Scanner Kamera: QR Code Sirkulasi')
                ->modalDescription('Arahkan kamera belakang HP atau webcam ke QR Code tiket siswa.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup Scanner')
                ->modalWidth('lg')
                ->modalContent(fn () => view('filament.components.circulation-camera-modal')),
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
