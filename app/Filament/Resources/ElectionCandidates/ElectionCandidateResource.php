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
