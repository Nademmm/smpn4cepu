<?php

namespace App\Livewire\Public;

use App\Enums\BookCategory;
use App\Models\Book;
use App\Models\BookReservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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

    // Modal Detail & E-Book
    public ?int $selectedBookId = null;
    public bool $showDetailModal = false;
    public bool $showPdfModal = false;
    public ?string $activePdfUrl = null;
    public ?string $activePdfTitle = null;
    public ?string $activePdfAuthor = null;
    public ?string $activePdfCategory = null;
    public ?int $activePdfPageCount = null;

    // Modal Form Peminjaman Fisik
    public bool $showBorrowModal = false;
    public ?int $borrowBookId = null;
    public string $borrowerName = '';
    public string $borrowerNisn = '';
    public string $borrowerClass = '';
    public string $pickupDate = '';
    public string $borrowNotes = '';
    public ?string $borrowErrorMessage = null;

    // Modal Tiket Peminjaman (Hasil Sukses + Barcode / QR)
    public bool $showTicketModal = false;
    public ?BookReservation $activeReservation = null;

    // Modal Cek Status Tiket Mandiri oleh Siswa
    public bool $showCheckTicketModal = false;
    public string $ticketSearchCode = '';
    public ?BookReservation $checkedTicket = null;
    public ?string $checkTicketErrorMessage = null;
    public $searchedHistory = null;

    // Modal Histori Peminjaman Akun Siswa
    public bool $showHistoryModal = false;

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

    /**
     * Buka formulir peminjaman buku fisik.
     */
    public function openBorrowModal(int $bookId): void
    {
        $book = Book::active()->find($bookId);
        if (!$book || ($book->physical_stock ?? 0) <= 0) {
            return;
        }

        $this->borrowBookId = $book->id;
        $this->borrowErrorMessage = null;
        $this->borrowNotes = '';

        // Otomatis isi data siswa jika sedang masuk akun
        if (Auth::check()) {
            $user = Auth::user();
            $this->borrowerName = $user->name ?? '';
            $this->borrowerNisn = $user->nisn ?? '';
            $this->borrowerClass = $user->student_class ?? 'Siswa SMPN 4 Cepu';
        } else {
            $this->borrowerName = '';
            $this->borrowerNisn = '';
            $this->borrowerClass = '';
        }

        // Default tanggal ambil hari ini atau besok jika akhir pekan
        $this->pickupDate = Carbon::now()->isWeekend()
            ? Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $this->showDetailModal = false;
        $this->showBorrowModal = true;
    }

    public function closeBorrowModal(): void
    {
        $this->showBorrowModal = false;
        $this->borrowBookId = null;
        $this->borrowErrorMessage = null;
    }

    /**
     * Kirim formulir peminjaman dan bangkitkan tiket digital ber-barcode via LibraryLoanService.
     */
    public function submitBorrow(?\App\Services\LibraryLoanService $loanService = null): void
    {
        $this->borrowErrorMessage = null;

        $this->validate([
            'borrowerName' => 'required|string|min:3|max:100',
            'borrowerClass' => 'required|string|min:2|max:50',
            'pickupDate' => 'required|date|after_or_equal:today',
        ], [
            'borrowerName.required' => 'Nama lengkap peminjam wajib diisi.',
            'borrowerClass.required' => 'Kelas / rombel peminjam wajib diisi.',
            'pickupDate.required' => 'Tanggal rencana pengambilan buku wajib dipilih.',
            'pickupDate.after_or_equal' => 'Tanggal pengambilan tidak boleh lewat dari hari ini.',
        ]);

        if (!$this->borrowBookId) {
            $this->borrowErrorMessage = 'Buku belum dipilih.';
            return;
        }

        $service = $loanService ?? app(\App\Services\LibraryLoanService::class);

        try {
            $reservation = $service->reserve(
                bookId: $this->borrowBookId,
                name: $this->borrowerName,
                class: $this->borrowerClass,
                nisn: !empty($this->borrowerNisn) ? $this->borrowerNisn : null,
                pickupDate: $this->pickupDate,
                notes: !empty($this->borrowNotes) ? $this->borrowNotes : null,
                userId: Auth::id(),
            );

            $this->activeReservation = $reservation;
            $this->showBorrowModal = false;
            $this->showTicketModal = true;
        } catch (\DomainException $e) {
            $this->borrowErrorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            $this->borrowErrorMessage = 'Terjadi kendala saat memproses tiket peminjaman. Silakan coba beberapa saat lagi.';
        }
    }

    public function closeTicketModal(): void
    {
        $this->showTicketModal = false;
        $this->activeReservation = null;
        $this->borrowBookId = null;
    }

    /**
     * Buka modal cek tiket mandiri oleh siswa.
     */
    public function openCheckTicketModal(): void
    {
        $this->ticketSearchCode = '';
        $this->checkedTicket = null;
        $this->searchedHistory = null;
        $this->checkTicketErrorMessage = null;
        $this->showCheckTicketModal = true;
    }

    public function closeCheckTicketModal(): void
    {
        $this->showCheckTicketModal = false;
        $this->ticketSearchCode = '';
        $this->checkedTicket = null;
        $this->searchedHistory = null;
        $this->checkTicketErrorMessage = null;
    }

    /**
     * Buka modal riwayat peminjaman akun siswa yang sedang login.
     */
    public function openHistoryModal(): void
    {
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal(): void
    {
        $this->showHistoryModal = false;
    }

    /**
     * Dapatkan daftar seluruh riwayat peminjaman buku untuk akun siswa yang sedang login.
     */
    public function getMyBorrowingHistoryProperty(): \Illuminate\Support\Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        $user = Auth::user();
        return app(\App\Services\LibraryLoanService::class)->getBorrowerHistory(
            userId: $user->id,
            nisn: $user->nisn ?? null,
        );
    }

    /**
     * Lacak status tiket berdasarkan kode reservasi sirkulasi atau nomor NISN siswa.
     */
    public function searchTicket(?\App\Services\LibraryLoanService $loanService = null): void
    {
        $this->checkTicketErrorMessage = null;
        $this->checkedTicket = null;
        $this->searchedHistory = null;

        $code = trim($this->ticketSearchCode);
        if (empty($code)) {
            $this->checkTicketErrorMessage = 'Silakan masukkan kode tiket (contoh: LIB-2026-ABC12) atau nomor NISN Anda.';
            return;
        }

        $service = $loanService ?? app(\App\Services\LibraryLoanService::class);

        // Jika input berupa nomor NISN (angka)
        if (preg_match('/^[0-9]{6,20}$/', $code)) {
            $history = $service->getBorrowerHistory(nisn: $code);
            if ($history->isEmpty()) {
                $this->checkTicketErrorMessage = "Tidak ditemukan riwayat peminjaman untuk NISN {$code}. Pastikan nomor NISN Anda benar.";
                return;
            }
            $this->searchedHistory = $history;
            return;
        }

        $ticket = $service->findByCode($code);
        if (!$ticket) {
            $this->checkTicketErrorMessage = 'Tiket dengan kode "' . htmlspecialchars($code) . '" tidak ditemukan. Pastikan kode sesuai dengan bukti reservasi.';
            return;
        }

        $this->checkedTicket = $ticket;
    }

    public function getSelectedBookProperty(): ?Book
    {
        if (!$this->selectedBookId) {
            return null;
        }

        return Book::active()->find($this->selectedBookId);
    }

    public function getBorrowBookProperty(): ?Book
    {
        if (!$this->borrowBookId) {
            return null;
        }

        return Book::active()->find($this->borrowBookId);
    }

    public function render()
    {
        $stats = Cache::remember('public_books_stats_v1', 300, function () {
            return [
                'total_titles' => Book::active()->count(),
                'total_ebooks' => Book::active()->where(function (Builder $q) {
                    $q->whereNotNull('pdf_path')->orWhereNotNull('pdf_url');
                })->count(),
                'total_physical' => (int) Book::active()->sum('physical_stock'),
            ];
        });

        $query = Book::active();

        if (!empty(trim($this->search))) {
            $query->search(trim($this->search));
        }

        if ($this->category !== 'semua') {
            $query->where('category', $this->category);
        }

        if ($this->format === 'digital') {
            $query->where(function (Builder $q) {
                $q->whereNotNull('pdf_path')->orWhereNotNull('pdf_url');
            });
        } elseif ($this->format === 'physical') {
            $query->where('physical_stock', '>', 0);
        }

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

        $categoriesList = [
            'semua' => 'Semua Koleksi',
            BookCategory::KELAS_7->value => 'Kelas VII',
            BookCategory::KELAS_8->value => 'Kelas VIII',
            BookCategory::KELAS_9->value => 'Kelas IX',
            BookCategory::LITERASI_UMUM->value => 'Literasi & Fiksi',
            BookCategory::KARYA_SEKOLAH->value => 'Karya Sekolah',
        ];

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
            'borrowBook' => $this->borrowBook,
        ]);
    }
}
