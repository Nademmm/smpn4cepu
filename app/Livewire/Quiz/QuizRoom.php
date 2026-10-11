<?php

namespace App\Livewire\Quiz;

use App\Models\QuestionBank;
use App\Services\QuizAssessmentService;
use Carbon\Carbon;
use Livewire\Component;

class QuizRoom extends Component
{
    public int $bankId;
    public ?QuestionBank $bank = null;
    public string $studentName = '';
    public string $studentClass = '';
    public bool $hasStarted = false;
    public string $participantIdentifier = '';
    public array $questions = [];
    public array $answers = []; // [question_id => selected_option_id]
    public int $durationSeconds = 1800; // Default 30 menit
    public ?Carbon $startedAt = null;
    public bool $isFinished = false;
    public ?array $resultSummary = null;

    public function mount(int $bankId, QuizAssessmentService $assessmentService): void
    {
        $this->bankId = $bankId;
        $this->questions = $assessmentService->getQuestionsForQuiz($bankId)->toArray();

        $this->bank = QuestionBank::with('subject')->find($bankId);
        if ($this->bank && $this->bank->duration_minutes > 0) {
            $this->durationSeconds = $this->bank->duration_minutes * 60;
        }
    }

    public function startQuiz(): void
    {
        $this->validate([
            'studentName' => 'required|string|min:3|max:100',
            'studentClass' => 'required|string|min:2|max:50',
        ], [
            'studentName.required' => 'Nama lengkap peserta didik wajib diisi.',
            'studentName.min' => 'Nama lengkap minimal 3 karakter.',
            'studentClass.required' => 'Kelas / rombel wajib diisi.',
        ]);

        $this->participantIdentifier = trim($this->studentName) . ' (' . trim($this->studentClass) . ')';
        $this->hasStarted = true;
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
