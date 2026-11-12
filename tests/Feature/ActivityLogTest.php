<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    public function test_activity_log_records_important_events_accurately(): void
    {
        $operator = User::where('email', 'operator@smpn4cepu.sch.id')->first();
        $this->assertNotNull($operator);

        $student = User::whereNotNull('nisn')->first();
        $this->assertNotNull($student);

        $log = ActivityLog::record(
            action: 'move_class',
            description: "{$operator->name} memindahkan Siswa {$student->name} dari rombel 7A ke 7B.",
            reason: 'penyesuaian pembagian kelas.',
            subject: $student,
            properties: [
                'from_class' => '7A',
                'to_class' => '7B',
            ],
            module: 'Kesiswaan',
            actor: $operator
        );

        $this->assertDatabaseHas('activity_logs', [
            'id' => $log->id,
            'actor_name' => $operator->name,
            'actor_role' => 'operator_sekolah',
            'action' => 'move_class',
            'reason' => 'penyesuaian pembagian kelas.',
            'module' => 'Kesiswaan',
        ]);

        $this->assertNotEmpty($log->formatted_created_at);
        $this->assertEquals($student->id, $log->subject_id);
    }

    public function test_activity_log_is_strictly_super_admin_only(): void
    {
        $superAdmin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($superAdmin);
        $this->actingAs($superAdmin)->get('/admin/activity-logs')->assertStatus(200);

        $operator = User::where('email', 'operator@smpn4cepu.sch.id')->first();
        $this->assertNotNull($operator);
        $this->flushSession();
        $this->actingAs($operator)->get('/admin/activity-logs')->assertStatus(403);

        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $this->assertNotNull($guru);
        $this->flushSession();
        $this->actingAs($guru)->get('/admin/activity-logs')->assertStatus(403);

        $waliKelas = User::where('email', 'walikelas@smpn4cepu.sch.id')->first();
        $this->assertNotNull($waliKelas);
        $this->flushSession();
        $this->actingAs($waliKelas)->get('/admin/activity-logs')->assertStatus(403);

        $perpus = User::where('email', 'perpus@smpn4cepu.sch.id')->first();
        $this->assertNotNull($perpus);
        $this->flushSession();
        $this->actingAs($perpus)->get('/admin/activity-logs')->assertStatus(403);

        $pilketos = User::where('email', 'pilketos@smpn4cepu.sch.id')->first();
        $this->assertNotNull($pilketos);
        $this->flushSession();
        $this->actingAs($pilketos)->get('/admin/activity-logs')->assertStatus(403);

        $student = User::whereNotNull('nisn')->first();
        $this->assertNotNull($student);
        $this->flushSession();
        $this->actingAs($student)->get('/admin/activity-logs')->assertStatus(403);
    }
}

