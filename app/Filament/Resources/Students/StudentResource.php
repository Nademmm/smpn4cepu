<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\RelationManagers\QuizAttemptsRelationManager;
use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'students';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $allowed = auth()->user()?->hasAnyRole(['super_admin', 'guru']) ?? false;

        return $allowed ? Response::allow() : Response::deny();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('quizAttempts')
            ->where(function (Builder $q) {
                $q->whereNotNull('nisn')
                    ->orWhereHas('roles', fn (Builder $rq) => $rq->where('name', 'siswa'));
            });
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'nisn', 'nis', 'student_class'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name . ($record->student_class ? " ({$record->student_class})" : '');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'NISN' => $record->nisn ?: '-',
            'NIS' => $record->nis ?: '-',
            'Rombel' => $record->student_class ?: '-',
            'L/P' => $record->gender === 'L' ? 'Laki-Laki' : ($record->gender === 'P' ? 'Perempuan' : '-'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Kesiswaan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Data Peserta Didik';
    }

    public static function getModelLabel(): string
    {
        return 'Peserta Didik (Siswa)';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Data Peserta Didik (Siswa)';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) User::whereNotNull('nisn')->where('student_class', 'not like', 'Kelas %')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QuizAttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
