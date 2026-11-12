<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah softDeletes pada question_banks jika belum ada
        if (Schema::hasTable('question_banks') && ! Schema::hasColumn('question_banks', 'deleted_at')) {
            Schema::table('question_banks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 2. Tambah period_year dan status pada election_candidates untuk arsip pilketos
        if (Schema::hasTable('election_candidates')) {
            Schema::table('election_candidates', function (Blueprint $table) {
                if (! Schema::hasColumn('election_candidates', 'period_year')) {
                    $table->string('period_year', 20)->default('2026/2027')->index();
                }
                if (! Schema::hasColumn('election_candidates', 'status')) {
                    $table->string('status', 20)->default('aktif')->index(); // aktif, diarsipkan
                }
            });
        }

        // 3. Buat tabel system_backups untuk pelacakan backup dan pemulihan
        if (! Schema::hasTable('system_backups')) {
            Schema::create('system_backups', function (Blueprint $table) {
                $table->id();
                $table->string('filename')->unique();
                $table->string('disk')->default('local');
                $table->string('filepath');
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->string('scope')->default('Database SQLite + File Unggahan (PDF & Gambar)');
                $table->string('status')->default('berhasil'); // berhasil, gagal
                $table->timestamp('tested_at')->nullable();
                $table->string('test_status')->nullable(); // lolos, gagal
                $table->text('test_notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['created_at', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_backups');

        if (Schema::hasTable('election_candidates')) {
            Schema::table('election_candidates', function (Blueprint $table) {
                $table->dropColumn(['period_year', 'status']);
            });
        }

        if (Schema::hasTable('question_banks')) {
            Schema::table('question_banks', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
