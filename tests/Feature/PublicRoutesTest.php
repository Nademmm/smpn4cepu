<?php

namespace Tests\Feature;

use App\Livewire\Public\ContactForm;
use App\Models\LibraryBook;
use App\Models\Post;
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
            '/sitemap.xml',
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

        $bank = QuestionBank::where('is_active', true)->first();
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

        $bank = QuestionBank::where('is_active', true)->first();
        if ($bank) {
            $this->actingAs($user)->get('/latihan-soal/' . $bank->id)->assertStatus(200);
        }
    }

    public function test_library_book_reservation_validates_and_creates_ticket(): void
    {
        $book = LibraryBook::where('available_stock', '>', 0)->first();
        $this->assertNotNull($book);

        $response = $this->postJson('/perpustakaan/reservasi', [
            'book_id' => $book->id,
            'borrower_name' => 'Ahmad Fauzi',
            'borrower_class' => '8A',
            'pickup_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'reservation_code', 'message']);
    }

    public function test_post_detail_and_scheduled_post_visibility(): void
    {
        $publishedPost = Post::published()->first();
        $this->assertNotNull($publishedPost);
        $this->get('/berita/' . $publishedPost->slug)->assertStatus(200);

        $futurePost = Post::create([
            'author_id' => $publishedPost->author_id,
            'title' => 'Berita Terjadwal Minggu Depan',
            'slug' => 'berita-terjadwal-minggu-depan',
            'category' => \App\Enums\PostCategory::BERITA,
            'excerpt' => 'Ringkasan berita terjadwal',
            'content' => 'Konten berita terjadwal',
            'is_published' => true,
            'published_at' => now()->addDays(7),
        ]);

        $this->get('/berita/' . $futurePost->slug)->assertStatus(404);
    }

    public function test_contact_form_submits_message_and_blocks_honeypot(): void
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

        Livewire::test(ContactForm::class)
            ->set('honey', 'bot-spam-value')
            ->set('name', 'Spam Bot')
            ->set('email_or_phone', 'spam@bot.com')
            ->set('subject', 'Spam Subject')
            ->set('message', 'Spam message body')
            ->call('submitMessage')
            ->assertSet('submitted', true);

        $this->assertDatabaseMissing('contact_messages', [
            'name' => 'Spam Bot',
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

    public function test_seo_endpoints_and_meta_are_consistent(): void
    {
        $robots = $this->get('/robots.txt');
        $robots->assertOk();
        $this->assertStringContainsString('Sitemap: ' . url('/sitemap.xml'), $robots->getContent());
        $this->assertStringContainsString('Disallow: /admin', $robots->getContent());

        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOk();
        $this->assertStringContainsString('<loc>' . route('posts') . '</loc>', $sitemap->getContent());
        $this->assertStringNotContainsString('/pilketos', $sitemap->getContent());

        $home = $this->get('/');
        $home->assertOk();
        $home->assertSee('<link rel="canonical" href="' . url('/') . '">', false);
        $home->assertSee('content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1"', false);
        $home->assertSee('"@type":"EducationalOrganization"', false);
        $home->assertSee('"@type":"WebPage"', false);
        $home->assertSee('"openingHoursSpecification"', false);
        $home->assertSee('type="application/rss+xml"', false);
        $home->assertSee('name="geo.region"', false);
        $home->assertDontSee('fonts.googleapis.com', false);

        $this->get('/login')->assertSee('content="noindex, follow"', false);
        $this->get('/pilketos')->assertSee('content="noindex, follow"', false);
        $this->get('/berita?q=lomba')->assertSee('content="noindex, follow"', false);
    }

    public function test_seo_machine_endpoints_are_cookie_free_and_valid(): void
    {
        foreach (['/sitemap.xml', '/robots.txt', '/feed.xml'] as $uri) {
            $response = $this->get($uri);
            $response->assertOk();
            $this->assertEmpty($response->headers->getCookies(), "Respons {$uri} tidak boleh mengirim cookie");
        }

        $sitemap = $this->get('/sitemap.xml')->getContent();
        $this->assertNotFalse(simplexml_load_string($sitemap), 'Sitemap harus XML valid');
        $this->assertStringContainsString('<lastmod>', $sitemap);

        $feed = $this->get('/feed.xml');
        $this->assertNotFalse(simplexml_load_string($feed->getContent()), 'Feed harus XML valid');
        $this->assertStringContainsString('application/rss+xml', $feed->headers->get('Content-Type'));
    }

    public function test_post_detail_has_news_article_schema_and_multilevel_breadcrumb(): void
    {
        $post = Post::published()->first();
        $this->assertNotNull($post, 'Seeder harus menyediakan minimal satu berita terbit');

        $response = $this->get(route('post.detail', $post->slug));
        $response->assertOk();
        $response->assertSee('"@type":"NewsArticle"', false);
        $response->assertSee('"@type":"EducationalOrganization"', false);
        $response->assertSee('<meta itemprop="position" content="3" />', false);
        $response->assertSee('<link rel="canonical" href="' . url('/berita/' . $post->slug) . '">', false);
    }
}
