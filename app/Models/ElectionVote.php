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

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ElectionCandidate::class, 'candidate_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(ElectionDevice::class, 'device_id');
    }
}
