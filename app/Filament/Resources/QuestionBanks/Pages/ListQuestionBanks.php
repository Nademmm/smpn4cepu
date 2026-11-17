<?php

namespace App\Filament\Resources\QuestionBanks\Pages;

use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\QuestionImportService;
use App\Support\SubjectPermissionFormHelper;
use App\Support\TemplateImportUiHelper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListQuestionBanks extends ListRecords
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('downloadTemplateXlsx')
                    ->label('Template Excel (.xlsx)')
                    ->icon('heroicon-o-table-cells')
                    ->action(function (): StreamedResponse {
                        $content = QuestionImportService::getTemplateXlsx();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_bank_soal_smpn4cepu.xlsx', [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]);
                    }),

                Action::make('downloadTemplateCsv')
                    ->label('Template CSV (.csv)')
                    ->icon('heroicon-o-document-text')
                    ->action(function (): StreamedResponse {
                        $content = QuestionImportService::getTemplateCsv();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_bank_soal_smpn4cepu.csv', [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                        ]);
                    }),

                Action::make('downloadTemplateDocx')
                    ->label('Template Word (.docx)')
                    ->icon('heroicon-o-document')
                    ->action(function (): StreamedResponse {
                        $content = QuestionImportService::getTemplateDocx();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_bank_soal_smpn4cepu.docx', [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ]);
                    }),

                Action::make('downloadTemplateTxt')
                    ->label('Template Teks Aiken (.txt)')
                    ->icon('heroicon-o-code-bracket')
                    ->action(function (): StreamedResponse {
                        $content = QuestionImportService::getTemplateTxt();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_bank_soal_aiken_smpn4cepu.txt', [
                            'Content-Type' => 'text/plain; charset=UTF-8',
                        ]);
                    }),
            ])
                ->label('Unduh Template Soal')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),

            Action::make('importFromTemplate')
                ->label('Import Soal dari Template')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->modalHeading('Import Paket & Butir Soal dari Template Apapun')
                ->modalDescription('Gunakan template resmi portal maupun template soal pribadi guru (Excel, CSV, Word .docx, TXT Aiken, atau JSON).')
                ->modalSubmitActionLabel('Buat Paket & Impor Soal')
                ->modalWidth('3xl')
                ->form([
                    Grid::make(2)
                        ->schema([
                            SubjectPermissionFormHelper::subjectField(useRelationship: false),

                            SubjectPermissionFormHelper::gradeLevelField(
                                'Tingkat Angkatan Kelas',
                                'Otomatis disesuaikan dengan izin mapel guru.'
                            ),

                            SubjectPermissionFormHelper::targetClassesField()
                                ->columnSpanFull(),

                            TextInput::make('title')
                                ->label('Judul Paket Ujian / Latihan Soal')
                                ->required()
                                ->placeholder('Contoh: Penilaian Harian Bab 1 - Bilangan Bulat')
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Deskripsi / Kisi-Kisi Singkat (Opsional)')
                                ->rows(2)
                                ->columnSpanFull(),

                            TextInput::make('duration_minutes')
                                ->label('Durasi (Menit)')
                                ->required()
                                ->numeric()
                                ->minValue(5)
                                ->maxValue(240)
                                ->default(30),

                            TextInput::make('passing_grade')
                                ->label('KKM (Poin)')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default(75),

                            TextInput::make('access_code')
                                ->label('Password / Token Masuk Soal')
                                ->required()
                                ->maxLength(50)
                                ->default(fn () => 'SOAL-' . Str::upper(Str::random(4))),

                            TextInput::make('max_attempts')
                                ->label('Maksimal Percobaan (0 = Bebas)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            Toggle::make('is_active')
                                ->label('Aktifkan Langsung di Portal Siswa')
                                ->default(true),

                            Toggle::make('allow_retry')
                                ->label('Izinkan Ulangi Latihan')
                                ->default(true),
                        ]),

                    ...TemplateImportUiHelper::questionUploadFields(
                        fileField: 'template_file',
                        required: true,
                        dehydratedFile: true,
                        autoFillTitle: true
                    ),
                ])
                ->action(function (array $data, QuestionImportService $importService): void {
                    $relativePath = $data['template_file'] ?? null;
                    if (! $relativePath || ! Storage::disk('local')->exists($relativePath)) {
                        Notification::make()
                            ->title('Berkas Tidak Ditemukan')
                            ->body('Berkas template gagal dibaca di server.')
                            ->danger()
                            ->send();

                        return;
                    }

                    /** @var User|null $actor */
                    $actor = Auth::user();
                    if (! $actor instanceof User) {
                        return;
                    }

                    $fullPath = Storage::disk('local')->path($relativePath);
                    $overrides = TemplateImportUiHelper::extractColumnOverrides($data);
                    $bankData = TemplateImportUiHelper::stripTransientKeys($data);

                    try {
                        $result = $importService->createBankAndImport(
                            bankData: $bankData,
                            file: $fullPath,
                            actor: $actor,
                            columnOverrides: $overrides,
                        );
                    } finally {
                        Storage::disk('local')->delete($relativePath);
                    }

                    if ($result['success']) {
                        $bank = $result['bank'];

                        ActivityLog::record(
                            action: 'import_question_bank',
                            description: "{$actor->name} membuat paket soal \"{$bank->title}\" berisi {$result['imported_questions']} butir soal dari {$result['format_label']}.",
                            reason: 'Impor paket soal baru dari template guru',
                            properties: [
                                'question_bank_id' => $bank->id,
                                'imported_questions' => $result['imported_questions'],
                                'format' => $result['format_label'],
                            ],
                            module: 'Akademik'
                        );

                        $msg = "Paket \"{$bank->title}\" berhasil dibuat dengan {$result['imported_questions']} butir soal ({$result['format_label']}).";
                        if ($result['skipped_count'] > 0) {
                            $msg .= " {$result['skipped_count']} baris dilewati.";
                        }

                        Notification::make()
                            ->title('Import Soal Berhasil!')
                            ->body($msg)
                            ->success()
                            ->duration(6000)
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import Soal Gagal')
                            ->body(implode(' ', $result['errors'] ?? ['Periksa kembali format template Anda.']))
                            ->danger()
                            ->send();
                    }
                }),

            CreateAction::make(),
        ];
    }
}
