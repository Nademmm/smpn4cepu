<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_teacher', function (Blueprint $table) {
            if (! Schema::hasColumn('subject_teacher', 'allowed_grades')) {
                $table->json('allowed_grades')->nullable()->after('teacher_id');
            }
            if (! Schema::hasColumn('subject_teacher', 'allowed_classes')) {
                $table->json('allowed_classes')->nullable()->after('allowed_grades');
            }
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            if (! Schema::hasColumn('learning_materials', 'target_classes')) {
                $table->json('target_classes')->nullable()->after('grade_level');
            }
        });

        Schema::table('question_banks', function (Blueprint $table) {
            if (! Schema::hasColumn('question_banks', 'target_classes')) {
                $table->json('target_classes')->nullable()->after('grade_level');
            }
        });

        if (Schema::hasTable('subjects') && Schema::hasTable('users') && Schema::hasTable('roles')) {
            try {
                (new \Database\Seeders\TeacherSubjectAssignmentSeeder())->run();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function down(): void
    {
        Schema::table('subject_teacher', function (Blueprint $table) {
            foreach (['allowed_classes', 'allowed_grades'] as $column) {
                if (Schema::hasColumn('subject_teacher', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            if (Schema::hasColumn('learning_materials', 'target_classes')) {
                $table->dropColumn('target_classes');
            }
        });

        Schema::table('question_banks', function (Blueprint $table) {
            if (Schema::hasColumn('question_banks', 'target_classes')) {
                $table->dropColumn('target_classes');
            }
        });
    }
};

