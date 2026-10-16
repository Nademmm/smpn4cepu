<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::updateOrCreate(
            ['id' => 1],
            [
                'principal_name' => 'Prasetyo Cahyo Nugroho, S.Pd., M.M.',
                'principal_title' => 'Kepala SMP Negeri 4 Cepu',
                'principal_photo_path' => 'images/principal/FOTO_KS_1.jpg',
                'principal_welcome_message' => 'Puji Syukur kami panjatkan kehadirat Tuhan Yang Maha Esa, karena pada akhirnya SMP Negeri 4 Cepu dapat menyelesaikan dan menerbitkan website sekolah. Website sekolah ini terbit sebagai jawaban atas kebutuhan informasi yang berbasis pada teknologi yang merupakan sebuah tuntutan jaman yang tak terelakan untuk mendukung terwujudnya ekosistem belajar digital yang inklusif dan berkualitas serta pemenuhan layanan sumber referensi dan informasi yang dibutuhkan oleh masyarakat. Kami memahami bahwa kebutuhan informasi pendidikan bagi murid, orangtua/wali murid, alumni dan masyarakat pada umumnya merupakan sebuah kebutuhan yang penting, terlebih untuk memperoleh informasi pendidikan di sekolah. Oleh karena itu SMP Negeri 4 Cepu selalu berbenah untuk meningkatkan dan melengkapi sarana dan prasarana dalam pengelolaan sistem informasi sekolah. Semoga dengan terbitnya website sekolah ini mampu memenuhi kebutuhan informasi yang dibutuhkan oleh murid, orangtua/wali murid, alumni dan masyarakat. Amin.',
                'school_phone' => '(0296) 421631',
                'school_email' => 'smpn4cepu@gmail.com',
                'school_address' => 'Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315',
                'postal_code' => '58315',
                'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.8598914382894!2d111.57969197475874!3d-7.142079092862376!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e77717454c7b897%3A0xe962d3a3dbeafeb8!2sSMP%20Negeri%204%20Cepu!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid',
                'instagram_url' => 'https://www.instagram.com/smpn4cepu_official?cplk=MW52cmoxajVnenVxNg==',
                'youtube_url' => 'https://youtube.com/@smpn4cepu?si=fh-hGAcEl9b_rj2S',
                'facebook_url' => 'https://www.facebook.com/share/1F72gGkHoD/',
                'tiktok_url' => 'https://www.tiktok.com/@smpn4cepu?_r=1&_t=ZS-9AQlZajBgUG',
                'whatsapp_contact' => '081234567890',
                'announcement_bar_active' => false,
                'announcement_bar_text' => null,
                'announcement_bar_link' => null,
            ]
        );
    }
}
