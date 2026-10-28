<?php

namespace App\Filament\Resources\ElectionVotes;

use App\Filament\Resources\ElectionVotes\Pages\ListElectionVotes;
use App\Filament\Resources\ElectionVotes\Tables\ElectionVotesTable;
use App\Models\ElectionVote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ElectionVoteResource extends Resource
{
    protected static ?string $model = ElectionVote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    public static function getNavigationGroup(): ?string
    {
        return 'Kesiswaan & Organisasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kotak Suara Pilketos';
    }

    public static function getModelLabel(): string
    {
        return 'Suara Masuk';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kotak Suara & Log Pilketos';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['candidate', 'device']);
    }

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $user = auth()->user();

        if (! $user) {
            return Response::deny();
        }

        if ($user->hasAnyRole(['super_admin', 'panitia_pilketos'])) {
            return Response::allow();
        }

        return Response::deny('Hanya Administrator dan Panitia Pilketos yang dapat mengakses Kotak Suara Pilketos.');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ElectionVotesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListElectionVotes::route('/'),
        ];
    }
}
