<?php

namespace App\Filament\Resources\QuestionBanks\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class QuestionBanksTable
{
    public static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Pindahkan Paket Soal ke Tong Sampah?')
                    ->modalDescription('Paket soal dan butir pertanyaan ini akan dinonaktifkan dari portal siswa dan dipindahkan ke Daftar Terhapus. Riwayat nilai kuis siswa tetap aman dan data dapat dipulihkan kapan saja sebelum masa retensi permanen.'),
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->modalHeading('Pulihkan Paket Soal?')
                    ->modalDescription('Paket soal dan seluruh butir pertanyaan terkait akan dikembalikan ke status aktif dan dapat diakses kembali oleh siswa.'),
                ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->visible(fn (): bool => static::isSuperAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => static::isSuperAdmin()),
                ]),
            ]);
    }
}
