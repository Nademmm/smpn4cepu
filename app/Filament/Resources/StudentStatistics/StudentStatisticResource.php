<?php

namespace App\Filament\Resources\StudentStatistics;

use App\Filament\Resources\StudentStatistics\Pages\CreateStudentStatistic;
use App\Filament\Resources\StudentStatistics\Pages\EditStudentStatistic;
use App\Filament\Resources\StudentStatistics\Pages\ListStudentStatistics;
use App\Filament\Resources\StudentStatistics\Schemas\StudentStatisticForm;
use App\Filament\Resources\StudentStatistics\Tables\StudentStatisticsTable;
use App\Models\StudentStatistic;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StudentStatisticResource extends Resource
{
    protected static ?string $model = StudentStatistic::class;

    protected static ?string $recordTitleAttribute = 'academic_year';

    public static function canManageStatistics(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasAnyRole(['super_admin', 'operator_sekolah']);
    }

    public static function canViewStatistics(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasAnyRole(['super_admin', 'operator_sekolah', 'wali_kelas']);
    }

    public static function canViewAny(): bool
    {
        return static::canViewStatistics();
    }

    public static function canCreate(): bool
    {
        return static::canManageStatistics();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManageStatistics();
    }

    public static function canDelete(Model $record): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $user */
        $user = Auth::user();
        if ($user instanceof User && $user->hasRole('wali_kelas') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            if (! empty($user->homeroom_class)) {
                $query->where('class_name', $user->homeroom_class);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['academic_year', 'class_name'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return "Statistik TA {$record->academic_year}" . ($record->class_name ? " ({$record->class_name})" : '');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Total Siswa' => number_format($record->total_count ?? 0) . ' Siswa',
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function getNavigationGroup(): ?string
    {
        return 'Kesiswaan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Rekap Statistik Rombel';
    }

    public static function getModelLabel(): string
    {
        return 'Statistik Siswa';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Rekap Statistik Rombel';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return StudentStatisticForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentStatisticsTable::configure($table);
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
            'index' => ListStudentStatistics::route('/'),
            'create' => CreateStudentStatistic::route('/create'),
            'edit' => EditStudentStatistic::route('/{record}/edit'),
        ];
    }
}
