<?php

namespace App\Filament\Resources\LibraryBooks\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LibraryBooksTable
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
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->getStateUsing(fn ($record) => $record->cover_url),
                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->description(fn ($record) => $record->author . ' · ' . $record->publisher)
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->searchable(),
                TextColumn::make('shelf_location')
                    ->label('Lokasi Rak')
                    ->description(fn ($record) => $record->call_number ? 'No. Panggil: ' . $record->call_number : null)
                    ->searchable(),
                TextColumn::make('available_stock')
                    ->label('Stok Pinjam')
                    ->formatStateUsing(fn ($record) => $record->available_stock . ' / ' . $record->total_stock . ' Eks')
                    ->badge()
                    ->color(fn ($record) => $record->available_stock > 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('publication_year')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Pindahkan Buku ke Daftar Terhapus?')
                    ->modalDescription('Buku ini akan disembunyikan dari katalog publik dan masuk ke Daftar Terhapus. Data buku tetap tersimpan dan dapat dipulihkan sebelum penghapusan permanen.'),
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->modalHeading('Pulihkan Data Buku?')
                    ->modalDescription('Buku akan dikembalikan ke daftar aktif perpustakaan.'),
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

