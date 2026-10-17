<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use Illuminate\Database\Seeder;

class ExtracurricularSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Pramuka Gugus Depan',
                'slug' => 'pramuka-gugus-depan',
                'category' => 'Wajib',
                'leader_name' => 'Bambang Sudarmanto, S.Pd.',
                'schedule_day' => 'Jumat, 14:00 - 16:30 WIB',
                'description' => 'Membentuk watak kedisiplinan, kemandirian, kecakapan bertahan hidup, kepedulian lingkungan hidup, dan jiwa kepemimpinan generasi muda.',
                'photo_path' => 'IMG_6522.JPG',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Palang Merah Remaja (PMR)',
                'slug' => 'palang-merah-remaja-pmr',
                'category' => 'Kemanusiaan',
                'leader_name' => 'Siti Nurhaliza, S.Pd.',
                'schedule_day' => 'Kamis, 15:00 - 16:30 WIB',
                'description' => 'Pelatihan pertolongan pertama, penanganan cedera ringan, kepedulian sosial, dan edukasi pola hidup bersih sehat bagi remaja.',
                'photo_path' => 'IMG_6518.JPG',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Seni Tari & Karawitan Tradisional',
                'slug' => 'seni-tari-karawitan',
                'category' => 'Kesenian',
                'leader_name' => 'Endang Lestari, S.Sn.',
                'schedule_day' => 'Rabu, 14:30 - 16:30 WIB',
                'description' => 'Melestarikan warisan budaya nusantara melalui olah gerak tari tradisional Jawa Tengah dan pergelaran kesenian gamelan karawitan.',
                'photo_path' => 'IMG_5797.JPG',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Futsal & Bulu Tangkis',
                'slug' => 'futsal-dan-bulu-tangkis',
                'category' => 'Olahraga',
                'leader_name' => 'Rudi Hartono, S.Pd.Jas.',
                'schedule_day' => 'Selasa & Sabtu, 15:30 - 17:00 WIB',
                'description' => 'Mengasah ketangkasan fisik, sportivitas tim, dan persiapan kontingen atlet sekolah untuk kejuaraan O2SN serta turnamen pelajar se-Kabupaten Blora.',
                'photo_path' => '20260717_092659.jpg',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Rohis & Bimbingan Baca Tulis Al-Quran (BTA)',
                'slug' => 'rohis-dan-bta',
                'category' => 'Keagamaan',
                'leader_name' => 'Ahmad Fauzan, S.Pd.I.',
                'schedule_day' => 'Senin, 15:00 - 16:30 WIB',
                'description' => 'Pembinaan akhlak mulia, hafalan juz amma, seni hadrah, dan bimbingan tajwid tartil Al-Quran selaras dengan visi Ceria Beriman.',
                'photo_path' => 'IMG_8383.JPG',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'Klub Komputer & Literasi Digital',
                'slug' => 'klub-komputer-dan-digital',
                'category' => 'Sains & Teknologi',
                'leader_name' => 'Wahyu Hidayat, S.Kom.',
                'schedule_day' => 'Rabu, 14:30 - 16:00 WIB',
                'description' => 'Pelatihan dasar desain grafis, coding pemula, pemanfaatan Google Suite for Education, dan etika berinternet sehat di laboratorium komputer.',
                'photo_path' => 'IMG_6522.JPG',
                'is_active' => true,
                'display_order' => 6,
            ],
        ];

        foreach ($data as $item) {
            Extracurricular::firstOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
