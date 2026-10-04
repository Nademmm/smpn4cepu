<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionCandidate extends Model
{
    protected $fillable = [
        'candidate_number',
        'candidate_name',
        'vice_candidate_name',
        'vision',
        'mission',
        'photo_path',
        'total_votes_cached',
    ];

    protected function casts(): array
    {
        return [
            'candidate_number' => 'integer',
            'total_votes_cached' => 'integer',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class, 'candidate_id');
    }
}
