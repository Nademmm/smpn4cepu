<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionVote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'candidate_id',
        'device_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (self $vote) {
            if ($vote->device_id) {
                ElectionDevice::where('id', $vote->device_id)->delete();
            }

            if ($vote->candidate_id) {
                $actualCount = self::where('candidate_id', $vote->candidate_id)->count();
                ElectionCandidate::where('id', $vote->candidate_id)->update([
                    'total_votes_cached' => $actualCount,
                ]);
            }

            \Illuminate\Support\Facades\Cache::forget('pilketos_public_live_count');
            \Illuminate\Support\Facades\Cache::forget('pilketos_admin_chart_data');
        });
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ElectionCandidate::class, 'candidate_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(ElectionDevice::class, 'device_id');
    }
}
