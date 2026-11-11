<?php

namespace App\Models;

use App\Services\BarcodeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class BookReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'reservation_code',
        'borrower_nisn',
        'borrower_name',
        'borrower_class',
        'pickup_date',
        'due_date',
        'status',
        'borrowed_at',
        'returned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'due_date' => 'date',
            'borrowed_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke model Book (Katalog Utama Digital Library).
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Label status yang ramah pembaca dan standar perpustakaan sekolah.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'Menunggu Pengambilan',
            'dipinjam', 'diambil' => 'Sedang Dipinjam',
            'dikembalikan', 'selesai' => 'Sudah Dikembalikan',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst((string)$this->status),
        };
    }

    /**
     * Warna badge status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'warning',
            'dipinjam', 'diambil' => 'info',
            'dikembalikan', 'selesai' => 'success',
            'dibatalkan' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Mengembalikan Barcode Code 128 dalam format SVG murni.
     */
    public function getBarcodeSvgAttribute(): string
    {
        return BarcodeService::generateCode128Svg($this->reservation_code ?? 'LIB-0000');
    }

    /**
     * Mengembalikan QR Code dalam format SVG murni.
     */
    public function getQrSvgAttribute(): string
    {
        return BarcodeService::generateQrCodeSvg($this->reservation_code ?? 'LIB-0000');
    }

    protected static function booted(): void
    {
        static::updated(function (self $reservation) {
            $oldStatus = $reservation->getOriginal('status');
            $newStatus = $reservation->status;

            if ($oldStatus !== $newStatus && $reservation->book) {
                $book = $reservation->book;

                // 1. Saat status beralih ke dipinjam / diambil: kurangi stok fisik
                if (!in_array($oldStatus, ['dipinjam', 'diambil'], true) && in_array($newStatus, ['dipinjam', 'diambil'], true)) {
                    if ($book->physical_stock > 0) {
                        $book->decrement('physical_stock');
                    }
                }
                // 2. Saat status beralih dari dipinjam/diambil ke dikembalikan/dibatalkan: kembalikan stok fisik
                elseif (in_array($oldStatus, ['dipinjam', 'diambil'], true) && !in_array($newStatus, ['dipinjam', 'diambil'], true)) {
                    $book->increment('physical_stock');
                }
            }
        });

        static::saved(function () {
            Cache::forget('admin_nav_badge_pending_reservations');
            Cache::forget('public_library_meta_v1');
            Cache::forget('public_books_stats_v1');
            Cache::forget('admin_school_overview_stats');
        });

        static::deleted(function (self $reservation) {
            if (in_array($reservation->status, ['dipinjam', 'diambil'], true) && $reservation->book) {
                $reservation->book->increment('physical_stock');
            }

            Cache::forget('admin_nav_badge_pending_reservations');
            Cache::forget('public_library_meta_v1');
            Cache::forget('public_books_stats_v1');
            Cache::forget('admin_school_overview_stats');
        });
    }
}
