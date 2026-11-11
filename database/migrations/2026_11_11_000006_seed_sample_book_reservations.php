<?php

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $book = Book::first();
        if (! $book) {
            return;
        }

        $budi = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $siswa = User::whereHas('roles', fn ($q) => $q->where('name', 'siswa'))->first();

        // 1. Tiket Status Menunggu Pengambilan (Siap di-scan serah terima di meja sirkulasi)
        BookReservation::firstOrCreate(
            ['reservation_code' => 'LIB-2026-RSXSD'],
            [
                'book_id' => $book->id,
                'user_id' => $budi?->id,
                'borrower_nisn' => '0087654321',
                'borrower_name' => 'Budi Santoso, S.Pd.',
                'borrower_class' => 'Guru / Karyawan',
                'pickup_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => BookReservation::STATUS_PENDING,
                'notes' => 'Peminjaman buku referensi Kurikulum Merdeka',
            ]
        );

        // 2. Tiket Siswa Status Sedang Dipinjam (Siap di-scan pengembalian buku)
        BookReservation::firstOrCreate(
            ['reservation_code' => 'LIB-2026-PINJAM'],
            [
                'book_id' => $book->id,
                'user_id' => $siswa?->id,
                'borrower_nisn' => $siswa?->nisn ?? '0098765432',
                'borrower_name' => $siswa?->name ?? 'Ahmad Fajar Prasetyo',
                'borrower_class' => $siswa?->student_class ?? '8A',
                'pickup_date' => now()->subDays(2)->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
                'status' => BookReservation::STATUS_BORROWED,
                'borrowed_at' => now()->subDays(2),
                'notes' => 'Buku tugas harian matematika',
            ]
        );

        // 3. Tiket Status Selesai Dikembalikan
        BookReservation::firstOrCreate(
            ['reservation_code' => 'LIB-2026-SELESAI'],
            [
                'book_id' => $book->id,
                'user_id' => $siswa?->id,
                'borrower_nisn' => $siswa?->nisn ?? '0098765432',
                'borrower_name' => $siswa?->name ?? 'Siti Rahmawati',
                'borrower_class' => '7B',
                'pickup_date' => now()->subDays(10)->toDateString(),
                'due_date' => now()->subDays(3)->toDateString(),
                'status' => BookReservation::STATUS_RETURNED,
                'borrowed_at' => now()->subDays(10),
                'returned_at' => now()->subDays(3),
                'notes' => 'Telah dikembalikan tepat waktu dalam kondisi baik',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        BookReservation::whereIn('reservation_code', [
            'LIB-2026-RSXSD',
            'LIB-2026-PINJAM',
            'LIB-2026-SELESAI',
        ])->delete();
    }
};
