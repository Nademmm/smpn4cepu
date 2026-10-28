<?php

namespace App\Livewire\Quiz;

use App\Models\QuestionBank;
use App\Services\QuizAssessmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class QuizRoom extends Component
{
    #[Locked]
    public int $bankId;

    public ?QuestionBank $bank = null;

    #[Locked]
    public string $bankTitle = '';

    #[Locked]
    public string $subjectName = '';

    #[Locked]
    public int $gradeLevel = 7;

    #[Locked]
    public int $durationMinutes = 30;

    #[Locked]
    public int $passingGrade = 75;

    #[Locked]
    public int $questionsCount = 0;

    #[Locked]
    public bool $requiresPassword = false;

    public string $studentName = '';
    public string $studentClass = '';
    public string $quizPassword = '';

    #[Locked]
    public bool $hasStarted = false;

    #[Locked]
    public string $participantIdentifier = '';

    #[Locked]
    public array $questions = [];

    public array $answers = []; // [question_id => selected_option_id]

    #[Locked]
    public int $durationSeconds = 1800; // Default 30 menit

    #[Locked]
    public ?Carbon $startedAt = null;

    #[Locked]
    public bool $isFinished = false;

    #[Locked]
    public ?array $resultSummary = null;

    public string $authErrorMessage = '';

    public function mount(int $bankId): void
    {
        $this->bankId = $bankId;
        $this->bank = QuestionBank::with('subject')->withCount('questions')->where('is_active', true)->find($bankId);

        if (! $this->bank) {
            abort(404);
        }

        $this->bankTitle = (string) $this->bank->title;
        $this->subjectName = (string) ($this->bank->subject?->name ?? 'Mata Pelajaran');
        $this->gradeLevel = (int) ($this->bank->grade_level ?: 7);
        $this->durationMinutes = (int) ($this->bank->duration_minutes ?: 30);
        $this->passingGrade = (int) ($this->bank->passing_grade ?? 75);
        $this->questionsCount = (int) ($this->bank->questions_count ?? $this->bank->questions()->count());
        $this->requiresPassword = $this->bank->requiresPassword();

        // Zero Pre-Start Question Leakage: Jangan muat butir soal ke state Livewire sebelum password diverifikasi di startQuiz()
        $this->questions = [];

        if ($this->bank->duration_minutes > 0) {
            $this->durationSeconds = $this->bank->duration_minutes * 60;
        }

        if (Auth::check()) {
            $this->studentName = Auth::user()->name;
            $this->studentClass = $this->normalizeCohort((string) Auth::user()->student_class, $this->gradeLevel);
        } else {
            $this->studentClass = 'Kelas ' . $this->gradeLevel;
        }
    }

    protected function normalizeCohort(string $rawClass, int $fallbackGrade = 7): string
    {
        $trimmed = trim($rawClass);
        if (preg_match('/7|VII/i', $trimmed)) {
            return 'Kelas 7';
        }
        if (preg_match('/8|VIII/i', $trimmed)) {
            return 'Kelas 8';
        }
        if (preg_match('/9|IX/i', $trimmed)) {
            return 'Kelas 9';
        }

        return in_array($fallbackGrade, [7, 8, 9], true) ? 'Kelas ' . $fallbackGrade : 'Kelas 7';
    }

    public function startQuiz(QuizAssessmentService $assessmentService): void
    {
        $this->authErrorMessage = '';

        if (! Auth::check()) {
            $this->authErrorMessage = 'Untuk menyimpan data dan hasil nilai pengerjaan, Anda wajib masuk terlebih dahulu.';
            return;
        }

        $throttleKey = 'quiz-token:' . Auth::id() . ':' . $this->bankId . ':' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('quizPassword', 'Terlalu banyak percobaan token salah. Silakan tunggu ' . RateLimiter::availableIn($throttleKey) . ' detik.');
            return;
        }

        $rules = [
            'studentClass' => 'required|in:Kelas 7,Kelas 8,Kelas 9',
        ];
        $messages = [
            'studentClass.required' => 'Tingkat angkatan kelas wajib dipilih.',
            'studentClass.in' => 'Pilih tingkat angkatan yang valid (Kelas 7, Kelas 8, atau Kelas 9).',
        ];

        $bank = QuestionBank::where('is_active', true)->findOrFail($this->bankId);

        if ($bank->requiresPassword()) {
            $rules['quizPassword'] = 'required|string|min:2|max:50';
            $messages['quizPassword.required'] = 'Password / Token akses soal wajib diisi.';
        }

        $this->validate($rules, $messages);

        if ($bank->requiresPassword() && ! $bank->verifyPassword($this->quizPassword)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('quizPassword', 'Password / Token soal tidak sesuai. Silakan minta token yang benar kepada Guru pengampu.');
            return;
        }

        RateLimiter::clear($throttleKey);

        // Muat soal HANYA setelah password soal terverifikasi benar (Zero Pre-Start Leakage)
        $loadedQuestions = $assessmentService->getQuestionsForQuiz($this->bankId)->toArray();
        if (empty($loadedQuestions)) {
            $this->authErrorMessage = 'Paket soal ini belum memiliki butir pertanyaan aktif.';
            return;
        }

        $this->questions = $loadedQuestions;
        $this->questionsCount = count($loadedQuestions);

        $cohort = $this->normalizeCohort($this->studentClass, $this->gradeLevel);
        $this->studentClass = $cohort;

        $user = Auth::user();
        if ($user && $this->normalizeCohort((string) $user->student_class, $this->gradeLevel) !== $cohort) {
            $user->update(['student_class' => $cohort]);
        }

        $this->studentName = $user->name;
        $this->participantIdentifier = trim($this->studentName) . ' (' . $cohort . ')';
        $this->hasStarted = true;
        $this->startedAt = now();
    }

    public function submitQuiz(QuizAssessmentService $assessmentService): void
    {
        if (! Auth::check() || ! $this->hasStarted || $this->isFinished) {
            return;
        }

        $userId = Auth::id();

        try {
            $startedAtTime = $this->startedAt ?? now();
            $elapsedSeconds = max(1, (int) $startedAtTime->diffInSeconds(now()));

            $attempt = $assessmentService->submitAttempt(
                $this->bankId,
                $this->participantIdentifier ?: ($this->studentName . ' (' . $this->studentClass . ')'),
                $this->answers,
                $startedAtTime,
                $userId
            );

            $summary = $assessmentService->getReviewDetails($attempt->id);
            $summary['duration_spent_seconds'] = $elapsedSeconds;

            $this->resultSummary = $summary;
            $this->isFinished = true;
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan hasil kuis: ' . $e->getMessage(), [
                'bank_id' => $this->bankId,
                'user_id' => $userId,
            ]);
            $this->authErrorMessage = 'Terjadi kendala saat memproses hasil kuis. Silakan coba lagi.';
        }
    }

    public function render()
    {
        return view('livewire.quiz.quiz-room');
    }
}
