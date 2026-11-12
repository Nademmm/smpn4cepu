<?php

namespace App\Filament\Resources\AcademicYears\Tables;

use App\Models\AcademicYear;
use App\Models\User;
use App\Services\AcademicYearService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class AcademicYearsTable
{
    public static function configure(Table $table): Table
    {
        $allRombelOptions = [
            '7A' => 'Kelas 7A', '7B' => 'Kelas 7B', '7C' => 'Kelas 7C', '7D' => 'Kelas 7D', '7E' => 'Kelas 7E', '7F' => 'Kelas 7F', '7G' => 'Kelas 7G',
            '8A' => 'Kelas 8A', '8B' => 'Kelas 8B', '8C' => 'Kelas 8C', '8D' => 'Kelas 8D', '8E' => 'Kelas 8E', '8F' => 'Kelas 8F', '8G' => 'Kelas 8G',
            '9A' => 'Kelas 9A', '9B' => 'Kelas 9B', '9C' => 'Kelas 9C', '9D' => 'Kelas 9D', '9E' => 'Kelas 9E', '9F' => 'Kelas 9F',
        ];

        $targetActionOptions = array_merge($allRombelOptions, [
            'lulus' => 'Lulus Sekolah (Menjadi Alumni)',
        ]);

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable()
                    ->weight('black'),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ucfirst((string) $state))
                    ->color('info')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktif' => 'Aktif Berjalan',
                        'persiapan' => 'Dalam Persiapan',
                        'ditutup' => 'Ditutup / Arsip',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'persiapan' => 'warning',
                        'ditutup' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('student_histories_count')
                    ->label('Siswa Terdata')
                    ->counts('studentHistories')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state): string => "{$state} Siswa"),

                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'aktif' => 'Aktif Berjalan',
                        'persiapan' => 'Dalam Persiapan',
                        'ditutup' => 'Ditutup / Arsip',
                    ]),
            ])
            ->recordActions([
                // 1. Atur Kenaikan Kelas Massal & Pengecualian
                Action::make('planClassPromotion')
                    ->label('Atur Kenaikan Kelas')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('primary')
                    ->visible(fn (AcademicYear $record): bool => $record->isPlanning())
                    ->modalHeading(fn (AcademicYear $record): string => "Atur Kenaikan Kelas - Tahun Ajaran {$record->name}")
                    ->modalDescription('Pilih rombel asal siswa saat ini, tentukan rombel tujuan utama, dan tentukan pengecualian per siswa (tinggal kelas, pindah sekolah, atau lulus).')
                    ->modalWidth('4xl')
                    ->form([
                        Select::make('source_class')
                            ->label('Rombel Asal (Siswa Tahun Berjalan)')
                            ->options($allRombelOptions)
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->helperText('Pilih kelas siswa saat ini yang akan dinaikkan / dipindahkan.'),

                        Select::make('default_action')
                            ->label('Rombel / Tindakan Tujuan Default')
                            ->options($targetActionOptions)
                            ->required()
                            ->searchable()
                            ->helperText('Seluruh siswa di rombel asal akan diarahkan ke tujuan ini, kecuali siswa yang diatur di bawah.'),

                        Repeater::make('student_overrides')
                            ->label('Pengecualian Per Siswa (Opsional)')
                            ->schema([
                                Select::make('student_id')
                                    ->label('Pilih Siswa')
                                    ->options(function (callable $get) use ($allRombelOptions): array {
                                        $sourceClass = $get('../../source_class');
                                        if (! $sourceClass) {
                                            return [];
                                        }

                                        return User::where('student_class', $sourceClass)
                                            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', 'aktif'))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable(),

                                Select::make('action')
                                    ->label('Status / Tindakan Pengecualian')
                                    ->options([
                                        'naik_kelas' => 'Naik Kelas ke Rombel Lain',
                                        'tinggal_kelas' => 'Tinggal Kelas (Tetap di Tingkat Ini)',
                                        'mutasi_keluar' => 'Pindah Sekolah / Mutasi Keluar',
                                        'lulus' => 'Lulus (Menjadi Alumni)',
                                    ])
                                    ->required()
                                    ->default('tinggal_kelas'),

                                Select::make('custom_class')
                                    ->label('Pilihan Rombel Khusus (Jika Naik ke Rombel Lain)')
                                    ->options($allRombelOptions)
                                    ->searchable()
                                    ->helperText('Kosongkan jika tinggal kelas di rombel lama atau jika mutasi/lulus.'),

                                TextInput::make('notes')
                                    ->label('Alasan / Catatan Pengecualian')
                                    ->placeholder('Misal: Pindah domisili ke luar kota'),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Pengecualian Siswa')
                            ->defaultItems(0)
                            ->collapsible(),
                    ])
                    ->action(function (AcademicYear $record, array $data, AcademicYearService $service): void {
                        $sourceClass = $data['source_class'];
                        $defaultAction = $data['default_action'];

                        $overrides = [];
                        foreach ($data['student_overrides'] ?? [] as $item) {
                            if (! empty($item['student_id'])) {
                                $overrides[$item['student_id']] = [
                                    'action' => $item['action'] ?? 'tinggal_kelas',
                                    'class' => $item['custom_class'] ?? null,
                                    'notes' => $item['notes'] ?? null,
                                ];
                            }
                        }

                        $res = $service->saveClassPromotionDraft(
                            $record->id,
                            $sourceClass,
                            $defaultAction,
                            $overrides
                        );

                        Notification::make()
                            ->title("Pemetaan Rombel {$sourceClass} Berhasil Disimpan")
                            ->body("Sebanyak {$res['total_students']} siswa dari {$sourceClass} telah berhasil dipetakan ke draf tahun ajaran {$record->name}.")
                            ->success()
                            ->send();
                    }),

                // 2. Pratinjau & Validasi Kenaikan
                Action::make('previewTransition')
                    ->label('Pratinjau Kenaikan')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (AcademicYear $record): bool => $record->isPlanning())
                    ->modalHeading(fn (AcademicYear $record): string => "Pratinjau Kenaikan Siswa - {$record->name}")
                    ->modalWidth('4xl')
                    ->modalContent(function (AcademicYear $record, AcademicYearService $service): View {
                        $preview = $service->previewYearTransition($record->id);
                        return view('filament.academic-years.preview-modal', ['preview' => $preview]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup Pratinjau'),

                // 3. Aktivasi Tahun Ajaran Baru
                Action::make('activateYear')
                    ->label('Aktifkan Tahun Ajaran')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AcademicYear $record): bool => $record->isPlanning())
                    ->requiresConfirmation()
                    ->modalHeading(fn (AcademicYear $record): string => "Aktifkan Tahun Ajaran {$record->name}?")
                    ->modalDescription('PERHATIAN: Mengaktifkan tahun ajaran baru akan menutup tahun berjalan dan menerapkan rombel baru ke seluruh akun siswa. Siswa kelas 9 yang lulus akan menjadi alumni, materi kuis dan pinjaman buku perpustakaan tetap aman tersimpan.')
                    ->action(function (AcademicYear $record, AcademicYearService $service): void {
                        $res = $service->activateYear($record->id);

                        Notification::make()
                            ->title("Tahun Ajaran {$record->name} Berhasil Diaktifkan!")
                            ->body("Tahun lama ditutup. {$res['students_updated']} siswa naik kelas, {$res['alumni_count']} siswa lulus menjadi alumni, dan statistik rombel telah disinkronkan.")
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),

                DeleteAction::make()
                    ->visible(fn (AcademicYear $record): bool => ! $record->isActive() && auth()->user()?->hasRole('super_admin') ?? false),
            ]);
    }
}
