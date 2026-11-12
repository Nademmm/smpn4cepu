<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\StudentAcademicHistory;
use App\Models\StudentStatistic;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class AcademicYearService
{
    /**
     * Dapatkan tahun ajaran yang sedang aktif saat ini.
     */
    public function getActiveYear(): AcademicYear
    {
        $year = AcademicYear::getActive();

        if (! $year) {
            $year = AcademicYear::firstOrCreate(
                ['name' => '2026/2027'],
                [
                    'semester' => 'ganjil',
                    'status' => 'aktif',
                    'start_date' => '2026-07-15',
                    'end_date' => '2027-06-30',
                    'description' => 'Tahun Ajaran Aktif SMP Negeri 4 Cepu',
                ]
            );
        }

        return $year;
    }

    /**
     * Dapatkan daftar tahun ajaran yang berstatus persiapan.
     */
    public function getPlanningYears(): Collection
    {
        return AcademicYear::planning()->orderBy('name', 'asc')->get();
    }

    /**
     * Buat tahun ajaran baru dengan status persiapan.
     */
    public function createPlanningYear(array $data): AcademicYear
    {
        $name = trim((string) ($data['name'] ?? ''));

        if (! preg_match('/^\d{4}\/\d{4}$/', $name)) {
            throw new InvalidArgumentException("Format tahun ajaran harus berupa YYYY/YYYY (contoh: 2027/2028).");
        }

        if (AcademicYear::where('name', $name)->exists()) {
            throw new InvalidArgumentException("Tahun ajaran {$name} sudah terdaftar di sistem.");
        }

        return AcademicYear::create([
            'name' => $name,
            'semester' => $data['semester'] ?? 'ganjil',
            'status' => 'persiapan',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'description' => $data['description'] ?? 'Tahun ajaran persiapan baru',
        ]);
    }

    /**
     * Simpan draf pemetaan kenaikan/perpindahan siswa untuk satu rombel asal.
     *
     * @param int $targetYearId ID tahun ajaran persiapan
     * @param string $sourceClass Rombel asal (misal 7A)
     * @param string $defaultAction Tindakan default (nama rombel seperti '8B', atau 'lulus')
     * @param array<int, array{action: string, class?: string|null, notes?: string|null}> $studentOverrides Pengecualian per siswa [student_id => ['action' => 'tinggal_kelas'|'mutasi_keluar'|'lulus'|'naik_kelas', 'class' => '...']]
     */
    public function saveClassPromotionDraft(
        int $targetYearId,
        string $sourceClass,
        string $defaultAction,
        array $studentOverrides = []
    ): array {
        $targetYear = AcademicYear::findOrFail($targetYearId);

        if (! $targetYear->isPlanning()) {
            throw new RuntimeException("Pemetaan siswa hanya bisa dilakukan pada tahun ajaran berstatus persiapan.");
        }

        $activeYear = $this->getActiveYear();

        // Ambil seluruh siswa aktif di rombel asal pada tahun ajaran aktif
        $students = User::where('student_class', $sourceClass)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'aktif');
            })
            ->get();

        if ($students->isEmpty()) {
            throw new InvalidArgumentException("Tidak ditemukan siswa aktif di rombel {$sourceClass}.");
        }

        $savedCount = 0;
        $now = now();

        DB::transaction(function () use ($students, $targetYear, $sourceClass, $defaultAction, $studentOverrides, &$savedCount, $now) {
            foreach ($students as $student) {
                $override = $studentOverrides[$student->id] ?? null;

                $action = $override['action'] ?? null;
                $targetClass = null;
                $status = 'persiapan';
                $notes = $override['notes'] ?? null;

                if ($action === 'tinggal_kelas') {
                    // Siswa tinggal kelas di rombel asal
                    $targetClass = $override['class'] ?? $sourceClass;
                    $status = 'tinggal_kelas';
                    $notes = $notes ?: "Tinggal kelas di {$targetClass}";
                } elseif ($action === 'mutasi_keluar' || $action === 'pindah_sekolah') {
                    $targetClass = null;
                    $status = 'mutasi_keluar';
                    $notes = $notes ?: "Pindah sekolah / mutasi keluar";
                } elseif ($action === 'lulus' || $defaultAction === 'lulus') {
                    $targetClass = null;
                    $status = 'lulus';
                    $notes = $notes ?: "Lulus pendidikan (Calon Alumni)";
                } else {
                    // Naik kelas ke rombel tujuan yang ditentukan
                    $targetClass = ! empty($override['class']) ? $override['class'] : $defaultAction;
                    $status = 'persiapan';
                    $notes = $notes ?: "Rencana naik ke kelas {$targetClass}";
                }

                $gradeLevel = null;
                if ($targetClass && preg_match('/^([789])/i', $targetClass, $m)) {
                    $gradeLevel = (int) $m[1];
                }

                StudentAcademicHistory::updateOrCreate(
                    [
                        'academic_year_id' => $targetYear->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'student_class' => $targetClass,
                        'grade_level' => $gradeLevel,
                        'status' => $status,
                        'notes' => $notes,
                        'updated_at' => $now,
                    ]
                );

                $savedCount++;
            }
        });

        /** @var User|null $actor */
        $actor = Auth::user();
        $actorName = $actor?->name ?? 'Operator';

        ActivityLog::record(
            action: 'save_promotion_draft',
            description: "{$actorName} menyimpan draf pemetaan kenaikan kelas rombel {$sourceClass} ke {$defaultAction} pada Tahun Ajaran {$targetYear->name} ({$savedCount} siswa).",
            reason: 'Penyusunan pemetaan kenaikan kelas persiapan',
            subject: $targetYear,
            properties: [
                'target_year' => $targetYear->name,
                'source_class' => $sourceClass,
                'default_action' => $defaultAction,
                'saved_count' => $savedCount,
            ],
            module: 'Tahun Ajaran'
        );

        return [
            'success' => true,
            'source_class' => $sourceClass,
            'total_students' => $savedCount,
            'target_year' => $targetYear->name,
        ];
    }

    /**
     * Buat pratinjau lengkap dan validasi sebelum aktivasi tahun ajaran baru.
     */
    public function previewYearTransition(int $targetYearId): array
    {
        $targetYear = AcademicYear::findOrFail($targetYearId);
        $activeYear = $this->getActiveYear();

        // 1. Rekapitulasi per Rombel Tujuan di Tahun Ajaran Baru
        $histories = StudentAcademicHistory::with('student')
            ->where('academic_year_id', $targetYear->id)
            ->get();

        $rombelGroups = [];
        $exceptions = [
            'tinggal_kelas' => [],
            'mutasi_keluar' => [],
            'lulus' => [],
        ];

        foreach ($histories as $h) {
            $status = $h->status;

            if ($status === 'tinggal_kelas') {
                $exceptions['tinggal_kelas'][] = [
                    'id' => $h->student_id,
                    'name' => $h->student?->name ?? 'Tanpa Nama',
                    'nisn' => $h->student?->nisn ?? '-',
                    'class' => $h->student_class ?: '-',
                    'notes' => $h->notes,
                ];
            } elseif ($status === 'mutasi_keluar') {
                $exceptions['mutasi_keluar'][] = [
                    'id' => $h->student_id,
                    'name' => $h->student?->name ?? 'Tanpa Nama',
                    'nisn' => $h->student?->nisn ?? '-',
                    'notes' => $h->notes,
                ];
            } elseif ($status === 'lulus') {
                $exceptions['lulus'][] = [
                    'id' => $h->student_id,
                    'name' => $h->student?->name ?? 'Tanpa Nama',
                    'nisn' => $h->student?->nisn ?? '-',
                    'notes' => $h->notes,
                ];
            }

            if (! empty($h->student_class)) {
                $cls = strtoupper($h->student_class);
                if (! isset($rombelGroups[$cls])) {
                    $rombelGroups[$cls] = [
                        'class_name' => $cls,
                        'male' => 0,
                        'female' => 0,
                        'total' => 0,
                    ];
                }

                $gender = strtoupper((string) ($h->student?->gender ?? ''));
                if ($gender === 'L') {
                    $rombelGroups[$cls]['male']++;
                } elseif ($gender === 'P') {
                    $rombelGroups[$cls]['female']++;
                }
                $rombelGroups[$cls]['total']++;
            }
        }

        ksort($rombelGroups);

        // 2. Cari Siswa Aktif yang Belum Memiliki Tujuan di Tahun Baru
        $mappedStudentIds = $histories->pluck('student_id')->toArray();

        $unassignedStudents = User::whereNotNull('student_class')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'aktif');
            })
            ->whereNotIn('id', $mappedStudentIds)
            ->select('id', 'name', 'nisn', 'student_class', 'gender')
            ->orderBy('student_class')
            ->orderBy('name')
            ->get();

        // 3. Pengecekan Data Ganda / Anomali Penempatan
        $duplicates = StudentAcademicHistory::where('academic_year_id', $targetYear->id)
            ->select('student_id', DB::raw('COUNT(*) as total'))
            ->groupBy('student_id')
            ->having('total', '>', 1)
            ->get();

        $totalActiveStudents = User::whereNotNull('student_class')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'aktif');
            })
            ->count();

        $totalMapped = count($mappedStudentIds);

        return [
            'target_year' => $targetYear,
            'active_year' => $activeYear,
            'rombel_summary' => array_values($rombelGroups),
            'exceptions' => $exceptions,
            'unassigned_students' => $unassignedStudents,
            'unassigned_count' => $unassignedStudents->count(),
            'duplicates_count' => $duplicates->count(),
            'total_mapped' => $totalMapped,
            'total_active_students' => $totalActiveStudents,
            'can_activate' => $targetYear->isPlanning() && $duplicates->isEmpty(),
        ];
    }

    /**
     * Aktivasi tahun ajaran baru secara atomik:
     * - Tutup tahun ajaran yang sedang aktif
     * - Aktifkan tahun ajaran target
     * - Perbarui kelas dan status pada akun siswa
     * - Siswa lulus diubah statusnya menjadi alumni (tidak dihapus)
     * - Sinkronkan seluruh rekapitulasi statistik rombel
     */
    public function activateYear(int $targetYearId): array
    {
        $targetYear = AcademicYear::findOrFail($targetYearId);

        if (! $targetYear->isPlanning()) {
            throw new RuntimeException("Tahun ajaran {$targetYear->name} tidak berstatus persiapan.");
        }

        return DB::transaction(function () use ($targetYear) {
            $previousActiveYear = AcademicYear::getActive();

            // 1. Ambil seluruh riwayat pemetaan di tahun ajaran baru
            $histories = StudentAcademicHistory::with('student')
                ->where('academic_year_id', $targetYear->id)
                ->get();

            $updatedCount = 0;
            $alumniCount = 0;
            $mutasiCount = 0;

            foreach ($histories as $history) {
                $student = $history->student;
                if (! $student) {
                    continue;
                }

                if ($history->status === 'lulus') {
                    // Siswa kelas 9 lulus menjadi alumni, akun tetap tersimpan
                    $student->update([
                        'student_class' => null,
                        'status' => 'alumni',
                    ]);
                    $history->update(['status' => 'lulus']);
                    $alumniCount++;
                } elseif ($history->status === 'mutasi_keluar') {
                    $student->update([
                        'student_class' => null,
                        'status' => 'mutasi_keluar',
                    ]);
                    $mutasiCount++;
                } else {
                    // Siswa aktif di rombel baru
                    $student->update([
                        'student_class' => $history->student_class,
                        'status' => 'aktif',
                    ]);
                    $history->update(['status' => 'aktif']);
                    $updatedCount++;
                }
            }

            // 2. Tutup tahun ajaran aktif sebelumnya
            if ($previousActiveYear && $previousActiveYear->id !== $targetYear->id) {
                $previousActiveYear->update(['status' => 'ditutup']);
            }

            // 3. Aktifkan tahun ajaran target
            $targetYear->update(['status' => 'aktif']);

            // 4. Sinkronkan statistik rombel untuk tahun ajaran baru
            User::syncAllRombelStatistics($targetYear->name);

            /** @var User|null $actor */
            $actor = Auth::user();
            $actorName = $actor?->name ?? 'Administrator';
            $previousYearName = $previousActiveYear?->name ?? '-';

            ActivityLog::record(
                action: 'activate_academic_year',
                description: "{$actorName} mengaktifkan Tahun Ajaran {$targetYear->name} dan menutup Tahun Ajaran {$previousYearName}.",
                reason: "Aktivasi kalender akademik resmi: {$updatedCount} siswa naik kelas/diperbarui, {$alumniCount} alumni lulus, dan {$mutasiCount} mutasi.",
                subject: $targetYear,
                properties: [
                    'previous_year' => $previousYearName,
                    'new_active_year' => $targetYear->name,
                    'students_updated' => $updatedCount,
                    'alumni_count' => $alumniCount,
                    'mutasi_count' => $mutasiCount,
                ],
                module: 'Tahun Ajaran'
            );

            return [
                'success' => true,
                'target_year' => $targetYear->name,
                'previous_year' => $previousActiveYear?->name,
                'students_updated' => $updatedCount,
                'alumni_count' => $alumniCount,
                'mutasi_count' => $mutasiCount,
                'total_processed' => $histories->count(),
            ];
        });
    }
}

