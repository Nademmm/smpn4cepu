<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'title',
        'category',
        'level',
        'winner_name',
        'rank',
        'event_date',
        'description',
        'photo_path',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_achievements_v1');
        });
        static::deleted(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_achievements_v1');
        });
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return \App\Support\PublicAssetResolver::resolve(
            $this->photo_path,
            ['storage/', '', 'images/assets/']
        );
    }
}
