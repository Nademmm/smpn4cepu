<?php

namespace App\Support;

use App\Enums\GradeLevel;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SubjectPermissionFormHelper
{
    public static function subjectField(): Select
    {
        return Select::make('subject_id')
            ->label('Mata Pelajaran')
            ->relationship(
                name: 'subject',
                titleAttribute: 'name',
                modifyQueryUsing: function (Builder $query): void {
                    /** @var User|null $user */
                    $user = Auth::user();

                    if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
                        $subjectIds = $user->subjects()->pluck('subjects.id')->all();
                        if (! empty($subjectIds)) {
                            $query->whereIn('id', $subjectIds);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    }
                }
            )
            ->default(function (): ?int {
                /** @var User|null $user */
                $user = Auth::user();

                return $user instanceof User ? $user->subjects()->first()?->id : null;
            })
            ->live()
            ->afterStateUpdated(function ($state, Set $set): void {
                /** @var User|null $user */
                $user = Auth::user();
                $subjectId = $state ? (int) $state : null;

                if (! ($user instanceof User) || ! $subjectId) {
                    $set('grade_level', null);
                    $set('target_classes', null);

                    return;
                }

                $allowedGrades = $user->allowedGradesForSubject($subjectId);
                $firstGrade = $allowedGrades[0] ?? null;
                $set('grade_level', $firstGrade);

                $allowedClasses = $firstGrade ? $user->allowedClassesForSubject($subjectId, $firstGrade) : [];
                $set('target_classes', ! empty($allowedClasses) ? $allowedClasses : null);
            })
            ->helperText('Bagi guru pengampu, pilihan otomatis difilter sesuai mata pelajaran yang ditugaskan oleh admin.')
            ->searchable()
            ->preload()
            ->required();
    }

    public static function gradeLevelField(string $label = 'Tingkat Kelas', ?string $helperText = null): Select
    {
        $field = Select::make('grade_level')
            ->label($label)
            ->options(function (Get $get): array {
                /** @var User|null $user */
                $user = Auth::user();
                $subjectId = $get('subject_id') ? (int) $get('subject_id') : null;

                if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
                    $allowedGrades = $subjectId ? $user->allowedGradesForSubject($subjectId) : [];
                    $options = [];
                    foreach ($allowedGrades as $grade) {
                        $enum = GradeLevel::tryFrom((int) $grade);
                        $options[(int) $grade] = $enum ? $enum->getLabel() : "Kelas {$grade}";
                    }

                    return $options;
                }

                $options = [];
                foreach (GradeLevel::cases() as $case) {
                    $options[$case->value] = $case->getLabel();
                }

                return $options;
            })
            ->default(function (Get $get): ?int {
                /** @var User|null $user */
                $user = Auth::user();
                $subjectId = $get('subject_id') ? (int) $get('subject_id') : ($user instanceof User ? $user->subjects()->first()?->id : null);

                if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah']) && $subjectId) {
                    return $user->allowedGradesForSubject((int) $subjectId)[0] ?? null;
                }

                return null;
            })
            ->live()
            ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                /** @var User|null $user */
                $user = Auth::user();
                $subjectId = $get('subject_id') ? (int) $get('subject_id') : null;
                $gradeLevel = $state instanceof \BackedEnum ? (int) $state->value : ($state ? (int) $state : null);

                if (! ($user instanceof User) || ! $subjectId || ! $gradeLevel) {
                    $set('target_classes', null);

                    return;
                }

                if ($user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
                    $allowedClasses = $user->allowedClassesForSubject($subjectId, $gradeLevel);
                    $set('target_classes', ! empty($allowedClasses) ? $allowedClasses : null);
                } else {
                    $set('target_classes', null);
                }
            })
            ->required();

        if ($helperText !== null) {
            $field->helperText($helperText);
        }

        return $field;
    }

    public static function targetClassesField(): Select
    {
        return Select::make('target_classes')
            ->label('Rombel Target')
            ->multiple()
            ->options(function (Get $get): array {
                /** @var User|null $user */
                $user = Auth::user();
                $subjectId = $get('subject_id') ? (int) $get('subject_id') : null;
                $rawGrade = $get('grade_level');
                $gradeLevel = $rawGrade instanceof \BackedEnum ? (int) $rawGrade->value : ($rawGrade ? (int) $rawGrade : null);

                if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
                    if (! $subjectId || ! $gradeLevel) {
                        return [];
                    }

                    $allowedClasses = $user->allowedClassesForSubject($subjectId, $gradeLevel);
                    if (! empty($allowedClasses)) {
                        return array_combine($allowedClasses, $allowedClasses);
                    }
                }

                $standard = User::standardClassesForGrade($gradeLevel);

                return array_combine($standard, $standard);
            })
            ->default(function (Get $get): ?array {
                /** @var User|null $user */
                $user = Auth::user();
                if (! ($user instanceof User) || ! $user->hasRole('guru') || $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
                    return null;
                }

                $subjectId = $get('subject_id') ? (int) $get('subject_id') : $user->subjects()->first()?->id;
                if (! $subjectId) {
                    return null;
                }

                $rawGrade = $get('grade_level');
                $gradeLevel = $rawGrade instanceof \BackedEnum
                    ? (int) $rawGrade->value
                    : ($rawGrade ? (int) $rawGrade : ($user->allowedGradesForSubject((int) $subjectId)[0] ?? null));

                if (! $gradeLevel) {
                    return null;
                }

                $allowedClasses = $user->allowedClassesForSubject((int) $subjectId, (int) $gradeLevel);

                return ! empty($allowedClasses) ? $allowedClasses : null;
            })
            ->dehydrateStateUsing(fn (?array $state): ?array => empty($state) ? null : array_values(array_unique($state)))
            ->helperText('Otomatis difilter sesuai kelas (rombel) yang Anda ampu. Biarkan terisi rombel Anda atau kosongkan jika berlaku untuk semua rombel tingkat terkait.');
    }

    /**
     * Validate and normalize form data for LearningMaterial and QuestionBank save actions.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function validateAndNormalizePayload(array $data, bool $isCreate = false): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($isCreate) {
            if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
                $data['created_by'] = $user->id;
            } else {
                $data['created_by'] ??= Auth::id();
            }
        }

        if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            $subjectId = isset($data['subject_id']) ? (int) $data['subject_id'] : null;
            $rawGrade = $data['grade_level'] ?? null;
            $gradeLevel = $rawGrade instanceof \BackedEnum ? (int) $rawGrade->value : ($rawGrade ? (int) $rawGrade : null);

            $targetClasses = null;
            if (isset($data['target_classes']) && is_array($data['target_classes'])) {
                $normalized = array_values(array_unique(array_filter(
                    array_map(fn ($c): string => strtoupper(trim((string) $c)), $data['target_classes']),
                    fn (string $c): bool => $c !== ''
                )));
                $targetClasses = ! empty($normalized) ? $normalized : null;
            }

            if ($targetClasses === null && $subjectId && $gradeLevel) {
                $allowedForGrade = $user->allowedClassesForSubject($subjectId, $gradeLevel);
                if (! empty($allowedForGrade)) {
                    $targetClasses = $allowedForGrade;
                }
            }

            $data['target_classes'] = $targetClasses;

            if (! $user->canStoreSubjectContent($subjectId, $gradeLevel, $targetClasses)) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Anda tidak memiliki izin mengajar untuk kombinasi mata pelajaran, tingkat kelas, atau rombel tersebut.',
                ]);
            }
        }

        return $data;
    }
}

