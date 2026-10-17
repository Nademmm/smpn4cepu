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

        if (file_exists(public_path('images/assets/' . $this->photo_path))) {
            return asset('images/assets/' . $this->photo_path);
        }

        return asset('storage/' . $this->photo_path);
    }
}
