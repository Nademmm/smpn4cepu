<?php

namespace App\Livewire\Pilketos;

use App\Models\ElectionCandidate;
use App\Models\ElectionVote;
use Livewire\Component;

class LiveCount extends Component
{
    public function render()
    {
        $data = \Illuminate\Support\Facades\Cache::remember('pilketos_live_chart_data', 10, function () {
            $candidates = ElectionCandidate::withCount('votes')
                ->orderBy('candidate_number')
                ->get();

            $totalVotes = ElectionVote::count();

            $chartData = [
                'labels' => $candidates->pluck('candidate_name')->toArray(),
                'votes' => $candidates->pluck('total_votes_cached')->toArray(),
            ];

            return compact('candidates', 'totalVotes', 'chartData');
        });

        return view('livewire.pilketos.live-count', $data);
    }
}
