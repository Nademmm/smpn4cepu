<?php

use App\Enums\BookCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'homeroom_class')) {
                $table->string('homeroom_class', 20)->nullable()->after('student_class')->index();
            }
        });

        Schema::table('question_banks', function (Blueprint $table): void {
            if (! Schema::hasColumn('question_banks', 'allow_retry')) {
                $table->boolean('allow_retry')->default(true)->after('is_active');
            }
            if (! Schema::hasColumn('question_banks', 'max_attempts')) {
                $table->unsignedSmallInteger('max_attempts')->nullable()->after('allow_retry');
            }
        });

        // Konsolidasi satu kali buku dari tabel lama library_books yang belum ada di books
        if (Schema::hasTable('library_books') && Schema::hasTable('books')) {
            $legacyBooks = DB::table('library_books')->whereNull('deleted_at')->get();
            foreach ($legacyBooks as $legacy) {
                $exists = false;
                if (! empty($legacy->isbn)) {
                    $exists = DB::table('books')->where('isbn', $legacy->isbn)->exists();
                }
                if (! $exists) {
                    $exists = DB::table('books')
                        ->where('title', $legacy->title)
                        ->where('author', $legacy->author ?? '')
                        ->exists();
                }

                if (! $exists) {
                    $baseSlug = Str::slug((string) $legacy->title) ?: 'buku-perpus';
                    $slug = $baseSlug;
                    $counter = 1;
                    while (DB::table('books')->where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter++;
                    }

                    DB::table('books')->insert([
                        'title' => $legacy->title,
                        'slug' => $slug,
                        'isbn' => $legacy->isbn,
                        'author' => $legacy->author,
                        'publisher' => $legacy->publisher,
                        'published_year' => $legacy->publication_year,
                        'category' => $legacy->category ?: BookCategory::BUKU_PAKET->value,
                        'description' => $legacy->synopsis,
                        'cover_path' => $legacy->cover_image,
                        'pdf_path' => $legacy->digital_file_path,
                        'page_count' => $legacy->page_count,
                        'language' => $legacy->language ?: 'Indonesia',
                        'physical_stock' => max(0, (int) ($legacy->available_stock ?? 0)),
                        'views_count' => 0,
                        'is_featured' => false,
                        'is_active' => true,
                        'created_at' => $legacy->created_at ?? now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('question_banks', function (Blueprint $table): void {
            $cols = array_filter(['allow_retry', 'max_attempts'], fn ($col) => Schema::hasColumn('question_banks', $col));
            if (! empty($cols)) {
                $table->dropColumn(array_values($cols));
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'homeroom_class')) {
                $table->dropIndex(['homeroom_class']);
                $table->dropColumn('homeroom_class');
            }
        });
    }
};

