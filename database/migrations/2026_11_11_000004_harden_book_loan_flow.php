<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Status kanonik: menunggu, diambil, selesai, dibatalkan, kadaluarsa.
        // Kolom enum MySQL lama tidak memuat 'kadaluarsa', jadi dilonggarkan menjadi VARCHAR.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE book_reservations MODIFY status VARCHAR(20) NOT NULL DEFAULT 'menunggu'");
        }

        DB::table('book_reservations')->where('status', 'dipinjam')->update(['status' => 'diambil']);
        DB::table('book_reservations')->where('status', 'dikembalikan')->update(['status' => 'selesai']);

        Schema::table('book_reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('book_reservations', 'renew_count')) {
                $table->unsignedTinyInteger('renew_count')->default(0);
            }
            if (! Schema::hasColumn('book_reservations', 'handed_over_by')) {
                $table->unsignedBigInteger('handed_over_by')->nullable();
            }
            if (! Schema::hasColumn('book_reservations', 'returned_to_by')) {
                $table->unsignedBigInteger('returned_to_by')->nullable();
            }
        });

        // Model stok baru: tiket 'menunggu' sudah menahan 1 eksemplar sejak dibuat.
        // Tiket 'menunggu' lama belum menahan stok, jadi disesuaikan satu kali di sini.
        $pending = DB::table('book_reservations')
            ->where('status', 'menunggu')
            ->selectRaw('book_id, count(*) as total')
            ->groupBy('book_id')
            ->get();

        foreach ($pending as $row) {
            $n = (int) $row->total;
            DB::table('books')
                ->where('id', $row->book_id)
                ->update(['physical_stock' => DB::raw("CASE WHEN physical_stock > {$n} THEN physical_stock - {$n} ELSE 0 END")]);
        }
    }

    public function down(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            foreach (['renew_count', 'handed_over_by', 'returned_to_by'] as $col) {
                if (Schema::hasColumn('book_reservations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
