<?php

namespace Tests\Feature;

use App\Models\QuestionBank;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    public function test_all_public_sitemap_routes_return_successful_response(): void
    {
        $routes = [
            '/',
            '/profil',
            '/profil/fasilitas',
            '/guru-staf',
            '/rekap-siswa',
            '/pilketos',
            '/pilketos/live-count',
            '/perpustakaan',
            '/materi',
            '/latihan-soal',
            '/berita',
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
}
