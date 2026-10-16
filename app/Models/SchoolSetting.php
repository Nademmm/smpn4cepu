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
     * Singleton instance untuk pengaturan situs dengan pembaruan otomatis (self-healing) data resmi.
     */
    public static function current(): self
    {
        $latestWelcome = 'Puji Syukur kami panjatkan kehadirat Tuhan Yang Maha Esa, karena pada akhirnya SMP Negeri 4 Cepu dapat menyelesaikan dan menerbitkan website sekolah. Website sekolah ini terbit sebagai jawaban atas kebutuhan informasi yang berbasis pada teknologi yang merupakan sebuah tuntutan jaman yang tak terelakan untuk mendukung terwujudnya ekosistem belajar digital yang inklusif dan berkualitas serta pemenuhan layanan sumber referensi dan informasi yang dibutuhkan oleh masyarakat. Kami memahami bahwa kebutuhan informasi pendidikan bagi murid, orangtua/wali murid, alumni dan masyarakat pada umumnya merupakan sebuah kebutuhan yang penting, terlebih untuk memperoleh informasi pendidikan di sekolah. Oleh karena itu SMP Negeri 4 Cepu selalu berbenah untuk meningkatkan dan melengkapi sarana dan prasarana dalam pengelolaan sistem informasi sekolah. Semoga dengan terbitnya website sekolah ini mampu memenuhi kebutuhan informasi yang dibutuhkan oleh murid, orangtua/wali murid, alumni dan masyarakat. Amin.';

        $setting = static::first();

        if (!$setting) {
            $setting = static::create([
                'principal_name' => 'Prasetyo Cahyo Nugroho, S.Pd., M.M.',
                'principal_title' => 'Kepala SMP Negeri 4 Cepu',
                'principal_photo_path' => 'images/principal/FOTO_KS_1.jpg',
                'principal_welcome_message' => $latestWelcome,
                'school_phone' => '(0296) 421631',
                'school_email' => 'smpn4cepu@gmail.com',
                'school_address' => 'Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315',
                'postal_code' => '58315',
                'announcement_bar_active' => false,
            ]);
        } elseif ($setting->principal_name === 'Drs. Suharto Widodo, M.Pd.' || empty($setting->principal_photo_path) || str_contains($setting->principal_welcome_message ?? '', 'Selamat datang di portal resmi')) {
            $setting->update([
                'principal_name' => 'Prasetyo Cahyo Nugroho, S.Pd., M.M.',
                'principal_title' => 'Kepala SMP Negeri 4 Cepu',
                'principal_photo_path' => 'images/principal/FOTO_KS_1.jpg',
                'principal_welcome_message' => $latestWelcome,
            ]);
        }

        return $setting;
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
