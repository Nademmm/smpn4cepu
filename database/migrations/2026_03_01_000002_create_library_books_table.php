<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 20)->nullable()->unique()->index();
            $table->string('title')->index();
            $table->string('author');
            $table->string('publisher');
            $table->string('category', 50)->index();
            $table->unsignedSmallInteger('publication_year');
            $table->string('shelf_location', 50)->index();
            $table->unsignedSmallInteger('total_stock')->default(1);
            $table->unsignedSmallInteger('available_stock')->default(1);
            $table->string('cover_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
