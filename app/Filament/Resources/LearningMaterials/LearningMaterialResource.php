<?php

namespace App\Filament\Resources\LearningMaterials;

use App\Filament\Resources\LearningMaterials\Pages\CreateLearningMaterial;
use App\Filament\Resources\LearningMaterials\Pages\EditLearningMaterial;
use App\Filament\Resources\LearningMaterials\Pages\ListLearningMaterials;
use App\Filament\Resources\LearningMaterials\Schemas\LearningMaterialForm;
use App\Filament\Resources\LearningMaterials\Tables\LearningMaterialsTable;
use App\Models\LearningMaterial;
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

class LearningMaterialResource extends Resource
{
    protected static ?string $model = LearningMaterial::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canManageAcademics(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasAnyRole(['super_admin', 'guru']);
    }

    public static function canViewAny(): bool
    {
        return static::canManageAcademics();
    }

    public static function canCreate(): bool
    {
        return static::canManageAcademics();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManageAcademics();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canManageAcademics();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'summary', 'subject.name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('subject');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Mapel' => $record->subject?->name ?? '-',
            'Kelas' => 'Kelas ' . ($record->grade_level ?? '-'),
        ];
    }

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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var User|null $user */
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            $subjectIds = $user->subjects()->pluck('subjects.id')->all();

            if (! empty($subjectIds)) {
                $query->where(function (Builder $q) use ($user, $subjectIds): void {
                    $q->where('created_by', $user->id)
                        ->orWhereIn('subject_id', $subjectIds);
                });
            } else {
                $query->where('created_by', $user->id);
            }
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
