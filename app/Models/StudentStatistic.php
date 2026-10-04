<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentStatistic extends Model
{
    protected $fillable = [
        'academic_year',
        'grade_level',
        'class_name',
        'male_count',
        'female_count',
        'total_count',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'male_count' => 'integer',
            'female_count' => 'integer',
            'total_count' => 'integer',
        ];
    }

    public function scopeYear(Builder $query, string $year): Builder
    {
        return $query->where('academic_year', $year);
    }
}
