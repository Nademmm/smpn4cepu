<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookReservation;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu untuk seluruh perubahan status sirkulasi buku fisik.
 *
 * Model stok: Book.physical_stock = eksemplar yang masih bisa dipesan.
 *  - Tiket dibuat (menunggu): stok -1 (eksemplar ditahan, mencegah pemesanan ganda)
 *  - Tiket batal / kadaluarsa / buku kembali: stok +1
 *  - Serah terima (menunggu -> diambil): stok tetap (sudah ditahan)
 * Semua mutasi berjalan di dalam transaksi dengan row lock agar aman dari balapan data.
 */
class LibraryLoanService
{
    public const LOAN_DAYS = 7;
    public const MAX_ACTIVE_LOANS = 3;
    public const MAX_RENEWALS = 1;
    public const MAX_ADVANCE_DAYS = 7;
    /** Tiket tetap valid sampai N hari setelah tanggal rencana ambil. */
    public const PICKUP_GRACE_DAYS = 2;

    /**
     * Buat tiket reservasi dan tahan 1 eksemplar.
     *
     * @throws DomainException jika tidak memenuhi aturan peminjaman
     */
    public function reserve(
        int $bookId,
        string $name,
        string $class,
        ?string $nisn,
        string $pickupDate,
        ?string $notes = null,
    ): BookReservation {
        $name = trim($name);
        $class = trim($class);
        $nisn = $nisn !== null && trim($nisn) !== '' ? trim($nisn) : null;
        $pickup = Carbon::parse($pickupDate)->startOfDay();

        if ($pickup->lt(Carbon::today())) {
            throw new DomainException('Tanggal pengambilan tidak boleh lewat dari hari ini.');
        }
        if ($pickup->gt(Carbon::today()->addDays(self::MAX_ADVANCE_DAYS))) {
            throw new DomainException('Tanggal pengambilan maksimal ' . self::MAX_ADVANCE_DAYS . ' hari dari sekarang.');
        }
        if ($pickup->isWeekend()) {
            throw new DomainException('Perpustakaan tutup pada hari Sabtu dan Minggu. Pilih hari Senin sampai Jumat.');
        }

        return DB::transaction(function () use ($bookId, $name, $class, $nisn, $pickup, $notes) {
            /** @var Book|null $book */
            $book = Book::active()->lockForUpdate()->find($bookId);
            if (! $book) {
                throw new DomainException('Buku tidak ditemukan di katalog perpustakaan.');
            }

            // Lepaskan eksemplar dari tiket basi milik buku ini sebelum menghitung stok.
            $this->expireStale($book->id);
            $book->refresh();

            if ((int) $book->physical_stock <= 0) {
                throw new DomainException('Mohon maaf, seluruh eksemplar buku fisik ini sedang dipinjam atau sudah dipesan.');
            }

            $borrowerQuery = fn ($q) => $nisn
                ? $q->where('borrower_nisn', $nisn)
                : $q->where('borrower_name', $name)->where('borrower_class', $class);

            $active = BookReservation::holdingStock()->where($borrowerQuery)->get();

            if ($active->contains('book_id', $book->id)) {
                throw new DomainException('Anda sudah memiliki tiket aktif untuk buku ini. Selesaikan peminjaman sebelumnya di perpustakaan.');
            }
            if ($active->contains(fn (BookReservation $r) => $r->is_overdue)) {
                throw new DomainException('Anda masih memiliki buku yang terlambat dikembalikan. Kembalikan terlebih dahulu ke perpustakaan.');
            }
            if ($active->count() >= self::MAX_ACTIVE_LOANS) {
                throw new DomainException('Batas peminjaman aktif adalah ' . self::MAX_ACTIVE_LOANS . ' buku sekaligus.');
            }

            $book->decrement('physical_stock');

            return BookReservation::create([
                'book_id' => $book->id,
                'reservation_code' => $this->generateCode(),
                'borrower_nisn' => $nisn,
                'borrower_name' => $name,
                'borrower_class' => $class,
                'pickup_date' => $pickup->toDateString(),
                'due_date' => $pickup->copy()->addDays(self::LOAN_DAYS)->toDateString(),
                'status' => BookReservation::STATUS_PENDING,
                'notes' => $notes ? trim($notes) : null,
            ]);
        });
    }

    /**
     * Cari tiket dari hasil scan/ketik. Toleran terhadap spasi, huruf kecil, dan karakter sisa scanner.
     */
    public function findByCode(?string $raw): ?BookReservation
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', (string) $raw));

        if ($code === '') {
            return null;
        }

        return BookReservation::with('book')->where('reservation_code', $code)->first();
    }

    /**
     * Serah terima buku ke siswa (menunggu -> diambil). Masa pinjam dihitung dari hari serah terima.
     */
    public function handOver(BookReservation $reservation, ?int $staffId = null): BookReservation
    {
        // Kadaluarsa diproses di luar transaksi serah terima agar pelepasan stok tidak ikut di-rollback.
        $fresh = BookReservation::find($reservation->id);
        if ($fresh && $fresh->is_pickup_expired) {
            $this->expireStale($fresh->book_id);
            throw new DomainException('Batas pengambilan tiket sudah lewat. Tiket otomatis kadaluarsa dan stok dilepas. Siswa perlu membuat tiket baru.');
        }

        return DB::transaction(function () use ($reservation, $staffId) {
            $r = $this->lock($reservation);

            if ($r->status !== BookReservation::STATUS_PENDING) {
                throw new DomainException("Tiket berstatus \"{$r->status_label}\" dan tidak dapat diserahterimakan.");
            }

            $r->update([
                'status' => BookReservation::STATUS_BORROWED,
                'borrowed_at' => now(),
                'due_date' => Carbon::today()->addDays(self::LOAN_DAYS)->toDateString(),
                'handed_over_by' => $staffId,
            ]);

            return $r;
        });
    }

    /**
     * Terima pengembalian (diambil -> selesai) dan kembalikan stok.
     */
    public function receiveReturn(BookReservation $reservation, ?int $staffId = null, ?string $conditionNote = null): BookReservation
    {
        return DB::transaction(function () use ($reservation, $staffId, $conditionNote) {
            $r = $this->lock($reservation);

            if ($r->status !== BookReservation::STATUS_BORROWED) {
                throw new DomainException("Tiket berstatus \"{$r->status_label}\", bukan buku yang sedang dipinjam.");
            }

            $r->returned_to_by = $staffId;
            $this->release($r, BookReservation::STATUS_RETURNED, $conditionNote);

            return $r;
        });
    }

    /**
     * Batalkan tiket yang belum diambil dan lepaskan eksemplar yang ditahan.
     */
    public function cancel(BookReservation $reservation, ?string $reason = null): BookReservation
    {
        return DB::transaction(function () use ($reservation, $reason) {
            $r = $this->lock($reservation);

            if ($r->status !== BookReservation::STATUS_PENDING) {
                throw new DomainException('Hanya tiket yang belum diambil yang dapat dibatalkan.');
            }

            $this->release($r, BookReservation::STATUS_CANCELLED, $reason);

            return $r;
        });
    }

    /**
     * Perpanjang masa pinjam satu kali, hanya jika belum terlambat.
     */
    public function renew(BookReservation $reservation): BookReservation
    {
        return DB::transaction(function () use ($reservation) {
            $r = $this->lock($reservation);

            if ($r->status !== BookReservation::STATUS_BORROWED) {
                throw new DomainException('Hanya buku yang sedang dipinjam yang dapat diperpanjang.');
            }
            if ($r->is_overdue) {
                throw new DomainException('Buku sudah terlambat, tidak dapat diperpanjang. Terima pengembalian terlebih dahulu.');
            }
            if ($r->renew_count >= self::MAX_RENEWALS) {
                throw new DomainException('Perpanjangan sudah mencapai batas maksimal (' . self::MAX_RENEWALS . ' kali).');
            }

            $r->update([
                'due_date' => $r->due_date->copy()->addDays(self::LOAN_DAYS)->toDateString(),
                'renew_count' => $r->renew_count + 1,
            ]);

            return $r;
        });
    }

    /**
     * Tandai tiket menunggu yang lewat batas ambil sebagai kadaluarsa dan lepaskan stoknya.
     *
     * @return int jumlah tiket yang dikadaluarsakan
     */
    public function expireStale(?int $bookId = null): int
    {
        $query = BookReservation::stalePending()->when($bookId, fn ($q) => $q->where('book_id', $bookId));

        $count = 0;
        foreach ($query->get() as $stale) {
            DB::transaction(function () use ($stale, &$count) {
                $r = $this->lock($stale);
                if ($r->status === BookReservation::STATUS_PENDING) {
                    $this->release($r, BookReservation::STATUS_EXPIRED);
                    $count++;
                }
            });
        }

        return $count;
    }

    private function lock(BookReservation $reservation): BookReservation
    {
        return BookReservation::lockForUpdate()->findOrFail($reservation->id);
    }

    /**
     * Ubah ke status akhir dan kembalikan 1 eksemplar ke stok.
     */
    private function release(BookReservation $r, string $finalStatus, ?string $note = null): void
    {
        $attributes = ['status' => $finalStatus];

        if ($finalStatus === BookReservation::STATUS_RETURNED) {
            $attributes['returned_at'] = now();
        }
        if ($note) {
            $attributes['notes'] = trim(($r->notes ? $r->notes . "\n" : '') . $note);
        }

        $r->forceFill($attributes)->save();

        Book::whereKey($r->book_id)->increment('physical_stock');
    }

    private function generateCode(): string
    {
        // Tanpa karakter ambigu (0/O, 1/I) agar mudah diketik manual jika scanner tidak tersedia.
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = 'LIB-' . date('Y') . '-' . $suffix;
        } while (BookReservation::where('reservation_code', $code)->exists());

        return $code;
    }
}
