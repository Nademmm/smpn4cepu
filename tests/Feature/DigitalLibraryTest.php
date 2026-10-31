<?php

namespace Tests\Feature;

use App\Enums\BookCategory;
use App\Livewire\Public\DigitalLibrary;
use App\Models\Book;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalLibraryTest extends TestCase
{
    public function test_digital_library_page_renders_successfully(): void
    {
        $response = $this->get('/perpustakaan');
        $response->assertStatus(200);
        $response->assertSee('Perpustakaan Digital');
        $response->assertSeeLivewire(DigitalLibrary::class);
    }

    public function test_digital_library_component_displays_books_and_stats(): void
    {
        $book = Book::active()->first();
        $this->assertNotNull($book);

        Livewire::test(DigitalLibrary::class)
            ->assertStatus(200)
            ->assertSee($book->title)
            ->assertSee($book->author);
    }

    public function test_digital_library_search_filters_results(): void
    {
        $book = Book::where('title', 'like', '%Matematika%')->first();
        $this->assertNotNull($book);

        Livewire::test(DigitalLibrary::class)
            ->set('search', 'Matematika')
            ->assertSee($book->title);
    }

    public function test_digital_library_category_filter(): void
    {
        Livewire::test(DigitalLibrary::class)
            ->call('setCategory', BookCategory::KELAS_7->value)
            ->assertSet('category', BookCategory::KELAS_7->value)
            ->assertStatus(200);
    }

    public function test_digital_library_read_ebook_increments_views(): void
    {
        $book = Book::where(function ($q) {
            $q->whereNotNull('pdf_path')->orWhereNotNull('pdf_url');
        })->first();

        $this->assertNotNull($book);
        $initialViews = $book->views_count;

        Livewire::test(DigitalLibrary::class)
            ->call('readEbook', $book->id)
            ->assertSet('showPdfModal', true)
            ->assertSet('activePdfTitle', $book->title)
            ->assertSet('activePdfAuthor', $book->author)
            ->assertSet('activePdfCategory', $book->category_label);

        $book->refresh();
        $this->assertEquals($initialViews + 1, $book->views_count);
    }
}

