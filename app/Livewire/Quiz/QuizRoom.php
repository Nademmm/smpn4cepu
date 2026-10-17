<?php

namespace App\Livewire\Quiz;

use App\Models\QuestionBank;
use App\Models\User;
use App\Services\QuizAssessmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Role;

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

    // Autentikasi Siswa
    public string $authTab = 'login'; // 'login' | 'register'
    public string $loginEmail = '';
    public string $loginPassword = '';
    public string $regName = '';
    public string $regEmail = '';
    public string $regClass = '';
    public string $regPassword = '';
    public string $authErrorMessage = '';
    public string $authSuccessMessage = '';

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

    public function setAuthTab(string $tab): void
    {
        $this->authTab = $tab;
        $this->authErrorMessage = '';
        $this->authSuccessMessage = '';
    }

    public function loginStudent(): void
    {
        $this->authErrorMessage = '';
        $this->authSuccessMessage = '';

        $throttleKey = 'quiz-login:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->authErrorMessage = 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam ' . RateLimiter::availableIn($throttleKey) . ' detik.';
            return;
        }

        $this->validate([
            'loginEmail' => 'required|string',
            'loginPassword' => 'required|string',
        ], [
            'loginEmail.required' => 'Email atau NISN wajib diisi.',
            'loginPassword.required' => 'Kata sandi wajib diisi.',
        ]);

        $input = trim($this->loginEmail);
        $user = User::where('email', $input)->orWhere('nisn', $input)->first();

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $this->loginPassword], true)) {
            RateLimiter::clear($throttleKey);
            session()->regenerate();
            $this->studentName = Auth::user()->name;
            $this->studentClass = $this->normalizeCohort((string) Auth::user()->student_class, $this->gradeLevel);
            $this->authSuccessMessage = 'Berhasil masuk sebagai ' . Auth::user()->name . '.';
            $this->loginEmail = '';
            $this->loginPassword = '';
            return;
        }

        RateLimiter::hit($throttleKey, 60);
        $this->authErrorMessage = 'Email / NISN atau kata sandi tidak cocok. Silakan periksa kembali.';
    }

    public function registerStudent(): void
    {
        $this->authErrorMessage = '';
        $this->authSuccessMessage = '';

        $throttleKey = 'quiz-register:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->authErrorMessage = 'Terlalu banyak percobaan pendaftaran. Silakan coba lagi dalam ' . RateLimiter::availableIn($throttleKey) . ' detik.';
            return;
        }

        $this->validate([
            'regName' => 'required|string|min:3|max:100',
            'regEmail' => 'required|email|max:150|unique:users,email',
            'regClass' => 'required|in:Kelas 7,Kelas 8,Kelas 9',
            'regPassword' => 'required|string|min:6',
        ], [
            'regName.required' => 'Nama lengkap siswa wajib diisi.',
            'regEmail.required' => 'Alamat email aktif wajib diisi.',
            'regEmail.email' => 'Format email tidak valid.',
            'regEmail.unique' => 'Email ini sudah terdaftar. Silakan gunakan tab Masuk.',
            'regClass.required' => 'Tingkat angkatan kelas wajib dipilih.',
            'regClass.in' => 'Pilih angkatan Kelas 7, Kelas 8, atau Kelas 9.',
            'regPassword.required' => 'Kata sandi wajib diisi.',
            'regPassword.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        RateLimiter::hit($throttleKey, 300);

        $cohort = $this->normalizeCohort($this->regClass, $this->gradeLevel);

        $user = User::create([
            'name' => trim($this->regName),
            'email' => strtolower(trim($this->regEmail)),
            'student_class' => $cohort,
            'password' => Hash::make($this->regPassword),
        ]);

        Role::firstOrCreate(['name' => 'siswa']);
        $user->assignRole('siswa');
        Auth::login($user, true);
        session()->regenerate();

        $this->studentName = $user->name;
        $this->studentClass = $cohort;
        $this->authSuccessMessage = 'Akun siswa berhasil dibuat dan langsung terhubung!';
    }

    public function logoutStudent(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->studentName = '';
        $this->studentClass = '';
        $this->quizPassword = '';
        $this->questions = [];
        $this->hasStarted = false;
        $this->authSuccessMessage = 'Anda telah keluar. Silakan masuk kembali untuk memulai kuis.';
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
        if ($user && $user->student_class !== $cohort) {
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
