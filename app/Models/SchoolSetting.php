<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'principal_name',
        'principal_title',
        'principal_photo_path',
        'principal_welcome_message',
        'school_phone',
        'school_email',
        'school_address',
        'postal_code',
        'maps_embed_url',
        'instagram_url',
        'youtube_url',
        'facebook_url',
        'tiktok_url',
        'whatsapp_contact',
        'announcement_bar_active',
        'announcement_bar_text',
        'announcement_bar_link',
    ];

    protected function casts(): array
    {
        return [
            'announcement_bar_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('school_setting_singleton');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('school_setting_singleton');
        });
    }

    /**
     * Singleton instance untuk pengaturan situs dengan caching.
     */
    public static function current(): self
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('school_setting_singleton', function () {
            return static::firstOrCreate([], [
                'principal_name' => 'Drs. Suharto Widodo, M.Pd.',
                'principal_title' => 'Kepala SMP Negeri 4 Cepu',
                'principal_welcome_message' => 'Selamat datang di portal resmi SMP Negeri 4 Cepu. Kami berkomitmen memberikan pendidikan terbaik yang tidak hanya berfokus pada prestasi akademik, melainkan juga membentuk karakter siswa yang berintegritas, mandiri, dan berbudaya lingkungan hidup. Mari bersama mewujudkan ekosistem belajar digital yang inklusif dan berkualitas.',
                'school_phone' => '(0296) 421631',
                'school_email' => 'smpn4cepu@gmail.com',
                'school_address' => 'Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315',
                'postal_code' => '58315',
                'announcement_bar_active' => false,
                'announcement_bar_text' => null,
                'announcement_bar_link' => null,
            ]);
        });
    }

    public function getPrincipalPhotoUrlAttribute(): ?string
    {
        if (!$this->principal_photo_path) {
            return null;
        }

        if (str_starts_with($this->principal_photo_path, 'http://') || str_starts_with($this->principal_photo_path, 'https://')) {
            return $this->principal_photo_path;
        }

        if (file_exists(public_path('storage/' . $this->principal_photo_path))) {
            return asset('storage/' . $this->principal_photo_path);
        }

        if (file_exists(public_path($this->principal_photo_path))) {
            return asset($this->principal_photo_path);
        }

        if (file_exists(public_path('images/principal/' . $this->principal_photo_path))) {
            return asset('images/principal/' . $this->principal_photo_path);
        }

        return asset('storage/' . $this->principal_photo_path);
    }
}
