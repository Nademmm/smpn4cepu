<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_name')->default('Sistem');
                $table->string('actor_role')->nullable();
                $table->string('module')->default('Kesiswaan');
                $table->string('action')->default('update');
                $table->text('description');
                $table->text('reason')->nullable();
                $table->nullableMorphs('subject');
                $table->string('subject_label')->nullable();
                $table->json('properties')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['created_at', 'module']);
                $table->index('action');
                $table->index('actor_id');
            });
        }

        try {
            if (Schema::hasTable('activity_logs') && DB::table('activity_logs')->count() === 0) {
                $operator = DB::table('users')->where('email', 'operator@smpn4cepu.sch.id')->first();
                $admin = DB::table('users')->where('email', 'admin@smpn4cepu.sch.id')->first();
                $student = DB::table('users')->whereNotNull('nisn')->first();

                if ($operator && $admin) {
                    DB::table('activity_logs')->insert([
                        [
                            'actor_id' => $operator->id,
                            'actor_name' => 'Operator A',
                            'actor_role' => 'operator_sekolah',
                            'module' => 'Kesiswaan',
                            'action' => 'move_class',
                            'description' => 'Operator A memindahkan Siswa B dari rombel 7A ke 7B.',
                            'reason' => 'penyesuaian pembagian kelas.',
                            'subject_type' => 'App\\Models\\User',
                            'subject_id' => $student?->id ?? 1,
                            'subject_label' => 'Siswa B',
                            'properties' => json_encode([
                                'from_class' => '7A',
                                'to_class' => '7B',
                                'student_name' => 'Siswa B',
                            ]),
                            'ip_address' => null,
                            'user_agent' => null,
                            'created_at' => '2026-11-12 14:30:00',
                            'updated_at' => '2026-11-12 14:30:00',
                        ],
                        [
                            'actor_id' => $admin->id,
                            'actor_name' => $admin->name,
                            'actor_role' => 'super_admin',
                            'module' => 'Tahun Ajaran',
                            'action' => 'activate_academic_year',
                            'description' => "{$admin->name} mengaktifkan Tahun Ajaran 2026/2027 dan menutup Tahun Ajaran 2025/2026.",
                            'reason' => 'Aktivasi kalender akademik resmi SMP Negeri 4 Cepu.',
                            'subject_type' => null,
                            'subject_id' => null,
                            'subject_label' => null,
                            'properties' => json_encode([
                                'previous_year' => '2025/2026',
                                'new_active_year' => '2026/2027',
                            ]),
                            'ip_address' => null,
                            'user_agent' => null,
                            'created_at' => '2026-11-10 08:30:00',
                            'updated_at' => '2026-11-10 08:30:00',
                        ],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

