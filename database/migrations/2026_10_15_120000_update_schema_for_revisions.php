<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah is_active ke learning_materials
        if (Schema::hasTable('learning_materials') && !Schema::hasColumn('learning_materials', 'is_active')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->index();
            });
        }

        // 2. Tambah user_id ke quiz_attempts
        if (Schema::hasTable('quiz_attempts') && !Schema::hasColumn('quiz_attempts', 'user_id')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        // 3. Tambah tiktok_url ke school_settings
        if (Schema::hasTable('school_settings') && !Schema::hasColumn('school_settings', 'tiktok_url')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->string('tiktok_url')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('learning_materials') && Schema::hasColumn('learning_materials', 'is_active')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        if (Schema::hasTable('quiz_attempts') && Schema::hasColumn('quiz_attempts', 'user_id')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasTable('school_settings') && Schema::hasColumn('school_settings', 'tiktok_url')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->dropColumn('tiktok_url');
            });
        }
    }
};
