<?php

namespace App\Filament\Resources\QuestionBanks\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $title = 'Riwayat Nilai & Peserta Ujian';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('participant_identifier')
            ->columns([
                TextColumn::make('participant_identifier')
                    ->label('Nama Siswa & Angkatan')
                    ->searchable()
                    ->sortable()
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

                TextColumn::make('started_at')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                DeleteAction::make()
                    ->label('Reset / Hapus Nilai'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
