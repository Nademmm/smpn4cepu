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
        // Jika kotak suara kosong (misal baru saja direset oleh panitia), bersihkan status penanda cookie lama
        if (\App\Models\ElectionDevice::count() === 0) {
            \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('pilketos_voter_token'));
            $this->hasVoted = false;
            return;
        }

        if ($fingerprintService->hasAlreadyVoted($request)) {
            $this->hasVoted = true;
        }
    }

    public function updatedClientHardwareHash(string $value, PilketosFingerprintService $fingerprintService, Request $request): void
    {
        if ($this->hasVoted) {
            return;
        }

        $fingerprint = $fingerprintService->generateFingerprint($request, $value);
        if ($fingerprintService->hasAlreadyVoted($request, $fingerprint)) {
            $this->hasVoted = true;
        }
    }

    public function submitVote(PilketosFingerprintService $fingerprintService, Request $request): void
    {
        if (!$this->selectedCandidateId || !ElectionCandidate::whereKey($this->selectedCandidateId)->exists()) {
            $this->errorMessage = 'Silakan pilih salah satu kandidat terlebih dahulu.';
            return;
        }

        try {
            $fingerprintService->recordVote($request, $this->selectedCandidateId, $this->clientHardwareHash);
            $this->hasVoted = true;
        } catch (\RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pilketos vote submission error: ' . $e->getMessage(), [
                'candidate_id' => $this->selectedCandidateId,
            ]);
            $this->errorMessage = 'Terjadi kendala teknis saat menyimpan suara. Silakan coba kembali.';
        }
    }

    public function render()
    {
        $candidates = ElectionCandidate::orderBy('candidate_number')->get();

        return view('livewire.pilketos.voting-booth', compact('candidates'));
    }
}
