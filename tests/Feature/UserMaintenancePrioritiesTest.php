<?php

namespace Tests\Feature;

use App\Livewire\Public\DigitalLibrary;
use App\Models\Book;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserMaintenancePrioritiesTest extends TestCase
{
    public function test_borrow_modal_can_be_opened_for_book_with_physical_stock(): void
    {
        $book = Book::active()->where('physical_stock', '>', 0)->first();
        if (!$book) {
            $book = Book::create([
                'title' => 'Buku Uji Sirkulasi Fisik',
                'slug' => 'buku-uji-sirkulasi-fisik',
                'category' => \App\Enums\BookCategory::BUKU_PAKET->value,
                'author' => 'Pengarang Uji',
                'physical_stock' => 5,
                'is_active' => true,
            ]);
        }

        Livewire::test(DigitalLibrary::class)
            ->call('openBorrowModal', $book->id)
            ->assertSet('showBorrowModal', true)
            ->assertSet('borrowBookId', $book->id);
    }

    public function test_class_filter_and_student_selection_in_library(): void
    {
        $student = User::whereNotNull('nisn')->where('student_class', '7A')->first();
        if (!$student) {
            $student = User::firstOrCreate(
                ['email' => 'siswa.7a@smpn4cepu.sch.id'],
                [
                    'name' => 'Siswa Kelas 7A Uji',
                    'nisn' => '0081234567',
                    'student_class' => '7A',
                    'password' => Hash::make('password123'),
                ]
            );
            $student->assignRole('siswa');
        }

        Livewire::test(DigitalLibrary::class)
            ->set('selectedClassFilter', '7A')
            ->assertSet('borrowerClass', '7A')
            ->call('selectStudent', $student->id)
            ->assertSet('borrowerName', $student->name)
            ->assertSet('borrowerNisn', $student->nisn);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('passwordLama123'),
        ]);

        $response = $this->actingAs($user)->post('/profil/ganti-password', [
            'current_password' => 'passwordLama123',
            'password' => 'passwordBaru2026!',
            'password_confirmation' => 'passwordBaru2026!',
        ]);

        $response->assertSessionHas('password_status');
        $user->refresh();
        $this->assertTrue(Hash::check('passwordBaru2026!', $user->password));
    }

    public function test_change_password_fails_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('passwordAsli123'),
        ]);

        $response = $this->actingAs($user)->post('/profil/ganti-password', [
            'current_password' => 'salahPassword',
            'password' => 'passwordBaru2026!',
            'password_confirmation' => 'passwordBaru2026!',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $user->refresh();
        $this->assertFalse(Hash::check('passwordBaru2026!', $user->password));
    }

    public function test_guru_cannot_create_books_or_subjects_in_admin(): void
    {
        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $this->assertNotNull($guru);

        // Guru dilarang membuat buku perpustakaan
        $this->actingAs($guru)->get('/admin/books/create')->assertStatus(403);
        $this->actingAs($guru)->get('/admin/library-books/create')->assertStatus(403);

        // Guru dilarang membuat mata pelajaran (hanya super_admin)
        $this->actingAs($guru)->get('/admin/subjects/create')->assertStatus(403);

        // Guru tetap bisa mengakses pembelajaran dan melihat nilai kuis
        $this->actingAs($guru)->get('/admin/learning-materials')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/question-banks')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/quiz-attempts')->assertStatus(200);
    }

    public function test_admin_can_assign_teacher_to_subject_and_relationship_works(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $subject = Subject::first();

        $this->assertNotNull($admin);
        $this->assertNotNull($guru);
        $this->assertNotNull($subject);

        $subject->teachers()->sync([$guru->id]);
        $this->assertTrue($subject->teachers->contains($guru));
        $this->assertTrue($guru->subjects->contains($subject));
    }
}
