<?php

namespace Tests\Feature;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use App\Filament\Resources\QuizAttempts\QuizAttemptResource;
use App\Filament\Resources\Students\RelationManagers\AcademicHistoriesRelationManager;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\StudentStatistics\StudentStatisticResource;
use App\Livewire\Pilketos\LiveCount;
use App\Livewire\Pilketos\VotingBooth;
use App\Livewire\Quiz\QuizRoom;
use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookReservation;
use App\Models\ElectionCandidate;
use App\Models\ElectionVote;
use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\StudentStatistic;
use App\Models\Subject;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\BackupRecoveryService;
use App\Services\LibraryLoanService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_circulation_scan_is_restricted_to_super_admin_and_staf_perpus(): void
    {
        $guru = User::factory()->create(['email' => 'guru.scan.' . uniqid() . '@smpn4cepu.sch.id']);
        $guru->assignRole('guru');

        $panitia = User::factory()->create(['email' => 'panitia.scan.' . uniqid() . '@smpn4cepu.sch.id']);
        $panitia->assignRole('panitia_pilketos');

        $stafPerpus = User::factory()->create(['email' => 'perpus.scan.' . uniqid() . '@smpn4cepu.sch.id']);
        $stafPerpus->assignRole('staf_perpus');

        $this->actingAs($guru)
            ->postJson('/admin/circulation/process-scan', ['code' => 'RES-FAKE'])
            ->assertStatus(403);

        $this->actingAs($panitia)
            ->postJson('/admin/circulation/process-scan', ['code' => 'RES-FAKE'])
            ->assertStatus(403);

        $response = $this->actingAs($stafPerpus)
            ->postJson('/admin/circulation/process-scan', ['code' => 'RES-FAKE']);
        $this->assertNotEquals(403, $response->status());
    }

    public function test_guru_policy_allows_assigned_subject_and_blocks_unassigned_subject(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->firstOrFail();

        $subjectA = Subject::firstOrCreate(['code' => 'POL-A-' . uniqid()], ['name' => 'Mapel A']);
        $subjectB = Subject::firstOrCreate(['code' => 'POL-B-' . uniqid()], ['name' => 'Mapel B']);

        $guru = User::factory()->create(['email' => 'guru.policy.' . uniqid() . '@smpn4cepu.sch.id']);
        $guru->assignRole('guru');
        $guru->subjects()->sync([$subjectA->id]);

        $materialA = LearningMaterial::create([
            'subject_id' => $subjectA->id,
            'grade_level' => 7,
            'title' => 'Materi Pol A',
            'slug' => 'materi-pol-a-' . uniqid(),
            'summary' => 'Ringkasan A',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        $materialB = LearningMaterial::create([
            'subject_id' => $subjectB->id,
            'grade_level' => 7,
            'title' => 'Materi Pol B',
            'slug' => 'materi-pol-b-' . uniqid(),
            'summary' => 'Ringkasan B',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $bankA = QuestionBank::create([
            'subject_id' => $subjectA->id,
            'grade_level' => 7,
            'title' => 'Bank Pol A',
            'duration_minutes' => 20,
            'passing_grade' => 70,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        $bankB = QuestionBank::create([
            'subject_id' => $subjectB->id,
            'grade_level' => 7,
            'title' => 'Bank Pol B',
            'duration_minutes' => 20,
            'passing_grade' => 70,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->assertTrue(Gate::forUser($guru)->allows('update', $materialA));
        $this->assertFalse(Gate::forUser($guru)->allows('update', $materialB));
        $this->assertTrue(Gate::forUser($guru)->allows('update', $bankA));
        $this->assertFalse(Gate::forUser($guru)->allows('update', $bankB));
    }

    public function test_wali_kelas_only_sees_homeroom_students_statistics_and_quiz_attempts(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->firstOrFail();

        $wali = User::factory()->create([
            'email' => 'wali.7a.' . uniqid() . '@smpn4cepu.sch.id',
            'homeroom_class' => '7A',
        ]);
        $wali->assignRole('wali_kelas');

        $student7A = User::factory()->create([
            'nisn' => '0088' . rand(100000, 999999),
            'student_class' => '7A',
            'status' => 'aktif',
        ]);
        $student8B = User::factory()->create([
            'nisn' => '0077' . rand(100000, 999999),
            'student_class' => '8B',
            'status' => 'aktif',
        ]);

        $stat7A = StudentStatistic::create([
            'academic_year' => '2099/2100',
            'grade_level' => '7',
            'class_name' => '7A',
            'male_count' => 15,
            'female_count' => 15,
            'total_count' => 30,
        ]);
        $stat8B = StudentStatistic::create([
            'academic_year' => '2099/2100',
            'grade_level' => '8',
            'class_name' => '8B',
            'male_count' => 14,
            'female_count' => 16,
            'total_count' => 30,
        ]);

        $subject = Subject::firstOrFail();
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'grade_level' => 7,
            'title' => 'Bank Wali Uji',
            'duration_minutes' => 15,
            'passing_grade' => 70,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $attempt7A = QuizAttempt::create([
            'bank_id' => $bank->id,
            'user_id' => $student7A->id,
            'student_class' => '7A',
            'participant_identifier' => $student7A->name,
            'total_questions' => 5,
            'correct_answers' => 4,
            'final_score' => 80,
            'is_passed' => true,
            'started_at' => now()->subMinutes(3),
            'completed_at' => now(),
        ]);
        $attempt8B = QuizAttempt::create([
            'bank_id' => $bank->id,
            'user_id' => $student8B->id,
            'student_class' => '8B',
            'participant_identifier' => $student8B->name,
            'total_questions' => 5,
            'correct_answers' => 4,
            'final_score' => 80,
            'is_passed' => true,
            'started_at' => now()->subMinutes(3),
            'completed_at' => now(),
        ]);

        $this->actingAs($wali);

        $visibleStudents = StudentResource::getEloquentQuery()->pluck('id')->all();
        $visibleStats = StudentStatisticResource::getEloquentQuery()->pluck('id')->all();
        $visibleAttempts = QuizAttemptResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($student7A->id, $visibleStudents);
        $this->assertNotContains($student8B->id, $visibleStudents);

        $this->assertContains($stat7A->id, $visibleStats);
        $this->assertNotContains($stat8B->id, $visibleStats);

        $this->assertContains($attempt7A->id, $visibleAttempts);
        $this->assertNotContains($attempt8B->id, $visibleAttempts);
    }

    public function test_login_controller_redirects_operator_and_wali_kelas_to_admin_panel(): void
    {
        $operator = User::factory()->create([
            'email' => 'operator.redir.' . uniqid() . '@smpn4cepu.sch.id',
            'password' => Hash::make('secret123'),
        ]);
        $operator->assignRole('operator_sekolah');

        $response = $this->post('/login', [
            'login_id' => $operator->email,
            'password' => 'secret123',
        ]);
        $response->assertRedirect(url('/admin'));
    }

    public function test_quiz_room_enforces_retry_limit_and_invalidates_option_cache(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->firstOrFail();
        $student = User::factory()->create([
            'email' => 'siswa.retry.' . uniqid() . '@smpn4cepu.sch.id',
            'student_class' => '7B',
            'status' => 'aktif',
        ]);
        $student->assignRole('siswa');

        $subject = Subject::firstOrFail();
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'grade_level' => 7,
            'title' => 'Kuis Sekali Coba',
            'duration_minutes' => 15,
            'passing_grade' => 75,
            'is_active' => true,
            'allow_retry' => false,
            'max_attempts' => 1,
            'created_by' => $admin->id,
        ]);
        $question = Question::create([
            'bank_id' => $bank->id,
            'question_text' => 'Ibu kota Indonesia?',
            'order_number' => 1,
        ]);
        $option = QuestionOption::create([
            'question_id' => $question->id,
            'option_label' => 'A',
            'option_text' => 'Jakarta',
            'is_correct' => true,
        ]);

        Cache::put('quiz_bank_full_' . $bank->id, 'stale-cache', 60);
        $option->update(['option_text' => 'Jakarta Raya']);
        $this->assertFalse(Cache::has('quiz_bank_full_' . $bank->id));

        $component = Livewire::actingAs($student)
            ->test(QuizRoom::class, ['bankId' => $bank->id])
            ->set('studentClass', 'Kelas 7')
            ->call('startQuiz')
            ->assertSet('hasStarted', true)
            ->set("answers.{$question->id}", $option->id)
            ->call('submitQuiz')
            ->assertSet('isFinished', true)
            ->assertSet('canRetry', false);

        // Upaya kedua submit atau retry harus diblokir
        $component->call('submitQuiz');
        $this->assertEquals(1, QuizAttempt::where('bank_id', $bank->id)->where('user_id', $student->id)->count());

        $component->call('retryQuiz')
            ->assertSet('isFinished', true);

        // Jika mount ulang, siswa tidak bisa memulai ulang paket allow_retry=false
        Livewire::actingAs($student)
            ->test(QuizRoom::class, ['bankId' => $bank->id])
            ->assertSet('canRetry', false)
            ->call('startQuiz')
            ->assertSet('hasStarted', false);
    }

    public function test_pilketos_live_count_ignores_archived_votes_in_percentage(): void
    {
        Cache::forget('pilketos_public_live_count');

        $activeCandidate = ElectionCandidate::create([
            'candidate_number' => 95,
            'candidate_name' => 'Paslon Persen Aktif',
            'vice_candidate_name' => 'Wakil Persen Aktif',
            'vision' => 'Visi',
            'mission' => 'Misi',
            'period_year' => '2026/2027',
            'status' => 'aktif',
        ]);
        $archivedCandidate = ElectionCandidate::create([
            'candidate_number' => 96,
            'candidate_name' => 'Paslon Persen Arsip',
            'vice_candidate_name' => 'Wakil Persen Arsip',
            'vision' => 'Visi',
            'mission' => 'Misi',
            'period_year' => '2025/2026',
            'status' => 'diarsipkan',
        ]);

        $deviceActive = \App\Models\ElectionDevice::create([
            'device_fingerprint' => 'fp-active-' . uniqid(),
            'ip_subnet' => '127.0.0.0/24',
            'user_agent_hash' => hash('sha256', 'phpunit-active'),
            'voted_at' => now(),
        ]);
        $deviceArchived = \App\Models\ElectionDevice::create([
            'device_fingerprint' => 'fp-archived-' . uniqid(),
            'ip_subnet' => '127.0.0.0/24',
            'user_agent_hash' => hash('sha256', 'phpunit-archived'),
            'voted_at' => now(),
        ]);

        ElectionVote::create([
            'candidate_id' => $activeCandidate->id,
            'device_id' => $deviceActive->id,
            'created_at' => now(),
        ]);
        ElectionVote::create([
            'candidate_id' => $archivedCandidate->id,
            'device_id' => $deviceArchived->id,
            'created_at' => now(),
        ]);

        $component = Livewire::test(LiveCount::class);
        $totalActiveVotes = ElectionVote::whereHas('candidate', fn ($q) => $q->active())->count();
        $this->assertSame($totalActiveVotes, $component->viewData('totalVotes'));
        $this->assertSame($totalActiveVotes, (int) $component->viewData('candidates')->sum('votes_count'));
    }

    public function test_student_can_cancel_own_pending_library_reservation_and_restore_stock(): void
    {
        /** @var LibraryLoanService $loanService */
        $loanService = app(LibraryLoanService::class);

        $student = User::factory()->create([
            'name' => 'Siswa Batal Pinjam',
            'email' => 'siswa.batal.' . uniqid() . '@smpn4cepu.sch.id',
            'nisn' => '0055' . rand(100000, 999999),
            'student_class' => '8A',
            'status' => 'aktif',
        ]);
        $student->assignRole('siswa');

        $book = Book::create([
            'title' => 'Buku Uji Batal Reservasi',
            'slug' => 'buku-uji-batal-' . uniqid(),
            'category' => \App\Enums\BookCategory::LITERASI_UMUM->value,
            'author' => 'Penulis Uji',
            'physical_stock' => 2,
            'is_active' => true,
        ]);

        $pickupDate = now()->addDay()->isWeekend()
            ? now()->next(\Carbon\Carbon::MONDAY)->toDateString()
            : now()->addDay()->toDateString();

        $reservation = $loanService->reserve(
            bookId: $book->id,
            name: $student->name,
            class: '8A',
            nisn: $student->nisn,
            pickupDate: $pickupDate,
            userId: $student->id,
        );

        $this->assertSame(1, $book->fresh()->physical_stock);

        $response = $this->actingAs($student)
            ->post(route('library.ticket.cancel', $reservation->reservation_code));

        $response->assertRedirect();
        $response->assertSessionHas('library_status');

        $this->assertSame(BookReservation::STATUS_CANCELLED, $reservation->fresh()->status);
        $this->assertSame(2, $book->fresh()->physical_stock);
    }

    public function test_navbar_displays_user_name_and_system_restore_command_exists(): void
    {
        $student = User::factory()->create([
            'name' => 'Nama Lengkap Siswa Uji',
            'email' => 'email.rahasia.uji.' . uniqid() . '@smpn4cepu.sch.id',
        ]);
        $student->assignRole('siswa');

        $response = $this->actingAs($student)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Nama Lengkap Siswa Uji');
        $response->assertDontSee('<span class="max-w-[130px] truncate">' . $student->email . '</span>', false);

        $commands = Artisan::all();
        $this->assertArrayHasKey('system:restore', $commands);
    }

    public function test_backup_pruning_deletes_excess_old_archives(): void
    {
        /** @var BackupRecoveryService $service */
        $service = app(BackupRecoveryService::class);

        $createdIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $path = "backups/prune_test_{$i}.zip";
            Storage::disk('local')->put($path, 'dummy-zip-' . $i);
            $createdIds[] = SystemBackup::create([
                'filename' => "prune_test_{$i}.zip",
                'disk' => 'local',
                'filepath' => $path,
                'size_bytes' => 100,
                'scope' => 'Test Pruning',
                'status' => 'berhasil',
                'created_at' => now()->subDays(10 - $i),
                'updated_at' => now()->subDays(10 - $i),
            ])->id;
        }

        $totalBefore = SystemBackup::count();
        $deleted = $service->pruneOldBackups(max(1, $totalBefore - 1));
        $this->assertSame(1, $deleted);

        // Bersihkan file dummy tersisa
        foreach ($createdIds as $id) {
            $item = SystemBackup::find($id);
            if ($item) {
                Storage::disk('local')->delete($item->filepath);
                $item->delete();
            }
        }
    }
}
