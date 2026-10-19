<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan DatabaseSeeder secara otomatis sehingga deployment di PaaS/Railway
        // yang mengeksekusi 'php artisan migrate --force' langsung memiliki seluruh master data resmi,
        // konten berita, fasilitas, ekstrakurikuler, galeri, staf & guru, dokumen, serta akun pengguna.
        $seeder = new DatabaseSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
