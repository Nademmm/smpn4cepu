<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Extracurricular extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'leader_name',
        'schedule_day',
        'description',
        'photo_path',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }

            $baseSlug = $model->slug;
            $counter = 1;

            while (static::where('slug', $model->slug)
                ->when($model->exists, fn ($q) => $q->where('id', '!=', $model->id))
                ->exists()) {
                $model->slug = $baseSlug . '-' . $counter++;
            }
        });

        static::saved(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_extracurriculars_v1');
        });
        static::deleted(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_extracurriculars_v1');
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
