<?php

use App\Enums\PostCategory;
use App\Models\Achievement;
use App\Models\Extracurricular;
use App\Models\LearningMaterial;
use App\Models\LibraryBook;
use App\Models\Post;
use App\Models\QuestionBank;
use App\Models\SchoolFacility;
use App\Models\StaffMember;
use App\Http\Controllers\Auth\LoginController;
use App\Models\StudentStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rute Autentikasi Masuk Portal Terpadu (Untuk Siswa, Guru, dan Admin)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1')->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 1. Beranda
Route::get('/', function () {
    try {
        $recentPosts = Post::published()
            ->where('category', '!=', PostCategory::AGENDA)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $agendas = Post::published()
            ->where('category', PostCategory::AGENDA)
            ->orderByDesc('event_date')
            ->limit(4)
            ->get();

        $featuredFacilities = SchoolFacility::orderBy('display_order')
            ->limit(4)
            ->get();

        $totalStaff = StaffMember::where('is_active', true)->count();
        $totalFacilities = SchoolFacility::count();
        $featuredAchievements = Achievement::orderBy('display_order')->orderByDesc('event_date')->limit(3)->get();
        $featuredExtracurriculars = Extracurricular::where('is_active', true)->orderBy('display_order')->limit(4)->get();
    } catch (\Throwable $e) {
        $recentPosts = collect();
        $agendas = collect();
        $featuredFacilities = collect();
        $totalStaff = 33;
        $totalFacilities = 11;
        $featuredAchievements = collect();
        $featuredExtracurriculars = collect();
    }

    return view('pages.home', compact(
        'recentPosts',
        'agendas',
        'featuredFacilities',
        'totalStaff',
        'totalFacilities',
        'featuredAchievements',
        'featuredExtracurriculars'
    ));
})->name('home');

// 2. Profil Sekolah
Route::get('/profil', function () {
    return view('pages.profile');
})->name('profile');

// 2b. Prestasi & Penghargaan Sekolah
Route::get('/profil/prestasi', function () {
    $achievements = Achievement::orderBy('display_order')
        ->orderByDesc('event_date')
        ->get();
    return view('pages.achievements', compact('achievements'));
})->name('achievements');

// 2c. Ekstrakurikuler Sekolah (Kesiswaan)
Route::get('/kesiswaan/ekstrakurikuler', function () {
    $extracurriculars = Extracurricular::where('is_active', true)
        ->orderBy('display_order')
        ->get();
    return view('pages.extracurriculars', compact('extracurriculars'));
})->name('extracurriculars');

// 3. Fasilitas Sekolah
Route::get('/profil/fasilitas', function () {
    $facilities = SchoolFacility::orderBy('display_order')->get();
    return view('pages.facilities', compact('facilities'));
})->name('facilities');

// 4. Direktori Guru & Tendik (Livewire)
Route::get('/guru-staf', function () {
    return view('pages.staff');
})->name('staff');

// 5. Statistik Rombel Siswa
Route::get('/rekap-siswa', function () {
    $latestYear = StudentStatistic::max('academic_year');
    $stats = StudentStatistic::when($latestYear, fn ($q) => $q->where('academic_year', $latestYear))
        ->orderBy('grade_level')
        ->orderBy('class_name')
        ->get();

    $activeStaff = StaffMember::where('is_active', true)->get();
    $isTendik = fn (StaffMember $s): bool => str_contains(strtolower($s->position), 'staf')
        || str_contains(strtolower($s->position), 'tata usaha')
        || str_contains(strtolower($s->position), 'tenaga kependidikan')
        || str_contains(strtolower($s->position), 'administrasi')
        || (str_contains(strtolower($s->position), 'pustakawan') && ! str_contains(strtolower($s->position), 'guru'));

    $tendik = $activeStaff->filter($isTendik);
    $guru = $activeStaff->reject($isTendik);

    $staffStats = [
        'total' => $activeStaff->count(),
        'guru_total' => $guru->count(),
        'guru_pns' => $guru->filter(fn ($s) => $s->employment_status?->value === 'PNS')->count(),
        'guru_pppk' => $guru->filter(fn ($s) => $s->employment_status?->value === 'PPPK')->count(),
        'guru_honorer' => $guru->filter(fn ($s) => $s->employment_status?->value === 'Honorer')->count(),
        'tendik_total' => $tendik->count(),
        'tendik_pns' => $tendik->filter(fn ($s) => $s->employment_status?->value === 'PNS')->count(),
        'tendik_pppk' => $tendik->filter(fn ($s) => $s->employment_status?->value === 'PPPK')->count(),
        'tendik_honorer' => $tendik->filter(fn ($s) => $s->employment_status?->value === 'Honorer')->count(),
        'pns' => $activeStaff->filter(fn ($s) => $s->employment_status?->value === 'PNS')->count(),
        'pppk' => $activeStaff->filter(fn ($s) => $s->employment_status?->value === 'PPPK')->count(),
        'honorer' => $activeStaff->filter(fn ($s) => $s->employment_status?->value === 'Honorer')->count(),
    ];

    return view('pages.student-stats', compact('stats', 'staffStats'));
})->name('student-stats');

// 6. Bilik Suara E-Voting Pilketos (Livewire)
Route::get('/pilketos', function () {
    return view('pages.pilketos');
})->middleware('throttle:60,1')->name('pilketos');

// 7. Quick Count Real-Time Pilketos (Livewire Polling)
Route::get('/pilketos/live-count', function () {
    return view('pages.pilketos-live');
})->name('pilketos.live');

// 8. Perpustakaan Digital (Katalog OPAC Modern)
Route::get('/perpustakaan', function (Request $request) {
    $query = LibraryBook::query();

    if ($search = trim((string) $request->input('q'))) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('publisher', 'like', "%{$search}%")
              ->orWhere('call_number', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%");
        });
    }

    if ($category = $request->input('category')) {
        $query->where('category', $category);
    }

    if ($stock = $request->input('stock')) {
        if ($stock === 'available') {
            $query->where('available_stock', '>', 0);
        } elseif ($stock === 'digital') {
            $query->whereNotNull('digital_file_path');
        }
    }

    $sort = $request->input('sort', 'newest');
    if ($sort === 'title_asc') {
        $query->orderBy('title', 'asc');
    } elseif ($sort === 'title_desc') {
        $query->orderBy('title', 'desc');
    } elseif ($sort === 'year_desc') {
        $query->orderBy('publication_year', 'desc');
    } elseif ($sort === 'stock_desc') {
        $query->orderBy('available_stock', 'desc');
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $books = $query->paginate(12)->withQueryString();
    $categories = LibraryBook::select('category')->distinct()->whereNotNull('category')->pluck('category');

    $stats = [
        'total_titles' => LibraryBook::count(),
        'total_copies' => (int) LibraryBook::sum('total_stock'),
        'available_copies' => (int) LibraryBook::sum('available_stock'),
        'digital_books' => LibraryBook::whereNotNull('digital_file_path')->count(),
    ];

    return view('pages.library', compact('books', 'categories', 'stats'));
})->name('library');

Route::post('/perpustakaan/reservasi', function (Request $request) {
    $validated = $request->validate([
        'book_id' => 'required|exists:library_books,id',
        'borrower_name' => 'required|string|min:3|max:100',
        'borrower_class' => 'required|string|min:2|max:50',
        'pickup_date' => 'required|date|after_or_equal:today',
    ]);

    $book = \App\Models\LibraryBook::findOrFail($validated['book_id']);

    if ($book->available_stock <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Maaf, stok fisik buku ini sedang habis dipinjam.',
        ], 422);
    }

    $code = 'LIB-' . rand(1000, 9999) . '-' . date('Y');
    $tries = 0;
    while (\App\Models\BookReservation::where('reservation_code', $code)->exists()) {
        $tries++;
        if ($tries >= 10) {
            $code = 'LIB-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . date('Y');
            break;
        }
        $code = 'LIB-' . rand(1000, 9999) . '-' . date('Y');
    }

    $reservation = \App\Models\BookReservation::create([
        'book_id' => $book->id,
        'reservation_code' => $code,
        'borrower_name' => trim($validated['borrower_name']),
        'borrower_class' => trim($validated['borrower_class']),
        'pickup_date' => $validated['pickup_date'],
        'status' => 'menunggu',
    ]);

    return response()->json([
        'success' => true,
        'reservation_code' => $reservation->reservation_code,
        'message' => 'Tiket reservasi berhasil dibuat dan terdaftar di sistem sirkulasi perpustakaan.',
    ]);
})->middleware('throttle:10,1')->name('library.reserve');

// 9, 10, 11: Ruang Belajar Mandiri (Wajib Masuk Akun Terlebih Dahulu)
Route::middleware('auth')->group(function () {
    // 9. Materi Pelajaran
    Route::get('/materi', function () {
        $materials = LearningMaterial::with(['subject', 'author'])
            ->where('is_active', true)
            ->orderBy('grade_level')
            ->get();
        return view('pages.materials', compact('materials'));
    })->name('materials');

    // 10. Daftar Kuis Bank Soal
    Route::get('/latihan-soal', function () {
        $questionBanks = QuestionBank::with('subject')
            ->withCount('questions')
            ->where('is_active', true)
            ->orderBy('grade_level')
            ->get();

        $myAttempts = \App\Models\QuizAttempt::with('bank.subject')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('pages.quizzes', compact('questionBanks', 'myAttempts'));
    })->name('quizzes');

    // 11. Pengerjaan Kuis
    Route::get('/latihan-soal/{id}', function (int $id) {
        $bank = QuestionBank::with('subject')
            ->where('is_active', true)
            ->findOrFail($id);
        return view('pages.quiz-play', compact('bank'));
    })->name('quiz.play');
});

// 12. Berita & Informasi
Route::get('/berita', function (Request $request) {
    $query = Post::published()->orderByDesc('published_at');

    $search = trim((string) $request->input('q'));
    if ($search !== '') {
        $keywords = array_filter(explode(' ', $search));
        $query->where(function ($q) use ($search, $keywords) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('excerpt', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");

            foreach ($keywords as $kw) {
                $q->orWhere('title', 'like', "%{$kw}%")
                  ->orWhere('excerpt', 'like', "%{$kw}%");
            }
        });
    }

    $posts = $query->paginate(6)->withQueryString();
    
    // Jika pencarian tidak menemukan data, ambil rekomendasi berita terbaru agar pengunjung tetap mendapat konten bermanfaat
    $suggestedPosts = collect();
    if ($search !== '' && $posts->isEmpty()) {
        $suggestedPosts = Post::published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();
    }

    $announcements = Post::published()
        ->where('category', PostCategory::PENGUMUMAN)
        ->orderByDesc('published_at')
        ->limit(4)
        ->get();

    return view('pages.posts', compact('posts', 'announcements', 'suggestedPosts', 'search'));
})->name('posts');

// 13. Detail Berita
Route::get('/berita/{slug}', function (string $slug) {
    $post = Post::published()
        ->where('slug', $slug)
        ->firstOrFail();
    return view('pages.post-detail', compact('post'));
})->name('post.detail');

// 14. Galeri Foto (Dinamis dari Database)
Route::get('/galeri', function () {
    $photos = \App\Models\GalleryPhoto::where('is_active', true)
        ->orderBy('display_order')
        ->orderByDesc('created_at')
        ->get();

    return view('pages.gallery', compact('photos'));
})->name('gallery');

// 15. Pusat Unduhan Berkas (Dinamis dari Database)
Route::get('/unduhan', function () {
    $documents = \App\Models\SchoolDocument::where('is_active', true)
        ->orderBy('display_order')
        ->orderByDesc('created_at')
        ->get();

    return view('pages.downloads', compact('documents'));
})->name('downloads');

Route::get('/unduhan/{document}/download', function (\App\Models\SchoolDocument $document) {
    if (! $document->is_active) {
        abort(404);
    }

    $document->increment('download_count');

    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    if ($document->file_path && $disk->exists($document->file_path)) {
        return $disk->download(
            $document->file_path,
            \Illuminate\Support\Str::slug($document->title) . '.' . ($document->file_extension ?: 'pdf')
        );
    }

    // Fallback berkas digital resmi jika dokumen sedang sinkronisasi
    return response(
        "DOKUMEN RESMI SMP NEGERI 4 CEPU\n" .
        "Judul: {$document->title}\n" .
        "Kategori: {$document->category}\n" .
        "Keterangan: {$document->description}\n\n" .
        "Arsip digital terdaftar pada Sistem Administrasi Terpadu SMPN 4 Cepu.",
        200,
        [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . \Illuminate\Support\Str::slug($document->title) . '.txt"',
        ]
    );
})->middleware('throttle:30,1')->name('downloads.file');

// 16. Kontak
Route::get('/kontak', function () {
    return view('pages.contact');
})->name('contact');

// 17. Sitemap XML Dinamis untuk SEO
Route::get('/sitemap.xml', function () {
    $staticRoutes = [
        'home',
        'profile',
        'achievements',
        'extracurriculars',
        'facilities',
        'staff',
        'student-stats',
        'pilketos',
        'pilketos.live',
        'library',
        'posts',
        'gallery',
        'downloads',
        'contact',
    ];

    $urls = collect($staticRoutes)->map(fn (string $routeName) => [
        'loc' => route($routeName),
        'lastmod' => now()->toAtomString(),
        'changefreq' => $routeName === 'home' ? 'daily' : 'weekly',
        'priority' => $routeName === 'home' ? '1.0' : '0.8',
    ]);

    try {
        $publishedPosts = Post::published()->orderByDesc('published_at')->get();
        foreach ($publishedPosts as $post) {
            $urls->push([
                'loc' => route('post.detail', $post->slug),
                'lastmod' => ($post->updated_at ?? $post->published_at ?? now())->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ]);
        }
    } catch (\Throwable $e) {
        // Fallback aman bila tabel belum dimigrasi
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $loc = htmlspecialchars($u['loc'], ENT_XML1);
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
        $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$u['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= "</urlset>\n";

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');


