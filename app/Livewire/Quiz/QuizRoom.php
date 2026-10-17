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
    public int $durationMinutes = 30;

    #[Locked]
    public int $passingGrade = 75;

    public string $studentName = '';
    public string $studentClass = '';

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

    public function mount(int $bankId, QuizAssessmentService $assessmentService): void
    {
        $this->bankId = $bankId;
        $this->bank = QuestionBank::with('subject')->where('is_active', true)->find($bankId);

        if (!$this->bank) {
            abort(404);
        }

        $this->bankTitle = (string) $this->bank->title;
        $this->subjectName = (string) ($this->bank->subject?->name ?? 'Mata Pelajaran');
        $this->durationMinutes = (int) ($this->bank->duration_minutes ?: 30);
        $this->passingGrade = (int) ($this->bank->passing_grade ?? 75);

        $this->questions = $assessmentService->getQuestionsForQuiz($bankId)->toArray();

        if ($this->bank->duration_minutes > 0) {
            $this->durationSeconds = $this->bank->duration_minutes * 60;
        }

        if (Auth::check()) {
            $this->studentName = Auth::user()->name;
            if (!empty(Auth::user()->student_class)) {
                $this->studentClass = Auth::user()->student_class;
            }
        }
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
            if (!empty(Auth::user()->student_class)) {
                $this->studentClass = Auth::user()->student_class;
            }
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
            'regClass' => 'required|string|min:2|max:50',
            'regPassword' => 'required|string|min:6',
        ], [
            'regName.required' => 'Nama lengkap siswa wajib diisi.',
            'regEmail.required' => 'Alamat email aktif wajib diisi.',
            'regEmail.email' => 'Format email tidak valid.',
            'regEmail.unique' => 'Email ini sudah terdaftar. Silakan gunakan tab Masuk.',
            'regClass.required' => 'Kelas / rombel wajib diisi.',
            'regPassword.required' => 'Kata sandi wajib diisi.',
            'regPassword.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        RateLimiter::hit($throttleKey, 300);

        $user = User::create([
            'name' => trim($this->regName),
            'email' => strtolower(trim($this->regEmail)),
            'student_class' => trim($this->regClass),
            'password' => Hash::make($this->regPassword),
        ]);

        Role::firstOrCreate(['name' => 'siswa']);
        $user->assignRole('siswa');
        Auth::login($user, true);
        session()->regenerate();

        $this->studentName = $user->name;
        $this->studentClass = trim($this->regClass);
        $this->authSuccessMessage = 'Akun siswa berhasil dibuat dan langsung terhubung!';
    }

    public function logoutStudent(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->studentName = '';
        $this->studentClass = '';
        $this->hasStarted = false;
        $this->authSuccessMessage = 'Anda telah keluar. Silakan masuk kembali untuk memulai kuis.';
    }

    public function startQuiz(): void
    {
        if (!Auth::check()) {
            $this->authErrorMessage = 'Untuk menyimpan data dan hasil nilai pengerjaan, Anda wajib masuk terlebih dahulu.';
            return;
        }

        $this->validate([
            'studentClass' => 'required|string|min:2|max:50',
        ], [
            'studentClass.required' => 'Kelas / rombel wajib diisi.',
        ]);

        $this->studentName = Auth::user()->name;
        $this->participantIdentifier = trim($this->studentName) . ' (' . trim($this->studentClass) . ')';
        $this->hasStarted = true;
        $this->startedAt = now();
    }

    public function submitQuiz(QuizAssessmentService $assessmentService): void
    {
        if (!Auth::check() || !$this->hasStarted || $this->isFinished) {
            return;
        }

        $userId = Auth::id();

        try {
            $attempt = $assessmentService->submitAttempt(
                $this->bankId,
                $this->participantIdentifier ?: ($this->studentName . ' (' . $this->studentClass . ')'),
                $this->answers,
                $this->startedAt ?? now(),
                $userId
            );

            $this->resultSummary = $assessmentService->getReviewDetails($attempt->id);
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
