<?php

namespace Tests\Feature;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use App\Filament\Resources\QuizAttempts\QuizAttemptResource;
use App\Models\LearningMaterial;
use App\Models\QuestionBank;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\User;
use App\Support\SubjectPermissionFormHelper;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TeacherSubjectAssignmentSeeder;
use Database\Seeders\TeacherUserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeacherSubjectPermissionSheetTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_teacher_user_and_assignment_seeders_enforce_exact_roles_and_41_sheet_mappings(): void
    {
        $this->seed(TeacherUserSeeder::class);
        $this->seed(TeacherSubjectAssignmentSeeder::class);

        $expectedSubjects = [
            'IPA-SMP', 'ING-SMP', 'SENI-SMP', 'IND-SMP', 'MAT-SMP', 'PPKN-SMP',
            'PAI-SMP', 'IPS-SMP', 'JAWA-SMP', 'BK-SMP', 'INF-SMP', 'PJOK-SMP',
        ];

        foreach ($expectedSubjects as $code) {
            $this->assertTrue(Subject::where('code', $code)->exists(), "Subject {$code} should exist.");
        }

        // Exact 41 pivot rows across the 31 teachers from Sheet1.
        $this->assertSame(41, DB::table('subject_teacher')->whereNotNull('allowed_grades')->count());

        // Non-teaching staff must NOT have 'guru' role.
        $kepsek = User::where('email', 'prasetyo.cahyo.nugroho@guru.smpn4cepu.sch.id')->firstOrFail();
        $this->assertFalse($kepsek->hasRole('guru'));
        $this->assertTrue($kepsek->hasRole('super_admin'));

        $pustakawan = User::where('email', 'yaatun@guru.smpn4cepu.sch.id')->firstOrFail();
        $this->assertFalse($pustakawan->hasRole('guru'));
        $this->assertTrue($pustakawan->hasRole('staf_perpus'));

        $tu = User::where('email', 'dyah.dirta.sukmasari@guru.smpn4cepu.sch.id')->firstOrFail();
        $this->assertFalse($tu->hasRole('guru'));

        $tendik = User::where('email', 'rakip@guru.smpn4cepu.sch.id')->firstOrFail();
        $this->assertFalse($tendik->hasRole('guru'));

        // Dual-role teacher + head librarian must have BOTH 'guru' and 'staf_perpus'.
        $laeli = User::where('email', 'laeli.hasanah@guru.smpn4cepu.sch.id')->firstOrFail();
        $this->assertTrue($laeli->hasRole('guru'));
        $this->assertTrue($laeli->hasRole('staf_perpus'));
        $ingId = Subject::where('code', 'ING-SMP')->value('id');
        $this->assertSame([7, 8], $laeli->allowedGradesForSubject($ingId));
        $this->assertSame(['7G'], $laeli->allowedClassesForSubject($ingId, 7));
        $this->assertSame(['8D', '8E', '8F', '8G'], $laeli->allowedClassesForSubject($ingId, 8));

        // Check one of the 5 newly created teachers ('Supriyanto Indra Kartiko' has 3 subjects).
        $supriyanto = User::where('email', 'supriyanto.indra.kartiko@guru.smpn4cepu.sch.id')->firstOrFail();
        $this->assertTrue($supriyanto->hasRole('guru'));
        $this->assertTrue(Hash::check('GuruCepu2026!', $supriyanto->password));
        $this->assertCount(3, $supriyanto->subjects()->get());

        // Check BK teacher ('Sampurno, S.Pd., Gr.').
        $sampurno = User::where('email', 'sampurno@guru.smpn4cepu.sch.id')->firstOrFail();
        $bkId = Subject::where('code', 'BK-SMP')->value('id');
        $this->assertSame([7, 8], $sampurno->allowedGradesForSubject($bkId));
        $this->assertSame(['7D', '7E', '7F', '7G'], $sampurno->allowedClassesForSubject($bkId, 7));
    }

    public function test_dual_subject_grade_isolation_and_split_rombel_permissions(): void
    {
        $this->seed(TeacherSubjectAssignmentSeeder::class);

        $ing = Subject::where('code', 'ING-SMP')->firstOrFail();
        $seni = Subject::where('code', 'SENI-SMP')->firstOrFail();
        $ind = Subject::where('code', 'IND-SMP')->firstOrFail();
        $mat = Subject::where('code', 'MAT-SMP')->firstOrFail();

        $ellies = User::where('email', 'ellies.erina@guru.smpn4cepu.sch.id')->firstOrFail();
        $husnul = User::where('email', 'husnul.khotimah@guru.smpn4cepu.sch.id')->firstOrFail();
        $setyorini = User::where('email', 'setyorini@guru.smpn4cepu.sch.id')->firstOrFail();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Ellies Erina: ING-SMP Grade 9 (9D-9F) & SENI-SMP Grade 8 (8C-8D) only.
        $ingGrade9 = LearningMaterial::create([
            'subject_id' => $ing->id,
            'grade_level' => 9,
            'target_classes' => ['9D', '9E', '9F'],
            'title' => 'Narrative Text Grade 9',
            'slug' => 'narrative-text-grade-9',
            'summary' => 'Materi kelas 9',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $ingGrade7 = LearningMaterial::create([
            'subject_id' => $ing->id,
            'grade_level' => 7,
            'target_classes' => ['7A', '7B', '7C'],
            'title' => 'Greeting Grade 7',
            'slug' => 'greeting-grade-7',
            'summary' => 'Materi kelas 7',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $seniGrade8 = LearningMaterial::create([
            'subject_id' => $seni->id,
            'grade_level' => 8,
            'target_classes' => ['8C', '8D'],
            'title' => 'Seni Rupa 8C-8D',
            'slug' => 'seni-rupa-8cd',
            'summary' => 'Materi seni kelas 8',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $seniGrade9 = LearningMaterial::create([
            'subject_id' => $seni->id,
            'grade_level' => 9,
            'target_classes' => ['9A'],
            'title' => 'Seni Musik 9A',
            'slug' => 'seni-musik-9a',
            'summary' => 'Materi seni kelas 9',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $this->assertTrue($ellies->can('view', $ingGrade9));
        $this->assertTrue($ellies->can('update', $ingGrade9));
        $this->assertTrue($ellies->can('update', $seniGrade8));

        $this->assertFalse($ellies->can('view', $ingGrade7));
        $this->assertFalse($ellies->can('update', $ingGrade7));
        $this->assertFalse($ellies->can('update', $seniGrade9));

        $this->actingAs($ellies);
        $visibleIds = LearningMaterialResource::getEloquentQuery()->pluck('id')->all();
        $this->assertContains($ingGrade9->id, $visibleIds);
        $this->assertContains($seniGrade8->id, $visibleIds);
        $this->assertNotContains($ingGrade7->id, $visibleIds);
        $this->assertNotContains($seniGrade9->id, $visibleIds);

        // Split-rombel isolation in same subject (IND-SMP) & same grade (7):
        // Husnul holds 7C-7E, Setyorini holds 7F-7G (plus 8A-8C).
        $husnulMaterial = LearningMaterial::create([
            'subject_id' => $ind->id,
            'grade_level' => 7,
            'target_classes' => ['7C', '7D', '7E'],
            'title' => 'Teks Deskripsi 7CDE',
            'slug' => 'teks-deskripsi-7cde',
            'summary' => 'Khusus 7C-7E',
            'created_by' => $husnul->id,
            'is_active' => true,
        ]);

        $setyoriniMaterial = LearningMaterial::create([
            'subject_id' => $ind->id,
            'grade_level' => 7,
            'target_classes' => ['7F', '7G'],
            'title' => 'Teks Deskripsi 7FG',
            'slug' => 'teks-deskripsi-7fg',
            'summary' => 'Khusus 7F-7G',
            'created_by' => $setyorini->id,
            'is_active' => true,
        ]);

        $this->assertFalse($setyorini->can('view', $husnulMaterial));
        $this->assertFalse($setyorini->can('update', $husnulMaterial));
        $this->assertFalse($husnul->can('view', $setyoriniMaterial));
        $this->assertFalse($husnul->can('delete', $setyoriniMaterial));

        $this->actingAs($setyorini);
        $setyoriniVisible = LearningMaterialResource::getEloquentQuery()->pluck('id')->all();
        $this->assertContains($setyoriniMaterial->id, $setyoriniVisible);
        $this->assertNotContains($husnulMaterial->id, $setyoriniVisible);

        // QuizBank & QuizAttempt rombel filtering for Husnul vs Setyorini on same bank.
        $sharedIndBank = QuestionBank::create([
            'subject_id' => $ind->id,
            'grade_level' => 7,
            'target_classes' => null,
            'title' => 'PH 1 Bahasa Indonesia Kelas 7',
            'duration_minutes' => 30,
            'passing_grade' => 75,
            'access_code' => 'IND7-OK',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $bank7CDE = QuestionBank::create([
            'subject_id' => $ind->id,
            'grade_level' => 7,
            'target_classes' => ['7C', '7D', '7E'],
            'title' => 'Latihan 7C-7E',
            'duration_minutes' => 20,
            'passing_grade' => 75,
            'access_code' => 'IND-7CDE',
            'is_active' => true,
            'created_by' => $husnul->id,
        ]);

        $this->actingAs($setyorini);
        $setyoriniBanks = QuestionBankResource::getEloquentQuery()->pluck('id')->all();
        $this->assertContains($sharedIndBank->id, $setyoriniBanks);
        $this->assertNotContains($bank7CDE->id, $setyoriniBanks);

        $student7C = User::factory()->create(['student_class' => '7C', 'status' => 'aktif']);
        $student7F = User::factory()->create(['student_class' => '7F', 'status' => 'aktif']);

        $attempt7C = QuizAttempt::create([
            'bank_id' => $sharedIndBank->id,
            'user_id' => $student7C->id,
            'participant_identifier' => $student7C->name,
            'student_class' => '7C',
            'total_questions' => 10,
            'correct_answers' => 9,
            'final_score' => 90,
            'is_passed' => true,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $attempt7F = QuizAttempt::create([
            'bank_id' => $sharedIndBank->id,
            'user_id' => $student7F->id,
            'participant_identifier' => $student7F->name,
            'student_class' => '7F',
            'total_questions' => 10,
            'correct_answers' => 8,
            'final_score' => 80,
            'is_passed' => true,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->actingAs($husnul);
        $husnulAttemptIds = QuizAttemptResource::getEloquentQuery()->pluck('id')->all();
        $this->assertSame([$attempt7C->id], $husnulAttemptIds);
        $this->assertTrue(QuizAttemptResource::getAuthorizationResponse('edit', $attempt7C)->allowed());
        $this->assertFalse(QuizAttemptResource::getAuthorizationResponse('edit', $attempt7F)->allowed());

        // Tampered form payload validation guard for teachers.
        $this->actingAs($husnul);
        $normalized = SubjectPermissionFormHelper::validateAndNormalizePayload([
            'subject_id' => $ind->id,
            'grade_level' => 7,
            'target_classes' => null,
        ], isCreate: true);
        $this->assertSame(['7C', '7D', '7E'], $normalized['target_classes']);

        $this->expectException(ValidationException::class);
        SubjectPermissionFormHelper::validateAndNormalizePayload([
            'subject_id' => $mat->id,
            'grade_level' => 7,
            'target_classes' => ['7C'],
        ], isCreate: true);
    }

    public function test_unrestricted_legacy_pivot_row_still_allows_all_grades(): void
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');
        $subject = Subject::create(['code' => 'TEST-1', 'name' => 'Mapel Uji']);
        $guru->subjects()->sync([$subject->id]);

        $this->assertSame([7, 8, 9], $guru->allowedGradesForSubject($subject->id));
        $this->assertTrue($guru->canAccessSubjectContent($subject->id, 8, ['8A']));
        $this->assertTrue($guru->canStoreSubjectContent($subject->id, 9, null));
    }
}

