<?php

namespace App\Console\Commands;

use App\Models\StudentStatistic;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use ZipArchive;

class ImportStudentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-students 
                            {file? : Path ke file Excel (.xlsx) atau kosongkan untuk memuat dari dataset JSON resmi}
                            {--year=2026/2027 : Tahun Ajaran untuk statistik siswa}
                            {--default-password=password123 : Password default untuk akun siswa}
                            {--dry-run : Jalankan parsing dan tampilkan laporan tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import seluruh akun siswa dari file Excel (.xlsx) atau dataset JSON resmi ke tabel users & student_statistics secara bulk.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $academicYear = (string) $this->option('year');
        $defaultPassword = (string) $this->option('default-password');
        $isDryRun = (bool) $this->option('dry-run');

        $fileArg = $this->argument('file');
        $allStudents = [];
        $classSummaries = [];

        if ($fileArg) {
            $filePath = (string) $fileArg;
            if (! str_starts_with($filePath, '/') && ! preg_match('/^[a-zA-Z]:/', $filePath)) {
                $filePath = base_path($filePath);
            }

            if (! file_exists($filePath)) {
                $this->error("File Excel tidak ditemukan di: {$filePath}");
                return Command::FAILURE;
            }

            $this->info("Membuka file Excel: {$filePath}");

            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                $this->error("Gagal membuka file Excel sebagai arsip zip.");
                return Command::FAILURE;
            }

            $sharedStrings = [];
            $sstXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sstXml) {
                $xml = simplexml_load_string($sstXml);
                foreach ($xml->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string) $si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }

            $wbXml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
            $relsXml = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));

            $relMap = [];
            foreach ($relsXml->Relationship as $rel) {
                $relMap[(string) $rel['Id']] = (string) $rel['Target'];
            }

            $sheets = [];
            foreach ($wbXml->sheets->sheet as $s) {
                $sheetName = (string) $s['name'];
                $rId = (string) $s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                $target = $relMap[$rId] ?? '';
                if (! str_starts_with($target, 'xl/')) {
                    $target = 'xl/' . $target;
                }
                $sheets[$sheetName] = $target;
            }

            foreach ($sheets as $sheetName => $target) {
                $sheetContent = $zip->getFromName($target);
                if (! $sheetContent) {
                    continue;
                }

                $sheetXml = simplexml_load_string($sheetContent);
                $sheetStudents = [];
                $maleCount = 0;
                $femaleCount = 0;

                foreach ($sheetXml->sheetData->row as $row) {
                    $rowNum = (int) $row['r'];
                    if ($rowNum < 8) {
                        continue;
                    }

                    $cells = [];
                    foreach ($row->c as $c) {
                        $cellRef = (string) $c['r'];
                        preg_match('/^([A-Z]+)/', $cellRef, $matches);
                        $col = $matches[1] ?? '';
                        $t = (string) $c['t'];
                        $val = (string) $c->v;

                        if ($t === 's') {
                            $val = $sharedStrings[(int) $val] ?? '';
                        }

                        $cells[$col] = trim($val);
                    }

                    $no = $cells['A'] ?? '';
                    $nisn = $cells['B'] ?? '';
                    $nis = $cells['C'] ?? '';
                    $studentName = $cells['D'] ?? '';
                    $gender = strtoupper($cells['E'] ?? '');
                    $birthPlace = $cells['F'] ?? '';
                    $birthDateRaw = $cells['G'] ?? '';
                    $birthDate = null;

                    if (ctype_digit($birthDateRaw) && (int) $birthDateRaw > 20000) {
                        $unix = ((int) $birthDateRaw - 25569) * 86400;
                        $birthDate = gmdate('Y-m-d', $unix);
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDateRaw)) {
                        $birthDate = $birthDateRaw;
                    }

                    if (ctype_digit($no) && ! empty($studentName) && ! empty($nisn)) {
                        $sheetStudents[] = [
                            'class' => $sheetName,
                            'nisn' => $nisn,
                            'nis' => $nis !== '' ? $nis : null,
                            'name' => $studentName,
                            'gender' => in_array($gender, ['L', 'P'], true) ? $gender : null,
                            'birth_place' => $birthPlace !== '' ? $birthPlace : null,
                            'birth_date' => $birthDate,
                        ];

                        if ($gender === 'L') {
                            $maleCount++;
                        } elseif ($gender === 'P') {
                            $femaleCount++;
                        }
                    }
                }

                preg_match('/^([789])/', $sheetName, $gradeMatch);
                $gradeLevel = isset($gradeMatch[1]) ? (int) $gradeMatch[1] : 7;

                $classSummaries[$sheetName] = [
                    'grade_level' => $gradeLevel,
                    'class_name' => $sheetName,
                    'male_count' => $maleCount,
                    'female_count' => $femaleCount,
                    'total_count' => count($sheetStudents),
                ];

                foreach ($sheetStudents as $st) {
                    $allStudents[] = $st;
                }
            }

            $zip->close();
        } else {
            $jsonPath = database_path('seeders/data/students_2026_2027.json');
            if (! file_exists($jsonPath)) {
                $this->error("Dataset JSON tidak ditemukan di: {$jsonPath}");
                return Command::FAILURE;
            }

            $this->info("Memuat dataset resmi: {$jsonPath}");
            $dataset = json_decode((string) file_get_contents($jsonPath), true);
            $allStudents = $dataset['students'] ?? [];
            foreach ($dataset['statistics'] ?? [] as $stat) {
                $classSummaries[$stat['class_name']] = $stat;
            }
        }

        $totalParsed = count($allStudents);
        $this->info("Total data siswa siap diproses: {$totalParsed} siswa dari " . count($classSummaries) . " rombel.");

        if ($totalParsed === 0) {
            $this->warn("Tidak ada data siswa yang ditemukan untuk diimpor.");
            return Command::SUCCESS;
        }

        $tableRows = [];
        $totMale = 0;
        $totFemale = 0;
        $totAll = 0;

        foreach ($classSummaries as $c) {
            $tableRows[] = [
                $c['class_name'],
                'Kelas ' . $c['grade_level'],
                $c['male_count'],
                $c['female_count'],
                $c['total_count'],
            ];
            $totMale += $c['male_count'];
            $totFemale += $c['female_count'];
            $totAll += $c['total_count'];
        }

        $this->table(
            ['Rombel', 'Tingkat', 'Laki-Laki (L)', 'Perempuan (P)', 'Total'],
            $tableRows
        );

        $this->info("Rekapitulasi: Laki-laki = {$totMale}, Perempuan = {$totFemale}, Total Keseluruhan = {$totAll}");

        if ($isDryRun) {
            $this->warn("Mode --dry-run aktif. Tidak ada perubahan yang disimpan ke database.");
            return Command::SUCCESS;
        }

        $this->info("Menyimpan akun siswa & statistik rombel secara bulk (High-Speed Upsert)...");

        $siswaRole = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $hashedPassword = Hash::make($defaultPassword, ['rounds' => 10]);
        $now = now();

        $rowsToUpsert = [];
        foreach ($allStudents as $st) {
            $nisn = trim((string) ($st['nisn'] ?? ''));
            if ($nisn === '') {
                continue;
            }

            $rowsToUpsert[] = [
                'name' => trim((string) $st['name']),
                'email' => strtolower($nisn) . '@siswa.smpn4cepu.sch.id',
                'nisn' => $nisn,
                'nis' => ! empty($st['nis']) ? trim((string) $st['nis']) : null,
                'gender' => in_array($st['gender'] ?? null, ['L', 'P'], true) ? $st['gender'] : null,
                'birth_place' => ! empty($st['birth_place']) ? trim((string) $st['birth_place']) : null,
                'birth_date' => ! empty($st['birth_date']) ? trim((string) $st['birth_date']) : null,
                'student_class' => trim((string) ($st['class'] ?? $st['sheet'] ?? '')),
                'password' => $hashedPassword,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::beginTransaction();

        try {
            foreach (array_chunk($rowsToUpsert, 200) as $chunk) {
                DB::table('users')->upsert(
                    $chunk,
                    ['nisn'],
                    ['name', 'email', 'nis', 'gender', 'birth_place', 'birth_date', 'student_class', 'password', 'updated_at']
                );
            }

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

            foreach ($classSummaries as $c) {
                StudentStatistic::updateOrCreate(
                    [
                        'academic_year' => $academicYear,
                        'class_name' => $c['class_name'],
                    ],
                    [
                        'grade_level' => (int) $c['grade_level'],
                        'male_count' => (int) $c['male_count'],
                        'female_count' => (int) $c['female_count'],
                        'total_count' => (int) $c['total_count'],
                    ]
                );
            }

            StudentStatistic::where('academic_year', '2025/2026')->delete();

            DB::commit();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            Cache::forget('public_student_stats_v1');
            Cache::forget('admin_school_overview_stats');
            Cache::forget('public_home_data_v1');

            $this->info("=================================================");
            $this->info("IMPOR BULK BERHASIL!");
            $this->info("- Total Akun Siswa    : {$totalParsed}");
            $this->info("- Rombel Diperbarui   : " . count($classSummaries) . " kelas");
            $this->info("- Tahun Ajaran Aktif  : {$academicYear}");
            $this->info("- Default Password    : {$defaultPassword}");
            $this->info("=================================================");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan saat mengimpor data siswa: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
