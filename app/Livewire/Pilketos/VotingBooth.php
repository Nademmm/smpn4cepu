<?php

namespace App\Livewire\Pilketos;

use App\Models\ElectionCandidate;
use App\Services\PilketosFingerprintService;
use Illuminate\Http\Request;
use Livewire\Component;

class VotingBooth extends Component
{
    public ?int $selectedCandidateId = null;
    public string $clientHardwareHash = '';
    public bool $hasVoted = false;
    public string $errorMessage = '';

    public function mount(Request $request, PilketosFingerprintService $fingerprintService): void
    {
        // Placeholder check saat bilik suara diakses
    }

    public function submitVote(PilketosFingerprintService $fingerprintService, Request $request): void
    {
        if (!$this->selectedCandidateId) {
            $this->errorMessage = 'Silakan pilih salah satu kandidat terlebih dahulu.';
            return;
        }

        try {
            $fingerprintService->recordVote($request, $this->selectedCandidateId, $this->clientHardwareHash);
            $this->hasVoted = true;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $candidates = ElectionCandidate::orderBy('candidate_number')->get();

        return view('livewire.pilketos.voting-booth', compact('candidates'));
    }
}
