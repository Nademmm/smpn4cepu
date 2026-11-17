<?php

namespace App\Filament\Resources\LearningMaterials\Pages;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\LearningMaterialImportService;
use App\Support\SubjectPermissionFormHelper;
use App\Support\TemplateImportUiHelper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListLearningMaterials extends ListRecords
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('downloadTemplateXlsx')
                    ->label('Template Excel (.xlsx)')
                    ->icon('heroicon-o-table-cells')
                    ->action(function (): StreamedResponse {
                        $content = LearningMaterialImportService::getTemplateXlsx();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_materi_smpn4cepu.xlsx', [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]);
                    }),

                Action::make('downloadTemplateCsv')
                    ->label('Template CSV (.csv)')
                    ->icon('heroicon-o-document-text')
                    ->action(function (): StreamedResponse {
                        $content = LearningMaterialImportService::getTemplateCsv();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_materi_smpn4cepu.csv', [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                        ]);
                    }),

                Action::make('downloadTemplateDocx')
                    ->label('Template Word (.docx)')
                    ->icon('heroicon-o-document')
                    ->action(function (): StreamedResponse {
                        $content = LearningMaterialImportService::getTemplateDocx();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_materi_smpn4cepu.docx', [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ]);
                    }),

                Action::make('downloadTemplateTxt')
                    ->label('Template Teks (.txt)')
                    ->icon('heroicon-o-code-bracket')
                    ->action(function (): StreamedResponse {
                        $content = LearningMaterialImportService::getTemplateTxt();

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'template_materi_smpn4cepu.txt', [
                            'Content-Type' => 'text/plain; charset=UTF-8',
                        ]);
                    }),
            ])
                ->label('Unduh Template Materi')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),

            Action::make('importMaterialsFromTemplate')
                ->label('Import Materi dari Template')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->modalHeading('Import Materi Pembelajaran dari Template Apapun')
                ->modalDescription('Unggah berkas Excel (.xlsx/.csv), Word (.docx/.dotx), TXT/MD, JSON, atau modul PDF/PPTX. Bisa memakai template resmi maupun template pribadi guru.')
                ->modalSubmitActionLabel('Proses & Simpan Materi')
                ->modalWidth('3xl')
                ->form([
                    Grid::make(2)
                        ->schema([
                            SubjectPermissionFormHelper::subjectField(useRelationship: false),

                            SubjectPermissionFormHelper::gradeLevelField('Tingkat Kelas'),

                            SubjectPermissionFormHelper::targetClassesField()
                                ->columnSpanFull(),

                            Toggle::make('is_active')
                                ->label('Publikasikan & Aktifkan Materi')
                                ->default(true),

                            Toggle::make('attach_source_document')
                                ->label('Lampirkan File Dokumen Asli untuk Diunduh Siswa')
                                ->helperText('Berlaku jika mengunggah 1 dokumen modul (.docx, .pdf, .pptx, .txt).')
                                ->default(true),
                        ]),

                    ...TemplateImportUiHelper::materialUploadFields('template_file', required: true, dehydratedFile: true),
                ])
                ->action(function (array $data, LearningMaterialImportService $importService): void {
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
                    $attachSource = (bool) ($data['attach_source_document'] ?? true);
                    $defaults = TemplateImportUiHelper::stripTransientKeys($data, ['attach_source_document']);

                    try {
                        $result = $importService->importBatch(
                            file: $fullPath,
                            defaults: $defaults,
                            actor: $actor,
                            columnOverrides: $overrides,
                            attachSourceDocument: $attachSource,
                        );
                    } finally {
                        Storage::disk('local')->delete($relativePath);
                    }

                    if ($result['success']) {
                        ActivityLog::record(
                            action: 'import_learning_materials',
                            description: "{$actor->name} mengimpor {$result['imported']} materi pembelajaran dari {$result['format_label']}.",
                            reason: 'Impor materi ajar dari template guru',
                            properties: [
                                'imported' => $result['imported'],
                                'skipped' => $result['skipped'],
                                'format' => $result['format_label'],
                            ],
                            module: 'Akademik'
                        );

                        $msg = "Berhasil menambahkan {$result['imported']} materi pembelajaran ({$result['format_label']}).";
                        if ($result['skipped'] > 0) {
                            $msg .= " {$result['skipped']} baris dilewati.";
                        }

                        Notification::make()
                            ->title('Import Materi Berhasil!')
                            ->body($msg)
                            ->success()
                            ->duration(6000)
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import Materi Gagal')
                            ->body(implode(' ', $result['errors'] ?? ['Periksa kembali format berkas atau izin mengajar Anda.']))
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('exportMaterials')
                ->label('Export Excel (.xlsx)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $materials = LearningMaterialResource::getEloquentQuery()->with('subject')->orderBy('id', 'desc')->get();
                    $xlsx = LearningMaterialImportService::exportMaterialsToXlsx($materials);

                    return response()->streamDownload(function () use ($xlsx): void {
                        echo $xlsx;
                    }, 'materi_pembelajaran_smpn4cepu_' . date('Ymd_His') . '.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),

            CreateAction::make(),
        ];
    }
}
