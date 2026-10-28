<?php

namespace App\Filament\Resources\QuizAttempts\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = 'Rincian Jawaban Siswa per Butir Soal';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question.order_index')
                    ->label('No.')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('question.question_text')
                    ->label('Pertanyaan Soal')
                    ->html()
                    ->wrap()
                    ->limit(140),

                TextColumn::make('selectedOption.option_label')
                    ->label('Opsi Dipilih')
                    ->badge()
                    ->color(fn ($record): string => $record->is_correct ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state, $record): string => $state
                        ? "{$state}. " . strip_tags((string) $record->selectedOption?->option_text)
                        : 'Tidak Dijawab')
                    ->wrap(),

                IconColumn::make('is_correct')
                    ->label('Status Benar')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('question.points')
                    ->label('Bobot Poin')
                    ->alignCenter()
                    ->suffix(' Poin'),
            ])
            ->defaultSort('id', 'asc')
            ->paginated([10, 25, 50]);
    }
}
