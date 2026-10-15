<?php

namespace App\Livewire\Quiz;

use App\Models\QuestionBank;
use App\Models\User;
use App\Services\QuizAssessmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $this->questions = $assessmentService->getQuestionsForQuiz($bankId)->toArray();

        $this->bank = QuestionBank::with('subject')->find($bankId);
        if ($this->bank && $this->bank->duration_minutes > 0) {
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

        $this->validate([
            'loginEmail' => 'required|string',
            'loginPassword' => 'required|string',
        ], [
            'loginEmail.required' => 'Email atau NISN wajib diisi.',
            'loginPassword.required' => 'Kata sandi wajib diisi.',
        ]);

        $credentials = ['email' => trim($this->loginEmail), 'password' => $this->loginPassword];

        if (Auth::attempt($credentials, true)) {
            session()->regenerate();
            $this->studentName = Auth::user()->name;
            $this->authSuccessMessage = 'Berhasil masuk sebagai ' . Auth::user()->name . '.';
            $this->loginEmail = '';
            $this->loginPassword = '';
            return;
        }

        $this->authErrorMessage = 'Email / NISN atau kata sandi tidak cocok. Silakan periksa kembali.';
    }

    public function registerStudent(): void
    {
        $this->authErrorMessage = '';
        $this->authSuccessMessage = '';

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

        $user = User::create([
            'name' => trim($this->regName),
            'email' => strtolower(trim($this->regEmail)),
            'password' => Hash::make($this->regPassword),
        ]);

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
        $userId = Auth::id();

        $attempt = $assessmentService->submitAttempt(
            $this->bankId,
            $this->participantIdentifier ?: ($this->studentName . ' (' . $this->studentClass . ')'),
            $this->answers,
            $this->startedAt ?? now(),
            $userId
        );

        $this->resultSummary = $assessmentService->getReviewDetails($attempt->id);
        $this->isFinished = true;
    }

    public function render()
    {
        return view('livewire.quiz.quiz-room');
    }
}
