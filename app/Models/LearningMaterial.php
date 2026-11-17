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
        'target_classes',
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
            'target_classes' => 'array',
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

    public function isVisibleToStudent(?User $user): bool
    {
        if (! ($user instanceof User)
            || ! $user->hasRole('siswa')
            || $user->hasAnyRole(['super_admin', 'operator_sekolah', 'guru', 'wali_kelas', 'staf_perpus', 'panitia_pilketos'])) {
            return true;
        }

        $targets = array_values(array_unique(array_filter(
            array_map(fn ($c): string => strtoupper(trim((string) $c)), is_array($this->target_classes) ? $this->target_classes : []),
            fn (string $c): bool => $c !== ''
        )));

        if (empty($targets)) {
            return true;
        }

        $studentClass = strtoupper(trim((string) $user->student_class));
        if ($studentClass === '' || ! preg_match('/^[789][A-Z]$/', $studentClass)) {
            return true;
        }

        return in_array($studentClass, $targets, true);
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
