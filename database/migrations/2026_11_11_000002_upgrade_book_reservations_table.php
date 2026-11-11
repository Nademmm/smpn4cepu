<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Recreate table in SQLite without the obsolete library_books foreign key
            Schema::disableForeignKeyConstraints();

            DB::statement('CREATE TABLE IF NOT EXISTS book_reservations_temp (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                book_id INTEGER NOT NULL,
                reservation_code VARCHAR(30) NOT NULL UNIQUE,
                borrower_nisn VARCHAR(20) NULL,
                borrower_name VARCHAR(255) NOT NULL,
                borrower_class VARCHAR(50) NOT NULL,
                pickup_date DATE NOT NULL,
                due_date DATE NULL,
                status VARCHAR(20) NOT NULL DEFAULT "menunggu",
                borrowed_at DATETIME NULL,
                returned_at DATETIME NULL,
                notes TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');

            if (Schema::hasTable('book_reservations')) {
                DB::statement('INSERT INTO book_reservations_temp (id, book_id, reservation_code, borrower_name, borrower_class, pickup_date, status, notes, created_at, updated_at)
                    SELECT id, book_id, reservation_code, borrower_name, borrower_class, pickup_date, status, notes, created_at, updated_at FROM book_reservations');
                Schema::drop('book_reservations');
            }

            DB::statement('ALTER TABLE book_reservations_temp RENAME TO book_reservations');
            DB::statement('CREATE INDEX IF NOT EXISTS book_reservations_book_id_index ON book_reservations (book_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS book_reservations_borrower_name_index ON book_reservations (borrower_name)');
            DB::statement('CREATE INDEX IF NOT EXISTS book_reservations_borrower_class_index ON book_reservations (borrower_class)');
            DB::statement('CREATE INDEX IF NOT EXISTS book_reservations_pickup_date_index ON book_reservations (pickup_date)');
            DB::statement('CREATE INDEX IF NOT EXISTS book_reservations_status_index ON book_reservations (status)');

            Schema::enableForeignKeyConstraints();
        } else {
            // Di MySQL / MariaDB (Production)
            try {
                Schema::table('book_reservations', function (Blueprint $table) {
                    $table->dropForeign(['book_id']);
                });
            } catch (\Throwable $e) {
                // Abaikan jika index/constraint sudah terhapus
            }

            Schema::table('book_reservations', function (Blueprint $table) {
                if (!Schema::hasColumn('book_reservations', 'borrower_nisn')) {
                    $table->string('borrower_nisn', 20)->nullable()->after('reservation_code');
                }
                if (!Schema::hasColumn('book_reservations', 'due_date')) {
                    $table->date('due_date')->nullable()->after('pickup_date');
                }
                if (!Schema::hasColumn('book_reservations', 'borrowed_at')) {
                    $table->timestamp('borrowed_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('book_reservations', 'returned_at')) {
                    $table->timestamp('returned_at')->nullable()->after('borrowed_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('book_reservations')) {
            Schema::table('book_reservations', function (Blueprint $table) {
                $colsToDrop = array_filter(
                    ['borrower_nisn', 'due_date', 'borrowed_at', 'returned_at'],
                    fn ($col) => Schema::hasColumn('book_reservations', $col)
                );
                if (!empty($colsToDrop)) {
                    $table->dropColumn($colsToDrop);
                }
            });
        }
    }
};
