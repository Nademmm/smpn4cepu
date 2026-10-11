<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'title' => 'Juara 1 Olimpiade Sains Nasional (OSN) Bidang IPA',
                'category' => 'Akademik',
                'level' => 'Kabupaten',
                'winner_name' => 'Fathia Rahma Azzahra (Kelas 8A)',
                'rank' => 'Juara 1',
                'event_date' => '2025-08-20',
                'description' => 'Berhasil meraih medali emas pada seleksi OSN jenjang SMP tingkat Kabupaten Blora dan melaju mewakili daerah ke tingkat Provinsi Jawa Tengah.',
                'photo_path' => 'IMG_6522.JPG',
                'display_order' => 1,
            ],
            [
                'title' => 'Juara 2 Festival & Lomba Seni Siswa Nasional (FLS2N) Tari Kreasi',
                'category' => 'Kesenian',
                'level' => 'Karesidenan',
                'winner_name' => 'Sanggar Tari Ceria SMPN 4 Cepu',
                'rank' => 'Juara 2',
                'event_date' => '2025-09-14',
                'description' => 'Penampilan memukau tari kreasi daerah Blora yang mengusung tema kelestarian hutan jati dalam ajang FLS2N tingkat Karesidenan Pati.',
                'photo_path' => 'IMG_6524.JPG',
                'display_order' => 2,
            ],
            [
                'title' => 'Juara 1 Turnamen Futsal Pelajar Tingkat Sub-Rayon Cepu',
                'category' => 'Olahraga',
                'level' => 'Kecamatan',
                'winner_name' => 'Tim Futsal Putra SMPN 4 Cepu',
                'rank' => 'Juara 1',
                'event_date' => '2025-10-05',
                'description' => 'Meraih trofi bergilir setelah mengalahkan lawan di partai final dengan sportivitas dan kerjasama tim yang solid.',
                'photo_path' => 'IMG_6530.JPG',
                'display_order' => 3,
            ],
            [
                'title' => 'Penghargaan Sekolah Adiwiyata Berkelanjutan',
                'category' => 'Lingkungan Hidup',
                'level' => 'Kabupaten',
                'winner_name' => 'Keluarga Besar SMP Negeri 4 Cepu',
                'rank' => 'Penghargaan Khusus',
                'event_date' => '2025-11-28',
                'description' => 'Apresiasi dari Dinas Lingkungan Hidup Kabupaten Blora atas konsistensi sekolah dalam pembudayaan pilah sampah, bank sampah mandiri, dan taman hijau rimbun.',
                'photo_path' => 'IMG_6531.JPG',
                'display_order' => 4,
            ],
        ];

        foreach ($data as $item) {
            Achievement::firstOrCreate(['title' => $item['title']], $item);
        }
    }
}
