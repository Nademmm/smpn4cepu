<?php

namespace Tests\Feature;

use App\Filament\Resources\Students\Pages\ListStudents;
use App\Models\StudentStatistic;
use App\Models\User;
use App\Services\StudentImportService;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    public function test_student_import_service_template_csv_is_valid(): void
    {
        $csv = StudentImportService::getTemplateCsv();
        $this->assertNotEmpty($csv);
        $this->assertStringContainsString('nisn,nis,nama,jenis_kelamin,rombel,tempat_lahir,tanggal_lahir', $csv);
        $this->assertStringContainsString('AFIKA ARDINA SARI', $csv);
    }

    public function test_student_import_service_imports_and_updates_from_csv(): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'std_import_') . '.csv';
        $csvContent = implode("\n", [
            'nisn,nis,nama,jenis_kelamin,rombel,tempat_lahir,tanggal_lahir',
            '0099887761,112233,Budi Santoso Uji,L,7A,Blora,2013-05-12',
            '0099887762,112234,Siti Nurhaliza Uji,P,7A,Cepu,2013-08-20',
        ]);
        file_put_contents($tempPath, $csvContent);

        $service = new StudentImportService();
        $result = $service->import($tempPath, '2026/2027', 'password123');

        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['total']);

        // Verifikasi akun siswa terdaftar di database
        $user1 = User::where('nisn', '0099887761')->first();
        $this->assertNotNull($user1);
        $this->assertEquals('Budi Santoso Uji', $user1->name);
        $this->assertEquals('7A', $user1->student_class);
        $this->assertEquals('0099887761@siswa.smpn4cepu.sch.id', $user1->email);
        $this->assertTrue($user1->hasRole('siswa'));
        $this->assertTrue(Hash::check('password123', $user1->password));

        // Ubah password user1 ke kata sandi kustom
        $user1->update(['password' => Hash::make('customPassword2026!')]);

        // Impor ulang file CSV yang sama dengan pembaruan nama
        $csvUpdated = implode("\n", [
            'nisn,nis,nama,jenis_kelamin,rombel,tempat_lahir,tanggal_lahir',
            '0099887761,112233,Budi Santoso Uji Terupdate,L,7A,Blora,2013-05-12',
            '0099887762,112234,Siti Nurhaliza Uji,P,7A,Cepu,2013-08-20',
        ]);
        file_put_contents($tempPath, $csvUpdated);

        $updateResult = $service->import($tempPath, '2026/2027', 'password123');
        $this->assertTrue($updateResult['success']);
        $this->assertEquals(0, $updateResult['imported']);
        $this->assertEquals(2, $updateResult['updated']);

        // Pastikan nama diperbarui dan password lama TIDAK tertimpa
        $user1->refresh();
        $this->assertEquals('Budi Santoso Uji Terupdate', $user1->name);
        $this->assertTrue(Hash::check('customPassword2026!', $user1->password));

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function test_admin_can_access_student_list_and_download_template(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($admin);

        Livewire::actingAs($admin)
            ->test(ListStudents::class)
            ->assertActionExists('downloadTemplate')
            ->assertActionExists('importExcel')
            ->assertActionVisible('downloadTemplate')
            ->assertActionVisible('importExcel')
            ->callAction('downloadTemplate')
            ->assertFileDownloaded('template_import_siswa_smpn4cepu.csv');
    }
}
