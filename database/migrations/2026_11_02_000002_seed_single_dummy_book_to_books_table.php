<?php

use Database\Seeders\BookSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan seeder 1 buku dummy contoh di produksi agar katalog perpustakaan memiliki sampel aktif
        try {
            (new BookSeeder())->run();
            Cache::forget('public_books_stats_v1');
            Cache::forget('public_books_category_counts_v1');
            Cache::forget('public_library_meta_v1');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

