<?php

namespace App\Filament\Resources\QuizAttempts\Tables;

use App\Filament\Resources\Students\Schemas\StudentForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_identifier')
                    ->label('Nama Peserta / Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): string => $record->user?->nisn ? "NISN: {$record->user->nisn}" : 'Peserta Umum / Tanpa NISN'),

                TextColumn::make('user.student_class')
                    ->label('Rombel')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('bank.subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('bank.title')
                    ->label('Paket Soal Kuis')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('correct_answers')
                    ->label('Benar / Soal')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state, $record): string => "{$state} / {$record->total_questions} Soal"),

                TextColumn::make('final_score')
                    ->label('Nilai Akhir')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => (float) $state >= 75 ? 'success' : ((float) $state >= 60 ? 'warning' : 'danger')),

                IconColumn::make('is_passed')
                    ->label('Ketuntasan')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('completed_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('completed_at', 'desc')
            ->filters([
                SelectFilter::make('bank_id')
                    ->label('Filter Paket Soal')
                    ->relationship('bank', 'title')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('student_class')
                    ->label('Filter Kelas / Rombel')
                    ->options(StudentForm::getRombelOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('user', fn (Builder $q) => $q->where('student_class', $data['value']));
                    }),

                TernaryFilter::make('is_passed')
                    ->label('Status Ketuntasan KKM')
                    ->trueLabel('Tuntas (Lulus KKM)')
                    ->falseLabel('Belum Tuntas (Remedial)'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Detail & Koreksi'),
                DeleteAction::make()
                    ->label('Reset'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus / Reset Nilai Terpilih'),
                ]),
            ]);
    }
}
