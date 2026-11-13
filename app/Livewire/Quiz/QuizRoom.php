<?php

namespace App\Livewire\Quiz;

use App\Models\QuestionBank;
use App\Models\QuizAttempt;
use App\Services\QuizAssessmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

    #[Locked]
    public bool $canRetry = true;

    #[Locked]
    public bool $allowRetrySetting = true;

    #[Locked]
    public int $attemptsUsed = 0;

    #[Locked]
    public int $maxAttempts = 0;

    #[Locked]
    public string $attemptToken = '';

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
            $this->restoreProgressFromSession();
        } else {
            $this->studentClass = 'Kelas ' . $this->gradeLevel;
        }

        $this->refreshRetryState($this->bank);
    }

    protected function refreshRetryState(?QuestionBank $bank = null): void
    {
        $bank ??= $this->bank ?? QuestionBank::find($this->bankId);
        $this->allowRetrySetting = $bank ? (bool) ($bank->allow_retry ?? true) : true;
        $this->maxAttempts = $bank ? (int) ($bank->max_attempts ?? 0) : 0;

        if (! Auth::check()) {
            $this->attemptsUsed = 0;
            $this->canRetry = true;
            return;
        }

        $this->attemptsUsed = QuizAttempt::where('bank_id', $this->bankId)
            ->where('user_id', Auth::id())
            ->count();

        if (! $this->allowRetrySetting) {
            $this->canRetry = $this->attemptsUsed === 0;
        } elseif ($this->maxAttempts > 0) {
            $this->canRetry = $this->attemptsUsed < $this->maxAttempts;
        } else {
            $this->canRetry = true;
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
        $this->refreshRetryState($bank);

        if (! $this->canRetry) {
            $this->authErrorMessage = 'Batas maksimal pengerjaan paket soal ini telah tercapai.';
            return;
        }

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
        $this->studentName = $user->name;
        $this->participantIdentifier = trim($this->studentName) . ' (' . $cohort . ')';
        $this->hasStarted = true;
        $this->startedAt = now();
        $this->attemptToken = (string) Str::uuid();
        $this->saveProgressToSession();
    }

    public function updatedAnswers(): void
    {
        $this->saveProgressToSession();
    }

    public function getRemainingSecondsProperty(): int
    {
        if (! $this->hasStarted || ! $this->startedAt) {
            return $this->durationSeconds;
        }

        $elapsed = (int) $this->startedAt->diffInSeconds(now());
        return max(0, $this->durationSeconds - $elapsed);
    }

    public function retryQuiz(): void
    {
        $this->refreshRetryState();
        if (! $this->canRetry) {
            $this->authErrorMessage = 'Pengulangan latihan untuk paket soal ini tidak diizinkan atau batas percobaan telah habis.';
            return;
        }

        $this->clearProgressSession();
        $this->hasStarted = false;
        $this->isFinished = false;
        $this->resultSummary = null;
        $this->answers = [];
        $this->questions = [];
        $this->startedAt = null;
        $this->attemptToken = '';
        $this->quizPassword = '';
        $this->authErrorMessage = '';
    }

    protected function getSessionProgressKey(): string
    {
        return 'active_quiz_progress_' . (Auth::id() ?: 'guest') . '_' . $this->bankId;
    }

    protected function saveProgressToSession(): void
    {
        if (! Auth::check() || ! $this->hasStarted || $this->isFinished) {
            return;
        }

        session()->put($this->getSessionProgressKey(), [
            'bank_id' => $this->bankId,
            'started_at' => $this->startedAt?->toIso8601String(),
            'questions' => $this->questions,
            'questions_count' => $this->questionsCount,
            'answers' => $this->answers,
            'participant_identifier' => $this->participantIdentifier,
            'student_name' => $this->studentName,
            'student_class' => $this->studentClass,
            'duration_seconds' => $this->durationSeconds,
            'attempt_token' => $this->attemptToken,
        ]);
    }

    protected function restoreProgressFromSession(): void
    {
        $key = $this->getSessionProgressKey();
        if (! session()->has($key)) {
            return;
        }

        $data = session()->get($key);
        if (empty($data['questions']) || empty($data['started_at'])) {
            session()->forget($key);
            return;
        }

        $startedAt = Carbon::parse($data['started_at']);
        $durationSeconds = (int) ($data['duration_seconds'] ?? $this->durationSeconds);
        $elapsed = (int) $startedAt->diffInSeconds(now());

        // Jika alokasi waktu sudah habis (dengan toleransi 120 detik), bersihkan sesi
        if ($elapsed > ($durationSeconds + 120)) {
            session()->forget($key);
            return;
        }

        $this->participantIdentifier = (string) ($data['participant_identifier'] ?? '');
        $this->studentName = (string) ($data['student_name'] ?? (Auth::user()?->name ?? ''));
        $this->studentClass = (string) ($data['student_class'] ?? '');
        $this->startedAt = $startedAt;
        $this->durationSeconds = $durationSeconds;
        $this->questions = $data['questions'];
        $this->questionsCount = (int) ($data['questions_count'] ?? count($this->questions));
        $this->answers = $data['answers'] ?? [];
        $this->attemptToken = (string) ($data['attempt_token'] ?? Str::uuid());
        $this->hasStarted = true;
    }

    protected function clearProgressSession(): void
    {
        session()->forget($this->getSessionProgressKey());
    }

    public function submitQuiz(QuizAssessmentService $assessmentService): void
    {
        if (! Auth::check() || ! $this->hasStarted || $this->isFinished) {
            return;
        }

        $userId = Auth::id();
        $token = $this->attemptToken ?: 'default';
        $lock = Cache::lock("quiz_submit_{$userId}_{$this->bankId}_{$token}", 10);

        if (! $lock->get()) {
            return;
        }

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
            $this->refreshRetryState();
            $this->clearProgressSession();
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan hasil kuis: ' . $e->getMessage(), [
                'bank_id' => $this->bankId,
                'user_id' => $userId,
            ]);
            $this->authErrorMessage = 'Terjadi kendala saat memproses hasil kuis. Silakan coba lagi.';
        } finally {
            $lock->release();
        }
    }

    public function render()
    {
        return view('livewire.quiz.quiz-room');
    }
}
