<?php

namespace App\Filament\Resources\Students\Tables;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentsTable
{
    public static function canManageStudents(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasAnyRole(['super_admin', 'operator_sekolah']);
    }

    public static function configure(Table $table): Table
    {
        $classOptions = [
            '7A' => 'Kelas 7A', '7B' => 'Kelas 7B', '7C' => 'Kelas 7C', '7D' => 'Kelas 7D', '7E' => 'Kelas 7E', '7F' => 'Kelas 7F', '7G' => 'Kelas 7G',
            '8A' => 'Kelas 8A', '8B' => 'Kelas 8B', '8C' => 'Kelas 8C', '8D' => 'Kelas 8D', '8E' => 'Kelas 8E', '8F' => 'Kelas 8F', '8G' => 'Kelas 8G',
            '9A' => 'Kelas 9A', '9B' => 'Kelas 9B', '9C' => 'Kelas 9C', '9D' => 'Kelas 9D', '9E' => 'Kelas 9E', '9F' => 'Kelas 9F',
        ];

        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('name')
                    ->label('Nama Lengkap Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('gender')
                    ->label('L/P')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'L' => 'Laki-Laki (L)',
                        'P' => 'Perempuan (P)',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'L' => 'info',
                        'P' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('student_class')
                    ->label('Rombel')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'alumni' => 'Alumni (Lulus)',
                        'mutasi_keluar' => 'Pindah Sekolah',
                        default => 'Siswa Aktif',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'alumni' => 'info',
                        'mutasi_keluar' => 'danger',
                        default => 'success',
                    })
                    ->sortable(),

                TextColumn::make('birth_place')
                    ->label('Tempat, Tgl Lahir')
                    ->searchable()
                    ->formatStateUsing(function ($state, User $record): string {
                        $place = $record->birth_place ?: '-';
                        $date = $record->birth_date ? $record->birth_date->translatedFormat('d M Y') : '';
                        return $date ? "{$place}, {$date}" : $place;
                    }),

                TextColumn::make('quiz_attempts_count')
                    ->label('Kuis Dikerjakan')
                    ->badge()
                    ->color(fn ($state): string => ((int) $state) > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state): string => ((int) $state) . ' Kuis')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email Login')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('student_class', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status Siswa')
                    ->options([
                        'aktif' => 'Siswa Aktif',
                        'alumni' => 'Alumni (Lulus)',
                        'mutasi_keluar' => 'Pindah Sekolah / Mutasi',
                    ]),

                SelectFilter::make('student_class')
                    ->label('Filter Rombel Kelas')
                    ->options($classOptions)
                    ->searchable(),

                SelectFilter::make('academic_year_id')
                    ->label('Filter Berdasarkan Tahun Ajaran')
                    ->options(fn (): array => \App\Models\AcademicYear::orderBy('name', 'desc')->pluck('name', 'id')->toArray())
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        if (! empty($data['value'])) {
                            return $query->whereHas('academicHistories', function (\Illuminate\Database\Eloquent\Builder $hq) use ($data) {
                                $hq->where('academic_year_id', $data['value']);
                            });
                        }
                        return $query;
                    }),

                SelectFilter::make('gender')
                    ->label('Filter Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-Laki (Putra)',
                        'P' => 'Perempuan (Putri)',
                    ]),
            ])
            ->recordActions([
                Action::make('moveStudentClass')
                    ->label('Pindah Rombel')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('info')
                    ->visible(fn (): bool => static::canManageStudents())
                    ->modalHeading(fn (User $record): string => "Pindahkan Rombel Siswa: {$record->name}")
                    ->form([
                        Select::make('target_class')
                            ->label('Pilih Rombel Tujuan')
                            ->options($classOptions)
                            ->default(fn (User $record): ?string => $record->student_class)
                            ->required()
                            ->searchable(),

                        TextInput::make('reason')
                            ->label('Alasan Pemindahan')
                            ->placeholder('Contoh: penyesuaian pembagian kelas')
                            ->default('penyesuaian pembagian kelas')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Alasan pemindahan akan dicatat ke dalam log aktivitas sistem.'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $oldClass = $record->student_class ?: '-';
                        $newClass = $data['target_class'];
                        $reason = trim((string) ($data['reason'] ?? 'penyesuaian pembagian kelas'));

                        if ($oldClass === $newClass) {
                            Notification::make()
                                ->title("Siswa {$record->name} sudah berada di rombel {$newClass}")
                                ->warning()
                                ->send();
                            return;
                        }

                        $record->update(['student_class' => $newClass]);

                        if ($oldClass !== '-') {
                            User::syncSingleRombelStatistic((string) $oldClass);
                        }
                        User::syncSingleRombelStatistic((string) $newClass);

                        /** @var User|null $actor */
                        $actor = Auth::user();
                        $actorName = $actor?->name ?? 'Operator';

                        ActivityLog::record(
                            action: 'move_class',
                            description: "{$actorName} memindahkan Siswa {$record->name} dari rombel {$oldClass} ke {$newClass}.",
                            reason: $reason,
                            subject: $record,
                            properties: [
                                'from_class' => $oldClass,
                                'to_class' => $newClass,
                                'student_name' => $record->name,
                                'nisn' => $record->nisn,
                            ],
                            module: 'Kesiswaan'
                        );

                        Notification::make()
                            ->title("Berhasil memindahkan Siswa {$record->name} ke rombel {$newClass}")
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Edit')
                    ->visible(fn (): bool => static::canManageStudents()),

                Action::make('resetPassword')
                    ->label('Reset Sandi')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (): bool => static::canManageStudents())
                    ->requiresConfirmation()
                    ->modalHeading('Reset Kata Sandi Siswa?')
                    ->modalDescription(fn (User $record): string => "Kata sandi untuk {$record->name} (NISN: {$record->nisn}) akan dikembalikan ke default: password123")
                    ->action(function (User $record): void {
                        $record->update([
                            'password' => Hash::make('password123', ['rounds' => 10]),
                        ]);

                        /** @var User|null $actor */
                        $actor = Auth::user();
                        $actorName = $actor?->name ?? 'Operator';

                        ActivityLog::record(
                            action: 'reset_password',
                            description: "{$actorName} mereset kata sandi akun Siswa {$record->name} (NISN: {$record->nisn}).",
                            reason: 'Reset kata sandi default ke password123',
                            subject: $record,
                            properties: [
                                'nisn' => $record->nisn,
                                'name' => $record->name,
                            ],
                            module: 'Keamanan & Akun'
                        );

                        Notification::make()
                            ->title("Sandi {$record->name} berhasil direset ke: password123")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (): bool => static::canManageStudents()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('moveClass')
                        ->label('Pindah Kelas / Rombel Massal')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('primary')
                        ->visible(fn (): bool => static::canManageStudents())
                        ->form([
                            Select::make('target_class')
                                ->label('Pilih Kelas / Rombel Tujuan')
                                ->options($classOptions)
                                ->required()
                                ->searchable(),

                            TextInput::make('reason')
                                ->label('Alasan Pemindahan')
                                ->placeholder('Contoh: penyesuaian pembagian kelas')
                                ->default('penyesuaian pembagian kelas')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Alasan pemindahan akan dicatat ke dalam log aktivitas sistem.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $targetClass = $data['target_class'];
                            $reason = trim((string) ($data['reason'] ?? 'penyesuaian pembagian kelas'));
                            $affectedClasses = [$targetClass];

                            foreach ($records as $student) {
                                if ($student->student_class) {
                                    $affectedClasses[] = $student->student_class;
                                }
                                $student->update(['student_class' => $targetClass]);
                            }

                            foreach (array_unique($affectedClasses) as $cls) {
                                User::syncSingleRombelStatistic((string) $cls);
                            }

                            /** @var User|null $actor */
                            $actor = Auth::user();
                            $actorName = $actor?->name ?? 'Operator';

                            ActivityLog::record(
                                action: 'bulk_move_class',
                                description: "{$actorName} memindahkan {$records->count()} siswa ke rombel {$targetClass}.",
                                reason: $reason,
                                properties: [
                                    'target_class' => $targetClass,
                                    'student_count' => $records->count(),
                                    'students' => $records->pluck('name')->slice(0, 10)->toArray(),
                                ],
                                module: 'Kesiswaan'
                            );

                            Notification::make()
                                ->title($records->count() . " siswa berhasil dipindahkan ke Kelas {$targetClass}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('planBulkPromotion')
                        ->label('Petakan ke Tahun Ajaran Baru (Persiapan)')
                        ->icon('heroicon-o-academic-cap')
                        ->color('success')
                        ->visible(fn (): bool => static::canManageStudents())
                        ->form([
                            Select::make('academic_year_id')
                                ->label('Pilih Tahun Ajaran Persiapan')
                                ->options(fn (): array => \App\Models\AcademicYear::planning()->pluck('name', 'id')->toArray())
                                ->required()
                                ->helperText('Hanya menampilkan tahun ajaran berstatus persiapan.'),

                            Select::make('target_class')
                                ->label('Rombel / Tindakan Tujuan')
                                ->options(array_merge($classOptions, [
                                    'lulus' => 'Lulus Sekolah (Calon Alumni)',
                                    'mutasi_keluar' => 'Pindah Sekolah / Mutasi Keluar',
                                ]))
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $targetYearId = (int) $data['academic_year_id'];
                            $targetClass = $data['target_class'];
                            $isLulus = $targetClass === 'lulus';
                            $isMutasi = $targetClass === 'mutasi_keluar';

                            foreach ($records as $student) {
                                \App\Models\StudentAcademicHistory::updateOrCreate(
                                    [
                                        'academic_year_id' => $targetYearId,
                                        'student_id' => $student->id,
                                    ],
                                    [
                                        'student_class' => (! $isLulus && ! $isMutasi) ? $targetClass : null,
                                        'grade_level' => (! $isLulus && ! $isMutasi && preg_match('/^([789])/i', $targetClass, $m)) ? (int) $m[1] : null,
                                        'status' => $isLulus ? 'lulus' : ($isMutasi ? 'mutasi_keluar' : 'persiapan'),
                                        'notes' => 'Pemetaan massal dari tabel siswa',
                                        'updated_at' => now(),
                                    ]
                                );
                            }

                            Notification::make()
                                ->title($records->count() . ' siswa berhasil dipetakan ke draf tahun ajaran baru')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulkResetPassword')
                        ->label('Reset Password ke Default (password123)')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->visible(fn (): bool => static::canManageStudents())
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $hashed = Hash::make('password123', ['rounds' => 10]);
                            User::whereIn('id', $records->pluck('id'))->update(['password' => $hashed]);

                            Notification::make()
                                ->title($records->count() . ' akun siswa berhasil direset sandinya ke: password123')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canManageStudents()),
                ]),
            ]);
    }
}
