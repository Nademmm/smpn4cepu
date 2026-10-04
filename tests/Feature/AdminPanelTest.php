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
}
