<?php

namespace App\Filament\Resources\QuestionBanks\Pages;

use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use App\Models\ActivityLog;
use App\Models\QuestionBank;
use App\Models\User;
use App\Services\QuestionImportService;
use App\Services\Support\FlexibleDocumentReader;
use App\Support\SubjectPermissionFormHelper;
use App\Support\TemplateImportUiHelper;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * @property QuestionBank $record
 */
class CreateQuestionBank extends CreateRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected ?string $pendingQuickImportFile = null;

    /** @var array<int, string> */
    protected array $pendingColumnOverrides = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $resolved = FlexibleDocumentReader::resolveUploadedFile($data['quick_import_file'] ?? null, 'local');
        $this->pendingQuickImportFile = $resolved['path'] ?? null;
        $this->pendingColumnOverrides = TemplateImportUiHelper::extractColumnOverrides($data);

        $data = TemplateImportUiHelper::stripTransientKeys($data);

        return SubjectPermissionFormHelper::validateAndNormalizePayload($data, isCreate: true);
    }

    protected function afterCreate(): void
    {
        if (! $this->pendingQuickImportFile) {
            return;
        }

        $relativePath = null;
        if (str_starts_with($this->pendingQuickImportFile, Storage::disk('local')->path(''))) {
            $relativePath = ltrim(str_replace(Storage::disk('local')->path(''), '', $this->pendingQuickImportFile), '\\/');
        }

        $result = app(QuestionImportService::class)->importIntoBank(
            bank: $this->record,
            file: $this->pendingQuickImportFile,
            replaceExisting: false,
            columnOverrides: $this->pendingColumnOverrides,
        );

        if ($relativePath && Storage::disk('local')->exists($relativePath)) {
            Storage::disk('local')->delete($relativePath);
        }

        $this->pendingQuickImportFile = null;
        $this->pendingColumnOverrides = [];

        if ($result['success']) {
            ActivityLog::record(
                action: 'import_questions',
                description: (Auth::user()?->name ?? 'Guru') . " mengimpor {$result['imported_questions']} butir soal ke paket \"{$this->record->title}\".",
                reason: "Impor soal cepat via {$result['format_label']}",
                properties: [
                    'question_bank_id' => $this->record->id,
                    'imported_questions' => $result['imported_questions'],
                    'format' => $result['format_label'],
                ],
                module: 'Akademik'
            );

            Notification::make()
                ->title('Butir Soal Berhasil Diimpor!')
                ->body("Berhasil menambahkan {$result['imported_questions']} butir soal ({$result['format_label']}) ke paket ujian ini.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Paket Disimpan, Namun Berkas Soal Belum Terbaca')
                ->body(implode(' ', $result['errors'] ?? ['Periksa kembali isi berkas template soal lalu gunakan tombol Import / Tambah Soal di halaman Edit.']))
                ->warning()
                ->send();
        }
    }
}

