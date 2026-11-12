<?php

use App\Models\AcademicYear;
use App\Models\User;
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
        // 1. Tabel Tahun Ajaran
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique(); // Contoh: '2026/2027', '2027/2028'
                $table->string('semester', 20)->default('ganjil'); // ganjil, genap
                $table->enum('status', ['persiapan', 'aktif', 'ditutup'])->default('persiapan');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('status');
            });
        }

        // 2. Tambah kolom status pada tabel users jika belum ada
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 20)->default('aktif')->after('student_class');
                $table->index('status');
            });
        }

        // 3. Tabel Riwayat Akademik Siswa per Tahun Ajaran
        if (! Schema::hasTable('student_academic_histories')) {
            Schema::create('student_academic_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('student_class', 20)->nullable(); // Misal: 7A, 8B, 9F
                $table->unsignedTinyInteger('grade_level')->nullable(); // 7, 8, 9
                $table->string('status', 30)->default('aktif'); // persiapan, aktif, naik_kelas, tinggal_kelas, lulus, mutasi_keluar
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->unique(['academic_year_id', 'student_id'], 'uniq_student_academic_year');
                $table->index(['academic_year_id', 'student_class']);
                $table->index('status');
            });
        }

        // 4. Tambah kolom academic_year_id dan student_class ke tabel quiz_attempts
        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                if (! Schema::hasColumn('quiz_attempts', 'academic_year_id')) {
                    $table->foreignId('academic_year_id')->nullable()->after('user_id')->constrained('academic_years')->nullOnDelete();
                }
                if (! Schema::hasColumn('quiz_attempts', 'student_class')) {
                    $table->string('student_class', 20)->nullable()->after('academic_year_id');
                    $table->index('student_class');
                }
            });
        }

        // 5. Seed Tahun Ajaran Awal (2026/2027 Aktif) dan Migrasikan Siswa Saat Ini
        $activeYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            [
                'semester' => 'ganjil',
                'status' => 'aktif',
                'start_date' => '2026-07-15',
                'end_date' => '2027-06-30',
                'description' => 'Tahun Ajaran Aktif 2026/2027 SMP Negeri 4 Cepu',
            ]
        );

        // Backfill data siswa yang sudah ada ke riwayat akademik 2026/2027
        $now = now();
        $students = User::whereNotNull('student_class')
            ->select('id', 'student_class')
            ->get();

        $historyRows = [];
        foreach ($students as $student) {
            $class = (string) $student->student_class;
            $grade = null;
            if (preg_match('/^([789])/i', $class, $matches)) {
                $grade = (int) $matches[1];
            }

            $historyRows[] = [
                'academic_year_id' => $activeYear->id,
                'student_id' => $student->id,
                'student_class' => $class,
                'grade_level' => $grade,
                'status' => 'aktif',
                'notes' => 'Migrasi otomatis data awal siswa aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($historyRows)) {
            foreach (array_chunk($historyRows, 200) as $chunk) {
                DB::table('student_academic_histories')->insertOrIgnore($chunk);
            }
        }

        // Backfill quiz_attempts yang ada dengan data tahun ajaran aktif dan kelas siswa saat itu
        DB::table('quiz_attempts')
            ->whereNull('academic_year_id')
            ->update(['academic_year_id' => $activeYear->id]);

        $attemptsWithUser = DB::table('quiz_attempts')
            ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
            ->whereNotNull('users.student_class')
            ->whereNull('quiz_attempts.student_class')
            ->select('quiz_attempts.id', 'users.student_class')
            ->get();

        foreach ($attemptsWithUser as $row) {
            DB::table('quiz_attempts')
                ->where('id', $row->id)
                ->update(['student_class' => $row->student_class]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('quiz_attempts', 'academic_year_id')) {
                    $table->dropForeign(['academic_year_id']);
                    $table->dropColumn('academic_year_id');
                }
                if (Schema::hasColumn('quiz_attempts', 'student_class')) {
                    $table->dropColumn('student_class');
                }
            });
        }

        Schema::dropIfExists('student_academic_histories');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        Schema::dropIfExists('academic_years');
    }
};
