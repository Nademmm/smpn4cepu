<?php

namespace Tests\Feature;

use App\Models\ElectionCandidate;
use App\Models\LearningMaterial;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\BackupRecoveryService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BackupAndRecoveryTest extends TestCase
{
    public function test_backup_service_creates_zip_containing_database_and_uploads(): void
    {
        // Siapkan dummy file unggahan publik untuk memastikan backup mencakup berkas
        Storage::disk('public')->put('materials/dummy_test_document.pdf', 'Konten PDF Dummy Materi');
        Storage::disk('public')->put('covers/dummy_test_cover.jpg', 'Konten Foto Cover Dummy');

        /** @var BackupRecoveryService $service */
        $service = app(BackupRecoveryService::class);
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($admin);

        $backup = $service->createBackup($admin);

        $this->assertInstanceOf(SystemBackup::class, $backup);
        $this->assertDatabaseHas('system_backups', [
            'id' => $backup->id,
            'filename' => $backup->filename,
            'status' => 'berhasil',
        ]);

        $this->assertTrue($backup->fileExists());
        $fullPath = Storage::disk($backup->disk)->path($backup->filepath);

        // Verifikasi isi arsip ZIP
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($fullPath));
        $this->assertNotFalse($zip->locateName('database/database.sqlite'));
        $this->assertNotFalse($zip->locateName('manifest.json'));
        $this->assertNotFalse($zip->locateName('storage_public/materials/dummy_test_document.pdf'));
        $zip->close();

        // Bersihkan dummy file
        Storage::disk('public')->delete(['materials/dummy_test_document.pdf', 'covers/dummy_test_cover.jpg']);
    }

    public function test_backup_recovery_simulation_verifies_integrity_successfully(): void
    {
        /** @var BackupRecoveryService $service */
        $service = app(BackupRecoveryService::class);
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($admin);

        $backup = $service->createBackup($admin);
        $result = $service->testRecovery($backup, $admin);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Lolos uji pemulihan', $result['message']);

        $backup->refresh();
        $this->assertEquals('lolos', $backup->test_status);
        $this->assertNotNull($backup->tested_at);
    }

    public function test_system_backups_panel_is_strictly_super_admin_only(): void
    {
        $superAdmin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($superAdmin);
        $this->actingAs($superAdmin)->get('/admin/system-backups')->assertStatus(200);

        $operator = User::where('email', 'operator@smpn4cepu.sch.id')->first();
        $this->assertNotNull($operator);
        $this->flushSession();
        $this->actingAs($operator)->get('/admin/system-backups')->assertStatus(403);

        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $this->assertNotNull($guru);
        $this->flushSession();
        $this->actingAs($guru)->get('/admin/system-backups')->assertStatus(403);
    }

    public function test_soft_deleted_question_bank_and_learning_material_can_be_restored(): void
    {
        $subject = Subject::first();
        $this->assertNotNull($subject);
        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $this->assertNotNull($guru);

        // 1. Uji Soft Delete & Restore pada Question Bank
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'grade_level' => 7,
            'title' => 'Bank Soal Uji Arsip Soft Delete',
            'duration_minutes' => 30,
            'passing_grade' => 75,
            'is_active' => true,
            'created_by' => $guru->id,
        ]);

        $bank->delete();
        $this->assertSoftDeleted('question_banks', ['id' => $bank->id]);

        $bank->restore();
        $this->assertNotSoftDeleted('question_banks', ['id' => $bank->id]);

        // 2. Uji Soft Delete & Restore pada Learning Material
        $material = LearningMaterial::create([
            'subject_id' => $subject->id,
            'grade_level' => 7,
            'title' => 'Materi Uji Arsip Soft Delete',
            'slug' => 'materi-uji-arsip-soft-delete-' . uniqid(),
            'summary' => 'Ringkasan materi uji',
            'created_by' => $guru->id,
        ]);

        $material->delete();
        $this->assertSoftDeleted('learning_materials', ['id' => $material->id]);

        $material->restore();
        $this->assertNotSoftDeleted('learning_materials', ['id' => $material->id]);
    }

    public function test_pilketos_candidate_can_be_archived(): void
    {
        $candidate = ElectionCandidate::create([
            'candidate_number' => 99,
            'candidate_name' => 'Kandidat Uji Arsip',
            'vice_candidate_name' => 'Wakil Uji Arsip',
            'vision' => 'Visi uji arsip',
            'mission' => 'Misi uji arsip',
            'period_year' => '2025/2026',
            'status' => 'aktif',
        ]);

        $candidate->update(['status' => 'diarsipkan']);

        $this->assertDatabaseHas('election_candidates', [
            'id' => $candidate->id,
            'status' => 'diarsipkan',
            'period_year' => '2025/2026',
        ]);
    }
}
