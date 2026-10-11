<?php

namespace App\Filament\Resources\SchoolFacilities;

use App\Filament\Resources\SchoolFacilities\Pages\CreateSchoolFacility;
use App\Filament\Resources\SchoolFacilities\Pages\EditSchoolFacility;
use App\Filament\Resources\SchoolFacilities\Pages\ListSchoolFacilities;
use App\Filament\Resources\SchoolFacilities\Schemas\SchoolFacilityForm;
use App\Filament\Resources\SchoolFacilities\Tables\SchoolFacilitiesTable;
use App\Models\SchoolFacility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SchoolFacilityResource extends Resource
{
    protected static ?string $model = SchoolFacility::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Deskripsi' => \Illuminate\Support\Str::limit($record->description ?? '-', 50),
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function getNavigationGroup(): ?string
    {
        return 'Profil & Informasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Fasilitas Sekolah';
    }

    public static function getModelLabel(): string
    {
        return 'Fasilitas';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Fasilitas Sekolah';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return SchoolFacilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchoolFacilitiesTable::configure($table);
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
            'index' => ListSchoolFacilities::route('/'),
            'create' => CreateSchoolFacility::route('/create'),
            'edit' => EditSchoolFacility::route('/{record}/edit'),
        ];
    }
}
