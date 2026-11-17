<?php

namespace App\Filament\Resources\QuestionBanks\Pages;

use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use App\Models\ActivityLog;
use App\Models\QuestionBank;
use App\Models\User;
use App\Services\QuestionImportService;
use App\Support\SubjectPermissionFormHelper;
use App\Support\TemplateImportUiHelper;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property QuestionBank $record
 */
class EditQuestionBank extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SubjectPermissionFormHelper::validateAndNormalizePayload(
            TemplateImportUiHelper::stripTransientKeys($data),
            isCreate: false
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importQuestions')
                ->label('Import Soal dari Template')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->modalHeading('Impor Butir Soal ke Paket Ini')
                ->modalDescription('Unggah berkas Excel (.xlsx), CSV, Word (.docx), TXT (Aiken/bernomor), atau JSON dari template manapun untuk menambahkan atau memperbarui butir soal.')
                ->modalSubmitActionLabel('Impor Sekarang')
                ->form([
                    Toggle::make('replace_existing')
                        ->label('Gantikan seluruh butir soal yang ada')
                        ->helperText('Jika dimatikan, butir soal baru dari file akan ditambahkan setelah soal yang sudah ada.')
                        ->default(false),
                    ...TemplateImportUiHelper::questionUploadFields('template_file', required: true, dehydratedFile: true),
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

                    $fullPath = Storage::disk('local')->path($relativePath);
                    $overrides = TemplateImportUiHelper::extractColumnOverrides($data);
                    $replace = (bool) ($data['replace_existing'] ?? false);

                    $result = $importService->importIntoBank(
                        bank: $this->record,
                        file: $fullPath,
                        replaceExisting: $replace,
                        columnOverrides: $overrides,
                    );

                    Storage::disk('local')->delete($relativePath);

                    if ($result['success']) {
                        /** @var User|null $actor */
                        $actor = Auth::user();
                        ActivityLog::record(
                            action: 'import_questions',
                            description: ($actor?->name ?? 'Guru') . " mengimpor {$result['imported_questions']} butir soal ke paket \"{$this->record->title}\".",
                            reason: ($replace ? 'Menggantikan' : 'Menambahkan') . " soal via {$result['format_label']}",
                            properties: [
                                'question_bank_id' => $this->record->id,
                                'imported_questions' => $result['imported_questions'],
                                'replace_existing' => $replace,
                            ],
                            module: 'Akademik'
                        );

                        $this->record->refresh();
                        $this->fillForm();

                        $msg = "Berhasil mengimpor {$result['imported_questions']} butir soal ({$result['format_label']}).";
                        if ($result['skipped_count'] > 0) {
                            $msg .= " {$result['skipped_count']} baris dilewati.";
                        }

                        Notification::make()
                            ->title('Impor Butir Soal Berhasil!')
                            ->body($msg)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Impor Soal Gagal')
                            ->body(implode(' ', $result['errors'] ?? ['Periksa kembali isi berkas template Anda.']))
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('exportBank')
                ->label('Export ke Excel (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $xlsxContent = QuestionImportService::exportBankToXlsx($this->record);
                    $slug = Str::slug((string) $this->record->title) ?: 'bank-soal';

                    return response()->streamDownload(function () use ($xlsxContent): void {
                        echo $xlsxContent;
                    }, "soal_{$slug}.xlsx", [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),

            DeleteAction::make(),
        ];
    }
}
