<?php

namespace App\Filament\Resources\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademicHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'academicHistories';

    protected static ?string $title = 'Riwayat Kelas & Tahun Ajaran';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student_class')
            ->columns([
                TextColumn::make('academicYear.academic_year')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('student_class')
                    ->label('Rombel')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('grade_level')
                    ->label('Tingkat')
                    ->formatStateUsing(fn (?int $state): string => $state ? 'Kelas ' . $state : '-')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'aktif' => 'Aktif',
                        'persiapan' => 'Persiapan Naik Kelas',
                        'lulus' => 'Lulus (Alumni)',
                        'mutasi_keluar' => 'Pindah Sekolah',
                        'ditutup' => 'Riwayat Tahun Lalu',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'aktif' => 'success',
                        'persiapan' => 'warning',
                        'lulus' => 'info',
                        'mutasi_keluar' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->wrap()
                    ->placeholder('-'),
            ])
            ->defaultSort('id', 'desc');
    }
}

