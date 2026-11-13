<?php

namespace App\Filament\Resources\ElectionCandidates\Pages;

use App\Filament\Resources\ElectionCandidates\ElectionCandidateResource;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListElectionCandidates extends ListRecords
{
    protected static string $resource = ElectionCandidateResource::class;

    protected function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pasangan Calon'),

            \Filament\Actions\Action::make('resetVotes')
                ->label('Reset Kotak Suara')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->visible(fn (): bool => $this->isSuperAdmin())
                ->authorize(fn (): bool => $this->isSuperAdmin())
                ->requiresConfirmation()
                ->modalHeading('Kosongkan Seluruh Kotak Suara?')
                ->modalDescription('Tindakan ini akan menghapus semua suara masuk dan riwayat perangkat pemilih. Seluruh kandidat akan kembali ke 0 suara sehingga simulasi atau hari pemilihan dapat dimulai dari awal.')
                ->modalSubmitActionLabel('Ya, Kosongkan Kotak Suara')
                ->action(function () {
                    $votesCount = \App\Models\ElectionVote::count();

                    \Illuminate\Support\Facades\DB::transaction(function () {
                        \App\Models\ElectionVote::query()->delete();
                        \App\Models\ElectionDevice::query()->delete();
                        \App\Models\ElectionCandidate::query()->update(['total_votes_cached' => 0]);
                    });

                    \Illuminate\Support\Facades\Cache::forget('pilketos_public_live_count');
                    \Illuminate\Support\Facades\Cache::forget('pilketos_admin_chart_data');
                    \Illuminate\Support\Facades\Cache::forget('admin_school_overview_stats');

                    ActivityLog::record(
                        action: 'reset_pilketos_votes',
                        description: "Super Admin mengosongkan kotak suara Pilketos ({$votesCount} suara direset ke 0).",
                        reason: 'Reset kotak suara untuk simulasi atau pembukaan periode pemilihan baru',
                        properties: [
                            'cleared_votes' => $votesCount,
                        ],
                        module: 'Kesiswaan'
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Kotak suara berhasil direset ke nol')
                        ->success()
                        ->send();
                }),
        ];
    }
}
