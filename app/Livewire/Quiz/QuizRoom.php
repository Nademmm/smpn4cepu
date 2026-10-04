<?php

namespace App\Livewire\Quiz;

use App\Models\QuestionBank;
use App\Services\QuizAssessmentService;
use Carbon\Carbon;
use Livewire\Component;

class QuizRoom extends Component
{
    public int $bankId;
    public string $participantIdentifier = '';
    public array $questions = [];
    public array $answers = []; // [question_id => selected_option_id]
    public ?Carbon $startedAt = null;
    public bool $isFinished = false;
    public ?array $resultSummary = null;

    public function mount(int $bankId, QuizAssessmentService $assessmentService): void
    {
        $this->bankId = $bankId;
        $this->questions = $assessmentService->getQuestionsForQuiz($bankId)->toArray();
        $this->startedAt = now();
    }

    public function submitQuiz(QuizAssessmentService $assessmentService): void
    {
        $attempt = $assessmentService->submitAttempt(
            $this->bankId,
            $this->participantIdentifier ?: 'ANON-' . session()->getId(),
            $this->answers,
            $this->startedAt ?? now()
        );

        $this->resultSummary = $assessmentService->getReviewDetails($attempt->id);
        $this->isFinished = true;
    }

    public function render()
    {
        return view('livewire.quiz.quiz-room');
    }
}
