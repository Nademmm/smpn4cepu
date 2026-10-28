<?php

namespace App\Filament\Resources\QuizAttempts\Pages;

use App\Filament\Resources\QuizAttempts\QuizAttemptResource;
use App\Models\QuizAttempt;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListQuizAttempts extends ListRecords
{
    protected static string $resource = QuizAttemptResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Hasil Kuis')
                ->badge(fn (): int => QuizAttempt::query()->count()),

            'passed' => Tab::make('Tuntas (Lulus KKM)')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_passed', true))
                ->badge(fn (): int => QuizAttempt::query()->where('is_passed', true)->count())
                ->badgeColor('success'),

            'remedial' => Tab::make('Belum Tuntas (Remedial)')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_passed', false))
                ->badge(fn (): int => QuizAttempt::query()->where('is_passed', false)->count())
                ->badgeColor('danger'),
        ];
    }
}
