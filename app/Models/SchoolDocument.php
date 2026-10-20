<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'description',
        'file_path',
        'file_size',
        'file_extension',
        'download_count',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'download_count' => 'integer',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('public_downloads_v1');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('public_downloads_v1');
        });
    }
}
