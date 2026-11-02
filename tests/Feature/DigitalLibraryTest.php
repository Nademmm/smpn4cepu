<?php

namespace Tests\Feature;

use App\Enums\BookCategory;
use App\Livewire\Public\DigitalLibrary;
use App\Models\Book;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalLibraryTest extends TestCase
{
    protected ?Book $testBook = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBook = Book::updateOrCreate(
            ['slug' => 'test-matematika-smp-kelas-vii'],
            [
                'title' => 'Matematika untuk SMP Kelas VII (Uji Katalog)',
                'isbn' => '978-602-244-000-0',
                'author' => 'Tim Kemendikbudristek',
                'publisher' => 'Pusat Perbukuan',
                'published_year' => 2024,
                'category' => BookCategory::KELAS_7->value,
                'description' => 'Buku paket uji otomatis perpustakaan digital.',
                'pdf_path' => 'books/ebooks/test-ebook.pdf',
                'page_count' => 200,
                'language' => 'id',
                'physical_stock' => 25,
                'views_count' => 10,
                'is_featured' => true,
                'is_active' => true,
            ]
        );
    }

    protected function tearDown(): void
    {
        if ($this->testBook) {
            $this->testBook->forceDelete();
        }

        parent::tearDown();
    }

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

    public function test_digital_library_deep_link_opens_book_detail_modal(): void
    {
        $book = Book::active()->first();
        $this->assertNotNull($book);

        Livewire::withQueryParams(['buku' => $book->slug])
            ->test(DigitalLibrary::class)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedBookId', $book->id)
            ->assertSee('Kutipan Daftar Pustaka')
            ->assertSee($book->title);
    }
}

