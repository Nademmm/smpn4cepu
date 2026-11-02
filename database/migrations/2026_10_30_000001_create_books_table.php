<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->string('isbn', 30)->nullable()->index();
            $table->string('author')->nullable()->index();
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable()->index();
            $table->string('category', 50)->index();
            $table->text('description')->nullable();
            $table->string('cover_url', 1000)->nullable();
            $table->string('cover_path')->nullable();
            $table->string('pdf_url', 1000)->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->string('language', 50)->default('id');
            $table->unsignedInteger('physical_stock')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Buat 1 buku dummy contoh saat tabel dibuat agar perpustakaan memiliki 1 sampel siap baca
        try {
            (new \Database\Seeders\BookSeeder())->run();
        } catch (\Throwable $e) {
            // Abaikan bila gagal saat pengujian
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

