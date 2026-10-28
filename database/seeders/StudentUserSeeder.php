<?php

namespace Database\Seeders;

use App\Models\StudentStatistic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StudentUserSeeder extends Seeder
{
    public function run(): void
    {
        $siswaRole = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $hashedPassword = Hash::make('password123', ['rounds' => 10]);
        $now = now();

        $hasBiodataColumns = Schema::hasColumns('users', ['nis', 'gender', 'birth_place', 'birth_date']);

        // 1. Akun uji otomatis (kompatibilitas pengujian fitur)
        $demoStudents = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@siswa.smpn4cepu.sch.id',
                'nisn' => '0081234567',
                'nis' => '269001',
                'gender' => 'L',
                'birth_place' => 'BLORA',
                'birth_date' => '2013-05-12',
                'student_class' => 'Kelas 7',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@siswa.smpn4cepu.sch.id',
                'nisn' => '0087654321',
                'nis' => '268001',
                'gender' => 'P',
                'birth_place' => 'BLORA',
                'birth_date' => '2012-08-20',
                'student_class' => 'Kelas 8',
            ],
            [
                'name' => 'Rian Pratama',
                'email' => 'rian.pratama@siswa.smpn4cepu.sch.id',
                'nisn' => '0091122334',
                'nis' => '267001',
                'gender' => 'L',
                'birth_place' => 'BLORA',
                'birth_date' => '2011-11-04',
                'student_class' => 'Kelas 9',
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'dewi.anggraini@siswa.smpn4cepu.sch.id',
                'nisn' => '0099887766',
                'nis' => '269002',
                'gender' => 'P',
                'birth_place' => 'BLORA',
                'birth_date' => '2013-02-14',
                'student_class' => 'Kelas 7',
            ],
        ];

        $rowsToUpsert = [];
        foreach ($demoStudents as $st) {
            $row = [
                'name' => $st['name'],
                'email' => $st['email'],
                'nisn' => $st['nisn'],
                'student_class' => $st['student_class'],
                'password' => $hashedPassword,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($hasBiodataColumns) {
                $row['nis'] = $st['nis'];
                $row['gender'] = $st['gender'];
                $row['birth_place'] = $st['birth_place'];
                $row['birth_date'] = $st['birth_date'];
            }
            $rowsToUpsert[] = $row;
        }

        // 2. Muat seluruh 609 data siswa resmi dari dataset JSON (Tahun Pelajaran 2026/2027)
        $jsonPath = database_path('seeders/data/students_2026_2027.json');
        $dataset = file_exists($jsonPath)
            ? json_decode((string) file_get_contents($jsonPath), true)
            : null;

        if (is_array($dataset) && ! empty($dataset['students'])) {
            foreach ($dataset['students'] as $st) {
                $nisn = trim((string) ($st['nisn'] ?? ''));
                if ($nisn === '') {
                    continue;
                }

                $row = [
                    'name' => trim((string) $st['name']),
                    'email' => strtolower($nisn) . '@siswa.smpn4cepu.sch.id',
                    'nisn' => $nisn,
                    'student_class' => trim((string) $st['class']),
                    'password' => $hashedPassword,
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($hasBiodataColumns) {
                    $row['nis'] = ! empty($st['nis']) ? trim((string) $st['nis']) : null;
                    $row['gender'] = ! empty($st['gender']) ? strtoupper(trim((string) $st['gender'])) : null;
                    $row['birth_place'] = ! empty($st['birth_place']) ? trim((string) $st['birth_place']) : null;
                    $row['birth_date'] = ! empty($st['birth_date']) ? trim((string) $st['birth_date']) : null;
                }

                $rowsToUpsert[] = $row;
            }
        }

        $updateCols = ['name', 'email', 'student_class', 'password', 'updated_at'];
        if ($hasBiodataColumns) {
            $updateCols = array_merge($updateCols, ['nis', 'gender', 'birth_place', 'birth_date']);
        }

        DB::transaction(function () use ($rowsToUpsert, $updateCols, $siswaRole, $dataset) {
            foreach (array_chunk($rowsToUpsert, 150) as $chunk) {
                DB::table('users')->upsert(
                    $chunk,
                    ['nisn'],
                    $updateCols
                );
            }

            // Tetapkan role 'siswa' secara bulk untuk semua user yang memiliki NISN
            $studentUserIds = User::whereNotNull('nisn')->pluck('id');
            $roleRows = [];
            foreach ($studentUserIds as $userId) {
                $roleRows[] = [
                    'role_id' => $siswaRole->id,
                    'model_type' => User::class,
                    'model_id' => $userId,
                ];
            }

            foreach (array_chunk($roleRows, 250) as $roleChunk) {
                DB::table('model_has_roles')->insertOrIgnore($roleChunk);
            }

            // 3. Sinkronisasi statistik rombel 2026/2027
            if (is_array($dataset) && ! empty($dataset['statistics'])) {
                foreach ($dataset['statistics'] as $stat) {
                    StudentStatistic::updateOrCreate(
                        [
                            'academic_year' => $stat['academic_year'],
                            'class_name' => $stat['class_name'],
                        ],
                        [
                            'grade_level' => (int) $stat['grade_level'],
                            'male_count' => (int) $stat['male_count'],
                            'female_count' => (int) $stat['female_count'],
                            'total_count' => (int) $stat['total_count'],
                        ]
                    );
                }

                // Hapus data dummy tahun lama (2025/2026) agar tabel rekap bersih
                StudentStatistic::where('academic_year', '2025/2026')->delete();
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Cache::forget('public_student_stats_v1');
        Cache::forget('admin_school_overview_stats');
        Cache::forget('public_home_data_v1');
    }
}
