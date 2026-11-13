<?php

namespace Tests\Feature;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use App\Filament\Resources\QuizAttempts\QuizAttemptResource;
use App\Filament\Resources\Students\RelationManagers\AcademicHistoriesRelationManager;
use App\Filament\Resources\Students\StudentResource;
use App\Livewire\Pilketos\LiveCount;
use App\Livewire\Pilketos\VotingBooth;
use App\Livewire\Quiz\QuizRoom;
use App\Models\AcademicYear;
use App\Models\ElectionCandidate;
use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\BackupRecoveryService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class OptimizationVerificationTest extends TestCase
{
    public function test_guru_only_sees_assigned_subject_materials_question_banks_and_quiz_attempts(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->firstOrFail();

        $subjectA = Subject::firstOrCreate(
            ['code' => 'UJI-A-' . uniqid()],
            ['name' => 'Matematika Uji Scope A']
        );
        $subjectB = Subject::firstOrCreate(
            ['code' => 'UJI-B-' . uniqid()],
            ['name' => 'Bahasa Uji Scope B']
        );

        $guruA = User::factory()->create([
            'name' => 'Guru Scope A',
            'email' => 'guru.scope.a.' . uniqid() . '@smpn4cepu.sch.id',
        ]);
        $guruA->assignRole('guru');
        $guruA->subjects()->sync([$subjectA->id]);

        $materialA = LearningMaterial::create([
            'subject_id' => $subjectA->id,
            'grade_level' => 7,
            'title' => 'Materi Mapel A',
            'slug' => 'materi-mapel-a-' . uniqid(),
            'summary' => 'Ringkasan A',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        $materialB = LearningMaterial::create([
            'subject_id' => $subjectB->id,
            'grade_level' => 7,
            'title' => 'Materi Mapel B',
            'slug' => 'materi-mapel-b-' . uniqid(),
            'summary' => 'Ringkasan B',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $bankA = QuestionBank::create([
            'subject_id' => $subjectA->id,
            'grade_level' => 7,
            'title' => 'Kuis Mapel A',
            'duration_minutes' => 30,
            'passing_grade' => 75,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        $bankB = QuestionBank::create([
            'subject_id' => $subjectB->id,
            'grade_level' => 7,
            'title' => 'Kuis Mapel B',
            'duration_minutes' => 30,
            'passing_grade' => 75,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $attemptA = QuizAttempt::create([
            'bank_id' => $bankA->id,
            'participant_identifier' => 'Siswa A (Kelas 7)',
            'total_questions' => 10,
            'correct_answers' => 8,
            'final_score' => 80,
            'is_passed' => true,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);
        $attemptB = QuizAttempt::create([
            'bank_id' => $bankB->id,
            'participant_identifier' => 'Siswa B (Kelas 7)',
            'total_questions' => 10,
            'correct_answers' => 7,
            'final_score' => 70,
            'is_passed' => false,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);

        $this->actingAs($guruA);

        $visibleMaterials = LearningMaterialResource::getEloquentQuery()->pluck('id')->all();
        $visibleBanks = QuestionBankResource::getEloquentQuery()->pluck('id')->all();
        $visibleAttempts = QuizAttemptResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($materialA->id, $visibleMaterials);
        $this->assertNotContains($materialB->id, $visibleMaterials);

        $this->assertContains($bankA->id, $visibleBanks);
        $this->assertNotContains($bankB->id, $visibleBanks);

        $this->assertContains($attemptA->id, $visibleAttempts);
        $this->assertNotContains($attemptB->id, $visibleAttempts);
    }

    public function test_archived_pilketos_candidates_are_excluded_from_active_booth_and_live_count(): void
    {
        Cache::forget('pilketos_public_live_count');
        Cache::forget('pilketos_admin_chart_data');

        $activeCandidate = ElectionCandidate::create([
            'candidate_number' => 91,
            'candidate_name' => 'Paslon Aktif Uji',
            'vice_candidate_name' => 'Wakil Aktif Uji',
            'vision' => 'Visi aktif',
            'mission' => 'Misi aktif',
            'period_year' => '2026/2027',
            'status' => 'aktif',
        ]);

        $archivedCandidate = ElectionCandidate::create([
            'candidate_number' => 92,
            'candidate_name' => 'Paslon Arsip Lama',
            'vice_candidate_name' => 'Wakil Arsip Lama',
            'vision' => 'Visi lama',
            'mission' => 'Misi lama',
            'period_year' => '2025/2026',
            'status' => 'diarsipkan',
        ]);

        $activeIds = ElectionCandidate::active()->pluck('id')->all();
        $this->assertContains($activeCandidate->id, $activeIds);
        $this->assertNotContains($archivedCandidate->id, $activeIds);

        Livewire::test(VotingBooth::class)
            ->assertSee('Paslon Aktif Uji')
            ->assertDontSee('Paslon Arsip Lama')
            ->set('selectedCandidateId', $archivedCandidate->id)
            ->call('submitVote')
            ->assertSet('hasVoted', false)
            ->assertSee('Silakan pilih salah satu kandidat aktif terlebih dahulu.');

        Livewire::test(LiveCount::class)
            ->assertSee('Paslon Aktif Uji')
            ->assertDontSee('Paslon Arsip Lama');
    }

    public function test_quiz_room_preserves_student_official_rombel_on_start(): void
    {
        $student = User::factory()->create([
            'name' => 'Siswa Rombel 7A',
            'email' => 'siswa.rombel.7a.' . uniqid() . '@smpn4cepu.sch.id',
            'nisn' => '0099' . rand(100000, 999999),
            'student_class' => '7A',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
        ]);
        $student->assignRole('siswa');

        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->firstOrFail();
        $subject = Subject::firstOrFail();
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'grade_level' => 7,
            'title' => 'Latihan Uji Rombel',
            'duration_minutes' => 15,
            'passing_grade' => 70,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        $question = Question::create([
            'bank_id' => $bank->id,
            'question_text' => 'Berapa 2 + 2?',
            'order_number' => 1,
        ]);
        QuestionOption::create([
            'question_id' => $question->id,
            'option_label' => 'A',
            'option_text' => '4',
            'is_correct' => true,
        ]);

        Livewire::actingAs($student)
            ->test(QuizRoom::class, ['bankId' => $bank->id])
            ->set('studentClass', 'Kelas 8')
            ->call('startQuiz')
            ->assertSet('hasStarted', true);

        $this->assertSame('7A', $student->fresh()->student_class);
    }

    public function test_backup_overview_reports_failed_latest_attempt_and_last_success(): void
    {
        /** @var BackupRecoveryService $service */
        $service = app(BackupRecoveryService::class);
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->firstOrFail();

        $successBackup = $service->createBackup($admin);
        $this->assertEquals('berhasil', $successBackup->status);

        $failedBackup = SystemBackup::create([
            'filename' => 'backup_failed_test.zip',
            'disk' => 'local',
            'filepath' => 'backups/backup_failed_test.zip',
            'size_bytes' => 0,
            'scope' => 'Database SQLite + File Unggahan (PDF & Gambar)',
            'status' => 'gagal',
            'test_notes' => 'Simulasi kegagalan disk',
            'created_by' => $admin->id,
            'created_at' => now()->addMinute(),
            'updated_at' => now()->addMinute(),
        ]);

        $overview = $service->getBackupOverview();

        $this->assertFalse($overview['is_latest_success']);
        $this->assertSame('Gagal', $overview['status']);
        $this->assertNotSame('Belum pernah dilakukan', $overview['last_backup_time']);

        $failedBackup->delete();
        \Illuminate\Support\Facades\Storage::disk($successBackup->disk)->delete($successBackup->filepath);
    }

    public function test_console_schedule_registers_daily_backup_and_trash_pruning(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $backupEvent = $events->first(fn ($event) => str_contains((string) $event->command, 'system:backup'));
        $pruneEvent = $events->first(fn ($event) => str_contains((string) $event->command, 'system:prune-trash'));

        $this->assertNotNull($backupEvent, 'Perintah system:backup harus terdaftar di scheduler.');
        $this->assertNotNull($pruneEvent, 'Perintah system:prune-trash harus terdaftar di scheduler.');
        $this->assertSame('0 2 * * *', $backupEvent->expression);
        $this->assertSame('0 3 * * *', $pruneEvent->expression);
    }

    public function test_student_resource_includes_academic_histories_relation_manager(): void
    {
        $relations = StudentResource::getRelations();
        $this->assertContains(AcademicHistoriesRelationManager::class, $relations);
    }
}
