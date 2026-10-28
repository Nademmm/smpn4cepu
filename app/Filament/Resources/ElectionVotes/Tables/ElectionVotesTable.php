<?php

namespace App\Filament\Resources\ElectionVotes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ElectionVotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID Suara')
                    ->sortable()
                    ->prefix('#')
                    ->fontFamily('mono'),

                TextColumn::make('candidate.candidate_number')
                    ->label('No. Urut')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state): string => "Paslon #{$state}")
                    ->sortable(),

                TextColumn::make('candidate.candidate_name')
                    ->label('Pasangan Calon Pilihan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): string => $record->candidate?->vice_candidate_name
                        ? "Wakil: {$record->candidate->vice_candidate_name}"
                        : '-'),

                TextColumn::make('device.ip_subnet')
                    ->label('Subnet Jaringan')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono')
                    ->placeholder('-'),

                TextColumn::make('device.device_fingerprint')
                    ->label('Hash Perangkat / Akun')
                    ->fontFamily('mono')
                    ->limit(20)
                    ->copyable()
                    ->copyMessage('Fingerprint berhasil disalin'),

                TextColumn::make('created_at')
                    ->label('Waktu Masuk Suara')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('candidate_id')
                    ->label('Filter Pasangan Calon')
                    ->relationship('candidate', 'candidate_name')
                    ->preload(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Batalkan Suara')
                    ->modalHeading('Batalkan & Reset Suara Ini?')
                    ->modalDescription('Suara ini akan dihapus dari kotak suara, total suara paslon akan dikurangi 1, dan perangkat/akun terkait dapat melakukan pemilihan ulang.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Batalkan Suara Terpilih'),
                ]),
            ]);
    }
}
