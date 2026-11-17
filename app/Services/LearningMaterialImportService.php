<?php

namespace App\Services;

use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use App\Services\Support\FlexibleDocumentReader;
use App\Support\SubjectPermissionFormHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearningMaterialImportService
{
    public const FIELD_LABELS = [
        'ignore' => '-- Abaikan Kolom Ini --',
        'title' => 'Judul Materi *',
        'summary' => 'Ringkasan Singkat',
        'content' => 'Isi Lengkap / Uraian Materi',
        'external_url' => 'Tautan Video / Drive / Referensi',
        'subject' => 'Mata Pelajaran (Opsional)',
        'grade_level' => 'Tingkat Kelas (7/8/9, Opsional)',
        'target_classes' => 'Rombel Target (mis. 7A,7B, Opsional)',
        'is_active' => 'Status Aktif (Ya/Tidak)',
    ];

    public function __construct(
        protected FlexibleDocumentReader $reader = new FlexibleDocumentReader()
    ) {}

    /**
     * Memeriksa dan mem-parsing template materi dari format apapun.
     *
     * @param  array<int|string, string>  $columnOverrides
     * @return array<string, mixed>
     */
    public function inspect(string|UploadedFile $file, array $columnOverrides = [], ?string $originalName = null): array
    {
        $filePath = $file instanceof UploadedFile ? (string) $file->getRealPath() : $file;
        $name = $originalName ?: ($file instanceof UploadedFile ? $file->getClientOriginalName() : basename($filePath));

        if (! is_file($filePath) || filesize($filePath) === 0) {
            return $this->emptyResult('Berkas tidak ditemukan atau kosong di server.');
        }

        $kind = $this->reader->detectKind($filePath, $name);

        if ($kind === 'ole2_legacy') {
            return $this->emptyResult(
                'Berkas biner Office lama (.xls / .doc) belum mendukung ekstraksi otomatis. Simpan sebagai .xlsx / .docx / .csv, atau unggah langsung pada kolom Lampiran Modul di form Buat Materi.'
            );
        }

        // Berkas dokumen langsung (PDF / Presentasi PPTX / POTX / ODP) -> 1 modul siap lampir
        if (in_array($kind, ['pdf', 'pptx', 'potx', 'ppt', 'odp'], true)) {
            $title = $this->titleFromFilename($name);

            return [
                'success' => true,
                'format_label' => 'Berkas Lampiran Modul (' . strtoupper($kind) . ')',
                'is_tabular' => false,
                'is_single_document' => true,
                'detected_headers' => [],
                'column_map' => [],
                'sample_rows' => [],
                'materials' => [[
                    'title' => $title,
                    'summary' => "Modul pembelajaran digital: {$title}.",
                    'content' => "<p>Silakan unduh dan pelajari berkas modul <strong>" . e($title) . '</strong> pada tombol Unduh Modul di bawah ini.</p>',
                    'external_url' => null,
                    'subject' => null,
                    'grade_level' => null,
                    'target_classes' => null,
                    'is_active' => true,
                ]],
                'total_found' => 1,
                'skipped_count' => 0,
                'warnings' => [],
                'errors' => [],
            ];
        }

        if ($kind === 'json') {
            return $this->parseJsonMaterials($filePath);
        }

        // Cek format tabular (.xlsx/.xltx/.ods/.csv/.tsv/HTML table/Word Table)
        $sheets = $this->reader->extractTabularSheets($filePath, $name);
        if (! empty($sheets)) {
            $tabular = $this->parseTabularSheets($sheets, $kind, $columnOverrides);
            if ($tabular['total_found'] > 0 || ! in_array($kind, ['docx', 'odt', 'txt', 'md', 'rtf'], true)) {
                return $tabular;
            }
        }

        // Cek paragraf Word (.docx/.dotx/.odt) atau teks terstruktur (.txt/.md/.rtf)
        $lines = $this->reader->extractWordOrTextLines($filePath, $name);
        if (! empty($lines)) {
            return $this->parseDocumentLines($lines, $kind, $name);
        }

        return $this->emptyResult('Format berkas materi tidak dapat dibaca.');
    }

    /**
     * Mengimpor 1 atau banyak materi dari template dengan validasi izin guru per baris.
     *
     * @param  array<string, mixed>  $defaults
     * @param  array<int|string, string>  $columnOverrides
     * @return array<string, mixed>
     */
    public function importBatch(
        string|UploadedFile $file,
        array $defaults,
        User $actor,
        array $columnOverrides = [],
        bool $attachSourceDocument = true,
        ?string $originalName = null
    ): array {
        $filePath = $file instanceof UploadedFile ? (string) $file->getRealPath() : $file;
        $name = $originalName ?: ($file instanceof UploadedFile ? $file->getClientOriginalName() : basename($filePath));

        $preview = $this->inspect($filePath, $columnOverrides, $name);
        if (empty($preview['materials'])) {
            return [
                'success' => false,
                'imported' => 0,
                'skipped' => $preview['skipped_count'],
                'created_ids' => [],
                'format_label' => $preview['format_label'],
                'warnings' => $preview['warnings'],
                'errors' => ! empty($preview['errors']) ? $preview['errors'] : ['Tidak ada materi valid yang berhasil dibaca dari template.'],
            ];
        }

        $baseNormalized = SubjectPermissionFormHelper::validateAndNormalizePayload($defaults, isCreate: true, actor: $actor);
        $isRestrictedGuru = $actor->hasRole('guru') && ! $actor->hasAnyRole(['super_admin', 'operator_sekolah']);

        $storedAttachmentPath = null;
        if ($attachSourceDocument && ! empty($preview['is_single_document']) && is_file($filePath)) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION)) ?: 'docx';
            $safeFilename = Str::slug(pathinfo($name, PATHINFO_FILENAME)) ?: 'modul-ajar';
            $targetRelative = 'materials/' . $safeFilename . '-' . Str::lower(Str::random(6)) . '.' . $ext;
            Storage::disk('public')->put($targetRelative, (string) file_get_contents($filePath));
            $storedAttachmentPath = $targetRelative;
        }

        $imported = 0;
        $skipped = (int) ($preview['skipped_count'] ?? 0);
        $warnings = $preview['warnings'] ?? [];
        $createdIds = [];

        try {
            DB::transaction(function () use (
                $preview,
                $baseNormalized,
                $defaults,
                $actor,
                $isRestrictedGuru,
                $storedAttachmentPath,
                &$imported,
                &$skipped,
                &$warnings,
                &$createdIds
            ): void {
                foreach ($preview['materials'] as $idx => $item) {
                    $rowNum = $idx + 1;
                    $title = trim((string) ($item['title'] ?? ''));
                    if ($title === '') {
                        $skipped++;
                        continue;
                    }

                    $subjectId = $this->resolveSubjectId($item['subject'] ?? null, (int) $baseNormalized['subject_id'], $actor, $isRestrictedGuru);
                    $gradeLevel = $this->resolveGradeLevel($item['grade_level'] ?? null, (int) $baseNormalized['grade_level']);
                    $targetClasses = $this->resolveTargetClasses($item['target_classes'] ?? null, $baseNormalized['target_classes'] ?? null);

                    if ($isRestrictedGuru && ! $actor->canStoreSubjectContent($subjectId, $gradeLevel, $targetClasses)) {
                        $skipped++;
                        $warnings[] = "Baris #{$rowNum} ({$title}): dilewati karena mapel/tingkat/rombel di luar izin mengajar Anda.";
                        continue;
                    }

                    $slug = $this->generateUniqueSlug($title);
                    $summary = trim((string) ($item['summary'] ?? ''));
                    if ($summary === '') {
                        $summary = Str::limit(strip_tags((string) ($item['content'] ?? '')), 180) ?: "Materi pembelajaran: {$title}.";
                    }

                    $content = $item['content'] ?? null;
                    $externalUrl = $this->normalizeUrl($item['external_url'] ?? null);
                    $isActive = isset($item['is_active']) ? (bool) $item['is_active'] : (bool) ($defaults['is_active'] ?? true);

                    $material = LearningMaterial::create([
                        'subject_id' => $subjectId,
                        'grade_level' => $gradeLevel,
                        'target_classes' => $targetClasses,
                        'title' => $title,
                        'slug' => $slug,
                        'summary' => $summary,
                        'content' => $content,
                        'attachment_path' => $storedAttachmentPath,
                        'external_url' => $externalUrl,
                        'created_by' => $actor->id,
                        'is_active' => $isActive,
                    ]);

                    $createdIds[] = $material->id;
                    $imported++;
                }
            });
        } catch (\Throwable $e) {
            if ($storedAttachmentPath) {
                Storage::disk('public')->delete($storedAttachmentPath);
            }

            return [
                'success' => false,
                'imported' => 0,
                'skipped' => count($preview['materials']),
                'created_ids' => [],
                'format_label' => $preview['format_label'],
                'warnings' => $warnings,
                'errors' => ['Gagal menyimpan materi ke database: ' . $e->getMessage()],
            ];
        }

        if ($imported === 0 && $storedAttachmentPath) {
            Storage::disk('public')->delete($storedAttachmentPath);
        }

        Cache::forget('public_active_materials_v1');

        return [
            'success' => $imported > 0,
            'imported' => $imported,
            'skipped' => $skipped,
            'created_ids' => $createdIds,
            'format_label' => $preview['format_label'],
            'warnings' => array_slice($warnings, 0, 15),
            'errors' => $imported === 0 ? (! empty($warnings) ? [$warnings[0]] : ['Tidak ada baris materi yang memenuhi izin atau format valid.']) : [],
        ];
    }

    /**
     * Mengekstrak 1 draft field form (title, slug, summary, content, external_url) dari file template apapun.
     *
     * @return array<string, mixed>|null
     */
    public function extractSingleFormDraft(string|UploadedFile $file, ?string $originalName = null): ?array
    {
        $preview = $this->inspect($file, [], $originalName);
        if (empty($preview['materials'])) {
            return null;
        }

        $first = $preview['materials'][0];
        $title = trim((string) ($first['title'] ?? ''));

        return [
            'title' => $title,
            'slug' => $this->generateUniqueSlug($title ?: 'materi-ajar'),
            'summary' => (string) ($first['summary'] ?? ''),
            'content' => (string) ($first['content'] ?? ''),
            'external_url' => $this->normalizeUrl($first['external_url'] ?? null),
            'format_label' => $preview['format_label'],
        ];
    }

    /**
     * @param  list<array{name: string, rows: list<list<string>>}>  $sheets
     * @param  array<int|string, string>  $columnOverrides
     */
    private function parseTabularSheets(array $sheets, string $kind, array $columnOverrides): array
    {
        $normalizedOverrides = [];
        foreach ($columnOverrides as $colIdx => $field) {
            if ($field !== '' && $field !== 'ignore' && array_key_exists($field, self::FIELD_LABELS)) {
                $normalizedOverrides[(int) $colIdx] = $field;
            }
        }

        $materials = [];
        $warnings = [];
        $skipped = 0;
        $bestHeaders = [];
        $bestMap = [];
        $bestSampleRows = [];

        $sheetCount = count($sheets);

        foreach ($sheets as $sheet) {
            if ($sheetCount > 1 && FlexibleDocumentReader::isGuideSheetName($sheet['name'])) {
                continue;
            }

            $rows = $sheet['rows'];
            if (empty($rows)) {
                continue;
            }

            $headerInfo = $this->locateHeaderRow($rows, $normalizedOverrides);
            $headerIdx = $headerInfo['header_row'];
            $colMap = $headerInfo['column_map'];
            $headers = $headerInfo['headers'];

            if (empty($colMap) && ! empty($normalizedOverrides)) {
                $headerIdx = -1;
                $colMap = $normalizedOverrides;
                $headers = array_map(fn (int $i): string => 'Kolom ' . ($i + 1), array_keys($rows[0]));
            }

            if (empty($colMap) || ! in_array('title', $colMap, true)) {
                continue;
            }

            if (empty($bestHeaders)) {
                $bestHeaders = $headers;
                $bestMap = $colMap;
            }

            $dataStart = $headerIdx >= 0 ? $headerIdx + 1 : 0;
            $dataRows = array_slice($rows, $dataStart);

            if (empty($bestSampleRows)) {
                $bestSampleRows = array_slice($dataRows, 0, 2);
            }

            foreach ($dataRows as $offset => $row) {
                $displayRow = $dataStart + $offset + 1;
                $mapped = [];
                foreach ($colMap as $colIdx => $field) {
                    if ($field !== 'ignore') {
                        $mapped[$field] = trim((string) ($row[$colIdx] ?? ''));
                    }
                }

                $title = trim((string) ($mapped['title'] ?? ''));
                if ($title === '') {
                    if (! empty(array_filter($row, fn (string $v): bool => $v !== ''))) {
                        $skipped++;
                        $warnings[] = "Sheet {$sheet['name']} Baris {$displayRow}: Judul materi kosong, dilewati.";
                    }
                    continue;
                }

                $rawContent = trim((string) ($mapped['content'] ?? ''));
                $summary = trim((string) ($mapped['summary'] ?? ''));
                if ($summary === '') {
                    $summary = $rawContent !== ''
                        ? Str::limit(strip_tags($rawContent), 200)
                        : "Materi pembelajaran: {$title}.";
                }

                $contentHtml = $rawContent !== '' ? $this->textToSafeHtml($rawContent) : null;

                $materials[] = [
                    'title' => $title,
                    'summary' => $summary,
                    'content' => $contentHtml,
                    'external_url' => $mapped['external_url'] ?? null,
                    'subject' => $mapped['subject'] ?? null,
                    'grade_level' => $mapped['grade_level'] ?? null,
                    'target_classes' => $mapped['target_classes'] ?? null,
                    'is_active' => $this->parseBooleanFlag($mapped['is_active'] ?? null, true),
                ];
            }
        }

        $formatName = match ($kind) {
            'xlsx' => 'Excel (.xlsx) - Daftar Materi',
            'ods' => 'OpenDocument Spreadsheet (.ods)',
            'docx' => 'Word (.docx) - Tabel Materi',
            'odt' => 'OpenDocument (.odt) - Tabel Materi',
            'xml_spreadsheet', 'html_table' => 'Excel Web/XML (.xls)',
            'tsv' => 'TSV (Pemisah Tab)',
            default => 'CSV / Teks Tabular',
        };

        return [
            'success' => ! empty($materials),
            'format_label' => $formatName,
            'is_tabular' => ! empty($bestHeaders),
            'is_single_document' => false,
            'detected_headers' => $bestHeaders,
            'column_map' => $bestMap,
            'sample_rows' => $bestSampleRows,
            'materials' => $materials,
            'total_found' => count($materials),
            'skipped_count' => $skipped,
            'warnings' => array_slice($warnings, 0, 15),
            'errors' => empty($materials) ? ['Kolom judul materi tidak terdeteksi otomatis. Sesuaikan Pemetaan Kolom atau cek header file Anda.'] : [],
        ];
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<int, string>  $overrides
     * @return array{header_row: int, column_map: array<int, string>, headers: array<int, string>}
     */
    private function locateHeaderRow(array $rows, array $overrides): array
    {
        $limit = min(count($rows), 25);
        $bestScore = 0;
        $bestIdx = -1;
        $bestMap = [];
        $bestHeaders = [];

        for ($r = 0; $r < $limit; $r++) {
            $row = $rows[$r];
            $map = [];
            foreach ($row as $colIdx => $cell) {
                $field = $overrides[$colIdx] ?? $this->mapHeaderAlias((string) $cell);
                if ($field !== null && ! in_array($field, $map, true)) {
                    $map[$colIdx] = $field;
                }
            }

            $hasTitle = in_array('title', $map, true);
            $score = count($map) + ($hasTitle ? 10 : 0);

            if ($hasTitle && $score > $bestScore && count($map) >= 1) {
                $bestScore = $score;
                $bestIdx = $r;
                $bestMap = $map;
                $bestHeaders = array_values(array_map(fn ($v, $i): string => trim((string) $v) !== '' ? trim((string) $v) : 'Kolom ' . ($i + 1), $row, array_keys($row)));
            }
        }

        if ($bestIdx >= 0 && ! empty($overrides)) {
            foreach ($overrides as $colIdx => $field) {
                $bestMap[$colIdx] = $field;
            }
        }

        return [
            'header_row' => $bestIdx,
            'column_map' => $bestMap,
            'headers' => $bestHeaders,
        ];
    }

    private function mapHeaderAlias(string $raw): ?string
    {
        if (! FlexibleDocumentReader::isLikelyHeaderCell($raw)) {
            return null;
        }

        $clean = strtolower(trim(preg_replace('/[\s_\-\.\/:()]+/', ' ', $raw) ?? ''));
        if ($clean === '') {
            return null;
        }

        foreach (['judul materi', 'nama materi', 'judul', 'topik', 'bab', 'pokok bahasan', 'title', 'topic', 'module', 'materi'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'title';
            }
        }

        foreach (['ringkasan', 'rangkuman', 'deskripsi', 'summary', 'overview', 'abstract', 'kompetensi', 'tujuan'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'summary';
            }
        }

        foreach (['isi materi', 'isi lengkap', 'panduan belajar', 'uraian', 'konten', 'content', 'body', 'detail', 'isi'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'content';
            }
        }

        foreach (['tautan', 'link', 'url', 'video', 'youtube', 'drive', 'referensi', 'external url'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'external_url';
            }
        }

        foreach (['mata pelajaran', 'mapel', 'subject', 'kode mapel'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'subject';
            }
        }

        foreach (['rombel', 'target kelas', 'kelas target', 'target classes', 'sub kelas'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'target_classes';
            }
        }

        foreach (['tingkat kelas', 'tingkat', 'jenjang', 'angkatan', 'grade', 'kelas'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'grade_level';
            }
        }

        foreach (['status aktif', 'publikasi', 'aktif', 'status', 'is active', 'published'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'is_active';
            }
        }

        return null;
    }

    /**
     * Mem-parsing dokumen Word/Teks: bisa multi-blok terstruktur ("Judul: ...") ATAU 1 dokumen utuh modul ajar.
     *
     * @param  list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>  $lines
     */
    private function parseDocumentLines(array $lines, string $kind, string $originalName): array
    {
        $nonEmpty = array_values(array_filter($lines, fn (array $l): bool => trim($l['text']) !== ''));
        if (empty($nonEmpty)) {
            return $this->emptyResult('Dokumen materi kosong.');
        }

        // Cek apakah menggunakan blok ber-tag multi-materi (Judul: / Materi:)
        $taggedCount = 0;
        foreach ($nonEmpty as $l) {
            if (preg_match('/^(?:judul(?:\s+materi)?|title)\s*[\:\=]\s*.+$/iu', $l['text'])) {
                $taggedCount++;
            }
        }

        if ($taggedCount >= 1) {
            $materials = $this->parseTaggedTextMaterials($nonEmpty);
            if (! empty($materials)) {
                return [
                    'success' => true,
                    'format_label' => 'Dokumen Terstruktur (' . strtoupper($kind) . ')',
                    'is_tabular' => false,
                    'is_single_document' => count($materials) === 1,
                    'detected_headers' => [],
                    'column_map' => [],
                    'sample_rows' => [],
                    'materials' => $materials,
                    'total_found' => count($materials),
                    'skipped_count' => 0,
                    'warnings' => [],
                    'errors' => [],
                ];
            }
        }

        // Mode 1 Dokumen Modul Utuh (Word .docx/.dotx/.odt atau .txt/.md/.rtf)
        $firstLine = $nonEmpty[0];
        $title = '';
        $bodyLines = $nonEmpty;

        if ($firstLine['is_heading'] || $firstLine['is_bold'] || mb_strlen($firstLine['text']) <= 120) {
            $title = trim(ltrim($firstLine['text'], "#* \t"));
            $bodyLines = array_slice($nonEmpty, 1);
        }

        if ($title === '') {
            $title = $this->titleFromFilename($originalName);
        }

        $bodyTexts = array_map(fn (array $l): string => $l['text'], $bodyLines);
        $summarySource = $bodyTexts[0] ?? $title;
        $summary = Str::limit(trim(strip_tags($summarySource)), 220);

        $htmlParts = [];
        foreach ($bodyLines as $line) {
            $clean = trim($line['text']);
            if ($clean === '') {
                continue;
            }
            if ($line['is_heading'] || str_starts_with($clean, '#')) {
                $htmlParts[] = '<h3>' . e(trim(ltrim($clean, '# '))) . '</h3>';
            } else {
                $htmlParts[] = '<p>' . e($clean) . '</p>';
            }
        }

        if (empty($htmlParts)) {
            $htmlParts[] = '<p>' . e($title) . '</p>';
        }

        return [
            'success' => true,
            'format_label' => 'Dokumen Modul (' . strtoupper($kind) . ')',
            'is_tabular' => false,
            'is_single_document' => true,
            'detected_headers' => [],
            'column_map' => [],
            'sample_rows' => [],
            'materials' => [[
                'title' => $title,
                'summary' => $summary,
                'content' => implode("\n", $htmlParts),
                'external_url' => null,
                'subject' => null,
                'grade_level' => null,
                'target_classes' => null,
                'is_active' => true,
            ]],
            'total_found' => 1,
            'skipped_count' => 0,
            'warnings' => [],
            'errors' => [],
        ];
    }

    /**
     * @param  list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>  $lines
     * @return list<array<string, mixed>>
     */
    private function parseTaggedTextMaterials(array $lines): array
    {
        $items = [];
        $current = null;
        $currentField = null;

        foreach ($lines as $l) {
            $text = trim($l['text']);
            if ($text === '' || preg_match('/^[-=_]{3,}$/', $text)) {
                continue;
            }

            if (preg_match('/^(?:judul(?:\s+materi)?|title)\s*[\:\=]\s*(.+)$/iu', $text, $m)) {
                if ($current !== null && trim((string) ($current['title'] ?? '')) !== '') {
                    $items[] = $this->finalizeTaggedMaterial($current);
                }
                $current = ['title' => trim($m[1]), 'summary' => '', 'content_lines' => [], 'external_url' => null, 'grade_level' => null, 'target_classes' => null];
                $currentField = 'title';
                continue;
            }

            if ($current === null) {
                continue;
            }

            if (preg_match('/^(?:ringkasan|rangkuman|summary|deskripsi)\s*[\:\=]\s*(.+)$/iu', $text, $m)) {
                $current['summary'] = trim($m[1]);
                $currentField = 'summary';
            } elseif (preg_match('/^(?:isi(?:\s+materi)?|konten|uraian|content)\s*[\:\=]\s*(.*)$/iu', $text, $m)) {
                if (trim($m[1]) !== '') {
                    $current['content_lines'][] = trim($m[1]);
                }
                $currentField = 'content';
            } elseif (preg_match('/^(?:tautan|link|url|video)\s*[\:\=]\s*(.+)$/iu', $text, $m)) {
                $current['external_url'] = trim($m[1]);
                $currentField = null;
            } elseif (preg_match('/^(?:kelas|tingkat)\s*[\:\=]\s*(.+)$/iu', $text, $m)) {
                $current['grade_level'] = trim($m[1]);
                $currentField = null;
            } elseif (preg_match('/^(?:rombel|target\s+kelas)\s*[\:\=]\s*(.+)$/iu', $text, $m)) {
                $current['target_classes'] = trim($m[1]);
                $currentField = null;
            } elseif ($currentField === 'summary') {
                $current['summary'] .= ' ' . $text;
            } else {
                $current['content_lines'][] = $text;
                $currentField = 'content';
            }
        }

        if ($current !== null && trim((string) ($current['title'] ?? '')) !== '') {
            $items[] = $this->finalizeTaggedMaterial($current);
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function finalizeTaggedMaterial(array $raw): array
    {
        $contentText = implode("\n", $raw['content_lines'] ?? []);
        $summary = trim((string) ($raw['summary'] ?? ''));
        if ($summary === '') {
            $summary = $contentText !== '' ? Str::limit($contentText, 200) : "Materi pembelajaran: {$raw['title']}.";
        }

        return [
            'title' => $raw['title'],
            'summary' => $summary,
            'content' => $contentText !== '' ? $this->textToSafeHtml($contentText) : null,
            'external_url' => $raw['external_url'] ?? null,
            'subject' => null,
            'grade_level' => $raw['grade_level'] ?? null,
            'target_classes' => $raw['target_classes'] ?? null,
            'is_active' => true,
        ];
    }

    private function parseJsonMaterials(string $filePath): array
    {
        $decoded = $this->reader->decodeJson($filePath);
        if (! is_array($decoded)) {
            return $this->emptyResult('Berkas JSON tidak valid.');
        }

        $rows = isset($decoded['materials']) && is_array($decoded['materials'])
            ? $decoded['materials']
            : (array_is_list($decoded) ? $decoded : [$decoded]);

        $materials = [];
        foreach ($rows as $item) {
            if (! is_array($item)) {
                continue;
            }

            $mapped = [];
            foreach ($item as $k => $v) {
                $field = $this->mapHeaderAlias((string) $k) ?? (in_array((string) $k, ['title', 'summary', 'content', 'external_url', 'subject', 'grade_level', 'target_classes', 'is_active'], true) ? (string) $k : null);
                if ($field !== null) {
                    $mapped[$field] = is_array($v) ? implode(', ', $v) : (string) $v;
                }
            }

            $title = trim((string) ($mapped['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $rawContent = trim((string) ($mapped['content'] ?? ''));
            $summary = trim((string) ($mapped['summary'] ?? ''));
            if ($summary === '') {
                $summary = $rawContent !== '' ? Str::limit(strip_tags($rawContent), 200) : "Materi pembelajaran: {$title}.";
            }

            $materials[] = [
                'title' => $title,
                'summary' => $summary,
                'content' => $rawContent !== '' ? $this->textToSafeHtml($rawContent) : null,
                'external_url' => $mapped['external_url'] ?? null,
                'subject' => $mapped['subject'] ?? null,
                'grade_level' => $mapped['grade_level'] ?? null,
                'target_classes' => $mapped['target_classes'] ?? null,
                'is_active' => $this->parseBooleanFlag($mapped['is_active'] ?? null, true),
            ];
        }

        return [
            'success' => ! empty($materials),
            'format_label' => 'JSON Daftar Materi',
            'is_tabular' => false,
            'is_single_document' => false,
            'detected_headers' => [],
            'column_map' => [],
            'sample_rows' => [],
            'materials' => $materials,
            'total_found' => count($materials),
            'skipped_count' => 0,
            'warnings' => [],
            'errors' => empty($materials) ? ['Berkas JSON tidak memuat data materi yang valid.'] : [],
        ];
    }

    private function resolveSubjectId(?string $rawSubject, int $fallbackSubjectId, User $actor, bool $isRestrictedGuru): int
    {
        $clean = trim((string) $rawSubject);
        if ($clean === '') {
            return $fallbackSubjectId;
        }

        $matched = Subject::query()
            ->whereRaw('LOWER(code) = ?', [strtolower($clean)])
            ->orWhereRaw('LOWER(name) = ?', [strtolower($clean)])
            ->first();

        if (! $matched) {
            return $fallbackSubjectId;
        }

        if ($isRestrictedGuru && ! $actor->subjects()->whereKey($matched->id)->exists()) {
            // Kembalikan id match agar canStoreSubjectContent() bisa menolak baris ilegal dengan akurat
            return (int) $matched->id;
        }

        return (int) $matched->id;
    }

    private function resolveGradeLevel(?string $rawGrade, int $fallbackGrade): int
    {
        $clean = strtoupper(trim((string) $rawGrade));
        if ($clean === '') {
            return $fallbackGrade;
        }

        if (preg_match('/\b(7|VII)\b/', $clean) || preg_match('/^7[A-Z]?$/', $clean)) {
            return 7;
        }
        if (preg_match('/\b(8|VIII)\b/', $clean) || preg_match('/^8[A-Z]?$/', $clean)) {
            return 8;
        }
        if (preg_match('/\b(9|IX)\b/', $clean) || preg_match('/^9[A-Z]?$/', $clean)) {
            return 9;
        }

        return $fallbackGrade;
    }

    /**
     * @param  array<int, string>|null  $fallbackClasses
     * @return array<int, string>|null
     */
    private function resolveTargetClasses(?string $rawClasses, ?array $fallbackClasses): ?array
    {
        $clean = trim((string) $rawClasses);
        if ($clean === '') {
            return $fallbackClasses;
        }

        $parts = preg_split('/[\s,;|]+/', strtoupper($clean), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $normalized = array_values(array_unique(array_filter(
            $parts,
            fn (string $p): bool => preg_match('/^[789][A-Z]$/', $p) === 1
        )));

        return ! empty($normalized) ? $normalized : $fallbackClasses;
    }

    private function parseBooleanFlag(?string $raw, bool $default = true): bool
    {
        $clean = strtolower(trim((string) $raw));
        if ($clean === '') {
            return $default;
        }

        if (in_array($clean, ['0', 'false', 'no', 'tidak', 'nonaktif', 'draft'], true)) {
            return false;
        }

        return true;
    }

    private function normalizeUrl(?string $raw): ?string
    {
        $clean = trim((string) $raw);
        if ($clean === '') {
            return null;
        }

        return filter_var($clean, FILTER_VALIDATE_URL) ? $clean : null;
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'materi-ajar';
        $candidate = $base;
        $counter = 2;

        while (LearningMaterial::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }

    private function titleFromFilename(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);

        return trim((string) Str::of($base)->replace(['_', '-'], ' ')->squish()->title()) ?: 'Materi Pembelajaran Baru';
    }

    private function textToSafeHtml(string $text): string
    {
        if (preg_match('/<(p|br|h[1-6]|ul|ol|li|strong|b|em|i)\b/i', $text) === 1) {
            return strip_tags($text, '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4><blockquote>');
        }

        $paragraphs = preg_split("/\r\n|\r|\n/", trim($text)) ?: [];
        $html = [];
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if ($p !== '') {
                $html[] = '<p>' . e($p) . '</p>';
            }
        }

        return implode("\n", $html);
    }

    private function emptyResult(string $message): array
    {
        return [
            'success' => false,
            'format_label' => 'Tidak Dikenali',
            'is_tabular' => false,
            'is_single_document' => false,
            'detected_headers' => [],
            'column_map' => [],
            'sample_rows' => [],
            'materials' => [],
            'total_found' => 0,
            'skipped_count' => 0,
            'warnings' => [],
            'errors' => [$message],
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string>>}
     */
    private static function sampleTemplateData(): array
    {
        $headers = [
            'Judul Materi',
            'Ringkasan Singkat',
            'Isi Lengkap Materi',
            'Tautan Video / Drive (Opsional)',
            'Rombel Target (Opsional)',
            'Status Aktif (Ya/Tidak)',
        ];

        $rows = [
            [
                'Bab 1: Klasifikasi Makhluk Hidup',
                'Pengantar ciri-ciri makhluk hidup dan urutan taksonomi 5 kingdom.',
                'Makhluk hidup memiliki ciri bernapas, memerlukan nutrisi, bergerak, tumbuh, berkembang biak, dan peka terhadap rangsang.',
                'https://drive.google.com/contoh-modul-ipa',
                '',
                'Ya',
            ],
            [
                'Bab 2: Interaksi Ekosistem & Lingkungan',
                'Hubungan komponen biotik dan abiotik dalam ekosistem sekolah.',
                'Komponen biotik meliputi produsen, konsumen, dan dekomposer. Komponen abiotik meliputi cahaya, suhu, air, dan tanah.',
                '',
                '',
                'Ya',
            ],
        ];

        return ['headers' => $headers, 'rows' => $rows];
    }

    public static function getTemplateCsv(): string
    {
        $data = self::sampleTemplateData();

        return FlexibleDocumentReader::buildCsv($data['headers'], $data['rows'], ';');
    }

    public static function getTemplateXlsx(): string
    {
        $data = self::sampleTemplateData();
        $rows = array_merge([$data['headers']], $data['rows']);

        $guideRows = [
            ['PANDUAN IMPOR MATERI PEMBELAJARAN SMPN 4 CEPU'],
            ['1. Anda dapat menggunakan template Excel/CSV/Word ini ATAU format file milik Anda sendiri.'],
            ['2. Kolom Judul Materi wajib terisi. Ringkasan dan Isi otomatis disesuaikan jika template Anda berbeda.'],
            ['3. Jika Rombel Target dikosongkan, sistem otomatis memakai Kelas/Rombel yang Anda pilih saat upload.'],
            ['4. Anda juga bisa mengunggah langsung file Word (.docx/.dotx), TXT, Markdown, atau PDF modul ajar.'],
        ];

        return FlexibleDocumentReader::buildXlsx([
            ['name' => 'Daftar Materi', 'rows' => $rows],
            ['name' => 'Petunjuk', 'rows' => $guideRows],
        ]);
    }

    public static function getTemplateDocx(): string
    {
        $paragraphs = [
            'Anda dapat mengunggah dokumen Word berisi 1 materi penuh (judul pada baris pertama, isi di bawahnya) ATAU beberapa materi berurutan seperti contoh berikut:',
            '',
            'Judul: Bab 1 - Bilangan Bulat dan Operasinya',
            'Ringkasan: Memahami operasi hitung penjumlahan, pengurangan, perkalian, dan pembagian bilangan bulat.',
            'Isi: Bilangan bulat terdiri atas bilangan bulat negatif, nol, dan bilangan bulat positif. Operasi perkalian dan pembagian didahulukan sebelum penjumlahan dan pengurangan.',
            'Tautan: https://www.youtube.com/watch?v=contoh',
            '---',
            'Judul: Bab 2 - Himpunan dan Diagram Venn',
            'Ringkasan: Menyatakan himpunan dan menyelesaikan masalah kontekstual dengan diagram Venn.',
            'Isi: Himpunan adalah kumpulan objek yang terdefinisi dengan jelas batasannya.',
        ];

        return FlexibleDocumentReader::buildDocx('TEMPLATE MATERI AJAR SMPN 4 CEPU', $paragraphs);
    }

    public static function getTemplateTxt(): string
    {
        return implode("\n", [
            'Judul: Bab 1 - Teks Deskripsi',
            'Ringkasan: Mengenali struktur identifikasi, deskripsi bagian, dan simpulan pada teks deskripsi.',
            'Isi: Teks deskripsi adalah paragraf yang menggambarkan objek secara terperinci seolah pembaca ikut merasakan dan melihat langsung.',
            '---',
            'Judul: Bab 2 - Teks Cerita Fantasi',
            'Ringkasan: Unsur intrinsik dan ciri kebahasaan cerita fantasi.',
            'Isi: Cerita fantasi memadukan imajinasi kreatif penulis dengan alur cerita yang runtut.',
        ]);
    }

    /**
     * @param  Collection<int, LearningMaterial>  $materials
     */
    public static function exportMaterialsToCsv(Collection $materials): string
    {
        $rows = self::buildExportRows($materials);
        $headers = array_shift($rows);

        return FlexibleDocumentReader::buildCsv($headers, $rows, ';');
    }

    /**
     * @param  Collection<int, LearningMaterial>  $materials
     */
    public static function exportMaterialsToXlsx(Collection $materials): string
    {
        $rows = self::buildExportRows($materials);

        return FlexibleDocumentReader::buildXlsx([
            ['name' => 'Materi Pembelajaran', 'rows' => $rows],
        ]);
    }

    /**
     * @param  Collection<int, LearningMaterial>  $materials
     * @return list<list<string>>
     */
    private static function buildExportRows(Collection $materials): array
    {
        $rows = [[
            'Judul Materi',
            'Mata Pelajaran',
            'Tingkat Kelas',
            'Rombel Target',
            'Ringkasan Singkat',
            'Isi Lengkap Materi',
            'Tautan Video / Drive',
            'Status Aktif',
        ]];

        foreach ($materials as $m) {
            $rows[] = [
                (string) $m->title,
                (string) ($m->subject?->name ?? ''),
                (string) ($m->grade_level ?? ''),
                is_array($m->target_classes) ? implode(', ', $m->target_classes) : '',
                (string) ($m->summary ?? ''),
                trim(strip_tags((string) ($m->content ?? ''))),
                (string) ($m->external_url ?? ''),
                $m->is_active ? 'Ya' : 'Tidak',
            ];
        }

        return $rows;
    }
}
