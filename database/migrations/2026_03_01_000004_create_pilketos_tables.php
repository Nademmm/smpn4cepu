<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('candidate_number')->unique();
            $table->string('candidate_name');
            $table->string('vice_candidate_name');
            $table->text('vision');
            $table->longText('mission');
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('total_votes_cached')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('election_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_fingerprint', 64)->unique()->index();
            $table->string('ip_subnet', 45)->index(); // Contoh: 192.168.1.0/24
            $table->string('user_agent_hash', 64)->index();
            $table->timestamp('voted_at')->index();
            $table->timestamps();
        });

        // Pemisahan data perangkat dan kandidat untuk menjaga hak pilih tetap anonim
        Schema::create('election_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('election_candidates')->cascadeOnDelete();
            $table->foreignId('device_id')->unique()->constrained('election_devices')->cascadeOnDelete();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_votes');
        Schema::dropIfExists('election_devices');
        Schema::dropIfExists('election_candidates');
    }
};
