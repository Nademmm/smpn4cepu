<?php

namespace App\Filament\Resources\QuestionBanks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionBanksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Kuis / Ujian')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->searchable(),
                TextColumn::make('grade_level')
                    ->label('Kelas')
                    ->formatStateUsing(fn ($state) => 'Kelas ' . $state)
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(fn ($state) => $state . ' Menit')
                    ->sortable(),
                TextColumn::make('passing_grade')
                    ->label('KKM')
                    ->formatStateUsing(fn ($state) => $state . ' Poin')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Pembuat Kuis')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

