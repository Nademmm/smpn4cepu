<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Akun Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Akun')
                ->badge(fn (): int => User::query()->count()),

            'staff' => Tab::make('Staf, Guru & Panitia')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas('roles', fn (Builder $q) => $q->whereIn('name', ['super_admin', 'guru', 'staf_perpus', 'panitia_pilketos'])))
                ->badge(fn (): int => User::query()->whereHas('roles', fn (Builder $q) => $q->whereIn('name', ['super_admin', 'guru', 'staf_perpus', 'panitia_pilketos']))->count())
                ->badgeColor('success'),

            'siswa' => Tab::make('Akun Siswa')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('nisn'))
                ->badge(fn (): int => User::query()->whereNotNull('nisn')->count())
                ->badgeColor('info'),
        ];
    }
}
