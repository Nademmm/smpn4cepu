<?php

namespace App\Filament\Resources\LibraryBooks;

use App\Filament\Resources\LibraryBooks\Pages\CreateLibraryBook;
use App\Filament\Resources\LibraryBooks\Pages\EditLibraryBook;
use App\Filament\Resources\LibraryBooks\Pages\ListLibraryBooks;
use App\Filament\Resources\LibraryBooks\Schemas\LibraryBookForm;
use App\Filament\Resources\LibraryBooks\Tables\LibraryBooksTable;
use App\Models\LibraryBook;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LibraryBookResource extends Resource
{
    protected static ?string $model = LibraryBook::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function getNavigationGroup(): ?string
    {
        return 'Akademik & Pembelajaran';
    }

    public static function getNavigationLabel(): string
    {
        return 'Buku Perpustakaan';
    }

    public static function getModelLabel(): string
    {
        return 'Buku Perpustakaan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Katalog Buku Perpustakaan';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function form(Schema $schema): Schema
    {
        return LibraryBookForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LibraryBooksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLibraryBooks::route('/'),
            'create' => CreateLibraryBook::route('/create'),
            'edit' => EditLibraryBook::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
