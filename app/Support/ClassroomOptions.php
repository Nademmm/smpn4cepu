<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ClassroomOptions
{
    public const DEFAULT_CLASSES = [
        '7A', '7B', '7C', '7D', '7E', '7F', '7G',
        '8A', '8B', '8C', '8D', '8E', '8F', '8G',
        '9A', '9B', '9C', '9D', '9E', '9F',
    ];

    /**
     * Dapatkan daftar seluruh rombel (standar + dinamis dari siswa aktif), urut secara alami.
     *
     * @return array<int, string>
     */
    public static function allClasses(): array
    {
        return Cache::remember('school_available_classes_v1', 600, function (): array {
            $dbClasses = User::activeStudents()
                ->whereNotNull('student_class')
                ->where('student_class', '!=', '')
                ->where('student_class', 'not like', 'Kelas %')
                ->distinct()
                ->pluck('student_class')
                ->map(fn ($c) => strtoupper(trim((string) $c)))
                ->filter()
                ->all();

            $merged = array_values(array_unique(array_merge(self::DEFAULT_CLASSES, $dbClasses)));
            natsort($merged);

            return array_values($merged);
        });
    }

    /**
     * Format array [kode => label] untuk komponen Select / Filter Filament.
     *
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        $options = [];
        foreach (self::allClasses() as $cls) {
            $options[$cls] = 'Kelas ' . $cls;
        }

        return $options;
    }

    /**
     * Bersihkan cache daftar rombel agar langsung mengikuti perubahan tahun ajaran atau siswa.
     */
    public static function flushCache(): void
    {
        Cache::forget('school_available_classes_v1');
        Cache::forget('library_available_classes_v1');
    }
}
