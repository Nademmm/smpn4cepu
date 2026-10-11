<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->index('author');
            $table->index('publisher');
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->index('title');
            $table->index(['grade_level', 'subject_id']);
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->index(['grade_level', 'subject_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropIndex(['grade_level', 'subject_id', 'is_active']);
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropIndex(['grade_level', 'subject_id']);
            $table->dropIndex(['title']);
        });

        Schema::table('library_books', function (Blueprint $table) {
            $table->dropIndex(['publisher']);
            $table->dropIndex(['author']);
        });
    }
};
