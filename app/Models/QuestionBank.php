<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    protected $fillable = [
        'subject_id',
        'grade_level',
        'title',
        'description',
        'duration_minutes',
        'passing_grade',
        'access_code',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'duration_minutes' => 'integer',
            'passing_grade' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            \Illuminate\Support\Facades\Cache::forget('public_active_question_banks_v1');
            \Illuminate\Support\Facades\Cache::forget('quiz_bank_full_' . $model->id);
        });
        static::deleted(function (self $model) {
            \Illuminate\Support\Facades\Cache::forget('public_active_question_banks_v1');
            \Illuminate\Support\Facades\Cache::forget('quiz_bank_full_' . $model->id);
        });
    }

    public function requiresPassword(): bool
    {
        return filled(trim((string) $this->access_code));
    }

    public function verifyPassword(string $input): bool
    {
        if (! $this->requiresPassword()) {
            return true;
        }

        return hash_equals(
            strtoupper(trim((string) $this->access_code)),
            strtoupper(trim($input))
        );
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'bank_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'bank_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
