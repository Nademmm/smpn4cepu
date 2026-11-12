<?php

namespace App\Filament\Resources\Books\Tables;

use App\Enums\BookCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->getStateUsing(fn ($record) => $record->cover_image)
                    ->width(48)
                    ->height(64)
                    ->defaultImageUrl(url('images/logo.png')),

                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->description(fn ($record) => ($record->author ?? '-') . ($record->publisher ? ' · ' . $record->publisher : ''))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) => BookCategory::tryFrom((string) $state)?->getLabel() ?? $state)
                    ->color(fn ($state) => BookCategory::tryFrom((string) $state)?->getColor() ?? 'gray')
                    ->sortable(),

                TextColumn::make('physical_stock')
                    ->label('Stok Fisik')
                    ->formatStateUsing(fn ($record) => $record->physical_stock . ' Eks')
                    ->badge()
                    ->color(fn ($record) => $record->physical_stock > 0 ? 'success' : 'danger')
                    ->sortable(),

                IconColumn::make('has_digital')
                    ->label('E-Book')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->has_digital),

                TextColumn::make('views_count')
                    ->label('Dibaca')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('published_year')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori Koleksi')
                    ->options(BookCategory::options()),

                Filter::make('has_digital')
                    ->label('Tersedia E-Book (Digital)')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $q) {
                        $q->whereNotNull('pdf_path')->orWhereNotNull('pdf_url');
                    })),

                Filter::make('has_physical')
                    ->label('Tersedia di Rak Fisik')
                    ->query(fn (Builder $query): Builder => $query->where('physical_stock', '>', 0)),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_public')
                    ->label('Lihat di Web')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn ($record) => route('library', ['buku' => $record->slug]), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Pindahkan Buku ke Tong Sampah?')
                    ->modalDescription('Katalog buku ini akan disembunyikan dari portal perpustakaan dan dipindahkan ke Daftar Terhapus. Riwayat sirkulasi dan peminjaman tetap tersimpan aman dan data dapat dipulihkan kapan saja sebelum masa retensi permanen.'),
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->modalHeading('Pulihkan Katalog Buku?')
                    ->modalDescription('Katalog buku fisik akan dikembalikan ke status aktif dan dapat kembali dipinjam oleh siswa.'),
                ForceDeleteAction::make()
                    ->label('Hapus Permanen'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

