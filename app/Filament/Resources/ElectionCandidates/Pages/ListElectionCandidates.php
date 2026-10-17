<?php

namespace App\Filament\Resources\ElectionCandidates\Pages;

use App\Filament\Resources\ElectionCandidates\ElectionCandidateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListElectionCandidates extends ListRecords
{
    protected static string $resource = ElectionCandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pasangan Calon'),

            \Filament\Actions\Action::make('resetVotes')
                ->label('Reset Kotak Suara')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                ->authorize(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                ->requiresConfirmation()
                ->modalHeading('Kosongkan Seluruh Kotak Suara?')
                ->modalDescription('Tindakan ini akan menghapus semua suara masuk dan riwayat perangkat pemilih. Seluruh kandidat akan kembali ke 0 suara sehingga simulasi atau hari pemilihan dapat dimulai dari awal.')
                ->modalSubmitActionLabel('Ya, Kosongkan Kotak Suara')
                ->action(function () {
                    \Illuminate\Support\Facades\DB::transaction(function () {
                        \App\Models\ElectionVote::query()->delete();
                        \App\Models\ElectionDevice::query()->delete();
                        \App\Models\ElectionCandidate::query()->update(['total_votes_cached' => 0]);
                    });

                    \Illuminate\Support\Facades\Cache::forget('pilketos_live_chart_data');
                    \Illuminate\Support\Facades\Cache::forget('admin_school_overview_stats');

                    \Filament\Notifications\Notification::make()
                        ->title('Kotak suara berhasil direset ke nol')
                        ->success()
                        ->send();
                }),
        ];
    }
}
