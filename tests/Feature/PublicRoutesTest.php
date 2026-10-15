<?php

namespace Tests\Feature;

use App\Livewire\Public\ContactForm;
use App\Models\ContactMessage;
use App\Models\QuestionBank;
use App\Models\SchoolDocument;
use Livewire\Livewire;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    public function test_all_public_sitemap_routes_return_successful_response(): void
    {
        $publicRoutes = [
            '/',
            '/profil',
            '/profil/prestasi',
            '/profil/fasilitas',
            '/guru-staf',
            '/rekap-siswa',
            '/kesiswaan/ekstrakurikuler',
            '/pilketos',
            '/pilketos/live-count',
            '/perpustakaan',
            '/berita',
            '/galeri',
            '/unduhan',
            '/kontak',
            '/login',
        ];

        foreach ($publicRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_materials_and_quizzes_redirect_guest_to_login(): void
    {
        $this->get('/materi')->assertRedirect('/login');
        $this->get('/latihan-soal')->assertRedirect('/login');

        $bank = QuestionBank::first();
        if ($bank) {
            $this->get('/latihan-soal/' . $bank->id)->assertRedirect('/login');
        }
    }

    public function test_authenticated_student_can_access_materials_and_quizzes(): void
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'siswa.test@smpn4cepu.sch.id'],
            [
                'name' => 'Siswa Test',
                'password' => bcrypt('password123'),
            ]
        );
        $user->assignRole('siswa');

        $this->actingAs($user)->get('/materi')->assertStatus(200);
        $this->actingAs($user)->get('/latihan-soal')->assertStatus(200);

        $bank = QuestionBank::first();
        if ($bank) {
            $this->actingAs($user)->get('/latihan-soal/' . $bank->id)->assertStatus(200);
        }
    }

    public function test_contact_form_submits_message_to_database(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Ratna Sari')
            ->set('email_or_phone', '081234567890')
            ->set('subject', 'Pertanyaan Pendaftaran Ekstrakurikuler')
            ->set('message', 'Halo admin, bagaimana tata cara mendaftar ekstrakurikuler robotik?')
            ->call('submitMessage')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Ratna Sari',
            'email_or_phone' => '081234567890',
            'subject' => 'Pertanyaan Pendaftaran Ekstrakurikuler',
            'is_read' => false,
        ]);
    }

    public function test_downloads_page_and_file_download_counter(): void
    {
        $doc = SchoolDocument::first();
        $this->assertNotNull($doc);

        $initialDownloads = $doc->download_count;

        $response = $this->get('/unduhan/' . $doc->id . '/download');
        $response->assertStatus(200);

        $doc->refresh();
        $this->assertEquals($initialDownloads + 1, $doc->download_count);
    }

    public function test_unknown_route_returns_custom_404_page(): void
    {
        $response = $this->get('/halaman-yang-pasti-tidak-ada-12345');
        $response->assertStatus(404);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('Kembali ke Beranda');
    }

    public function test_student_can_login_using_nisn_and_is_redirected(): void
    {
        $student = \App\Models\User::firstOrCreate(
            ['nisn' => '0089991111'],
            [
                'name' => 'Siswa Coba NISN',
                'email' => 'siswa.nisn@smpn4cepu.sch.id',
                'student_class' => '7A',
                'password' => bcrypt('password123'),
            ]
        );
        $student->assignRole('siswa');

        $response = $this->post('/login', [
            'login_id' => '0089991111',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/latihan-soal');
        $this->assertAuthenticatedAs($student);
    }
}
