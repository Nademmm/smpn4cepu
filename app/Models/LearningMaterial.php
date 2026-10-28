<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningMaterial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject_id',
        'grade_level',
        'title',
        'slug',
        'summary',
        'content',
        'attachment_path',
        'external_url',
        'created_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('public_active_materials_v1');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('public_active_materials_v1');
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeGrade(Builder $query, int $grade): Builder
    {
        return $query->where('grade_level', $grade);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->external_url) {
            return $this->external_url;
        }

        return \App\Support\PublicAssetResolver::resolve(
            $this->attachment_path,
            ['storage/', '']
        );
    }
}
