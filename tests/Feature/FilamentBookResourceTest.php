<?php

namespace Tests\Feature;

use App\Enums\BookCategory;
use App\Filament\Resources\Books\Pages\CreateBook;
use App\Models\Book;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentBookResourceTest extends TestCase
{
    public function test_super_admin_can_access_books_resource(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        if (!$admin) {
            $admin = User::factory()->create(['email' => 'admin@smpn4cepu.sch.id']);
            $admin->assignRole('super_admin');
        }

        $response = $this->actingAs($admin)->get('/admin/books');
        $response->assertStatus(200);

        $responseCreate = $this->actingAs($admin)->get('/admin/books/create');
        $responseCreate->assertStatus(200);

        $book = Book::first();
        if ($book) {
            $responseEdit = $this->actingAs($admin)->get('/admin/books/' . $book->id . '/edit');
            $responseEdit->assertStatus(200);
        }
    }

    public function test_admin_can_create_book_manually_via_filament_form(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        if (!$admin) {
            $admin = User::factory()->create(['email' => 'admin@smpn4cepu.sch.id']);
            $admin->assignRole('super_admin');
        }

        $this->actingAs($admin);

        Book::where('slug', 'modul-karya-tulis-ilmiah-remaja-smpn-4-cepu')->forceDelete();

        Livewire::test(CreateBook::class)
            ->fillForm([
                'title' => 'Modul Karya Tulis Ilmiah Remaja SMPN 4 Cepu',
                'slug' => 'modul-karya-tulis-ilmiah-remaja-smpn-4-cepu',
                'author' => 'Tim Pembina KIR SMPN 4 Cepu',
                'publisher' => 'SMP Negeri 4 Cepu Press',
                'category' => BookCategory::KARYA_SEKOLAH->value,
                'published_year' => 2024,
                'isbn' => '978-602-99999-0-1',
                'page_count' => 95,
                'language' => 'id',
                'description' => 'Panduan penulisan karya ilmiah remaja bagi siswa SMPN 4 Cepu.',
                'physical_stock' => 15,
                'is_featured' => true,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('books', [
            'slug' => 'modul-karya-tulis-ilmiah-remaja-smpn-4-cepu',
            'author' => 'Tim Pembina KIR SMPN 4 Cepu',
            'physical_stock' => 15,
        ]);

        Book::where('slug', 'modul-karya-tulis-ilmiah-remaja-smpn-4-cepu')->forceDelete();
    }
}
