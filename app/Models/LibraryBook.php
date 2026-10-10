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

    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        if (file_exists(public_path('storage/' . $this->cover_image))) {
            return asset('storage/' . $this->cover_image);
        }

        if (file_exists(public_path($this->cover_image))) {
            return asset($this->cover_image);
        }

        return asset('storage/' . $this->cover_image);
    }

    public function getDigitalFileUrlAttribute(): ?string
    {
        if (!$this->digital_file_path) {
            return null;
        }

        if (str_starts_with($this->digital_file_path, 'http://') || str_starts_with($this->digital_file_path, 'https://')) {
            return $this->digital_file_path;
        }

        if (file_exists(public_path('storage/' . $this->digital_file_path))) {
            return asset('storage/' . $this->digital_file_path);
        }

        if (file_exists(public_path($this->digital_file_path))) {
            return asset($this->digital_file_path);
        }

        return asset('storage/' . $this->digital_file_path);
    }
}
