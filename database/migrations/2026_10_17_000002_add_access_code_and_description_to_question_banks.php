<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('question_banks')) {
            Schema::table('question_banks', function (Blueprint $table) {
                if (! Schema::hasColumn('question_banks', 'description')) {
                    $table->text('description')->nullable()->after('title');
                }
                if (! Schema::hasColumn('question_banks', 'access_code')) {
                    $table->string('access_code', 50)->nullable()->after('passing_grade');
                }
            });
        }

        // Normalisasi student_class pada tabel users menjadi per angkatan (Kelas 7, Kelas 8, Kelas 9)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'student_class')) {
            DB::table('users')
                ->where('student_class', 'like', '7%')
                ->update(['student_class' => 'Kelas 7']);

            DB::table('users')
                ->where('student_class', 'like', '8%')
                ->update(['student_class' => 'Kelas 8']);

            DB::table('users')
                ->where('student_class', 'like', '9%')
                ->update(['student_class' => 'Kelas 9']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('question_banks')) {
            Schema::table('question_banks', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('question_banks', 'description')) {
                    $columns[] = 'description';
                }
                if (Schema::hasColumn('question_banks', 'access_code')) {
                    $columns[] = 'access_code';
                }
                if (! empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
