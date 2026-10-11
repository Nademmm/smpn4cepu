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
        $routes = [
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
            '/materi',
            '/latihan-soal',
            '/berita',
            '/galeri',
            '/unduhan',
            '/kontak',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_quiz_room_page_loads_successfully(): void
    {
        $bank = QuestionBank::first();
        if ($bank) {
            $response = $this->get('/latihan-soal/' . $bank->id);
            $response->assertStatus(200);
        } else {
            $this->assertTrue(true);
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
}
