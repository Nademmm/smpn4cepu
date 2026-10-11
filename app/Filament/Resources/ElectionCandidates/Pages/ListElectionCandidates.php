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
                ->requiresConfirmation()
                ->modalHeading('Kosongkan Seluruh Kotak Suara?')
                ->modalDescription('Tindakan ini akan menghapus semua suara masuk dan riwayat perangkat pemilih. Seluruh kandidat akan kembali ke 0 suara sehingga simulasi atau hari pemilihan dapat dimulai dari awal.')
                ->modalSubmitActionLabel('Ya, Kosongkan Kotak Suara')
                ->action(function () {
                    \App\Models\ElectionVote::query()->delete();
                    \App\Models\ElectionDevice::query()->delete();
                    \App\Models\ElectionCandidate::query()->update(['total_votes_cached' => 0]);

                    \Filament\Notifications\Notification::make()
                        ->title('Kotak suara berhasil direset ke nol')
                        ->success()
                        ->send();
                }),
        ];
    }
}
