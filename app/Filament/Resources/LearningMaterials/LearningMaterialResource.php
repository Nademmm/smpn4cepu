<?php

namespace App\Filament\Resources\LearningMaterials;

use App\Filament\Resources\LearningMaterials\Pages\CreateLearningMaterial;
use App\Filament\Resources\LearningMaterials\Pages\EditLearningMaterial;
use App\Filament\Resources\LearningMaterials\Pages\ListLearningMaterials;
use App\Filament\Resources\LearningMaterials\Schemas\LearningMaterialForm;
use App\Filament\Resources\LearningMaterials\Tables\LearningMaterialsTable;
use App\Models\LearningMaterial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LearningMaterialResource extends Resource
{
    protected static ?string $model = LearningMaterial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    public static function getNavigationGroup(): ?string
    {
        return 'Akademik & Pembelajaran';
    }

    public static function getNavigationLabel(): string
    {
        return 'Materi Pelajaran';
    }

    public static function getModelLabel(): string
    {
        return 'Materi Pelajaran';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Materi Pembelajaran';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return LearningMaterialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningMaterialsTable::configure($table);
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
            'index' => ListLearningMaterials::route('/'),
            'create' => CreateLearningMaterial::route('/create'),
            'edit' => EditLearningMaterial::route('/{record}/edit'),
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
