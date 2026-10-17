<?php

namespace App\Filament\Resources\QuestionBanks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->label('Angkatan')
                    ->formatStateUsing(fn ($state) => 'Kelas ' . $state)
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('access_code')
                    ->label('Password / Token')
                    ->badge()
                    ->color('warning')
                    ->copyable()
                    ->copyMessage('Token soal berhasil disalin!')
                    ->placeholder('Terbuka'),
                TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Soal')
                    ->formatStateUsing(fn ($state) => $state . ' Butir')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('attempts_count')
                    ->counts('attempts')
                    ->label('Peserta')
                    ->formatStateUsing(fn ($state) => $state . ' Siswa')
                    ->badge()
                    ->color('primary')
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
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Pembuat Kuis')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('grade_level')
                    ->label('Filter Angkatan Kelas')
                    ->options([
                        7 => 'Kelas VII (Angkatan 7)',
                        8 => 'Kelas VIII (Angkatan 8)',
                        9 => 'Kelas IX (Angkatan 9)',
                    ]),
                SelectFilter::make('subject_id')
                    ->label('Filter Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->preload(),
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
