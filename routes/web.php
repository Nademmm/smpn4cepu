<?php

use App\Enums\PostCategory;
use App\Models\LearningMaterial;
use App\Models\LibraryBook;
use App\Models\Post;
use App\Models\QuestionBank;
use App\Models\SchoolFacility;
use App\Models\StudentStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 1. Beranda
Route::get('/', function () {
    $recentPosts = Post::where('is_published', true)
        ->orderByDesc('published_at')
        ->limit(3)
        ->get();

    $featuredFacilities = SchoolFacility::orderBy('display_order')
        ->limit(3)
        ->get();

    return view('pages.home', compact('recentPosts', 'featuredFacilities'));
})->name('home');

// 2. Profil Sekolah
Route::get('/profil', function () {
    return view('pages.profile');
})->name('profile');

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
    $stats = StudentStatistic::orderByDesc('academic_year')
        ->orderBy('grade_level')
        ->orderBy('class_name')
        ->get();
    return view('pages.student-stats', compact('stats'));
})->name('student-stats');

// 6. Bilik Suara E-Voting Pilketos (Livewire)
Route::get('/pilketos', function () {
    return view('pages.pilketos');
})->name('pilketos');

// 7. Quick Count Real-Time Pilketos (Livewire Polling)
Route::get('/pilketos/live-count', function () {
    return view('pages.pilketos-live');
})->name('pilketos.live');

// 8. Perpustakaan Digital
Route::get('/perpustakaan', function (Request $request) {
    $query = LibraryBook::query();

    if ($search = $request->input('q')) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%");
        });
    }

    $books = $query->orderBy('title')->get();
    return view('pages.library', compact('books'));
})->name('library');

// 9. Ruang Belajar Mandiri: Materi
Route::get('/materi', function () {
    $materials = LearningMaterial::with(['subject', 'author'])
        ->where('is_active', true)
        ->orderBy('grade_level')
        ->get();
    return view('pages.materials', compact('materials'));
})->name('materials');

// 10. Ruang Belajar Mandiri: Daftar Kuis Bank Soal
Route::get('/latihan-soal', function () {
    $questionBanks = QuestionBank::with('subject')
        ->where('is_active', true)
        ->get();
    return view('pages.quizzes', compact('questionBanks'));
})->name('quizzes');

// 11. Ruang Belajar Mandiri: Pengerjaan Kuis
Route::get('/latihan-soal/{id}', function (int $id) {
    $bank = QuestionBank::with('subject')->findOrFail($id);
    return view('pages.quiz-play', compact('bank'));
})->name('quiz.play');

// 12. Berita & Informasi
Route::get('/berita', function (Request $request) {
    $query = Post::where('is_published', true)->orderByDesc('published_at');

    if ($cat = $request->input('cat')) {
        if ($catEnum = PostCategory::tryFrom($cat)) {
            $query->where('category', $catEnum);
        }
    }

    $posts = $query->get();
    return view('pages.posts', compact('posts'));
})->name('posts');

// 13. Detail Berita
Route::get('/berita/{slug}', function (string $slug) {
    $post = Post::where('slug', $slug)
        ->where('is_published', true)
        ->firstOrFail();
    return view('pages.post-detail', compact('post'));
})->name('post.detail');

// 14. Galeri Foto
Route::get('/galeri', function () {
    return view('pages.gallery');
})->name('gallery');

// 15. Pusat Unduhan Berkas
Route::get('/unduhan', function () {
    return view('pages.downloads');
})->name('downloads');

// 16. Kontak
Route::get('/kontak', function () {
    return view('pages.contact');
})->name('contact');

