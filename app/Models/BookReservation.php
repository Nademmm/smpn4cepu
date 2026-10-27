<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'reservation_code',
        'borrower_name',
        'borrower_class',
        'pickup_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    protected static function booted(): void
    {
        static::updated(function (self $reservation) {
            $oldStatus = $reservation->getOriginal('status');
            $newStatus = $reservation->status;

            if ($oldStatus !== $newStatus && $reservation->book) {
                if ($oldStatus !== 'diambil' && $newStatus === 'diambil' && $reservation->book->available_stock > 0) {
                    $reservation->book->decrement('available_stock');
                } elseif ($oldStatus === 'diambil' && $newStatus !== 'diambil' && $reservation->book->available_stock < $reservation->book->total_stock) {
                    $reservation->book->increment('available_stock');
                }
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('admin_nav_badge_pending_reservations');
            \Illuminate\Support\Facades\Cache::forget('public_library_meta_v1');
            \Illuminate\Support\Facades\Cache::forget('admin_school_overview_stats');
        });

        static::deleted(function (self $reservation) {
            if ($reservation->status === 'diambil' && $reservation->book && $reservation->book->available_stock < $reservation->book->total_stock) {
                $reservation->book->increment('available_stock');
            }

            \Illuminate\Support\Facades\Cache::forget('admin_nav_badge_pending_reservations');
            \Illuminate\Support\Facades\Cache::forget('public_library_meta_v1');
            \Illuminate\Support\Facades\Cache::forget('admin_school_overview_stats');
        });
    }
}
