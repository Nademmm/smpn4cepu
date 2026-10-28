<?php

namespace App\Support;

class Seo
{
    /**
     * URL dasar situs yang dipakai untuk canonical, sitemap, robots, dan JSON-LD.
     *
     * Memakai APP_URL agar versi "www" dan domain sementara tidak pernah menjadi
     * canonical. Bila APP_URL belum diset ke domain asli (localhost atau domain
     * hosting sementara), turunkan dari request aktif tanpa awalan "www.".
     */
    public static function baseUrl(): string
    {
        $configured = rtrim((string) config('app.url'), '/');
        $host = (string) parse_url($configured, PHP_URL_HOST);

        $isRealDomain = $host !== ''
            && ! in_array($host, ['localhost', '127.0.0.1'], true)
            && ! str_ends_with($host, '.railway.app');

        if ($isRealDomain) {
            return $configured;
        }

        return preg_replace('#^(https?://)www\.#i', '$1', rtrim(request()->root(), '/'));
    }

    /**
     * Bangun URL absolut dari path relatif. Path "/" menghasilkan URL tanpa
     * garis miring akhir agar konsisten dengan canonical beranda.
     */
    public static function url(string $path = ''): string
    {
        $path = '/' . ltrim($path, '/');

        return self::baseUrl() . ($path === '/' ? '' : $path);
    }

    /**
     * Ubah URL aset (bisa memakai host berbeda) menjadi URL absolut di domain utama.
     */
    public static function absoluteAsset(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return self::url($url);
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        return self::url($path) . ($query ? '?' . $query : '');
    }

    /**
     * Kunci cache yang harus dibersihkan ketika konten publik berubah.
     *
     * @return array<int, string>
     */
    public static function cacheKeys(): array
    {
        return ['public_sitemap_xml_v2', 'public_feed_xml_v1'];
    }
}
