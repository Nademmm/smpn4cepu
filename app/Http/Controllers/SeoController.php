<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Extracurricular;
use App\Models\GalleryPhoto;
use App\Models\LibraryBook;
use App\Models\Post;
use App\Models\SchoolDocument;
use App\Models\SchoolFacility;
use App\Models\StaffMember;
use App\Models\StudentStatistic;
use App\Support\Seo;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember('public_sitemap_xml_v2', 3600, fn (): string => $this->buildSitemap());

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        // Halaman bertanda noindex (login, pilketos) sengaja tidak di-Disallow agar tag noindex tetap terbaca crawler
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /livewire',
            'Disallow: /logout',
            'Disallow: /materi',
            'Disallow: /latihan-soal',
            'Disallow: /unduhan/*/download',
            'Disallow: /*?q=',
            '',
            'Sitemap: ' . Seo::url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function feed(): Response
    {
        $xml = Cache::remember('public_feed_xml_v1', 1800, fn (): string => $this->buildFeed());

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    private function buildSitemap(): string
    {
        $lastPost = $this->latest(Post::published());

        // [nama rute, path, model sumber lastmod]
        $pages = [
            ['/', 'home', $lastPost, 'daily', '1.0'],
            ['/profil', 'profile', null, 'monthly', '0.8'],
            ['/profil/prestasi', 'achievements', $this->latest(Achievement::query()), 'monthly', '0.7'],
            ['/kesiswaan/ekstrakurikuler', 'extracurriculars', $this->latest(Extracurricular::query()), 'monthly', '0.7'],
            ['/profil/fasilitas', 'facilities', $this->latest(SchoolFacility::query()), 'monthly', '0.7'],
            ['/guru-staf', 'staff', $this->latest(StaffMember::query()), 'monthly', '0.7'],
            ['/rekap-siswa', 'student-stats', $this->latest(StudentStatistic::query()), 'monthly', '0.6'],
            ['/perpustakaan', 'library', $this->latest(LibraryBook::query()), 'weekly', '0.7'],
            ['/berita', 'posts', $lastPost, 'daily', '0.9'],
            ['/galeri', 'gallery', $this->latest(GalleryPhoto::query()), 'weekly', '0.6'],
            ['/unduhan', 'downloads', $this->latest(SchoolDocument::query()), 'monthly', '0.6'],
            ['/kontak', 'contact', null, 'yearly', '0.6'],
        ];

        $urls = [];
        foreach ($pages as [$path, , $lastmod, $changefreq, $priority]) {
            $urls[] = [
                'loc' => Seo::url($path),
                'lastmod' => $lastmod?->toAtomString(),
                'changefreq' => $changefreq,
                'priority' => $priority,
                'images' => [],
            ];
        }

        try {
            $posts = Post::published()
                ->orderByDesc('published_at')
                ->get(['slug', 'title', 'featured_image', 'updated_at', 'published_at']);

            foreach ($posts as $post) {
                $image = Seo::absoluteAsset($post->featured_image_url);

                $urls[] = [
                    'loc' => Seo::url('/berita/' . $post->slug),
                    'lastmod' => ($post->updated_at ?? $post->published_at)?->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                    'images' => $image ? [['loc' => $image, 'title' => $post->title]] : [],
                ];
            }
        } catch (\Throwable $e) {
            // Fallback aman bila tabel belum dimigrasi
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if ($u['lastmod']) {
                $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            foreach ($u['images'] as $img) {
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>' . htmlspecialchars($img['loc'], ENT_XML1) . "</image:loc>\n";
                $xml .= '      <image:title>' . htmlspecialchars((string) $img['title'], ENT_XML1) . "</image:title>\n";
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        return $xml . "</urlset>\n";
    }

    private function buildFeed(): string
    {
        $siteName = 'SMP Negeri 4 Cepu';
        $home = Seo::url('/');

        try {
            $posts = Post::published()->orderByDesc('published_at')->limit(20)->get();
        } catch (\Throwable $e) {
            $posts = collect();
        }

        $esc = fn (?string $v): string => htmlspecialchars((string) $v, ENT_XML1, 'UTF-8');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n<channel>\n";
        $xml .= '  <title>' . $esc("Berita {$siteName}") . "</title>\n";
        $xml .= '  <link>' . $esc($home) . "</link>\n";
        $xml .= '  <description>' . $esc("Berita, pengumuman, dan agenda terbaru {$siteName}, Blora.") . "</description>\n";
        $xml .= "  <language>id-ID</language>\n";
        $xml .= '  <atom:link href="' . $esc(Seo::url('/feed.xml')) . '" rel="self" type="application/rss+xml"/>' . "\n";

        foreach ($posts as $post) {
            $link = Seo::url('/berita/' . $post->slug);
            $date = ($post->published_at ?? $post->created_at) ?? Carbon::now();

            $xml .= "  <item>\n";
            $xml .= '    <title>' . $esc($post->title) . "</title>\n";
            $xml .= '    <link>' . $esc($link) . "</link>\n";
            $xml .= '    <guid isPermaLink="true">' . $esc($link) . "</guid>\n";
            $xml .= '    <pubDate>' . $date->toRfc2822String() . "</pubDate>\n";
            if ($post->category) {
                $xml .= '    <category>' . $esc($post->category->label()) . "</category>\n";
            }
            $xml .= '    <description>' . $esc(strip_tags((string) $post->excerpt)) . "</description>\n";
            $xml .= "  </item>\n";
        }

        return $xml . "</channel>\n</rss>\n";
    }

    private function latest($query): ?Carbon
    {
        try {
            $value = $query->max('updated_at');

            return $value ? Carbon::parse($value) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
