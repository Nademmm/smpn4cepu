<?php

namespace App\Filament\Resources\QuizAttempts;

use App\Filament\Resources\QuizAttempts\Pages\EditQuizAttempt;
use App\Filament\Resources\QuizAttempts\Pages\ListQuizAttempts;
use App\Filament\Resources\QuizAttempts\RelationManagers\AnswersRelationManager;
use App\Filament\Resources\QuizAttempts\Schemas\QuizAttemptForm;
use App\Filament\Resources\QuizAttempts\Tables\QuizAttemptsTable;
use App\Models\QuizAttempt;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class QuizAttemptResource extends Resource
{
    protected static ?string $model = QuizAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function getNavigationLabel(): string
    {
        return 'Hasil & Nilai Kuis';
    }

    public static function getModelLabel(): string
    {
        return 'Hasil Kuis Siswa';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Hasil & Nilai Kuis Siswa';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Akademik & Pembelajaran';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['user', 'bank.subject']);
        /** @var User|null $user */
        $user = Auth::user();

        if ($user instanceof User && ! $user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            $isGuru = $user->hasRole('guru');
            $isWaliKelas = $user->hasRole('wali_kelas');

            if ($isGuru || $isWaliKelas) {
                $assignedSubjects = $isGuru ? $user->subjects()->get() : collect();
                $homeroomClass = $isWaliKelas ? $user->homeroom_class : null;

                if (! $isGuru && empty($homeroomClass)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where(function (Builder $scopeQuery) use ($user, $isGuru, $assignedSubjects, $homeroomClass): void {
                        if ($isGuru) {
                            $scopeQuery->orWhereHas('bank', function (Builder $bankQuery) use ($user): void {
                                $bankQuery->where('created_by', $user->id);
                            });

                            foreach ($assignedSubjects as $subject) {
                                $allowedGrades = $user->allowedGradesForSubject((int) $subject->id);
                                $allowedClasses = array_values(array_unique(array_filter(
                                    array_map(
                                        fn ($c): string => strtoupper(trim((string) $c)),
                                        User::parsePivotArray($subject->pivot?->allowed_classes)
                                    ),
                                    fn (string $c): bool => $c !== ''
                                )));

                                $scopeQuery->orWhere(function (Builder $attemptBranch) use ($subject, $allowedGrades, $allowedClasses): void {
                                    $attemptBranch->whereHas('bank', function (Builder $bankQ) use ($subject, $allowedGrades): void {
                                        $bankQ->where('subject_id', $subject->id)
                                            ->whereIn('grade_level', $allowedGrades);
                                    });

                                    if (! empty($allowedClasses)) {
                                        $attemptBranch->where(function (Builder $clsQ) use ($allowedClasses): void {
                                            $clsQ->whereIn('student_class', $allowedClasses)
                                                ->orWhereNull('student_class');
                                        });
                                    }
                                });
                            }
                        }

                        if (! empty($homeroomClass)) {
                            $scopeQuery->orWhere('student_class', $homeroomClass);
                        }
                    });
                }
            }
        }

        return $query;
    }

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return Response::deny();
        }

        if ($user->hasAnyRole(['super_admin', 'operator_sekolah'])) {
            return Response::allow();
        }

        if ($user->hasAnyRole(['guru', 'wali_kelas'])) {
            if ($record instanceof QuizAttempt && $user instanceof User) {
                $matchesHomeroom = $user->hasRole('wali_kelas')
                    && ! empty($user->homeroom_class)
                    && strcasecmp((string) $record->student_class, (string) $user->homeroom_class) === 0;

                $bank = $record->bank;
                $matchesTeacher = $user->hasRole('guru') && (
                    ((int) ($bank?->created_by ?? 0) === (int) $user->id)
                    || $user->canAccessSubjectContent(
                        $bank?->subject_id,
                        $bank?->grade_level,
                        $record->student_class ? [$record->student_class] : null
                    )
                );

                if (! $matchesHomeroom && ! $matchesTeacher) {
                    return Response::deny('Anda tidak memiliki izin mengakses hasil kuis untuk mapel atau rombel ini.');
                }
            }

            return Response::allow();
        }

        return Response::deny('Hanya Guru, Wali Kelas, dan Administrator yang dapat mengakses Rekap Nilai Kuis Siswa.');
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasAnyRole(['super_admin', 'guru', 'wali_kelas', 'operator_sekolah']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return QuizAttemptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuizAttemptsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuizAttempts::route('/'),
            'edit' => EditQuizAttempt::route('/{record}/edit'),
        ];
    }
}
