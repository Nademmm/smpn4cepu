<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class QuizAttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'quizAttempts';

    protected static ?string $title = 'Riwayat Pengerjaan & Nilai Kuis Siswa';

    public static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('participant_identifier')
            ->columns([
                TextColumn::make('academicYear.academic_year')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('student_class')
                    ->label('Rombel Saat Kuis')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('bank.subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('bank.title')
                    ->label('Judul Paket Soal / Kuis')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('final_score')
                    ->label('Nilai Akhir')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0) . ' / 100')
                    ->badge()
                    ->color(fn ($record): string => $record->is_passed ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('correct_answers')
                    ->label('Jawaban Benar')
                    ->formatStateUsing(fn ($state, $record) => "{$state} / {$record->total_questions} Soal"),

                IconColumn::make('is_passed')
                    ->label('Lulus KKM')
                    ->boolean(),

                TextColumn::make('completed_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Filter Tahun Ajaran')
                    ->relationship('academicYear', 'academic_year')
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                DeleteAction::make()
                    ->label('Reset / Hapus Nilai')
                    ->visible(fn (): bool => static::isSuperAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::isSuperAdmin()),
                ]),
            ]);
    }
}
