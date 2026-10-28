<?php

namespace App\Console\Commands;

use App\Models\StudentStatistic;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use ZipArchive;

class ImportStudentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-students 
                            {file=temp_students.xlsx : Path to Excel (.xlsx) file}
                            {--year=2026/2027 : Academic year for student statistics}
                            {--default-password=password123 : Default password for imported students}
                            {--dry-run : Run parsing and display report without saving to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all student accounts from Excel spreadsheet into users and update student_statistics.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = (string) $this->argument('file');
        if (! str_starts_with($filePath, '/') && ! preg_match('/^[a-zA-Z]:/', $filePath)) {
            $filePath = base_path($filePath);
        }

        if (! file_exists($filePath)) {
            $this->error("File Excel tidak ditemukan di: {$filePath}");
            return Command::FAILURE;
        }

        $academicYear = (string) $this->option('year');
        $defaultPassword = (string) $this->option('default-password');
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("Membuka file Excel: {$filePath}");

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            $this->error("Gagal membuka file Excel sebagai arsip zip.");
            return Command::FAILURE;
        }

        // 1. Ekstrak shared strings
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

        // 2. Ekstrak daftar sheet dari workbook
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

        $this->info("Menemukan " . count($sheets) . " sheet kelas. Memproses data siswa...");

        $allStudents = [];
        $classSummaries = [];

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
                $pob = $cells['F'] ?? '';
                $dob = $cells['G'] ?? '';

                if (ctype_digit($no) && ! empty($studentName) && ! empty($nisn)) {
                    $sheetStudents[] = [
                        'sheet' => $sheetName,
                        'no' => (int) $no,
                        'nisn' => $nisn,
                        'nis' => $nis,
                        'name' => $studentName,
                        'gender' => $gender,
                        'pob' => $pob,
                        'dob' => $dob,
                    ];

                    if ($gender === 'L') {
                        $maleCount++;
                    } elseif ($gender === 'P') {
                        $femaleCount++;
                    }
                }
            }

            // Tentukan grade level (misal '7A' -> 7, '8B' -> 8, '9C' -> 9)
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

        $totalParsed = count($allStudents);
        $this->info("Total data siswa berhasil diparsing: {$totalParsed} siswa dari " . count($classSummaries) . " rombel.");

        if ($totalParsed === 0) {
            $this->warn("Tidak ada data siswa yang ditemukan untuk diimpor.");
            return Command::SUCCESS;
        }

        // Tampilkan tabel ringkasan per rombel
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

        // 3. Proses Impor ke Database
        $this->info("Memulai penyimpanan akun siswa & statistik rombel ke database...");

        $siswaRole = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $hashedPassword = Hash::make($defaultPassword);

        $createdCount = 0;
        $updatedCount = 0;

        $bar = $this->output->createProgressBar($totalParsed);
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($allStudents as $st) {
                $nisn = $st['nisn'];
                $email = strtolower($nisn) . '@siswa.smpn4cepu.sch.id';
                $name = $st['name'];
                $className = $st['sheet'];

                $user = User::where('nisn', $nisn)->first();

                if ($user) {
                    $user->update([
                        'name' => $name,
                        'email' => $email,
                        'student_class' => $className,
                    ]);
                    $updatedCount++;
                } else {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'nisn' => $nisn,
                        'student_class' => $className,
                        'password' => $hashedPassword,
                        'email_verified_at' => now(),
                    ]);
                    $createdCount++;
                }

                if (! $user->hasRole('siswa')) {
                    $user->assignRole($siswaRole);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // 4. Perbarui data StudentStatistic untuk Tahun Ajaran tersebut
            $this->info("Memperbarui data statistik rombel untuk Tahun Ajaran: {$academicYear}...");
            foreach ($classSummaries as $c) {
                StudentStatistic::updateOrCreate(
                    [
                        'academic_year' => $academicYear,
                        'class_name' => $c['class_name'],
                    ],
                    [
                        'grade_level' => $c['grade_level'],
                        'male_count' => $c['male_count'],
                        'female_count' => $c['female_count'],
                        'total_count' => $c['total_count'],
                    ]
                );
            }

            // Bersihkan Cache agar halaman publik dan admin langsung terupdate
            Cache::forget('public_student_stats_v1');
            Cache::forget('admin_school_overview_stats');
            Cache::forget('public_home_data_v1');

            DB::commit();

            $this->info("=================================================");
            $this->info("IMPOR BERHASIL!");
            $this->info("- Akun Baru Dibuat    : {$createdCount}");
            $this->info("- Akun Diperbarui     : {$updatedCount}");
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
