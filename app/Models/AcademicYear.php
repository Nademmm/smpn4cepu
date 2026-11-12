<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'semester',
        'status',
        'start_date',
        'end_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function studentHistories(): HasMany
    {
        return $this->hasMany(StudentAcademicHistory::class, 'academic_year_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'academic_year_id');
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(StudentStatistic::class, 'academic_year', 'name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopePlanning(Builder $query): Builder
    {
        return $query->where('status', 'persiapan');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'ditutup');
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function isPlanning(): bool
    {
        return $this->status === 'persiapan';
    }

    public function isClosed(): bool
    {
        return $this->status === 'ditutup';
    }

    public static function getActive(): ?self
    {
        return self::where('status', 'aktif')->latest('id')->first();
    }

    public static function getPlanning(): ?self
    {
        return self::where('status', 'persiapan')->latest('id')->first();
    }
}

