<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TeacherLoginTest extends TestCase
{
    /**
     * Pastikan semua akun guru/staf bisa login via portal /masuk dengan email dan diarahkan ke Filament.
     */
    public function test_all_teachers_can_login_via_portal_with_email(): void
    {
        $teachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'guru');
        })->get();

        $this->assertGreaterThan(0, $teachers->count(), 'Tidak ada guru yang ditemukan');

        foreach ($teachers as $teacher) {
            $response = $this->post(route('login.post'), [
                'login_id' => $teacher->email,
                'password' => 'GuruCepu2026!',
            ]);

            $response->assertRedirect(route('filament.admin.pages.dashboard'));
            $this->assertAuthenticatedAs($teacher);

            // Cek akses dashboard filament langsung
            $dashResponse = $this->actingAs($teacher)->get('/admin');
            $dashResponse->assertStatus(200);

            Auth::logout();
            $this->flushSession();
        }
    }

    /**
     * Pastikan semua guru yang memiliki NIP bisa login menggunakan NIP mereka.
     */
    public function test_teachers_with_nip_can_login_via_nip(): void
    {
        $teachersWithNip = User::whereHas('roles', function ($q) {
            $q->where('name', 'guru');
        })->whereNotNull('nip')->where('nip', '!=', '')->get();

        $this->assertGreaterThan(0, $teachersWithNip->count());

        foreach ($teachersWithNip as $teacher) {
            $response = $this->post(route('login.post'), [
                'login_id' => $teacher->nip,
                'password' => 'GuruCepu2026!',
            ]);

            $response->assertRedirect(route('filament.admin.pages.dashboard'));
            $this->assertAuthenticatedAs($teacher);

            Auth::logout();
            $this->flushSession();
        }
    }

    /**
     * Pastikan guru bisa login menggunakan Nama Lengkap mereka.
     */
    public function test_teachers_can_login_via_name(): void
    {
        $sampleTeachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'guru');
        })->take(5)->get();

        foreach ($sampleTeachers as $teacher) {
            $response = $this->post(route('login.post'), [
                'login_id' => $teacher->name,
                'password' => 'GuruCepu2026!',
            ]);

            $response->assertRedirect(route('filament.admin.pages.dashboard'));
            $this->assertAuthenticatedAs($teacher);

            Auth::logout();
            $this->flushSession();
        }
    }

    /**
     * Pastikan guru dapat mengakses modul materi dan bank soal di Filament.
     */
    public function test_teacher_can_access_learning_materials_and_question_banks(): void
    {
        $teacher = User::whereHas('roles', function ($q) {
            $q->where('name', 'guru');
        })->where('email', '!=', 'admin@smpn4cepu.sch.id')->first();

        $this->assertNotNull($teacher);

        $this->actingAs($teacher)->get('/admin/learning-materials')->assertStatus(200);
        $this->actingAs($teacher)->get('/admin/question-banks')->assertStatus(200);
    }

    /**
     * Pastikan akun guru ditolak jika kata sandi salah.
     */
    public function test_teacher_login_fails_with_wrong_password(): void
    {
        $teacher = User::whereHas('roles', function ($q) {
            $q->where('name', 'guru');
        })->first();

        $response = $this->post(route('login.post'), [
            'login_id' => $teacher->email,
            'password' => 'PasswordSalah123!',
        ]);

        $response->assertSessionHasErrors('login_id');
        $this->assertGuest();
    }
}
