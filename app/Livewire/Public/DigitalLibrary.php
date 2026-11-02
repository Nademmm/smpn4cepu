<?php

namespace App\Livewire\Public;

use App\Enums\BookCategory;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class DigitalLibrary extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = 'semua';
    public string $format = 'all'; // all, digital, physical
    public string $sort = 'latest'; // latest, title_asc, popular, year_desc
    public string $buku = '';

    public ?int $selectedBookId = null;
    public bool $showDetailModal = false;
    public bool $showPdfModal = false;
    public ?string $activePdfUrl = null;
    public ?string $activePdfTitle = null;
    public ?string $activePdfAuthor = null;
    public ?string $activePdfCategory = null;
    public ?int $activePdfPageCount = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => 'semua'],
        'format' => ['except' => 'all'],
        'sort' => ['except' => 'latest'],
        'buku' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (!empty(trim($this->buku))) {
            $book = Book::active()->where('slug', trim($this->buku))->first();
            if ($book) {
                $this->selectedBookId = $book->id;
                $this->showDetailModal = true;
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFormat(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
        $this->resetPage();
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->category = 'semua';
        $this->format = 'all';
        $this->sort = 'latest';
        $this->resetPage();
    }

    public function openDetail(int $bookId): void
    {
        $book = Book::active()->find($bookId);
        if (!$book) {
            return;
        }

        $this->selectedBookId = $book->id;
        $this->buku = $book->slug;
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedBookId = null;
        $this->buku = '';
    }

    public function readEbook(int $bookId): void
    {
        $book = Book::active()->find($bookId);
        if (!$book) {
            return;
        }

        // Tambah counter views
        $book->increment('views_count');

        $this->activePdfUrl = $book->pdf_file;
        $this->activePdfTitle = $book->title;
        $this->activePdfAuthor = $book->author ?? 'Penyusun Resmi';
        $this->activePdfCategory = $book->category_label;
        $this->activePdfPageCount = $book->page_count ?? 6;
        $this->showPdfModal = true;
        $this->showDetailModal = false;
        $this->buku = '';
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->activePdfUrl = null;
        $this->activePdfTitle = null;
        $this->activePdfAuthor = null;
        $this->activePdfCategory = null;
        $this->activePdfPageCount = null;
    }

    public function getSelectedBookProperty(): ?Book
    {
        if (!$this->selectedBookId) {
            return null;
        }

        return Book::active()->find($this->selectedBookId);
    }

    public function render()
    {
        // 1. Ambil statistik koleksi real-time dari database
        $stats = Cache::remember('public_books_stats_v1', 300, function () {
            return [
                'total_titles' => Book::active()->count(),
                'total_ebooks' => Book::active()->where(function (Builder $q) {
                    $q->whereNotNull('pdf_path')->orWhereNotNull('pdf_url');
                })->count(),
                'total_physical' => (int) Book::active()->sum('physical_stock'),
            ];
        });

        // 2. Query katalog buku
        $query = Book::active();

        // Filter pencarian
        if (!empty(trim($this->search))) {
            $query->search(trim($this->search));
        }

        // Filter kategori
        if ($this->category !== 'semua') {
            $query->where('category', $this->category);
        }

        // Filter ketersediaan format
        if ($this->format === 'digital') {
            $query->where(function (Builder $q) {
                $q->whereNotNull('pdf_path')->orWhereNotNull('pdf_url');
            });
        } elseif ($this->format === 'physical') {
            $query->where('physical_stock', '>', 0);
        }

        // Pengurutan
        switch ($this->sort) {
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'popular':
                $query->orderByDesc('views_count')->orderByDesc('created_at');
                break;
            case 'year_desc':
                $query->orderByDesc('published_year')->orderByDesc('created_at');
                break;
            case 'latest':
            default:
                $query->orderByDesc('is_featured')->orderByDesc('created_at');
                break;
        }

        $books = $query->paginate(12);

        // Kategori pills data
        $categoriesList = [
            'semua' => 'Semua Koleksi',
            BookCategory::KELAS_7->value => 'Kelas VII',
            BookCategory::KELAS_8->value => 'Kelas VIII',
            BookCategory::KELAS_9->value => 'Kelas IX',
            BookCategory::LITERASI_UMUM->value => 'Literasi & Fiksi',
            BookCategory::KARYA_SEKOLAH->value => 'Karya Sekolah',
        ];

        // Hitung jumlah buku per kategori untuk badge pill
        $categoryCounts = Cache::remember('public_books_category_counts_v1', 300, function () {
            $counts = Book::active()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category')->toArray();
            $counts['semua'] = Book::active()->count();
            return $counts;
        });

        return view('livewire.public.digital-library', [
            'books' => $books,
            'stats' => $stats,
            'categoriesList' => $categoriesList,
            'categoryCounts' => $categoryCounts,
            'selectedBook' => $this->selectedBook,
        ]);
    }
}

