<?php

namespace Database\Seeders;

use App\Models\GalleryPhoto;
use Illuminate\Database\Seeder;

class GalleryPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $photos = [
            [
                'photo_path' => '20260717_092659.jpg',
                'title' => 'Gedung Utama dan Halaman Sekolah',
                'caption' => 'Suasana gerbang dan gedung induk pembelajaran SMP Negeri 4 Cepu yang asri dan representatif.',
                'tag' => 'Lingkungan Sekolah',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'photo_path' => 'IMG_5797.JPG',
                'title' => 'Aktivitas Belajar dan Keakraban Siswa',
                'caption' => 'Keseruan proses belajar dan interaksi antarsiswa di lingkungan sekolah.',
                'tag' => 'Kesiswaan',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'photo_path' => 'IMG_6518.JPG',
                'title' => 'Kegiatan Pembelajaran dan Kreativitas',
                'caption' => 'Siswa mengeksplorasi bakat serta keterampilan melalui kegiatan aplikatif di ruang kelas.',
                'tag' => 'Pembelajaran',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'photo_path' => 'IMG_6522.JPG',
                'title' => 'Kebersamaan Siswa dan Pendidik',
                'caption' => 'Interaksi hangat dan dialog edukatif antara guru dan peserta didik dalam suasana santai.',
                'tag' => 'Warga Sekolah',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'photo_path' => 'IMG_8383.JPG',
                'title' => 'Dokumentasi Program dan Upacara Sekolah',
                'caption' => 'Momentum pelaksanaan agenda penting, apel kedisiplinan, dan kegiatan satuan pendidikan.',
                'tag' => 'Agenda',
                'display_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($photos as $photo) {
            GalleryPhoto::firstOrCreate(['title' => $photo['title']], $photo);
        }
    }
}
