<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\StudentImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function canManageStudents(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasAnyRole(['super_admin', 'operator_sekolah']);
    }

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol Unduh Template Excel / CSV
            Action::make('downloadTemplate')
                ->label('Unduh Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (): bool => $this->canManageStudents())
                ->action(function (): StreamedResponse {
                    $csvContent = StudentImportService::getTemplateCsv();
                    return response()->streamDownload(function () use ($csvContent) {
                        echo $csvContent;
                    }, 'template_import_siswa_smpn4cepu.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            // 2. Tombol Import Massal Excel / CSV
            Action::make('importExcel')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->visible(fn (): bool => $this->canManageStudents())
                ->modalHeading('Import Akun Siswa Massal dari Excel / CSV')
                ->modalDescription('Unggah berkas Excel (.xlsx) atau CSV daftar siswa. Mendukung format ekspor Dapodik resmi (multi-sheet per rombel) maupun format template tabular CSV.')
                ->modalSubmitActionLabel('Mulai Proses Impor')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Pilih Berkas Excel (.xlsx) / CSV')
                        ->disk('local')
                        ->directory('temp_imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv',
                            'text/plain',
                            'application/csv',
                        ])
                        ->required()
                        ->helperText('Format yang didukung: .xlsx atau .csv. Maksimal ukuran berkas 20 MB.'),

                    TextInput::make('academic_year')
                        ->label('Tahun Ajaran Rombel')
                        ->default('2026/2027')
                        ->required()
                        ->helperText('Tahun ajaran untuk sinkronisasi data statistik rombel siswa.'),

                    TextInput::make('default_password')
                        ->label('Password Default Akun Siswa Baru')
                        ->default('password123')
                        ->required()
                        ->helperText('Password default bagi siswa yang baru pertama kali didaftarkan akunnya.'),
                ])
                ->action(function (array $data, StudentImportService $importService): void {
                    $relativePath = $data['attachment'] ?? null;
                    if (! $relativePath || ! Storage::disk('local')->exists($relativePath)) {
                        Notification::make()
                            ->title('Gagal Membaca Berkas')
                            ->body('Berkas yang diunggah tidak ditemukan pada server.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $fullPath = Storage::disk('local')->path($relativePath);
                    $academicYear = $data['academic_year'] ?? '2026/2027';
                    $defaultPassword = $data['default_password'] ?? 'password123';

                    $result = $importService->import($fullPath, $academicYear, $defaultPassword);

                    Storage::disk('local')->delete($relativePath);

                    if ($result['success']) {
                        /** @var User|null $actor */
                        $actor = Auth::user();
                        $actorName = $actor?->name ?? 'Operator';

                        ActivityLog::record(
                            action: 'import_students',
                            description: "{$actorName} mengimpor {$result['imported']} akun siswa baru dan memperbarui {$result['updated']} data siswa di {$result['classes_count']} rombel.",
                            reason: "Impor massal berkas Excel/CSV untuk Tahun Ajaran {$academicYear}",
                            properties: [
                                'academic_year' => $academicYear,
                                'imported' => $result['imported'],
                                'updated' => $result['updated'],
                                'classes_count' => $result['classes_count'],
                            ],
                            module: 'Kesiswaan'
                        );

                        $msg = "Berhasil mengimpor {$result['imported']} akun siswa baru dan memperbarui {$result['updated']} siswa di {$result['classes_count']} rombel.";
                        if (! empty($result['errors'])) {
                            $msg .= ' Catatan: ' . implode(', ', array_slice($result['errors'], 0, 3));
                        }

                        Notification::make()
                            ->title('Impor Siswa Berhasil')
                            ->body($msg)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Impor Siswa Gagal')
                            ->body(implode(', ', $result['errors']))
                            ->danger()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Tambah Siswa Baru')
                ->icon('heroicon-o-user-plus'),

            Action::make('syncStatistics')
                ->label('Sinkronkan Rekap Rombel')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (): bool => $this->canManageStudents())
                ->action(function (): void {
                    $synced = User::syncAllRombelStatistics('2026/2027');

                    Notification::make()
                        ->title("Berhasil menyinkronkan statistik {$synced} rombel dari data siswa aktif")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Siswa')
                ->badge(fn (): int => User::whereNotNull('nisn')->where('student_class', 'not like', 'Kelas %')->count()),

            'grade_7' => Tab::make('Kelas VII (7A-7G)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('student_class', 'like', '7%'))
                ->badge(fn (): int => User::where('student_class', 'like', '7%')->count()),

            'grade_8' => Tab::make('Kelas VIII (8A-8G)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('student_class', 'like', '8%'))
                ->badge(fn (): int => User::where('student_class', 'like', '8%')->count()),

            'grade_9' => Tab::make('Kelas IX (9A-9F)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('student_class', 'like', '9%'))
                ->badge(fn (): int => User::where('student_class', 'like', '9%')->count()),

            'male' => Tab::make('Putra (L)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', 'L'))
                ->badge(fn (): int => User::whereNotNull('nisn')->where('student_class', 'not like', 'Kelas %')->where('gender', 'L')->count()),

            'female' => Tab::make('Putri (P)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', 'P'))
                ->badge(fn (): int => User::whereNotNull('nisn')->where('student_class', 'not like', 'Kelas %')->where('gender', 'P')->count()),

            'alumni' => Tab::make('Arsip Alumni (Lulus)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'alumni'))
                ->badge(fn (): int => User::where('status', 'alumni')->count()),

            'mutasi' => Tab::make('Arsip Mutasi Keluar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'mutasi_keluar'))
                ->badge(fn (): int => User::where('status', 'mutasi_keluar')->count()),
        ];
    }
}
