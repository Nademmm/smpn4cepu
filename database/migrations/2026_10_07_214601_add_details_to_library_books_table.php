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
        Schema::table('library_books', function (Blueprint $table) {
            $table->text('synopsis')->nullable()->after('category');
            $table->unsignedSmallInteger('page_count')->nullable()->after('publication_year');
            $table->string('language', 50)->default('Bahasa Indonesia')->after('page_count');
            $table->string('call_number', 50)->nullable()->after('shelf_location');
            $table->string('digital_file_path')->nullable()->after('cover_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->dropColumn(['synopsis', 'page_count', 'language', 'call_number', 'digital_file_path']);
        });
    }
};
