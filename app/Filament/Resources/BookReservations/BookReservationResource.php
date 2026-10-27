<?php

namespace App\Filament\Resources\BookReservations;

use App\Filament\Resources\BookReservations\Pages\EditBookReservation;
use App\Filament\Resources\BookReservations\Pages\ListBookReservations;
use App\Filament\Resources\BookReservations\Schemas\BookReservationForm;
use App\Filament\Resources\BookReservations\Tables\BookReservationsTable;
use App\Models\BookReservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BookReservationResource extends Resource
{
    protected static ?string $model = BookReservation::class;

    protected static ?string $recordTitleAttribute = 'reservation_code';

    public static function getGloballySearchableAttributes(): array
    {
        return ['reservation_code', 'borrower_name', 'borrower_class'];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('book');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Peminjam' => $record->borrower_name . ' (' . $record->borrower_class . ')',
            'Buku' => $record->book?->title ?? '-',
            'Status' => ucfirst($record->status),
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    public static function getNavigationGroup(): ?string
    {
        return 'Perpustakaan & Literasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Reservasi Buku';
    }

    public static function getModelLabel(): string
    {
        return 'Reservasi Buku';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Reservasi Buku';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = \Illuminate\Support\Facades\Cache::remember(
            'admin_nav_badge_pending_reservations',
            60,
            fn () => static::getModel()::where('status', 'menunggu')->count()
        );

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return BookReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookReservationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookReservations::route('/'),
            'edit' => EditBookReservation::route('/{record}/edit'),
        ];
    }
}
