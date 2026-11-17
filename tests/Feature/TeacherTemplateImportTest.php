<?php

namespace Tests\Feature;

use App\Filament\Resources\LearningMaterials\Pages\CreateLearningMaterial;
use App\Filament\Resources\LearningMaterials\Pages\ListLearningMaterials;
use App\Filament\Resources\QuestionBanks\Pages\CreateQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\EditQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\ListQuestionBanks;
use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\User;
use App\Services\LearningMaterialImportService;
use App\Services\QuestionImportService;
use App\Services\Support\FlexibleDocumentReader;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class TeacherTemplateImportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * @return array{0: User, 1: User, 2: Subject}
     */
    private function createTwoTeachersForSameSubject(): array
    {
        $subject = Subject::firstOrCreate(
            ['code' => 'UJI-IPA'],
            ['name' => 'IPA Uji Template']
        );

        /** @var User $teacherA */
        $teacherA = User::factory()->create(['email_verified_at' => now()]);
        $teacherA->assignRole('guru');
        DB::table('subject_teacher')->insert([
            'subject_id' => $subject->id,
            'teacher_id' => $teacherA->id,
            'allowed_grades' => json_encode([7]),
            'allowed_classes' => json_encode(['7A', '7B', '7C']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /** @var User $teacherB */
        $teacherB = User::factory()->create(['email_verified_at' => now()]);
        $teacherB->assignRole('guru');
        DB::table('subject_teacher')->insert([
            'subject_id' => $subject->id,
            'teacher_id' => $teacherB->id,
            'allowed_grades' => json_encode([7]),
            'allowed_classes' => json_encode(['7D', '7E', '7F']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$teacherA, $teacherB, $subject];
    }

    private function writeTempFile(string $extension, string $contents): string
    {
        $base = tempnam(sys_get_temp_dir(), 'tpl_test_');
        $path = $base . '.' . ltrim($extension, '.');
        @unlink($base);
        file_put_contents($path, $contents);

        return $path;
    }

    public function test_question_import_supports_all_official_template_formats_round_trip(): void
    {
        $service = app(QuestionImportService::class);
        $templates = [
            'xlsx' => QuestionImportService::getTemplateXlsx(),
            'csv' => QuestionImportService::getTemplateCsv(),
            'docx' => QuestionImportService::getTemplateDocx(),
            'txt' => QuestionImportService::getTemplateTxt(),
        ];

        foreach ($templates as $ext => $binaryOrText) {
            $path = $this->writeTempFile($ext, $binaryOrText);
            try {
                $preview = $service->inspect($path);
                $this->assertTrue($preview['success'], "Format {$ext} seharusnya sukses diparsing: " . implode(', ', $preview['errors']));
                $this->assertSame(2, $preview['total_found'], "Format {$ext} harus menghasilkan 2 soal.");
                $this->assertTrue($preview['questions'][0]['options'][1]['is_correct'], "Kunci B pada {$ext} harus true.");
                $this->assertTrue($preview['questions'][1]['options'][1]['is_correct'], "Kunci B soal 2 pada {$ext} harus true.");
            } finally {
                @unlink($path);
            }
        }
    }

    public function test_question_import_handles_diverse_custom_teacher_templates_and_column_overrides(): void
    {
        $service = app(QuestionImportService::class);

        // 1. Custom Excel with kop rows (row 1-2), Petunjuk sheet first, 5 options, full-text answer key
        $xlsxBinary = FlexibleDocumentReader::buildXlsx([
            [
                'name' => 'Petunjuk',
                'rows' => [['Ini halaman petunjuk sebelum soal']],
            ],
            [
                'name' => 'Soal Guru A',
                'rows' => [
                    ['SMP NEGERI 4 CEPU - BANK SOAL MANDIRI'],
                    [''],
                    ['Urutan', 'Butir Soal', 'Opsi 1', 'Opsi 2', 'Opsi 3', 'Opsi 4', 'Opsi 5', 'Jawaban Benar', 'Skor'],
                    ['1', 'Ibu kota Jawa Tengah adalah...', 'Solo', 'Semarang', 'Tegal', 'Magelang', 'Pekalongan', 'Semarang', '20'],
                    ['2', 'Simbol kimia air adalah...', 'CO2', 'O2', 'H2O', 'NaCl', '', '3', '15'],
                ],
            ],
        ]);
        $xlsxPath = $this->writeTempFile('xlsx', $xlsxBinary);

        // 2. Word (.docx) with Table format
        $docxTableBinary = FlexibleDocumentReader::buildDocx(
            'SOAL FORMAT TABEL',
            [],
            [
                ['No', 'Soal', 'A', 'B', 'C', 'D', 'Kunci'],
                ['1', '2 + 2 x 3 = ...', '12', '8', '10', '6', 'B'],
            ]
        );
        $docxTablePath = $this->writeTempFile('docx', $docxTableBinary);

        // 3. Word (.docx) with bold correct option, inline stem list 1) 2), and End-of-doc Answer Key
        $docxBoldAndEndKey = $this->buildCustomDocxXml([
            ['text' => '1. Perhatikan pernyataan berikut:', 'bold' => false],
            ['text' => '1) Bernapas', 'bold' => false],
            ['text' => '2) Bergerak', 'bold' => false],
            ['text' => 'Yang termasuk ciri makhluk hidup adalah...', 'bold' => false],
            ['text' => 'A. Hanya 1', 'bold' => false],
            ['text' => 'B. Hanya 2', 'bold' => false],
            ['text' => 'C. 1 dan 2 benar', 'bold' => true],
            ['text' => 'D. Semua salah', 'bold' => false],
            ['text' => '2. Planet terdekat dengan matahari adalah...', 'bold' => false],
            ['text' => 'A. Venus   B. Merkurius   C. Mars   D. Bumi', 'bold' => false],
            ['text' => 'Kunci Jawaban:', 'bold' => true],
            ['text' => '2. B', 'bold' => false],
        ]);
        $docxCustomPath = $this->writeTempFile('docx', $docxBoldAndEndKey);

        // 4. Weird CSV columns needing explicit manual columnOverrides
        $weirdCsvPath = $this->writeTempFile('csv', implode("\n", [
            'TeksAja|J1|J2|J3|KunciNya',
            'Hasil 5 + 5 adalah?|8|10|12|B',
        ]));

        // 5. JSON array export
        $jsonPath = $this->writeTempFile('json', json_encode([
            [
                'question' => 'Bilangan prima terkecil?',
                'option_a' => '1',
                'option_b' => '2',
                'option_c' => '3',
                'option_d' => '5',
                'correct' => 'B',
                'points' => 25,
            ],
        ]));

        try {
            $xlsxRes = $service->inspect($xlsxPath);
            $this->assertTrue($xlsxRes['success']);
            $this->assertSame(2, $xlsxRes['total_found']);
            $this->assertTrue($xlsxRes['questions'][0]['options'][1]['is_correct']);
            $this->assertSame(20, $xlsxRes['questions'][0]['points']);
            $this->assertTrue($xlsxRes['questions'][1]['options'][2]['is_correct']);

            $tableRes = $service->inspect($docxTablePath);
            $this->assertTrue($tableRes['success']);
            $this->assertSame(1, $tableRes['total_found']);
            $this->assertTrue($tableRes['questions'][0]['options'][1]['is_correct']);

            $customDocxRes = $service->inspect($docxCustomPath);
            $this->assertTrue($customDocxRes['success']);
            $this->assertSame(2, $customDocxRes['total_found']);
            $this->assertStringContainsString('1) Bernapas', $customDocxRes['questions'][0]['question_text']);
            $this->assertTrue($customDocxRes['questions'][0]['options'][2]['is_correct']);
            $this->assertCount(4, $customDocxRes['questions'][1]['options']);
            $this->assertTrue($customDocxRes['questions'][1]['options'][1]['is_correct']);

            $overrideRes = $service->inspect($weirdCsvPath, [
                0 => 'question_text',
                1 => 'option_a',
                2 => 'option_b',
                3 => 'option_c',
                4 => 'correct_answer',
            ]);
            $this->assertTrue($overrideRes['success']);
            $this->assertSame(1, $overrideRes['total_found']);
            $this->assertTrue($overrideRes['questions'][0]['options'][1]['is_correct']);

            $jsonRes = $service->inspect($jsonPath);
            $this->assertTrue($jsonRes['success']);
            $this->assertSame(1, $jsonRes['total_found']);
            $this->assertSame(25, $jsonRes['questions'][0]['points']);
        } finally {
            @unlink($xlsxPath);
            @unlink($docxTablePath);
            @unlink($docxCustomPath);
            @unlink($weirdCsvPath);
            @unlink($jsonPath);
        }
    }

    public function test_same_and_different_teachers_can_reuse_same_templates_with_strict_permission_isolation(): void
    {
        Storage::fake('public');
        [$teacherA, $teacherB, $subject] = $this->createTwoTeachersForSameSubject();

        $questionSvc = app(QuestionImportService::class);
        $materialSvc = app(LearningMaterialImportService::class);

        $sharedQuestionCsv = $this->writeTempFile('csv', QuestionImportService::getTemplateCsv());
        $sharedMaterialCsv = $this->writeTempFile('csv', LearningMaterialImportService::getTemplateCsv());

        try {
            // Both Teacher A (7A-7C) and Teacher B (7D-7F) use the EXACT SAME Question & Material template files
            $resBankA = $questionSvc->createBankAndImport([
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A', '7B'],
                'title' => 'Latihan Bab 1 Kelas 7AB',
            ], $sharedQuestionCsv, $teacherA);

            $resBankB = $questionSvc->createBankAndImport([
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7D', '7E'],
                'title' => 'Latihan Bab 1 Kelas 7DE',
            ], $sharedQuestionCsv, $teacherB);

            $this->assertTrue($resBankA['success']);
            $this->assertTrue($resBankB['success']);
            $this->assertSame($teacherA->id, $resBankA['bank']->created_by);
            $this->assertSame($teacherB->id, $resBankB['bank']->created_by);
            $this->assertSame(2, $resBankA['imported_questions']);
            $this->assertSame(2, $resBankB['imported_questions']);

            // Teacher A cannot import a QuestionBank into Grade 9 (outside allowed grade 7)
            $this->expectException(ValidationException::class);
            $questionSvc->createBankAndImport([
                'subject_id' => $subject->id,
                'grade_level' => 9,
                'target_classes' => ['9A'],
                'title' => 'Paket Ilegal Kelas 9',
            ], $sharedQuestionCsv, $teacherA);
        } finally {
            @unlink($sharedQuestionCsv);
        }

        // Material import with identical titles by Teacher A and Teacher B must create separate unique slugs cleanly
        try {
            $resMatA = $materialSvc->importBatch($sharedMaterialCsv, [
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A'],
                'is_active' => true,
            ], $teacherA);

            $resMatB = $materialSvc->importBatch($sharedMaterialCsv, [
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7D'],
                'is_active' => true,
            ], $teacherB);

            $this->assertTrue($resMatA['success']);
            $this->assertTrue($resMatB['success']);
            $this->assertSame(2, $resMatA['imported']);
            $this->assertSame(2, $resMatB['imported']);

            $matA = LearningMaterial::findOrFail($resMatA['created_ids'][0]);
            $matB = LearningMaterial::findOrFail($resMatB['created_ids'][0]);
            $this->assertNotSame($matA->slug, $matB->slug);
            $this->assertSame($teacherA->id, $matA->created_by);
            $this->assertSame($teacherB->id, $matB->created_by);
        } finally {
            @unlink($sharedMaterialCsv);
        }
    }

    public function test_material_import_supports_xlsx_csv_docx_txt_pdf_and_validates_row_permissions(): void
    {
        Storage::fake('public');
        [$teacherA, , $subject] = $this->createTwoTeachersForSameSubject();
        $materialSvc = app(LearningMaterialImportService::class);

        // Spreadsheet missing Ringkasan column + containing 1 unauthorized row (9A) alongside 1 valid row (7A)
        $xlsxPath = $this->writeTempFile('xlsx', FlexibleDocumentReader::buildXlsx([
            [
                'name' => 'Materi',
                'rows' => [
                    ['Topik', 'Uraian', 'Rombel'],
                    ['Pengukuran Besaran Pokok', 'Mengenal panjang, massa, dan waktu dengan alat ukur baku.', '7A'],
                    ['Materi Ilegal Kelas 9', 'Tidak boleh masuk oleh guru kelas 7.', '9A'],
                ],
            ],
        ]));

        // Single Word docx module -> should parse text AND save attachment_path
        $docxPath = $this->writeTempFile('docx', FlexibleDocumentReader::buildDocx(
            'Modul Zat dan Perubahannya',
            [
                'Materi ini membahas wujud zat padat, cair, dan gas serta perubahan fisika maupun kimia.',
                'Perubahan fisika tidak menghasilkan zat baru, sedangkan perubahan kimia menghasilkan zat baru.',
            ]
        ));

        // PDF upload directly -> single downloadable module
        $pdfPath = $this->writeTempFile('pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        try {
            $xlsxImport = $materialSvc->importBatch($xlsxPath, [
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A'],
            ], $teacherA);

            $this->assertTrue($xlsxImport['success']);
            $this->assertSame(1, $xlsxImport['imported']);
            $this->assertSame(1, $xlsxImport['skipped']);

            $docxImport = $materialSvc->importBatch($docxPath, [
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7B'],
            ], $teacherA, attachSourceDocument: true);

            $this->assertTrue($docxImport['success']);
            $docxMaterial = LearningMaterial::findOrFail($docxImport['created_ids'][0]);
            $this->assertNotEmpty($docxMaterial->attachment_path);
            $this->assertTrue(Storage::disk('public')->exists($docxMaterial->attachment_path));
            $this->assertStringContainsString('Modul Zat', $docxMaterial->title);

            $pdfImport = $materialSvc->importBatch($pdfPath, [
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A'],
            ], $teacherA, attachSourceDocument: true, originalName: 'Rangkuman_IPA_Bab3.pdf');

            $this->assertTrue($pdfImport['success']);
            $pdfMaterial = LearningMaterial::findOrFail($pdfImport['created_ids'][0]);
            $this->assertTrue(Storage::disk('public')->exists($pdfMaterial->attachment_path));
        } finally {
            @unlink($xlsxPath);
            @unlink($docxPath);
            @unlink($pdfPath);
        }
    }

    public function test_extension_validation_rule_allows_safe_teacher_templates_and_blocks_executables(): void
    {
        $rule = FlexibleDocumentReader::materialAttachmentRule();

        foreach (['dotx', 'xltx', 'potx', 'docx', 'xlsx', 'csv', 'txt', 'odt', 'ods', 'pdf', 'zip', 'png'] as $allowedExt) {
            $failed = false;
            $file = UploadedFile::fake()->create("template.{$allowedExt}", 10);
            $rule->validate('attachment_path', $file, function () use (&$failed): void {
                $failed = true;
            });
            $this->assertFalse($failed, "Ekstensi .{$allowedExt} seharusnya diizinkan.");
        }

        foreach (['php', 'exe', 'bat', 'sh', 'svg', 'js'] as $blockedExt) {
            $failed = false;
            $file = UploadedFile::fake()->create("evil.{$blockedExt}", 10);
            $rule->validate('attachment_path', $file, function () use (&$failed): void {
                $failed = true;
            });
            $this->assertTrue($failed, "Ekstensi .{$blockedExt} harus diblokir.");
        }
    }

    public function test_filament_list_and_edit_pages_download_and_import_templates_for_guru(): void
    {
        [$teacherA, , $subject] = $this->createTwoTeachersForSameSubject();

        // 1. QuestionBanks List Page template downloads & import action
        Livewire::actingAs($teacherA)
            ->test(ListQuestionBanks::class)
            ->assertActionExists('downloadTemplateXlsx')
            ->assertActionExists('downloadTemplateCsv')
            ->assertActionExists('downloadTemplateDocx')
            ->assertActionExists('downloadTemplateTxt')
            ->assertActionExists('importFromTemplate')
            ->callAction('downloadTemplateXlsx')
            ->assertFileDownloaded('template_bank_soal_smpn4cepu.xlsx')
            ->callAction('downloadTemplateDocx')
            ->assertFileDownloaded('template_bank_soal_smpn4cepu.docx');

        $csvFile = UploadedFile::fake()->createWithContent(
            'soal_ipa_guru.csv',
            QuestionImportService::getTemplateCsv()
        );

        Livewire::actingAs($teacherA)
            ->test(ListQuestionBanks::class)
            ->callAction('importFromTemplate', [
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A', '7B'],
                'title' => 'Ujian IPA Dari Livewire',
                'duration_minutes' => 40,
                'passing_grade' => 75,
                'access_code' => 'IPA7-LIVE',
                'is_active' => true,
                'allow_retry' => true,
                'max_attempts' => 0,
                'template_file' => $csvFile,
            ])
            ->assertHasNoActionErrors();

        $bank = QuestionBank::where('title', 'Ujian IPA Dari Livewire')->first();
        $this->assertNotNull($bank);
        $this->assertSame($teacherA->id, $bank->created_by);
        $this->assertSame(2, $bank->questions()->count());

        // 2. Export & EditQuestionBank append import
        $exportedXlsx = QuestionImportService::exportBankToXlsx($bank);
        $reimportFile = UploadedFile::fake()->createWithContent('reimport.xlsx', $exportedXlsx);

        Livewire::actingAs($teacherA)
            ->test(EditQuestionBank::class, ['record' => $bank->getRouteKey()])
            ->assertActionExists('importQuestions')
            ->assertActionExists('exportBank')
            ->callAction('exportBank')
            ->assertFileDownloaded('soal_ujian-ipa-dari-livewire.xlsx')
            ->callAction('importQuestions', [
                'replace_existing' => false,
                'template_file' => $reimportFile,
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(4, Question::where('bank_id', $bank->id)->count());

        // 3. CreateQuestionBank form WITH quick_import_file & CreateLearningMaterial page with template attachment
        $docxQuizFile = UploadedFile::fake()->createWithContent(
            'soal_word_guru.docx',
            QuestionImportService::getTemplateDocx()
        );

        Livewire::actingAs($teacherA)
            ->test(CreateQuestionBank::class)
            ->fillForm([
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A', '7B'],
                'title' => 'Paket Soal Dari Form Create',
                'duration_minutes' => 30,
                'passing_grade' => 75,
                'access_code' => 'FORM-CREATE-01',
                'is_active' => true,
                'allow_retry' => true,
                'max_attempts' => 0,
                'quick_import_file' => $docxQuizFile,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdFromForm = QuestionBank::where('title', 'Paket Soal Dari Form Create')->first();
        $this->assertNotNull($createdFromForm);
        $this->assertSame(2, $createdFromForm->questions()->count());

        Storage::fake('public');
        $dotxTemplateAttachment = UploadedFile::fake()->createWithContent(
            'lembar_kerja_siswa.dotx',
            LearningMaterialImportService::getTemplateDocx()
        );

        Livewire::actingAs($teacherA)
            ->test(CreateLearningMaterial::class)
            ->fillForm([
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A'],
                'title' => 'LKPD IPA Template Guru',
                'slug' => 'lkpd-ipa-template-guru',
                'summary' => 'Lembar kerja siswa format Word template.',
                'content' => '<p>Unduh template lalu kerjakan.</p>',
                'attachment_path' => $dotxTemplateAttachment,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(LearningMaterial::where('slug', 'lkpd-ipa-template-guru')->exists());

        // 4. ListLearningMaterials template downloads, export & import action
        $matXlsxFile = UploadedFile::fake()->createWithContent(
            'materi_guru_a.xlsx',
            LearningMaterialImportService::getTemplateXlsx()
        );

        Livewire::actingAs($teacherA)
            ->test(ListLearningMaterials::class)
            ->assertActionDoesNotExist('downloadTemplateXlsx')
            ->assertActionDoesNotExist('downloadTemplateCsv')
            ->assertActionDoesNotExist('downloadTemplateDocx')
            ->assertActionDoesNotExist('downloadTemplateTxt')
            ->assertActionExists('importMaterialsFromTemplate')
            ->assertActionExists('exportMaterials')
            ->callAction('importMaterialsFromTemplate', [
                'subject_id' => $subject->id,
                'grade_level' => 7,
                'target_classes' => ['7A', '7B'],
                'is_active' => true,
                'attach_source_document' => false,
                'template_file' => $matXlsxFile,
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(
            3,
            LearningMaterial::where('created_by', $teacherA->id)->where('subject_id', $subject->id)->count()
        );
    }

    public function test_student_portal_target_classes_isolation_and_quiz_attempts_export(): void
    {
        [$teacherA, $teacherB, $subject] = $this->createTwoTeachersForSameSubject();

        $mat7A = LearningMaterial::create([
            'subject_id' => $subject->id,
            'grade_level' => 7,
            'target_classes' => ['7A', '7B'],
            'title' => 'Materi Khusus Rombel 7A-7B',
            'slug' => 'materi-khusus-7a-7b',
            'summary' => 'Ringkasan 7A-7B',
            'created_by' => $teacherA->id,
            'is_active' => true,
        ]);

        $bank7A = QuestionBank::create([
            'subject_id' => $subject->id,
            'grade_level' => 7,
            'target_classes' => ['7A', '7B'],
            'title' => 'Ujian Khusus Rombel 7A-7B',
            'duration_minutes' => 30,
            'passing_grade' => 75,
            'access_code' => 'R7AB',
            'is_active' => true,
            'created_by' => $teacherA->id,
        ]);

        /** @var User $student7A */
        $student7A = User::factory()->create(['student_class' => '7A', 'status' => 'aktif']);
        $student7A->assignRole('siswa');

        /** @var User $student7D */
        $student7D = User::factory()->create(['student_class' => '7D', 'status' => 'aktif']);
        $student7D->assignRole('siswa');

        $this->actingAs($student7A)->get('/belajar/materi')->assertOk()->assertSee('Materi Khusus Rombel 7A-7B');
        $this->actingAs($student7A)->get('/belajar/latihan-soal')->assertOk()->assertSee('Ujian Khusus Rombel 7A-7B');
        $this->actingAs($student7A)->get('/belajar/latihan-soal/' . $bank7A->id)->assertOk();

        $this->actingAs($student7D)->get('/belajar/materi')->assertOk()->assertDontSee('Materi Khusus Rombel 7A-7B');
        $this->actingAs($student7D)->get('/belajar/latihan-soal')->assertOk()->assertDontSee('Ujian Khusus Rombel 7A-7B');
        $this->actingAs($student7D)->get('/belajar/latihan-soal/' . $bank7A->id)->assertStatus(403);

        // Export Rekap Nilai (.xlsx) on ListQuizAttempts for Guru
        Livewire::actingAs($teacherA)
            ->test(\App\Filament\Resources\QuizAttempts\Pages\ListQuizAttempts::class)
            ->assertActionExists('exportScoresXlsx')
            ->callAction('exportScoresXlsx')
            ->assertFileDownloaded();
    }

    /**
     * @param  list<array{text: string, bold: bool}>  $items
     */
    private function buildCustomDocxXml(array $items): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'c_docx_');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE | ZipArchive::CREATE);

        $body = '';
        foreach ($items as $item) {
            $rPr = $item['bold'] ? '<w:rPr><w:b/></w:rPr>' : '';
            $escaped = htmlspecialchars($item['text'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $body .= "<w:p><w:r>{$rPr}<w:t xml:space=\"preserve\">{$escaped}</w:t></w:r></w:p>";
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' . $body . '</w:body></w:document>');
        $zip->close();

        $bytes = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $bytes;
    }
}
