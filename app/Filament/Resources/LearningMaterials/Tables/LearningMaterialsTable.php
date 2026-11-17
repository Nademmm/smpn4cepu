<?php

namespace App\Filament\Resources\LearningMaterials\Tables;

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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LearningMaterialsTable
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
                    ->label('Judul Materi')
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
                IconColumn::make('attachment_path')
                    ->label('Berkas')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus'),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Penyusun')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Pindahkan Materi ke Tong Sampah?')
                    ->modalDescription('Materi ini akan disembunyikan dari portal siswa dan dipindahkan ke Daftar Terhapus. Data materi dan berkas lampiran tetap tersimpan dan dapat dipulihkan kapan saja sebelum masa retensi permanen.'),
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->modalHeading('Pulihkan Materi Pelajaran?')
                    ->modalDescription('Materi ini akan dikembalikan ke status aktif dan dapat kembali diakses oleh siswa.'),
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

