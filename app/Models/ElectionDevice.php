<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ElectionDevice extends Model
{
    protected $fillable = [
        'device_fingerprint',
        'ip_subnet',
        'user_agent_hash',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
        ];
    }

    public function vote(): HasOne
    {
        return $this->hasOne(ElectionVote::class, 'device_id');
    }
}
