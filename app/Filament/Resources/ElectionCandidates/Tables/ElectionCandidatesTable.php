<?php

namespace App\Filament\Resources\ElectionCandidates\Tables;

use App\Models\ActivityLog;
use App\Models\ElectionCandidate;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ElectionCandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate_number')
                    ->label('No. Urut')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                ImageColumn::make('photo_path')
                    ->label('Foto Paslon')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->photo_url),
                TextColumn::make('candidate_name')
                    ->label('Calon Ketua OSIS')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('vice_candidate_name')
                    ->label('Calon Wakil Ketua OSIS')
                    ->searchable(),
                TextColumn::make('total_votes_cached')
                    ->label('Total Suara')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('period_year')
                    ->label('Periode')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'diarsipkan' => 'Arsip Periode Selesai',
                        default => 'Aktif Berjalan',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'diarsipkan' => 'gray',
                        default => 'success',
                    })
                    ->sortable(),
            ])
            ->defaultSort('candidate_number', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status Periode')
                    ->options([
                        'aktif' => 'Aktif Berjalan',
                        'diarsipkan' => 'Diarsipkan (Selesai)',
                    ]),
                SelectFilter::make('period_year')
                    ->label('Filter Tahun Periode')
                    ->options(fn (): array => ElectionCandidate::distinct()->pluck('period_year', 'period_year')->toArray()),
            ])
            ->recordActions([
                Action::make('archive')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (ElectionCandidate $record): bool => $record->status === 'aktif')
                    ->requiresConfirmation()
                    ->modalHeading('Arsipkan Paslon & Periode Ini?')
                    ->modalDescription('Periode Pilketos yang selesai akan diarsipkan. Data perolehan suara tetap tersimpan dalam arsip operasional.')
                    ->action(function (ElectionCandidate $record): void {
                        $record->update(['status' => 'diarsipkan']);

                        ActivityLog::record(
                            action: 'archive_pilketos_candidate',
                            description: "Panitia mengarsipkan Paslon No. {$record->candidate_number} ({$record->candidate_name}) Periode {$record->period_year}.",
                            reason: 'Pengarsipan hasil Pilketos yang telah selesai',
                            subject: $record,
                            module: 'Kesiswaan'
                        );

                        Notification::make()
                            ->title("Paslon No. {$record->candidate_number} berhasil diarsipkan")
                            ->success()
                            ->send();
                    }),

                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ElectionCandidate $record): bool => $record->status === 'diarsipkan')
                    ->action(function (ElectionCandidate $record): void {
                        $record->update(['status' => 'aktif']);

                        Notification::make()
                            ->title("Paslon No. {$record->candidate_number} kembali aktif")
                            ->success()
                            ->send();
                    }),

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

