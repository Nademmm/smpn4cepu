<?php

namespace App\Models;

use App\Enums\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'category',
        'title',
        'slug',
        'excerpt',
        'content',
        'event_date',
        'featured_image',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => PostCategory::class,
            'is_published' => 'boolean',
            'event_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeCategory(Builder $query, PostCategory|string $category): Builder
    {
        $value = $category instanceof PostCategory ? $category->value : $category;
        return $query->where('category', $value);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_sitemap_xml_v1');
        });
        static::deleted(function () {
            \App\Support\PublicAssetResolver::flush();
            \Illuminate\Support\Facades\Cache::forget('public_home_data_v1');
            \Illuminate\Support\Facades\Cache::forget('public_sitemap_xml_v1');
        });
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return \App\Support\PublicAssetResolver::resolve(
            $this->featured_image,
            ['storage/', '', 'images/assets/']
        );
    }
}
