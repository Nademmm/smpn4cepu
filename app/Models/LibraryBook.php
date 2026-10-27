<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publisher',
        'category',
        'synopsis',
        'publication_year',
        'page_count',
        'language',
        'shelf_location',
        'call_number',
        'total_stock',
        'available_stock',
        'cover_image',
        'digital_file_path',
    ];

    protected $appends = ['cover_url', 'digital_file_url'];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'page_count' => 'integer',
            'total_stock' => 'integer',
            'available_stock' => 'integer',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('available_stock', '>', 0);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_library_meta_v1');
            \Illuminate\Support\Facades\Cache::forget('admin_school_overview_stats');
        });
        static::deleted(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_library_meta_v1');
            \Illuminate\Support\Facades\Cache::forget('admin_school_overview_stats');
        });
    }

    public function getCoverUrlAttribute(): ?string
    {
        return \App\Support\PublicAssetResolver::resolve(
            $this->cover_image,
            ['storage/', '']
        );
    }

    public function getDigitalFileUrlAttribute(): ?string
    {
        return \App\Support\PublicAssetResolver::resolve(
            $this->digital_file_path,
            ['storage/', '']
        );
    }
}
