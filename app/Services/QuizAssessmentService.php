<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuizAssessmentService
{
    /**
     * Mengambil daftar pertanyaan dan opsi TANPA mengekspos kolom 'is_correct' ke klien (Zero Client Leakage).
     */
    public function getQuestionsForQuiz(int $bankId): Collection
    {
        $bank = QuestionBank::with(['questions.options' => function ($query) {
            $query->select('id', 'question_id', 'option_text');
        }])->where('is_active', true)->findOrFail($bankId);

        return $bank->questions->map(function (Question $question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'points' => $question->points,
                'options' => $question->options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'text' => $opt->option_text,
                ])->shuffle()->values(), // Acak urutan opsi jawaban di sisi server
            ];
        });
    }

    /**
     * Alias kompatibilitas pengambilan daftar soal untuk klien.
     */
    public function getQuestionsForClient(int $questionBankId): Collection
    {
        return $this->getQuestionsForQuiz($questionBankId);
    }

    /**
     * Evaluasi otomatis jawaban siswa dan simpan rekaman attempt ke database.
     *
     * @param array<int, int> $answers Format: [question_id => selected_option_id]
     */
    public function submitAttempt(
        int $bankId,
        string $participantIdentifier,
        array $answers,
        Carbon $startedAt,
        ?int $userId = null
    ): QuizAttempt {
        $bank = QuestionBank::with('questions.options')->where('is_active', true)->findOrFail($bankId);

        if ($bank->questions->isEmpty()) {
            throw new InvalidArgumentException('Bank soal tidak memiliki butir pertanyaan.');
        }

        return DB::transaction(function () use ($bank, $participantIdentifier, $answers, $startedAt, $userId) {
            $totalQuestions = $bank->questions->count();
            $correctAnswersCount = 0;
            $totalEarnedPoints = 0;
            $maxPossiblePoints = $bank->questions->sum('points');

            $recordsToInsert = [];

            foreach ($bank->questions as $question) {
                $selectedOptionId = $answers[$question->id] ?? null;
                $isCorrect = false;

                if ($selectedOptionId) {
                    $correctOption = $question->options->firstWhere('is_correct', true);
                    if ($correctOption && (int)$correctOption->id === (int)$selectedOptionId) {
                        $isCorrect = true;
                        $correctAnswersCount++;
                        $totalEarnedPoints += $question->points;
                    }
                }

                $recordsToInsert[] = [
                    'question_id' => $question->id,
                    'selected_option_id' => $selectedOptionId,
                    'is_correct' => $isCorrect,
                ];
            }

            // Hitung nilai akhir berbasis skala 100
            $finalScore = $maxPossiblePoints > 0
                ? round(($totalEarnedPoints / $maxPossiblePoints) * 100, 2)
                : 0.00;

            $isPassed = $finalScore >= $bank->passing_grade;

            // Simpan data Attempt
            $attempt = QuizAttempt::create([
                'bank_id' => $bank->id,
                'user_id' => $userId,
                'participant_identifier' => $participantIdentifier,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswersCount,
                'final_score' => $finalScore,
                'is_passed' => $isPassed,
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            // Simpan seluruh butir jawaban sekaligus (Bulk Insert 1 Query)
            $now = now();
            $answerRows = array_map(fn (array $record) => [
                'attempt_id' => $attempt->id,
                'question_id' => $record['question_id'],
                'selected_option_id' => $record['selected_option_id'],
                'is_correct' => $record['is_correct'],
                'created_at' => $now,
                'updated_at' => $now,
            ], $recordsToInsert);

            if (!empty($answerRows)) {
                QuizAttemptAnswer::insert($answerRows);
            }

            return $attempt->load(['answers.question', 'answers.selectedOption']);
        });
    }

    /**
     * Dapatkan pembahasan dan review setelah pengerjaan selesai.
     */
    public function getReviewDetails(int $attemptId): array
    {
        $attempt = QuizAttempt::with([
            'bank.subject',
            'answers.question.options',
            'answers.selectedOption'
        ])->findOrFail($attemptId);

        return [
            'attempt_id' => $attempt->id,
            'subject' => $attempt->bank->subject->name,
            'title' => $attempt->bank->title,
            'score' => $attempt->final_score,
            'final_score' => $attempt->final_score,
            'correct_count' => $attempt->correct_answers,
            'total_questions' => $attempt->total_questions,
            'is_passed' => $attempt->is_passed,
            'passing_grade' => $attempt->bank->passing_grade,
            'reviews' => $attempt->answers->map(function (QuizAttemptAnswer $answer) {
                $correctOption = $answer->question->options->firstWhere('is_correct', true);

                return [
                    'question_id' => $answer->question_id,
                    'question_text' => $answer->question->question_text,
                    'explanation' => $answer->question->explanation,
                    'points' => $answer->question->points,
                    'user_selected' => $answer->selectedOption?->option_text ?? 'Tidak Menjawab',
                    'correct_answer' => $correctOption?->option_text ?? '',
                    'is_correct' => $answer->is_correct,
                ];
            })->all(),
        ];
    }
}
