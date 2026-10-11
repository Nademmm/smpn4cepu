<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::firstOrCreate(
            ['id' => 1],
            [
                'principal_name' => 'Drs. Suharto Widodo, M.Pd.',
                'principal_title' => 'Kepala SMP Negeri 4 Cepu',
                'principal_welcome_message' => 'Selamat datang di portal resmi SMP Negeri 4 Cepu. Kami berkomitmen memberikan pendidikan terbaik yang tidak hanya berfokus pada prestasi akademik, melainkan juga membentuk karakter siswa yang berintegritas, mandiri, dan berbudaya lingkungan hidup. Mari bersama mewujudkan ekosistem belajar digital yang inklusif dan berkualitas.',
                'school_phone' => '(0296) 421631',
                'school_email' => 'smpn4cepu@smpn4cepu.sch.id',
                'school_address' => 'Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315',
                'postal_code' => '58315',
                'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.8598914382894!2d111.57969197475874!3d-7.142079092862376!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e77717454c7b897%3A0xe962d3a3dbeafeb8!2sSMP%20Negeri%204%20Cepu!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid',
                'instagram_url' => 'https://instagram.com/smpn4cepu_official',
                'youtube_url' => 'https://youtube.com/@smpnegeri4cepu',
                'facebook_url' => 'https://facebook.com/smpnegeri4cepu',
                'whatsapp_contact' => '081234567890',
                'announcement_bar_active' => false,
                'announcement_bar_text' => null,
                'announcement_bar_link' => null,
            ]
        );
    }
}
