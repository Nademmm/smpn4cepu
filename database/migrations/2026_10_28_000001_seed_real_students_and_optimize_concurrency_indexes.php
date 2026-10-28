<?php

use Database\Seeders\StudentUserSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('name', 'users_name_idx');
                $table->index('student_class', 'users_student_class_idx');
            });
        }

        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'quiz_attempts_user_created_idx');
            });
        }

        if (Schema::hasTable('users') && Schema::hasTable('roles')) {
            (new StudentUserSeeder())->run();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->dropIndex('quiz_attempts_user_created_idx');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_student_class_idx');
                $table->dropIndex('users_name_idx');
            });
        }
    }
};
