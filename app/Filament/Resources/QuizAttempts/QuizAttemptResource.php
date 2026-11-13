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
                $subjectIds = $isGuru ? $user->subjects()->pluck('subjects.id')->all() : [];
                $homeroomClass = $isWaliKelas ? $user->homeroom_class : null;

                if (! $isGuru && empty($homeroomClass)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where(function (Builder $scopeQuery) use ($user, $isGuru, $subjectIds, $homeroomClass): void {
                        if ($isGuru) {
                            $scopeQuery->orWhereHas('bank', function (Builder $bankQuery) use ($user, $subjectIds): void {
                                if (! empty($subjectIds)) {
                                    $bankQuery->where(function (Builder $q) use ($user, $subjectIds): void {
                                        $q->where('created_by', $user->id)
                                            ->orWhereIn('subject_id', $subjectIds);
                                    });
                                } else {
                                    $bankQuery->where('created_by', $user->id);
                                }
                            });
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

        if ($user->hasAnyRole(['super_admin', 'guru', 'wali_kelas', 'operator_sekolah'])) {
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
