<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StaffMember extends Model
{
    protected $fillable = [
        'nip',
        'name',
        'position',
        'employment_status',
        'photo_path',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'employment_status' => EmploymentStatus::class,
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('display_order', 'asc');
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_student_stats_v1');
        });
        static::deleted(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_student_stats_v1');
        });
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return \App\Support\PublicAssetResolver::resolve(
            $this->photo_path,
            ['storage/', '', 'images/']
        );
    }
}
