<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'caption',
        'tag',
        'photo_path',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_gallery_photos_v1');
        });
        static::deleted(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_gallery_photos_v1');
        });
    }

    public function getPhotoUrlAttribute(): string
    {
        if (empty($this->photo_path)) {
            return asset('images/assets/20260717_092659.jpg');
        }

        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }

        if (str_starts_with($this->photo_path, 'images/')) {
            return asset($this->photo_path);
        }

        return \App\Support\PublicAssetResolver::resolve(
            $this->photo_path,
            ['storage/', '', 'images/assets/'],
            'images/assets/' . basename($this->photo_path)
        );
    }
}
