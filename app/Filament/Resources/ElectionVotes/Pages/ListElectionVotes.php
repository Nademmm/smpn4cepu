<?php

namespace App\Filament\Resources\ElectionVotes\Pages;

use App\Filament\Resources\ElectionVotes\ElectionVoteResource;
use App\Models\ElectionCandidate;
use App\Models\ElectionVote;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class ListElectionVotes extends ListRecords
{
    protected static string $resource = ElectionVoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculateCandidateVotes')
                ->label('Sinkronkan Ulang Perolehan Suara')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('info')
                ->action(function (): void {
                    $candidates = ElectionCandidate::query()->get();

                    foreach ($candidates as $candidate) {
                        $actual = ElectionVote::where('candidate_id', $candidate->id)->count();
                        $candidate->update(['total_votes_cached' => $actual]);
                    }

                    Cache::forget('pilketos_public_live_count');
                    Cache::forget('pilketos_admin_chart_data');

                    Notification::make()
                        ->title('Perolehan Suara Berhasil Disinkronkan')
                        ->body('Total suara seluruh kandidat Pilketos telah dihitung ulang dari Kotak Suara.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
