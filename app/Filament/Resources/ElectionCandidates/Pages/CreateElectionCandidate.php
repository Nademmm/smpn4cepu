<?php

namespace App\Filament\Resources\ElectionCandidates\Pages;

use App\Filament\Resources\ElectionCandidates\ElectionCandidateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectionCandidate extends CreateRecord
{
    protected static string $resource = ElectionCandidateResource::class;
}
