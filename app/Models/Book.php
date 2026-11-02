<?php

namespace App\Models;

use App\Enums\BookCategory;
use App\Support\PublicAssetResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'isbn',
        'author',
        'publisher',
        'published_year',
        'category',
        'description',
        'cover_url',
        'cover_path',
        'pdf_url',
        'pdf_path',
        'page_count',
        'language',
        'physical_stock',
        'views_count',
        'is_featured',
        'is_active',
    ];

    protected $appends = [
        'cover_image',
        'pdf_file',
        'synopsis',
        'category_label',
        'has_digital',
        'has_physical',
    ];

    protected function casts(): array
    {
        return [
            'published_year' => 'integer',
            'page_count' => 'integer',
            'physical_stock' => 'integer',
            'views_count' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope untuk buku yang aktif tampil di katalog.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk buku unggulan / rekomendasi.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope pencarian cepat judul, pengarang, penerbit, atau ISBN.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('author', 'like', "%{$term}%")
                ->orWhere('publisher', 'like', "%{$term}%")
                ->orWhere('isbn', 'like', "%{$term}%");
        });
    }

    /**
     * Accessor untuk gambar cover: Prioritaskan upload lokal, lalu URL eksternal, lalu placeholder.
     */
    public function getCoverImageAttribute(): string
    {
        if (!empty($this->cover_path)) {
            if (Storage::disk('public')->exists($this->cover_path)) {
                return Storage::disk('public')->url($this->cover_path);
            }
            $resolved = PublicAssetResolver::resolve($this->cover_path);
            if ($resolved) {
                return $resolved;
            }
        }

        if (!empty($this->cover_url)) {
            return $this->cover_url;
        }

        return $this->getPlaceholderCover();
    }

    /**
     * Accessor untuk file PDF: Prioritaskan upload lokal, lalu link eksternal, atau null.
     */
    public function getPdfFileAttribute(): ?string
    {
        if (!empty($this->pdf_path)) {
            if (Storage::disk('public')->exists($this->pdf_path)) {
                return Storage::disk('public')->url($this->pdf_path);
            }
            $resolved = PublicAssetResolver::resolve($this->pdf_path);
            if ($resolved) {
                return $resolved;
            }
        }

        if (!empty($this->pdf_url)) {
            return $this->pdf_url;
        }

        return null;
    }

    /**
     * Alias synopsis ke description agar kompatibel dengan view yang menggunakan synopsis.
     */
    public function getSynopsisAttribute(): ?string
    {
        return $this->description;
    }

    /**
     * Mutator agar bisa mengisi 'synopsis' langsung ke 'description'.
     */
    public function setSynopsisAttribute(?string $value): void
    {
        $this->attributes['description'] = $value;
    }

    /**
     * Label teks kategori yang ramah pengguna.
     */
    public function getCategoryLabelAttribute(): string
    {
        $enum = BookCategory::tryFrom((string) $this->category);

        return $enum ? $enum->getLabel() : (string) ($this->category ?? 'Umum');
    }

    /**
     * Apakah buku memiliki berkas digital (E-Book)?
     */
    public function getHasDigitalAttribute(): bool
    {
        return !empty($this->pdf_path) || !empty($this->pdf_url);
    }

    /**
     * Apakah buku tersedia di rak fisik perpustakaan sekolah?
     */
    public function getHasPhysicalAttribute(): bool
    {
        return (int) $this->physical_stock > 0;
    }

    /**
     * Placeholder SVG cover yang estetis sesuai identitas SMPN 4 Cepu.
     */
    protected function getPlaceholderCover(): string
    {
        $safeTitle = e(Str::limit($this->title ?? 'Buku SMPN 4 Cepu', 35));
        $category = e($this->category_label);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="420" viewBox="0 0 300 420" fill="none">
    <rect width="300" height="420" rx="16" fill="#1B2A4A"/>
    <rect x="12" y="12" width="276" height="396" rx="12" stroke="#F5B700" stroke-width="2" stroke-opacity="0.4"/>
    <circle cx="150" cy="140" r="48" fill="#1F5FBF" fill-opacity="0.3"/>
    <path d="M125 155V130C125 125 137 122 150 122C163 122 175 125 175 130V155M125 155C125 150 137 147 150 147C163 147 175 150 175 155M125 155C137 158 150 158 150 158M175 155C163 158 150 158 150 158V147" stroke="#F5B700" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="40" y="210" width="220" height="24" rx="12" fill="#1F5FBF"/>
    <text x="150" y="226" fill="#FFFFFF" font-family="Nunito, sans-serif" font-size="11" font-weight="800" text-anchor="middle">{$category}</text>
    <text x="150" y="270" fill="#FFFFFF" font-family="Nunito, sans-serif" font-size="14" font-weight="800" text-anchor="middle">{$safeTitle}</text>
    <text x="150" y="375" fill="#FFE3A0" font-family="Nunito, sans-serif" font-size="10" font-weight="700" text-anchor="middle" letter-spacing="1">SMP NEGERI 4 CEPU</text>
</svg>
SVG;

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (empty($book->slug)) {
                $baseSlug = Str::slug($book->title);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $book->slug = $slug;
            }
        });

        static::saved(function () {
            PublicAssetResolver::flush();
            Cache::forget('public_library_meta_v1');
            Cache::forget('public_books_stats_v1');
            Cache::forget('public_books_category_counts_v1');
            Cache::forget('public_sitemap_xml_v2');
            Cache::forget('admin_school_overview_stats');
        });

        static::deleted(function () {
            PublicAssetResolver::flush();
            Cache::forget('public_library_meta_v1');
            Cache::forget('public_books_stats_v1');
            Cache::forget('public_books_category_counts_v1');
            Cache::forget('public_sitemap_xml_v2');
            Cache::forget('admin_school_overview_stats');
        });
    }
}

