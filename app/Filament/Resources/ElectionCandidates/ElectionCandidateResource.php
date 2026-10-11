<?php

namespace App\Filament\Resources\ElectionCandidates;

use App\Filament\Resources\ElectionCandidates\Pages\CreateElectionCandidate;
use App\Filament\Resources\ElectionCandidates\Pages\EditElectionCandidate;
use App\Filament\Resources\ElectionCandidates\Pages\ListElectionCandidates;
use App\Filament\Resources\ElectionCandidates\Schemas\ElectionCandidateForm;
use App\Filament\Resources\ElectionCandidates\Tables\ElectionCandidatesTable;
use App\Models\ElectionCandidate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ElectionCandidateResource extends Resource
{
    protected static ?string $model = ElectionCandidate::class;

    protected static ?string $recordTitleAttribute = 'candidate_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['candidate_name', 'vice_candidate_name', 'candidate_number'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return "Paslon #{$record->candidate_number}: {$record->candidate_name}";
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Wakil' => $record->vice_candidate_name ?? '-',
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function getNavigationGroup(): ?string
    {
        return 'Kesiswaan & Organisasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kandidat Pilketos';
    }

    public static function getModelLabel(): string
    {
        return 'Kandidat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kandidat Pilketos';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return ElectionCandidateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElectionCandidatesTable::configure($table);
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
            'index' => ListElectionCandidates::route('/'),
            'create' => CreateElectionCandidate::route('/create'),
            'edit' => EditElectionCandidate::route('/{record}/edit'),
        ];
    }
}
