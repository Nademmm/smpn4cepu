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
            '/admin/extracurriculars',
            '/admin/extracurriculars/create',
            '/admin/learning-materials',
            '/admin/learning-materials/create',
            '/admin/question-banks',
            '/admin/question-banks/create',
            '/admin/school-settings',
            '/admin/school-facilities',
            '/admin/staff-members',
            '/admin/library-books',
        ];

        foreach ($resources as $uri) {
            $response = $this->actingAs($admin)->get($uri);
            $response->assertStatus(200);
        }
    }
}
