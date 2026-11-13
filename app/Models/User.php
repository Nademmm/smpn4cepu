<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    public static bool $skipStatisticSync = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'nisn',
        'nis',
        'gender',
        'birth_place',
        'birth_date',
        'student_class',
        'homeroom_class',
        'status',
        'dapodik_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $user) {
            if ($user->wasChanged(['student_class', 'gender', 'status']) || $user->wasRecentlyCreated) {
                \App\Support\ClassroomOptions::flushCache();

                if (self::$skipStatisticSync) {
                    return;
                }

                $oldClass = $user->getOriginal('student_class');
                if ($oldClass && preg_match('/^[789][A-Z]$/i', (string) $oldClass)) {
                    self::syncSingleRombelStatistic(strtoupper((string) $oldClass));
                }
                if ($user->student_class && preg_match('/^[789][A-Z]$/i', (string) $user->student_class)) {
                    self::syncSingleRombelStatistic(strtoupper((string) $user->student_class));
                }
            }
        });

        static::deleted(function (self $user) {
            \App\Support\ClassroomOptions::flushCache();

            if (! self::$skipStatisticSync && $user->student_class && preg_match('/^[789][A-Z]$/i', (string) $user->student_class)) {
                self::syncSingleRombelStatistic(strtoupper((string) $user->student_class));
            }
        });
    }

    public static function syncSingleRombelStatistic(string $className, ?string $academicYear = null): void
    {
        if (! preg_match('/^([789])[A-Z]$/i', $className, $m)) {
            return;
        }

        $academicYear = $academicYear ?: (AcademicYear::getActive()?->name ?? '2026/2027');

        $baseQuery = self::where('student_class', $className)
            ->where(function ($q): void {
                $q->whereNull('status')->orWhere('status', 'aktif');
            });

        $male = (clone $baseQuery)->where('gender', 'L')->count();
        $female = (clone $baseQuery)->where('gender', 'P')->count();

        if ($male === 0 && $female === 0) {
            return;
        }

        StudentStatistic::updateOrCreate(
            [
                'academic_year' => $academicYear,
                'class_name' => strtoupper($className),
            ],
            [
                'grade_level' => (int) $m[1],
                'male_count' => $male,
                'female_count' => $female,
                'total_count' => $male + $female,
            ]
        );
    }

    public static function syncAllRombelStatistics(?string $academicYear = null): int
    {
        $academicYear = $academicYear ?: (AcademicYear::getActive()?->name ?? '2026/2027');

        $classes = \App\Support\ClassroomOptions::allClasses();

        $synced = 0;
        foreach ($classes as $cls) {
            self::syncSingleRombelStatistic($cls, $academicYear);
            $synced++;
        }

        return $synced;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'operator_sekolah', 'guru', 'wali_kelas', 'staf_perpus', 'panitia_pilketos']);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'created_by');
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'created_by');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id', 'subject_id')
            ->withPivot(['allowed_grades', 'allowed_classes'])
            ->withTimestamps();
    }

    /**
     * @return list<mixed>
     */
    public static function parsePivotArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? array_values($decoded) : [];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    public static function standardClassesForGrade(?int $gradeLevel = null): array
    {
        $map = [
            7 => ['7A', '7B', '7C', '7D', '7E', '7F', '7G'],
            8 => ['8A', '8B', '8C', '8D', '8E', '8F', '8G'],
            9 => ['9A', '9B', '9C', '9D', '9E', '9F'],
        ];

        if ($gradeLevel !== null) {
            return $map[$gradeLevel] ?? [];
        }

        return array_merge($map[7], $map[8], $map[9]);
    }

    /**
     * @return list<int>
     */
    public function allowedGradesForSubject(?int $subjectId): array
    {
        if (! $subjectId) {
            return [];
        }

        if (! $this->hasRole('guru') || $this->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return [7, 8, 9];
        }

        $subject = $this->relationLoaded('subjects')
            ? $this->subjects->firstWhere('id', $subjectId)
            : $this->subjects()->whereKey($subjectId)->first();

        if (! $subject) {
            return [];
        }

        $grades = array_values(array_unique(array_filter(
            array_map('intval', self::parsePivotArray($subject->pivot?->allowed_grades)),
            fn (int $g): bool => in_array($g, [7, 8, 9], true)
        )));

        if (empty($grades)) {
            $classes = self::parsePivotArray($subject->pivot?->allowed_classes);
            if (! empty($classes)) {
                $grades = array_values(array_unique(array_filter(
                    array_map(fn ($cls): int => (int) substr((string) $cls, 0, 1), $classes),
                    fn (int $g): bool => in_array($g, [7, 8, 9], true)
                )));
            }
        }

        if (empty($grades)) {
            return [7, 8, 9];
        }

        sort($grades);

        return $grades;
    }

    /**
     * @return list<string> Empty array for restricted-check means unrestricted across allowed grades when teacher teaches that subject/grade.
     */
    public function allowedClassesForSubject(?int $subjectId, ?int $gradeLevel = null): array
    {
        if (! $this->hasRole('guru') || $this->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return self::standardClassesForGrade($gradeLevel);
        }

        if (! $subjectId) {
            return [];
        }

        $subject = $this->relationLoaded('subjects')
            ? $this->subjects->firstWhere('id', $subjectId)
            : $this->subjects()->whereKey($subjectId)->first();

        if (! $subject) {
            return [];
        }

        if ($gradeLevel !== null && ! in_array($gradeLevel, $this->allowedGradesForSubject($subjectId), true)) {
            return [];
        }

        $classes = array_values(array_unique(array_filter(
            array_map(fn ($c): string => strtoupper(trim((string) $c)), self::parsePivotArray($subject->pivot?->allowed_classes)),
            fn (string $c): bool => $c !== ''
        )));

        if (empty($classes)) {
            return [];
        }

        if ($gradeLevel !== null) {
            $classes = array_values(array_filter(
                $classes,
                fn (string $c): bool => str_starts_with($c, (string) $gradeLevel)
            ));
        }

        return $classes;
    }

    /**
     * Check whether teacher can view/manage an existing content item.
     *
     * @param  array<int, string>|null  $targetClasses
     */
    public function canAccessSubjectContent(?int $subjectId, ?int $gradeLevel = null, ?array $targetClasses = null): bool
    {
        if (! $this->hasRole('guru') || $this->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return true;
        }

        if (! $subjectId) {
            return false;
        }

        $subject = $this->relationLoaded('subjects')
            ? $this->subjects->firstWhere('id', $subjectId)
            : $this->subjects()->whereKey($subjectId)->first();

        if (! $subject) {
            return false;
        }

        $allowedGrades = $this->allowedGradesForSubject($subjectId);
        if ($gradeLevel !== null && ! in_array((int) $gradeLevel, $allowedGrades, true)) {
            return false;
        }

        $rawAllowedClasses = self::parsePivotArray($subject->pivot?->allowed_classes);
        $allowedClasses = array_values(array_unique(array_filter(
            array_map(fn ($c): string => strtoupper(trim((string) $c)), $rawAllowedClasses),
            fn (string $c): bool => $c !== ''
        )));

        if (! empty($allowedClasses)) {
            $normalizedTargets = array_values(array_unique(array_filter(
                array_map(fn ($c): string => strtoupper(trim((string) $c)), $targetClasses ?? []),
                fn (string $c): bool => $c !== ''
            )));

            if (! empty($normalizedTargets)) {
                return ! empty(array_intersect($allowedClasses, $normalizedTargets));
            }

            if ($gradeLevel !== null) {
                return ! empty($this->allowedClassesForSubject($subjectId, (int) $gradeLevel));
            }
        }

        return true;
    }

    /**
     * Validate teacher form payload strictly (every selected target_class must belong to teacher's allowed_classes).
     *
     * @param  array<int, string>|null  $targetClasses
     */
    public function canStoreSubjectContent(?int $subjectId, ?int $gradeLevel, ?array $targetClasses = null): bool
    {
        if (! $this->hasRole('guru') || $this->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return true;
        }

        if (! $subjectId || ! $gradeLevel || ! $this->canAccessSubjectContent($subjectId, $gradeLevel, $targetClasses)) {
            return false;
        }

        $allowedForGrade = $this->allowedClassesForSubject($subjectId, $gradeLevel);
        if (empty($allowedForGrade)) {
            // Pivot has no rombel-level restriction (legacy / admin unrestricted assignment).
            return true;
        }

        $normalizedTargets = array_values(array_unique(array_filter(
            array_map(fn ($c): string => strtoupper(trim((string) $c)), $targetClasses ?? []),
            fn (string $c): bool => $c !== ''
        )));

        if (empty($normalizedTargets)) {
            return true;
        }

        return empty(array_diff($normalizedTargets, $allowedForGrade));
    }

    public function applyTeacherContentScope(
        Builder $query,
        string $subjectCol = 'subject_id',
        string $gradeCol = 'grade_level',
        string $ownerCol = 'created_by',
        string $targetClassesCol = 'target_classes'
    ): Builder {
        if (! $this->hasRole('guru') || $this->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return $query;
        }

        $assignedSubjects = $this->subjects()->get();

        return $query->where(function (Builder $outer) use (
            $assignedSubjects,
            $subjectCol,
            $gradeCol,
            $ownerCol,
            $targetClassesCol
        ): void {
            $outer->where($ownerCol, $this->id);

            foreach ($assignedSubjects as $subject) {
                $allowedGrades = $this->allowedGradesForSubject((int) $subject->id);
                $allowedClasses = array_values(array_unique(array_filter(
                    array_map(
                        fn ($c): string => strtoupper(trim((string) $c)),
                        self::parsePivotArray($subject->pivot?->allowed_classes)
                    ),
                    fn (string $c): bool => $c !== ''
                )));

                $outer->orWhere(function (Builder $branch) use (
                    $subject,
                    $allowedGrades,
                    $allowedClasses,
                    $subjectCol,
                    $gradeCol,
                    $targetClassesCol
                ): void {
                    $branch->where($subjectCol, $subject->id)
                        ->whereIn($gradeCol, $allowedGrades);

                    if (! empty($allowedClasses)) {
                        $branch->where(function (Builder $clsQ) use ($targetClassesCol, $allowedClasses): void {
                            $clsQ->whereNull($targetClassesCol)
                                ->orWhere($targetClassesCol, '[]')
                                ->orWhere(function (Builder $jsonQ) use ($targetClassesCol, $allowedClasses): void {
                                    foreach ($allowedClasses as $className) {
                                        $jsonQ->orWhereJsonContains($targetClassesCol, $className);
                                    }
                                });
                        });
                    }
                });
            }
        });
    }

    public function bookReservations(): HasMany
    {
        return $this->hasMany(BookReservation::class, 'user_id');
    }

    public function academicHistories(): HasMany
    {
        return $this->hasMany(StudentAcademicHistory::class, 'student_id');
    }

    public function currentAcademicHistory(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StudentAcademicHistory::class, 'student_id')
            ->whereHas('academicYear', fn ($q) => $q->where('status', 'aktif'));
    }

    public function scopeActiveStudents(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'aktif')->whereNotNull('student_class');
    }

    public function scopeAlumni(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'alumni');
    }
}
