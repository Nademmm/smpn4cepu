<?php

namespace App\Filament\Resources\SchoolDocuments;

use App\Filament\Resources\SchoolDocuments\Pages\CreateSchoolDocument;
use App\Filament\Resources\SchoolDocuments\Pages\EditSchoolDocument;
use App\Filament\Resources\SchoolDocuments\Pages\ListSchoolDocuments;
use App\Filament\Resources\SchoolDocuments\Schemas\SchoolDocumentForm;
use App\Filament\Resources\SchoolDocuments\Tables\SchoolDocumentsTable;
use App\Models\SchoolDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SchoolDocumentResource extends Resource
{
    protected static ?string $model = SchoolDocument::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'category'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Kategori' => $record->category,
            'Total Unduh' => $record->download_count . ' kali',
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    public static function getNavigationGroup(): ?string
    {
        return 'Profil & Informasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dokumen Unduhan';
    }

    public static function getModelLabel(): string
    {
        return 'Dokumen Unduhan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Dokumen Unduhan';
    }

    public static function getNavigationSort(): ?int
    {
        return 7;
    }

    public static function form(Schema $schema): Schema
    {
        return SchoolDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchoolDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchoolDocuments::route('/'),
            'create' => CreateSchoolDocument::route('/create'),
            'edit' => EditSchoolDocument::route('/{record}/edit'),
        ];
    }
}
