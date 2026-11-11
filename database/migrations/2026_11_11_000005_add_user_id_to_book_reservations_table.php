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
        Schema::table('book_reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('book_reservations', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('book_id');
            }
        });

        // Hubungkan tiket sirkulasi lama ke akun user berdasarkan kesamaan NISN
        $users = DB::table('users')->whereNotNull('nisn')->select('id', 'nisn')->get();
        foreach ($users as $user) {
            DB::table('book_reservations')
                ->whereNull('user_id')
                ->where('borrower_nisn', $user->nisn)
                ->update(['user_id' => $user->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('book_reservations', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
