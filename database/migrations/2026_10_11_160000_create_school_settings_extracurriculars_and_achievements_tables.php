<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pengaturan Identitas, Kontak & Sambutan Kepala Sekolah
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            // Sambutan Kepala Sekolah
            $table->string('principal_name')->default('Drs. Suharto Widodo, M.Pd.');
            $table->string('principal_title')->default('Kepala SMP Negeri 4 Cepu');
            $table->string('principal_photo_path')->nullable();
            $table->text('principal_welcome_message')->nullable();

            // Identitas & Kontak Resmi
            $table->string('school_phone')->default('(0296) 421631');
            $table->string('school_email')->default('smpn4cepu@smpn4cepu.sch.id');
            $table->string('school_address')->default('Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315');
            $table->string('postal_code', 10)->default('58315');
            $table->text('maps_embed_url')->nullable();
            
            // Media Sosial Resmi
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('whatsapp_contact')->nullable();

            // Banner Pengumuman Kilat (Marquee / Alert)
            $table->boolean('announcement_bar_active')->default(false);
            $table->string('announcement_bar_text')->nullable();
            $table->string('announcement_bar_link')->nullable();

            $table->timestamps();
        });

        // 2. Ekstrakurikuler Sekolah (Kesiswaan)
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('Wajib'); // Wajib, Olahraga, Kesenian, Keagamaan, Sains
            $table->string('leader_name')->nullable(); // Nama Guru Pembina / Pelatih
            $table->string('schedule_day')->nullable(); // Misal: Jumat, 14:00 - 16:30 WIB
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
        });

        // 3. Prestasi & Penghargaan Sekolah (Siswa & Satuan Pendidikan)
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('Akademik'); // Akademik, Olahraga, Kesenian, Lingkungan Hidup, Keagamaan
            $table->string('level')->default('Kabupaten'); // Kecamatan, Kabupaten, Karesidenan, Provinsi, Nasional
            $table->string('winner_name'); // Nama Siswa / Tim
            $table->string('rank')->default('Juara 1'); // Juara 1, Juara 2, Juara 3, Medali Emas, dll
            $table->date('event_date')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('extracurriculars');
        Schema::dropIfExists('school_settings');
    }
};
