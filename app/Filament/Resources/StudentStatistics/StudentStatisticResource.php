<?php

namespace App\Filament\Resources\StudentStatistics;

use App\Filament\Resources\StudentStatistics\Pages\CreateStudentStatistic;
use App\Filament\Resources\StudentStatistics\Pages\EditStudentStatistic;
use App\Filament\Resources\StudentStatistics\Pages\ListStudentStatistics;
use App\Filament\Resources\StudentStatistics\Schemas\StudentStatisticForm;
use App\Filament\Resources\StudentStatistics\Tables\StudentStatisticsTable;
use App\Models\StudentStatistic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentStatisticResource extends Resource
{
    protected static ?string $model = StudentStatistic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function getNavigationGroup(): ?string
    {
        return 'Profil & Informasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Statistik Siswa';
    }

    public static function getModelLabel(): string
    {
        return 'Statistik Siswa';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Statistik Siswa';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
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
