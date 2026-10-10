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
                     ->where('published_at', '<=', now());
    }

    public function scopeCategory(Builder $query, PostCategory|string $category): Builder
    {
        $value = $category instanceof PostCategory ? $category->value : $category;
        return $query->where('category', $value);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
            return $this->featured_image;
        }

        if (file_exists(public_path('storage/' . $this->featured_image))) {
            return asset('storage/' . $this->featured_image);
        }

        if (file_exists(public_path($this->featured_image))) {
            return asset($this->featured_image);
        }

        if (file_exists(public_path('images/assets/' . $this->featured_image))) {
            return asset('images/assets/' . $this->featured_image);
        }

        return asset('storage/' . $this->featured_image);
    }
}
