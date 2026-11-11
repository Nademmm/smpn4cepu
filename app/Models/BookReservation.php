<?php

namespace App\Models;

use App\Services\BarcodeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class BookReservation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'menunggu';
    public const STATUS_BORROWED = 'diambil';
    public const STATUS_RETURNED = 'selesai';
    public const STATUS_CANCELLED = 'dibatalkan';
    public const STATUS_EXPIRED = 'kadaluarsa';

    /** Status yang sedang menahan 1 eksemplar fisik. */
    public const HOLDING_STOCK = [self::STATUS_PENDING, self::STATUS_BORROWED];

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
        'renew_count',
        'handed_over_by',
        'returned_to_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'due_date' => 'date',
            'borrowed_at' => 'datetime',
            'returned_at' => 'datetime',
            'renew_count' => 'integer',
        ];
    }

    /**
     * Relasi ke model Book (Katalog Utama Digital Library).
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function scopeHoldingStock(Builder $query): Builder
    {
        return $query->whereIn('status', self::HOLDING_STOCK);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BORROWED)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today());
    }

    /** Tiket menunggu yang batas pengambilannya sudah lewat. */
    public function scopeStalePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
            ->whereDate('pickup_date', '<', Carbon::today()->subDays(\App\Services\LibraryLoanService::PICKUP_GRACE_DAYS));
    }

    /** Batas terakhir tiket masih boleh diambil. */
    public function getPickupDeadlineAttribute(): ?Carbon
    {
        return $this->pickup_date?->copy()->addDays(\App\Services\LibraryLoanService::PICKUP_GRACE_DAYS);
    }

    public function getIsPickupExpiredAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->pickup_deadline !== null
            && Carbon::today()->gt($this->pickup_deadline);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_BORROWED
            && $this->due_date !== null
            && Carbon::today()->gt($this->due_date);
    }

    public function getDaysLateAttribute(): int
    {
        if (! $this->due_date) {
            return 0;
        }

        $end = $this->returned_at ?? Carbon::now();

        return max(0, (int) $this->due_date->copy()->startOfDay()->diffInDays($end->copy()->startOfDay(), false));
    }

    /**
     * Label status yang ramah pembaca dan standar perpustakaan sekolah.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_overdue) {
            return 'Terlambat Dikembalikan';
        }

        return match ($this->status) {
            self::STATUS_PENDING => $this->is_pickup_expired ? 'Batas Pengambilan Lewat' : 'Menunggu Pengambilan',
            self::STATUS_BORROWED => 'Sedang Dipinjam',
            self::STATUS_RETURNED => 'Sudah Dikembalikan',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_EXPIRED => 'Kadaluarsa (Tidak Diambil)',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Warna badge status.
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->is_overdue) {
            return 'danger';
        }

        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_BORROWED => 'info',
            self::STATUS_RETURNED => 'success',
            self::STATUS_CANCELLED, self::STATUS_EXPIRED => 'gray',
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
        // Stok fisik dikelola secara transaksional oleh LibraryLoanService.
        // Satu-satunya pengecualian: menghapus tiket aktif harus melepas eksemplar yang ditahan.
        static::deleted(function (self $reservation) {
            if (in_array($reservation->status, self::HOLDING_STOCK, true) && $reservation->book) {
                $reservation->book->increment('physical_stock');
            }

            static::flushCaches();
        });

        static::saved(fn () => static::flushCaches());
    }

    public static function flushCaches(): void
    {
        Cache::forget('admin_nav_badge_pending_reservations');
        Cache::forget('public_library_meta_v1');
        Cache::forget('public_books_stats_v1');
        Cache::forget('admin_school_overview_stats');
    }
}
