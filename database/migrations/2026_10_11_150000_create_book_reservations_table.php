<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('library_books')->cascadeOnDelete();
            $table->string('reservation_code', 30)->unique()->index();
            $table->string('borrower_name')->index();
            $table->string('borrower_class', 50)->index();
            $table->date('pickup_date')->index();
            $table->enum('status', ['menunggu', 'diambil', 'dibatalkan', 'selesai'])->default('menunggu')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reservations');
    }
};
