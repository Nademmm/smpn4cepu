<?php

namespace App\Filament\Resources\LibraryBooks;

use App\Filament\Resources\LibraryBooks\Pages\CreateLibraryBook;
use App\Filament\Resources\LibraryBooks\Pages\EditLibraryBook;
use App\Filament\Resources\LibraryBooks\Pages\ListLibraryBooks;
use App\Filament\Resources\LibraryBooks\Schemas\LibraryBookForm;
use App\Filament\Resources\LibraryBooks\Tables\LibraryBooksTable;
use App\Models\LibraryBook;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class LibraryBookResource extends Resource
{
    protected static ?string $model = LibraryBook::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canManageLibrary(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasAnyRole(['super_admin', 'staf_perpus']);
    }

    public static function canViewAny(): bool
    {
        return static::canManageLibrary();
    }

    public static function canCreate(): bool
    {
        return static::canManageLibrary();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManageLibrary();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canManageLibrary();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'author', 'isbn', 'publisher', 'category'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Penulis' => $record->author ?? '-',
            'Kategori' => $record->category ?? '-',
        ];
    }

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
