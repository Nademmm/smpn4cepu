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

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('pilketos_public_live_count');
            \Illuminate\Support\Facades\Cache::forget('pilketos_admin_chart_data');
        });
        static::deleted(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('pilketos_public_live_count');
            \Illuminate\Support\Facades\Cache::forget('pilketos_admin_chart_data');
        });
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return \App\Support\PublicAssetResolver::resolve(
            $this->photo_path,
            ['', 'images/assets/', 'storage/']
        );
    }
}
