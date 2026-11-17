<?php

namespace App\Support;

use App\Services\LearningMaterialImportService;
use App\Services\QuestionImportService;
use App\Services\Support\FlexibleDocumentReader;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TemplateImportUiHelper
{
    public const MAX_MAPPED_COLUMNS = 16;

    /**
     * Komponen upload template soal + pratinjau langsung + pemetaan kolom fleksibel.
     *
     * @return list<\Filament\Schemas\Components\Component|\Filament\Forms\Components\Field>
     */
    public static function questionUploadFields(
        string $fileField = 'template_file',
        bool $required = true,
        bool $dehydratedFile = true,
        bool $autoFillTitle = false
    ): array {
        return self::buildUploadAndPreviewFields(
            mode: 'question',
            fileField: $fileField,
            required: $required,
            dehydratedFile: $dehydratedFile,
            autoFillTitle: $autoFillTitle,
            label: 'Pilih Berkas Template Soal (Format Apapun)',
            helperText: 'Bebas pakai template sendiri atau template portal: Excel (.xlsx, .xltx, .ods), CSV/TSV, Word (.docx, .dotx tabel atau daftar bernomor), TXT (Aiken/bernomor), maupun JSON. Maksimal 20 MB.',
            fieldOptions: QuestionImportService::FIELD_LABELS,
            fileRule: FlexibleDocumentReader::importFileRule(),
        );
    }

    /**
     * Komponen upload template materi + pratinjau langsung + pemetaan kolom fleksibel.
     *
     * @return list<\Filament\Schemas\Components\Component|\Filament\Forms\Components\Field>
     */
    public static function materialUploadFields(
        string $fileField = 'template_file',
        bool $required = true,
        bool $dehydratedFile = true
    ): array {
        return self::buildUploadAndPreviewFields(
            mode: 'material',
            fileField: $fileField,
            required: $required,
            dehydratedFile: $dehydratedFile,
            autoFillTitle: false,
            label: 'Pilih Berkas Template Materi (Format Apapun)',
            helperText: 'Mendukung Excel (.xlsx, .xltx, .ods), CSV/TSV, Word (.docx, .dotx), TXT/MD/RTF, JSON, serta unggahan langsung PDF atau Presentasi (.pptx/.potx). Maksimal 25 MB.',
            fieldOptions: LearningMaterialImportService::FIELD_LABELS,
            fileRule: FlexibleDocumentReader::materialAttachmentRule(),
        );
    }

    /**
     * Mengekstrak peta override kolom [colIndex => fieldKey] dari data form.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    public static function extractColumnOverrides(array $data): array
    {
        $overrides = [];
        for ($i = 0; $i < self::MAX_MAPPED_COLUMNS; $i++) {
            $key = "col_map_{$i}";
            if (! empty($data[$key]) && is_string($data[$key])) {
                $overrides[$i] = $data[$key];
            }
        }

        return $overrides;
    }

    /**
     * Menghapus seluruh field sementara milik import/preview agar tidak mengganggu mass assignment model.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function stripTransientKeys(array $data, array $extraKeys = []): array
    {
        $keysToRemove = array_merge([
            'template_file',
            'quick_import_file',
            'template_extract_file',
            'detected_headers',
            'preview_html',
        ], $extraKeys);

        for ($i = 0; $i < self::MAX_MAPPED_COLUMNS; $i++) {
            $keysToRemove[] = "col_map_{$i}";
        }

        foreach ($keysToRemove as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $fieldOptions
     * @return list<\Filament\Schemas\Components\Component|\Filament\Forms\Components\Field>
     */
    private static function buildUploadAndPreviewFields(
        string $mode,
        string $fileField,
        bool $required,
        bool $dehydratedFile,
        bool $autoFillTitle,
        string $label,
        string $helperText,
        array $fieldOptions,
        ValidationRule $fileRule
    ): array {
        $refreshPreview = static function (string $mode, mixed $fileState, Get $get, Set $set, bool $applyAutoMap) use ($autoFillTitle): void {
            $resolved = FlexibleDocumentReader::resolveUploadedFile($fileState, 'local');
            if (! $resolved) {
                $set('detected_headers', []);
                $set('preview_html', '');
                for ($i = 0; $i < self::MAX_MAPPED_COLUMNS; $i++) {
                    $set("col_map_{$i}", null);
                }

                return;
            }

            $overrides = [];
            if (! $applyAutoMap) {
                for ($i = 0; $i < self::MAX_MAPPED_COLUMNS; $i++) {
                    $val = $get("col_map_{$i}");
                    if (is_string($val) && $val !== '') {
                        $overrides[$i] = $val;
                    }
                }
            }

            $preview = $mode === 'question'
                ? app(QuestionImportService::class)->inspect($resolved['path'], $overrides, $resolved['name'])
                : app(LearningMaterialImportService::class)->inspect($resolved['path'], $overrides, $resolved['name']);

            $headers = array_slice($preview['detected_headers'] ?? [], 0, self::MAX_MAPPED_COLUMNS);
            $set('detected_headers', $headers);

            if ($applyAutoMap) {
                $colMap = $preview['column_map'] ?? [];
                for ($i = 0; $i < self::MAX_MAPPED_COLUMNS; $i++) {
                    $set("col_map_{$i}", $colMap[$i] ?? ($i < count($headers) ? 'ignore' : null));
                }
            }

            if ($autoFillTitle && trim((string) $get('title')) === '') {
                $base = pathinfo($resolved['name'], PATHINFO_FILENAME);
                $derivedTitle = trim((string) Str::of($base)->replace(['_', '-'], ' ')->squish()->title());
                if ($derivedTitle !== '') {
                    $set('title', $derivedTitle);
                }
            }

            $set('preview_html', self::renderPreviewHtml($mode, $preview));
        };

        $mappingSelects = [];
        for ($i = 0; $i < self::MAX_MAPPED_COLUMNS; $i++) {
            $mappingSelects[] = Select::make("col_map_{$i}")
                ->label(function (Get $get) use ($i): string {
                    $headers = $get('detected_headers') ?? [];
                    $colTitle = trim((string) ($headers[$i] ?? ''));
                    $colLabel = $colTitle !== '' ? $colTitle : 'Kolom ' . ($i + 1);

                    return 'Kolom "' . Str::limit($colLabel, 32) . '"';
                })
                ->options($fieldOptions)
                ->visible(fn (Get $get): bool => is_array($get('detected_headers')) && $i < count($get('detected_headers')))
                ->live()
                ->dehydrated($dehydratedFile)
                ->afterStateUpdated(function (Get $get, Set $set) use ($mode, $fileField, $refreshPreview): void {
                    $refreshPreview($mode, $get($fileField), $get, $set, false);
                });
        }

        return [
            FileUpload::make($fileField)
                ->label($label)
                ->disk('local')
                ->directory('temp_imports')
                ->maxSize(25600)
                ->required($required)
                ->dehydrated($dehydratedFile)
                ->rules([$fileRule])
                ->live()
                ->afterStateUpdated(function ($state, Get $get, Set $set) use ($mode, $refreshPreview): void {
                    $refreshPreview($mode, $state, $get, $set, true);
                })
                ->helperText($helperText)
                ->columnSpanFull(),

            Hidden::make('detected_headers')
                ->default([])
                ->dehydrated(false),

            Hidden::make('preview_html')
                ->default('')
                ->dehydrated(false),

            Html::make(function (Get $get): HtmlString {
                $html = (string) ($get('preview_html') ?? '');

                return new HtmlString($html);
            })
                ->visible(fn (Get $get): bool => trim((string) ($get('preview_html') ?? '')) !== '')
                ->columnSpanFull(),

            Section::make('Sesuaikan Pemetaan Kolom (Opsional)')
                ->description('Sistem sudah membaca kolom template Anda secara otomatis. Jika format guru berbeda dan ada kolom yang belum pas, sesuaikan di sini lalu pratinjau akan langsung terbarui.')
                ->collapsed()
                ->visible(fn (Get $get): bool => is_array($get('detected_headers')) && count($get('detected_headers')) > 0)
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema($mappingSelects),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $preview
     */
    private static function renderPreviewHtml(string $mode, array $preview): string
    {
        $format = e((string) ($preview['format_label'] ?? 'Berkas'));
        $count = (int) ($preview['total_found'] ?? 0);
        $skipped = (int) ($preview['skipped_count'] ?? 0);
        $warnings = $preview['warnings'] ?? [];
        $errors = $preview['errors'] ?? [];

        if (! ($preview['success'] ?? false) || $count === 0) {
            $errMsg = e((string) ($errors[0] ?? 'Belum ada data valid yang terbaca dari berkas ini.'));

            return '<div style="padding:12px 14px;border-radius:10px;border:1px solid #fca5a5;background:#fef2f2;color:#991b1b;font-size:13px;">'
                . '<strong>Gagal Membaca Template (' . $format . '):</strong> ' . $errMsg
                . '</div>';
        }

        $unit = $mode === 'question' ? 'butir soal' : 'materi pembelajaran';
        $samplesHtml = '';

        if ($mode === 'question') {
            foreach (array_slice($preview['questions'] ?? [], 0, 2) as $idx => $q) {
                $qText = e(Str::limit((string) ($q['question_text'] ?? ''), 120));
                $optCount = count($q['options'] ?? []);
                $correctText = '';
                foreach ($q['options'] ?? [] as $o) {
                    if (! empty($o['is_correct'])) {
                        $correctText = e($o['option_label'] . '. ' . Str::limit((string) $o['option_text'], 45));
                        break;
                    }
                }
                $samplesHtml .= '<li style="margin-top:4px;"><strong>#' . ($idx + 1) . ':</strong> ' . $qText
                    . ' <span style="color:#475569;">(' . $optCount . ' opsi, Kunci: <strong>' . $correctText . '</strong>)</span></li>';
            }
        } else {
            foreach (array_slice($preview['materials'] ?? [], 0, 2) as $idx => $m) {
                $mTitle = e(Str::limit((string) ($m['title'] ?? ''), 100));
                $samplesHtml .= '<li style="margin-top:4px;"><strong>#' . ($idx + 1) . ':</strong> ' . $mTitle . '</li>';
            }
        }

        $warningHtml = '';
        if ($skipped > 0 || ! empty($warnings)) {
            $warnPreview = e(implode(' | ', array_slice($warnings, 0, 2)));
            $warningHtml = '<div style="margin-top:8px;padding:8px 10px;border-radius:8px;background:#fffbeb;border:1px solid #fcd34d;color:#92400e;font-size:12px;">'
                . 'Catatan: ' . $skipped . ' baris dilewati. ' . $warnPreview
                . '</div>';
        }

        return '<div style="padding:12px 14px;border-radius:10px;border:1px solid #86efac;background:#f0fdf4;color:#14532d;font-size:13px;">'
            . '<div><strong>Siap Diimpor (' . $format . '):</strong> Ditemukan <strong>' . $count . ' ' . $unit . '</strong>.</div>'
            . '<ul style="margin:6px 0 0 16px;padding:0;list-style:disc;font-size:12px;">' . $samplesHtml . '</ul>'
            . $warningHtml
            . '</div>';
    }
}
