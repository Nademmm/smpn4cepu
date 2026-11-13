<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_label',
        'option_text',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        $flush = static function (self $model): void {
            Cache::forget('public_active_question_banks_v1');
            $question = $model->question()->first(['id', 'bank_id']);
            if ($question?->bank_id) {
                Cache::forget('quiz_bank_full_' . $question->bank_id);
            }
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
