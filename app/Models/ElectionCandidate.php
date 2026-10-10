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

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }

        if (file_exists(public_path('storage/' . $this->photo_path))) {
            return asset('storage/' . $this->photo_path);
        }

        if (file_exists(public_path($this->photo_path))) {
            return asset($this->photo_path);
        }

        return asset('storage/' . $this->photo_path);
    }
}
