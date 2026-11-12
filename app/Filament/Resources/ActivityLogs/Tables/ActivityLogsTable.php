<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ActivityLogsTable
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                ViewColumn::make('log_entry')
                    ->label('Riwayat Perubahan')
                    ->view('filament.tables.columns.activity-log-entry')
                    ->searchable(query: function ($query, string $search) {
                        $query->where('description', 'like', "%{$search}%")
                            ->orWhere('reason', 'like', "%{$search}%")
                            ->orWhere('actor_name', 'like', "%{$search}%")
                            ->orWhere('subject_label', 'like', "%{$search}%");
                    }),

                TextColumn::make('actor_name')
                    ->label('Pelaku')
                    ->searchable()
                    ->badge()
                    ->color(fn (ActivityLog $record): string => match ($record->actor_role) {
                        'super_admin' => 'danger',
                        'operator_sekolah' => 'warning',
                        'guru' => 'success',
                        default => 'info',
                    })
                    ->description(fn (ActivityLog $record): string => $record->actor_role ? strtoupper(str_replace('_', ' ', $record->actor_role)) : '-'),

                TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tercatat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->label('Filter Modul')
                    ->options([
                        'Kesiswaan' => 'Kesiswaan & Siswa',
                        'Tahun Ajaran' => 'Tahun Ajaran & Kenaikan',
                        'Akademik' => 'Akademik & Pembelajaran',
                        'Perpustakaan' => 'Perpustakaan',
                        'Keamanan & Akun' => 'Keamanan & Akun',
                        'Pengaturan' => 'Pengaturan Sekolah',
                    ]),

                SelectFilter::make('actor_role')
                    ->label('Filter Peran Pelaku')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'operator_sekolah' => 'Operator Sekolah',
                        'guru' => 'Guru Mapel',
                        'wali_kelas' => 'Wali Kelas',
                        'staf_perpus' => 'Petugas Perpustakaan',
                        'sistem' => 'Sistem Otomatis',
                    ]),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Rincian Log Riwayat Perubahan')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->infolist([
                        Section::make('Informasi Utama')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('formatted_created_at')
                                        ->label('Waktu Kejadian')
                                        ->weight('bold'),

                                    TextEntry::make('module')
                                        ->label('Modul')
                                        ->badge(),

                                    TextEntry::make('actor_name')
                                        ->label('Pelaku')
                                        ->badge()
                                        ->color(fn (ActivityLog $record): string => match ($record->actor_role) {
                                            'super_admin' => 'danger',
                                            'operator_sekolah' => 'warning',
                                            default => 'info',
                                        }),

                                    TextEntry::make('actor_role')
                                        ->label('Peran Pelaku')
                                        ->formatStateUsing(fn (?string $state): string => $state ? strtoupper(str_replace('_', ' ', $state)) : '-'),
                                ]),

                                TextEntry::make('description')
                                    ->label('Deskripsi Tindakan')
                                    ->weight('bold'),

                                TextEntry::make('reason')
                                    ->label('Alasan Perubahan')
                                    ->placeholder('Tidak ada alasan spesifik dicatat')
                                    ->color('gray'),

                                TextEntry::make('subject_label')
                                    ->label('Objek / Sasaran Terkait')
                                    ->placeholder('-'),
                            ]),

                        Section::make('Data Teknis & Perubahan')
                            ->collapsed()
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('ip_address')
                                        ->label('Alamat IP')
                                        ->placeholder('-'),

                                    TextEntry::make('user_agent')
                                        ->label('Perangkat / Browser')
                                        ->placeholder('-'),
                                ]),

                                KeyValueEntry::make('properties')
                                    ->label('Metadata Perubahan Data')
                                    ->placeholder('Tidak ada rekaman perubahan data teknis'),
                            ]),
                    ]),

                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (): bool => static::isSuperAdmin()),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->visible(fn (): bool => static::isSuperAdmin()),
            ]);
    }
}
