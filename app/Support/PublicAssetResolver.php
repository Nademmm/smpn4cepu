<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class PublicAssetResolver
{
    /**
     * In-memory cache of public_path() file existence checks across the request lifecycle,
     * backed by persistent application cache to eliminate slow Windows/OneDrive stat() calls.
     *
     * @var array<string, bool>|null
     */
    protected static ?array $memoryCache = null;

    protected const CACHE_KEY = 'public_asset_existence_map_v1';

    /**
     * Check if a relative path under public_path() exists, using a cached map.
     */
    public static function exists(string $relativePath): bool
    {
        $normalized = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
        if ($normalized === '') {
            return false;
        }

        if (static::$memoryCache === null) {
            try {
                static::$memoryCache = Cache::get(self::CACHE_KEY, []);
                if (! is_array(static::$memoryCache)) {
                    static::$memoryCache = [];
                }
            } catch (\Throwable $e) {
                static::$memoryCache = [];
            }
        }

        if (array_key_exists($normalized, static::$memoryCache)) {
            return static::$memoryCache[$normalized];
        }

        $exists = is_file(public_path($normalized));
        static::$memoryCache[$normalized] = $exists;

        try {
            Cache::put(self::CACHE_KEY, static::$memoryCache, now()->addHours(12));
        } catch (\Throwable $e) {
            // Ignore cache write failures
        }

        return $exists;
    }

    /**
     * Resolve an asset URL from candidate relative paths under public/, falling back to $defaultRelative.
     *
     * @param  string|null  $rawPath
     * @param  array<int, string>  $candidatePrefixes
     * @param  string|null  $fallbackAsset
     */
    public static function resolve(?string $rawPath, array $candidatePrefixes = ['storage/', '', 'images/assets/'], ?string $fallbackAsset = null): ?string
    {
        if (! $rawPath) {
            return $fallbackAsset ? asset($fallbackAsset) : null;
        }

        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }

        $clean = ltrim(str_replace('\\', '/', trim($rawPath)), '/');

        foreach ($candidatePrefixes as $prefix) {
            $candidate = $prefix === '' ? $clean : rtrim($prefix, '/') . '/' . ($prefix === 'images/assets/' ? basename($clean) : $clean);
            if (static::exists($candidate)) {
                return asset($candidate);
            }
        }

        return $fallbackAsset ? asset($fallbackAsset) : asset('storage/' . $clean);
    }

    /**
     * Flush the cached existence map when new files/models are saved.
     */
    public static function flush(): void
    {
        static::$memoryCache = null;
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}
