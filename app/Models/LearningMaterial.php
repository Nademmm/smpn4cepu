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
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
        ];
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

        if (!$this->attachment_path) {
            return null;
        }

        if (str_starts_with($this->attachment_path, 'http://') || str_starts_with($this->attachment_path, 'https://')) {
            return $this->attachment_path;
        }

        if (file_exists(public_path('storage/' . $this->attachment_path))) {
            return asset('storage/' . $this->attachment_path);
        }

        if (file_exists(public_path($this->attachment_path))) {
            return asset($this->attachment_path);
        }

        return asset('storage/' . $this->attachment_path);
    }
}
