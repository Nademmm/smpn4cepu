<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Berita, Pengumuman, Agenda
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('category', ['berita', 'pengumuman', 'agenda'])->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('content');
            $table->date('event_date')->nullable()->index();
            $table->string('featured_image')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tenaga Pendidik & Kependidikan
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 25)->nullable()->unique();
            $table->string('name');
            $table->string('position');
            $table->enum('employment_status', ['PNS', 'PPPK', 'Honorer'])->default('PNS');
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Statistik Siswa & Rombel
        Schema::create('student_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 9)->index();
            $table->unsignedTinyInteger('grade_level')->index(); // 7, 8, 9
            $table->string('class_name', 20); // Contoh: 7A, 8B
            $table->unsignedSmallInteger('male_count')->default(0);
            $table->unsignedSmallInteger('female_count')->default(0);
            $table->unsignedSmallInteger('total_count')->default(0);
            $table->timestamps();

            $table->unique(['academic_year', 'class_name']);
        });

        // Fasilitas Sekolah
        Schema::create('school_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_facilities');
        Schema::dropIfExists('student_statistics');
        Schema::dropIfExists('staff_members');
        Schema::dropIfExists('posts');
    }
};
