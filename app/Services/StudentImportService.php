<?php

namespace App\Services;

use App\Models\StudentStatistic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use ZipArchive;

class StudentImportService
{
    /**
     * Parse berkas Excel (.xlsx) atau CSV dan simpan akun siswa ke basis data.
     *
     * @return array{success: bool, imported: int, updated: int, total: int, classes_count: int, classes: array<string>, errors: array<string>}
     */
    public function import(
        string|UploadedFile $file,
        string $academicYear = '2026/2027',
        string $defaultPassword = 'password123'
    ): array {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($filePath)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'total' => 0,
                'classes_count' => 0,
                'classes' => [],
                'errors' => ['Berkas tidak ditemukan pada server.'],
            ];
        }

        $extension = strtolower(pathinfo(
            $file instanceof UploadedFile ? $file->getClientOriginalName() : $filePath,
            PATHINFO_EXTENSION
        ));

        $students = [];
        $errors = [];

        if ($extension === 'xlsx') {
            $parsed = $this->parseXlsx($filePath);
            $students = $parsed['students'];
            $errors = $parsed['errors'];
        } else {
            $parsed = $this->parseCsv($filePath);
            $students = $parsed['students'];
            $errors = $parsed['errors'];
        }

        if (empty($students)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'total' => 0,
                'classes_count' => 0,
                'classes' => [],
                'errors' => ! empty($errors) ? $errors : ['Tidak ada baris data siswa yang valid dalam berkas.'],
            ];
        }

        $existingNisns = User::whereNotNull('nisn')->pluck('password', 'nisn')->toArray();
        $siswaRole = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $defaultHashedPassword = Hash::make($defaultPassword, ['rounds' => 10]);
        $now = now();

        $rowsToUpsert = [];
        $affectedClasses = [];
        $importedCount = 0;
        $updatedCount = 0;

        foreach ($students as $st) {
            $nisn = trim((string) ($st['nisn'] ?? ''));
            if ($nisn === '') {
                continue;
            }

            $isExisting = array_key_exists($nisn, $existingNisns);
            if ($isExisting) {
                $updatedCount++;
                // Pertahankan password akun yang sudah ada
                $password = $existingNisns[$nisn];
            } else {
                $importedCount++;
                $password = $defaultHashedPassword;
            }

            $rawClass = trim((string) ($st['student_class'] ?? $st['class'] ?? ''));
            $cleanClass = $this->normalizeClass($rawClass);

            if ($cleanClass !== '') {
                $affectedClasses[$cleanClass] = true;
            }

            $rowsToUpsert[] = [
                'name' => trim((string) $st['name']),
                'email' => strtolower($nisn) . '@siswa.smpn4cepu.sch.id',
                'nisn' => $nisn,
                'nis' => ! empty($st['nis']) ? trim((string) $st['nis']) : null,
                'gender' => in_array($st['gender'] ?? null, ['L', 'P'], true) ? $st['gender'] : null,
                'birth_place' => ! empty($st['birth_place']) ? trim((string) $st['birth_place']) : null,
                'birth_date' => ! empty($st['birth_date']) ? $st['birth_date'] : null,
                'student_class' => $cleanClass ?: null,
                'password' => $password,
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
                    ['name', 'email', 'nis', 'gender', 'birth_place', 'birth_date', 'student_class', 'updated_at']
                );
            }

            // Pasangkan role siswa untuk setiap user yang memiliki NISN
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

            // Sinkronkan statistik rombel untuk kelas yang terpengaruh
            foreach (array_keys($affectedClasses) as $cls) {
                User::syncSingleRombelStatistic($cls, $academicYear);
            }

            DB::commit();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            Cache::forget('public_student_stats_v1');
            Cache::forget('admin_school_overview_stats');
            Cache::forget('public_home_data_v1');

            return [
                'success' => true,
                'imported' => $importedCount,
                'updated' => $updatedCount,
                'total' => count($rowsToUpsert),
                'classes_count' => count($affectedClasses),
                'classes' => array_keys($affectedClasses),
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'total' => 0,
                'classes_count' => 0,
                'classes' => [],
                'errors' => ['Database error: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Membaca berkas Excel (.xlsx).
     * Mendukung multi-sheet format Dapodik (nama sheet = rombel) maupun single-sheet tabular format.
     *
     * @return array{students: array<int, array<string, mixed>>, errors: array<string>}
     */
    protected function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [
                'students' => [],
                'errors' => ['Gagal membuka berkas Excel (.xlsx). Pastikan berkas tidak rusak atau terenkripsi.'],
            ];
        }

        $sharedStrings = [];
        $sstXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sstXml) {
            $xml = simplexml_load_string($sstXml);
            if ($xml && isset($xml->si)) {
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
        }

        $wbXmlStr = $zip->getFromName('xl/workbook.xml');
        $relsXmlStr = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (! $wbXmlStr || ! $relsXmlStr) {
            $zip->close();
            return [
                'students' => [],
                'errors' => ['Struktur berkas Excel (.xlsx) tidak valid.'],
            ];
        }

        $wbXml = simplexml_load_string($wbXmlStr);
        $relsXml = simplexml_load_string($relsXmlStr);

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

        $allStudents = [];
        $errors = [];

        foreach ($sheets as $sheetName => $target) {
            $sheetContent = $zip->getFromName($target);
            if (! $sheetContent) {
                continue;
            }

            $sheetXml = simplexml_load_string($sheetContent);
            if (! $sheetXml || ! isset($sheetXml->sheetData)) {
                continue;
            }

            $isDapodikSheet = (bool) preg_match('/^[789][A-Z]/i', trim($sheetName));
            $headerMap = [];
            $headerRowPassed = false;

            foreach ($sheetXml->sheetData->row as $row) {
                $rowNum = (int) $row['r'];
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

                if (empty(array_filter($cells))) {
                    continue;
                }

                // Format Dapodik: Tiap sheet adalah nama kelas (7A, 7B, dsb)
                // Baris data siswa dimulai setelah judul/header (biasanya row >= 7 atau 8)
                if ($isDapodikSheet) {
                    $no = $cells['A'] ?? '';
                    $nisn = $cells['B'] ?? '';
                    $nis = $cells['C'] ?? '';
                    $studentName = $cells['D'] ?? '';
                    $gender = strtoupper($cells['E'] ?? '');
                    $birthPlace = $cells['F'] ?? '';
                    $birthDateRaw = $cells['G'] ?? '';

                    // Deteksi baris header Dapodik jika belum terlewat
                    if (str_contains(strtolower($nisn), 'nisn') || str_contains(strtolower($studentName), 'nama')) {
                        $headerRowPassed = true;
                        continue;
                    }

                    if (! empty($studentName) && ! empty($nisn) && preg_match('/^\d{4,20}$/', $nisn)) {
                        $allStudents[] = [
                            'student_class' => $sheetName,
                            'nisn' => $nisn,
                            'nis' => $nis !== '' ? $nis : null,
                            'name' => $studentName,
                            'gender' => in_array($gender, ['L', 'P'], true) ? $gender : null,
                            'birth_place' => $birthPlace !== '' ? $birthPlace : null,
                            'birth_date' => $this->parseBirthDate($birthDateRaw),
                        ];
                    }
                    continue;
                }

                // Format Single Sheet dengan baris Header Kolom
                if (! $headerRowPassed) {
                    $hasHeaderMatch = false;
                    foreach ($cells as $col => $headerText) {
                        $mapped = $this->mapKeyToField(strtolower(trim($headerText)));
                        if ($mapped) {
                            $headerMap[$col] = $mapped;
                            $hasHeaderMatch = true;
                        }
                    }
                    if ($hasHeaderMatch && isset($headerMap)) {
                        $headerRowPassed = true;
                    }
                    continue;
                }

                $rowData = [];
                foreach ($cells as $col => $val) {
                    $field = $headerMap[$col] ?? null;
                    if ($field) {
                        $rowData[$field] = $val;
                    }
                }

                $nisn = trim((string) ($rowData['nisn'] ?? ''));
                $name = trim((string) ($rowData['name'] ?? ''));

                if (! empty($nisn) && ! empty($name) && preg_match('/^\d{4,20}$/', $nisn)) {
                    $gender = strtoupper(trim((string) ($rowData['gender'] ?? '')));
                    $birthDateRaw = $rowData['birth_date'] ?? '';

                    $allStudents[] = [
                        'student_class' => $rowData['student_class'] ?? $sheetName,
                        'nisn' => $nisn,
                        'nis' => ! empty($rowData['nis']) ? trim((string) $rowData['nis']) : null,
                        'name' => $name,
                        'gender' => in_array($gender, ['L', 'P'], true) ? $gender : null,
                        'birth_place' => ! empty($rowData['birth_place']) ? trim((string) $rowData['birth_place']) : null,
                        'birth_date' => $this->parseBirthDate($birthDateRaw),
                    ];
                }
            }
        }

        $zip->close();

        return [
            'students' => $allStudents,
            'errors' => $errors,
        ];
    }

    /**
     * Membaca berkas CSV.
     *
     * @return array{students: array<int, array<string, mixed>>, errors: array<string>}
     */
    protected function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [
                'students' => [],
                'errors' => ['Gagal membuka berkas CSV.'],
            ];
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        $header = fgetcsv($handle, 4096, $delimiter);
        if (! $header) {
            fclose($handle);
            return [
                'students' => [],
                'errors' => ['Header berkas CSV tidak ditemukan.'],
            ];
        }

        $headerMap = [];
        foreach ($header as $idx => $h) {
            $mapped = $this->mapKeyToField(strtolower(trim((string) $h)));
            if ($mapped) {
                $headerMap[$idx] = $mapped;
            }
        }

        $students = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
            $rowNumber++;
            if (empty(array_filter($row))) {
                continue;
            }

            $rowData = [];
            foreach ($row as $idx => $val) {
                $field = $headerMap[$idx] ?? null;
                if ($field) {
                    $rowData[$field] = trim((string) $val);
                }
            }

            $nisn = trim((string) ($rowData['nisn'] ?? ''));
            $name = trim((string) ($rowData['name'] ?? ''));

            if (! empty($nisn) && ! empty($name) && preg_match('/^\d{4,20}$/', $nisn)) {
                $gender = strtoupper(trim((string) ($rowData['gender'] ?? '')));
                $birthDateRaw = $rowData['birth_date'] ?? '';

                $students[] = [
                    'student_class' => $rowData['student_class'] ?? '7A',
                    'nisn' => $nisn,
                    'nis' => ! empty($rowData['nis']) ? trim((string) $rowData['nis']) : null,
                    'name' => $name,
                    'gender' => in_array($gender, ['L', 'P'], true) ? $gender : null,
                    'birth_place' => ! empty($rowData['birth_place']) ? trim((string) $rowData['birth_place']) : null,
                    'birth_date' => $this->parseBirthDate($birthDateRaw),
                ];
            }
        }

        fclose($handle);

        return [
            'students' => $students,
            'errors' => [],
        ];
    }

    /**
     * Memetakan nama kolom Excel/CSV ke atribut siswa.
     */
    protected function mapKeyToField(string $key): ?string
    {
        $clean = preg_replace('/[^a-z0-9_]/', '', $key);

        if (str_contains($clean, 'nisn')) return 'nisn';
        if ($clean === 'nis' || str_contains($clean, 'nomor_induk') || str_contains($clean, 'no_induk')) return 'nis';
        if (str_contains($clean, 'nama') || str_contains($clean, 'name') || str_contains($clean, 'peserta_didik') || str_contains($clean, 'siswa')) return 'name';
        if (str_contains($clean, 'gender') || str_contains($clean, 'kelamin') || str_contains($clean, 'jk') || $clean === 'lp') return 'gender';
        if (str_contains($clean, 'kelas') || str_contains($clean, 'rombel') || str_contains($clean, 'class')) return 'student_class';
        if (str_contains($clean, 'tempat_lahir') || str_contains($clean, 'tempat') || str_contains($clean, 'birth_place')) return 'birth_place';
        if (str_contains($clean, 'tanggal_lahir') || str_contains($clean, 'tgl_lahir') || str_contains($clean, 'birth_date') || str_contains($clean, 'tgl')) return 'birth_date';

        return null;
    }

    /**
     * Parse tanggal lahir dari serial Excel atau string format umum.
     */
    protected function parseBirthDate(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $trimmed = trim($raw);

        // Jika berupa serial number Excel (contoh: 41532)
        if (ctype_digit($trimmed) && (int) $trimmed > 20000) {
            $unix = ((int) $trimmed - 25569) * 86400;
            return gmdate('Y-m-d', $unix);
        }

        // Format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
            return $trimmed;
        }

        // Format DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $trimmed, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        try {
            return Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Normalisasi nama rombel / kelas (contoh: '7A', 'Kelas 7A' -> '7A').
     */
    protected function normalizeClass(string $rawClass): string
    {
        $trimmed = strtoupper(trim($rawClass));
        if (preg_match('/([789][A-Z])/i', $trimmed, $m)) {
            return strtoupper($m[1]);
        }

        return $trimmed;
    }

    /**
     * Dapatkan konten CSV template resmi untuk diunduh oleh admin.
     */
    public static function getTemplateCsv(): string
    {
        $rows = [
            ['nisn', 'nis', 'nama', 'jenis_kelamin', 'rombel', 'tempat_lahir', 'tanggal_lahir'],
            ['0137031983', '269772', 'AFIKA ARDINA SARI', 'P', '7A', 'BLORA', '2013-09-10'],
            ['0136043304', '269773', 'AHMAD NUR ROMADHONI', 'L', '7A', 'BLORA', '2013-07-15'],
            ['0123456789', '269800', 'BAGUS PRASETYO', 'L', '8B', 'CEPU', '2012-05-20'],
            ['0123456790', '269801', 'CITRA LESTARI', 'P', '9A', 'BLORA', '2011-11-04'],
        ];

        $output = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return (string) $csv;
    }
}

