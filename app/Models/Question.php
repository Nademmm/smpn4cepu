<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'bank_id',
        'question_text',
        'explanation',
        'points',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            \Illuminate\Support\Facades\Cache::forget('public_active_question_banks_v1');
            \Illuminate\Support\Facades\Cache::forget('quiz_bank_full_' . $model->bank_id);
        });
        static::deleted(function (self $model) {
            \Illuminate\Support\Facades\Cache::forget('public_active_question_banks_v1');
            \Illuminate\Support\Facades\Cache::forget('quiz_bank_full_' . $model->bank_id);
        });
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'bank_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }
}
