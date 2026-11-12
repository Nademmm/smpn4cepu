<?php

namespace Tests\Feature;

use App\Enums\BookCategory;
use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookReservation;
use App\Models\QuestionBank;
use App\Models\QuizAttempt;
use App\Models\StudentAcademicHistory;
use App\Models\Subject;
use App\Models\User;
use App\Services\AcademicYearService;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicYearTransitionTest extends TestCase
{
    protected User $admin;
    protected AcademicYearService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'siswa']);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@smpn4cepu.sch.id'],
            [
                'name' => 'Admin Penguji Transisi',
                'password' => bcrypt('password123'),
            ]
        );
        $this->admin->assignRole('super_admin');

        $this->service = app(AcademicYearService::class);
    }

    public function test_academic_years_have_statuses_and_active_year_is_retrievable(): void
    {
        $activeYear = $this->service->getActiveYear();
        $this->assertNotNull($activeYear);
        $this->assertEquals('aktif', $activeYear->status);
        $this->assertEquals('2026/2027', $activeYear->name);

        // Buat tahun ajaran baru dengan status persiapan
        $planningYear = $this->service->createPlanningYear([
            'name' => '2027/2028',
            'semester' => 'ganjil',
            'start_date' => '2027-07-15',
            'end_date' => '2028-06-30',
        ]);

        $this->assertEquals('persiapan', $planningYear->status);
        $this->assertTrue($planningYear->isPlanning());
        $this->assertTrue($activeYear->isActive());
    }

    public function test_bulk_student_promotion_with_per_student_exceptions(): void
    {
        $activeYear = $this->service->getActiveYear();
        $initial7ACount = User::activeStudents()->where('student_class', '7A')->count();

        // Buat 4 siswa di kelas 7A
        $s1 = User::factory()->create(['name' => 'Siswa Naik 1', 'student_class' => '7A', 'gender' => 'L', 'status' => 'aktif']);
        $s2 = User::factory()->create(['name' => 'Siswa Naik 2', 'student_class' => '7A', 'gender' => 'P', 'status' => 'aktif']);
        $s3 = User::factory()->create(['name' => 'Siswa Tinggal', 'student_class' => '7A', 'gender' => 'L', 'status' => 'aktif']);
        $s4 = User::factory()->create(['name' => 'Siswa Pindah', 'student_class' => '7A', 'gender' => 'P', 'status' => 'aktif']);

        // Catat riwayat di tahun aktif
        foreach ([$s1, $s2, $s3, $s4] as $s) {
            StudentAcademicHistory::create([
                'academic_year_id' => $activeYear->id,
                'student_id' => $s->id,
                'student_class' => '7A',
                'grade_level' => 7,
                'status' => 'aktif',
            ]);
        }

        $planningYear = $this->service->createPlanningYear(['name' => '2027/2028']);

        // 7A dinaikkan secara massal ke 8B, dengan pengecualian: s3 tinggal kelas di 7A, s4 pindah sekolah
        $result = $this->service->saveClassPromotionDraft(
            $planningYear->id,
            '7A',
            '8B', // Default 7A -> 8B
            [
                $s3->id => ['action' => 'tinggal_kelas', 'class' => '7A', 'notes' => 'Tinggal kelas'],
                $s4->id => ['action' => 'mutasi_keluar', 'notes' => 'Pindah ke Surabaya'],
            ]
        );

        $this->assertTrue($result['success']);
        $this->assertEquals($initial7ACount + 4, $result['total_students']);

        // Verifikasi draf pemetaan
        $h1 = StudentAcademicHistory::where('academic_year_id', $planningYear->id)->where('student_id', $s1->id)->first();
        $this->assertEquals('8B', $h1->student_class);
        $this->assertEquals('persiapan', $h1->status);

        $h3 = StudentAcademicHistory::where('academic_year_id', $planningYear->id)->where('student_id', $s3->id)->first();
        $this->assertEquals('7A', $h3->student_class);
        $this->assertEquals('tinggal_kelas', $h3->status);

        $h4 = StudentAcademicHistory::where('academic_year_id', $planningYear->id)->where('student_id', $s4->id)->first();
        $this->assertNull($h4->student_class);
        $this->assertEquals('mutasi_keluar', $h4->status);
    }

    public function test_old_quiz_attempt_class_is_preserved_after_promotion(): void
    {
        $activeYear = $this->service->getActiveYear();
        $student = User::factory()->create(['name' => 'Budi Santoso', 'student_class' => '7A', 'status' => 'aktif']);

        $subject = Subject::firstOrCreate(['code' => 'MTK_TEST'], ['name' => 'Matematika Uji']);
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'title' => 'Kuis Himpunan 7A',
            'grade_level' => 7,
            'passing_grade' => 75,
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        // Siswa mengerjakan kuis saat berada di 7A pada tahun ajaran 2026/2027
        $attempt = QuizAttempt::create([
            'bank_id' => $bank->id,
            'user_id' => $student->id,
            'academic_year_id' => $activeYear->id,
            'student_class' => '7A',
            'participant_identifier' => $student->name,
            'total_questions' => 10,
            'correct_answers' => 9,
            'final_score' => 90.00,
            'is_passed' => true,
            'started_at' => Carbon::now()->subMinutes(15),
            'completed_at' => Carbon::now(),
        ]);

        $this->assertEquals('7A', $attempt->student_class);
        $this->assertEquals($activeYear->id, $attempt->academic_year_id);

        // Buat tahun 2027/2028 dan naikkan siswa ke 8B
        $planningYear = $this->service->createPlanningYear(['name' => '2027/2028']);
        $this->service->saveClassPromotionDraft($planningYear->id, '7A', '8B');
        $this->service->activateYear($planningYear->id);

        // Setelah aktivasi, kelas akun siswa berubah menjadi 8B
        $student->refresh();
        $this->assertEquals('8B', $student->student_class);

        // TETAPI nilai lama kuis tetap tercatat di 7A dan tahun ajaran 2026/2027
        $attempt->refresh();
        $this->assertEquals('7A', $attempt->student_class);
        $this->assertEquals($activeYear->id, $attempt->academic_year_id);
    }

    public function test_preview_year_transition_shows_summary_unassigned_and_duplicates(): void
    {
        $activeYear = $this->service->getActiveYear();
        $initial7ACount = User::activeStudents()->where('student_class', '7A')->count();
        $s1 = User::factory()->create(['student_class' => '7A', 'gender' => 'L', 'status' => 'aktif']);
        $s2 = User::factory()->create(['student_class' => '7A', 'gender' => 'P', 'status' => 'aktif']);
        $sUnassigned = User::factory()->create(['student_class' => '7B', 'gender' => 'L', 'status' => 'aktif']);

        $planningYear = $this->service->createPlanningYear(['name' => '2027/2028']);

        // Baru memetakan 7A ke 8A, 7B belum dipetakan
        $this->service->saveClassPromotionDraft($planningYear->id, '7A', '8A');

        $preview = $this->service->previewYearTransition($planningYear->id);

        $this->assertEquals($initial7ACount + 2, $preview['total_mapped']);
        $this->assertGreaterThanOrEqual(1, $preview['unassigned_count']);
        $this->assertEquals(0, $preview['duplicates_count']);

        // Cek rekap rombel baru
        $this->assertNotEmpty($preview['rombel_summary']);
        $rombel8A = collect($preview['rombel_summary'])->firstWhere('class_name', '8A');
        $this->assertNotNull($rombel8A);
        $this->assertEquals($initial7ACount + 2, $rombel8A['total']);
    }

    public function test_graduated_students_become_alumni_and_library_loans_remain_safe(): void
    {
        $activeYear = $this->service->getActiveYear();

        $gradStudent = User::factory()->create([
            'name' => 'Siswa Kelas Sembilan',
            'student_class' => '9A',
            'status' => 'aktif',
        ]);

        $book = Book::create([
            'title' => 'Buku Ensiklopedia Sains',
            'slug' => 'buku-ensiklopedia-sains-' . uniqid(),
            'category' => BookCategory::LITERASI_UMUM->value,
            'author' => 'Dr. Bambang',
            'physical_stock' => 5,
            'is_active' => true,
        ]);

        // Siswa meminjam buku fisik perpus yang belum dikembalikan
        $loan = BookReservation::create([
            'user_id' => $gradStudent->id,
            'book_id' => $book->id,
            'borrower_name' => $gradStudent->name,
            'borrower_nisn' => '9999999999',
            'borrower_class' => $gradStudent->student_class,
            'reservation_code' => 'RES-TEST-999',
            'status' => BookReservation::STATUS_BORROWED,
            'pickup_date' => now(),
            'borrowed_at' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $planningYear = $this->service->createPlanningYear(['name' => '2027/2028']);

        // Siswa 9A diarahkan untuk lulus
        $this->service->saveClassPromotionDraft($planningYear->id, '9A', 'lulus');

        // Aktivasi tahun ajaran baru
        $this->service->activateYear($planningYear->id);

        // Siswa sekarang berstatus alumni, BUKAN dihapus
        $gradStudent->refresh();
        $this->assertEquals('alumni', $gradStudent->status);
        $this->assertNull($gradStudent->student_class);

        // Akun tetap ada dan pinjaman buku perpustakaan TETAP UTUH & aman
        $loan->refresh();
        $this->assertNotNull($loan);
        $this->assertEquals(BookReservation::STATUS_BORROWED, $loan->status);
        $this->assertEquals($gradStudent->id, $loan->user_id);
    }
}

