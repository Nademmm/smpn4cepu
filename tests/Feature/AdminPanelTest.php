<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_admin_can_access_filament_dashboard(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_filament_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_access_all_filament_resources(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($admin);

        $resources = [
            '/admin/achievements',
            '/admin/achievements/create',
            '/admin/book-reservations',
            '/admin/contact-messages',
            '/admin/election-candidates',
            '/admin/election-candidates/create',
            '/admin/extracurriculars',
            '/admin/extracurriculars/create',
            '/admin/gallery-photos',
            '/admin/gallery-photos/create',
            '/admin/learning-materials',
            '/admin/learning-materials/create',
            '/admin/library-books',
            '/admin/library-books/create',
            '/admin/posts',
            '/admin/posts/create',
            '/admin/question-banks',
            '/admin/question-banks/create',
            '/admin/school-documents',
            '/admin/school-documents/create',
            '/admin/school-facilities',
            '/admin/school-facilities/create',
            '/admin/school-settings',
            '/admin/staff-members',
            '/admin/staff-members/create',
            '/admin/students',
            '/admin/students/create',
            '/admin/student-statistics',
            '/admin/student-statistics/create',
            '/admin/quiz-attempts',
            '/admin/election-votes',
            '/admin/subjects',
            '/admin/subjects/create',
            '/admin/users',
            '/admin/users/create',
            '/admin/shield/roles',
        ];

        foreach ($resources as $uri) {
            $response = $this->actingAs($admin)->get($uri);
            $response->assertStatus(200, "Failed asserting 200 OK on {$uri}");
        }
    }

    public function test_rbac_role_isolation_on_admin_panel(): void
    {
        $student = User::whereNotNull('nisn')->first();
        $this->assertNotNull($student);
        $this->actingAs($student)->get('/admin')->assertStatus(403);

        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $this->assertNotNull($guru);
        $this->flushSession();
        $this->actingAs($guru)->get('/admin/learning-materials')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/question-banks')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/students')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/quiz-attempts')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/users')->assertStatus(403);
        $this->actingAs($guru)->get('/admin/election-candidates')->assertStatus(403);
        $this->actingAs($guru)->get('/admin/election-votes')->assertStatus(403);

        $perpus = User::where('email', 'perpus@smpn4cepu.sch.id')->first();
        $this->assertNotNull($perpus);
        $this->flushSession();
        $this->actingAs($perpus)->get('/admin/library-books')->assertStatus(200);
        $this->actingAs($perpus)->get('/admin/book-reservations')->assertStatus(200);
        $this->actingAs($perpus)->get('/admin/users')->assertStatus(403);
        $this->actingAs($perpus)->get('/admin/students')->assertStatus(403);

        $pilketos = User::where('email', 'pilketos@smpn4cepu.sch.id')->first();
        $this->assertNotNull($pilketos);
        $this->flushSession();
        $this->actingAs($pilketos)->get('/admin/election-candidates')->assertStatus(200);
        $this->actingAs($pilketos)->get('/admin/election-votes')->assertStatus(200);
        $this->actingAs($pilketos)->get('/admin/users')->assertStatus(403);
        $this->actingAs($pilketos)->get('/admin/students')->assertStatus(403);
    }
}
