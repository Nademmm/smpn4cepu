<?php

namespace App\Services\Support;

use Closure;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use ZipArchive;

class FlexibleDocumentReader
{
    /** @var list<string> */
    public const IMPORT_EXTENSIONS = [
        'xlsx', 'xltx', 'xlsm', 'xls', 'ods',
        'csv', 'tsv', 'txt', 'md', 'rtf',
        'docx', 'dotx', 'odt', 'json',
    ];

    /** @var list<string> */
    public const MATERIAL_ATTACHMENT_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'dot', 'dotx', 'dotm', 'odt', 'rtf', 'txt', 'md',
        'xls', 'xlsx', 'xltx', 'xltm', 'xlsm', 'ods', 'csv', 'tsv', 'json',
        'ppt', 'pptx', 'pot', 'potx', 'potm', 'odp',
        'zip', 'rar', '7z',
        'png', 'jpg', 'jpeg', 'webp',
    ];

    /** @var list<string> */
    public const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'com', 'scr', 'sh', 'bash',
        'ps1', 'vbs', 'js', 'mjs', 'cjs', 'jar', 'msi', 'dll', 'sys', 'cgi',
        'pl', 'py', 'rb', 'asp', 'aspx', 'jsp', 'hta', 'svg',
    ];

    public static function importFileRule(): ValidationRule
    {
        return self::extensionValidationRule(self::IMPORT_EXTENSIONS, 'template soal/materi');
    }

    public static function materialAttachmentRule(): ValidationRule
    {
        return self::extensionValidationRule(self::MATERIAL_ATTACHMENT_EXTENSIONS, 'lampiran materi');
    }

    public static function isGuideSheetName(string $sheetName): bool
    {
        return preg_match('/^(?:petunjuk|panduan|guide|instructions?|help|catatan|keterangan)/i', trim($sheetName)) === 1;
    }

    public static function isLikelyHeaderCell(string $raw): bool
    {
        $trimmed = trim($raw);
        if ($trimmed === '' || mb_strlen($trimmed) > 55) {
            return false;
        }

        // Kalimat bernomor ("1. Anda dapat...") bukan judul kolom
        if (preg_match('/^\d{1,2}\.\s+[A-Za-z]/', $trimmed) === 1) {
            return false;
        }

        return true;
    }

    /**
     * @param  list<string>  $allowedExtensions
     */
    private static function extensionValidationRule(array $allowedExtensions, string $contextLabel): ValidationRule
    {
        return new class($allowedExtensions, $contextLabel) implements ValidationRule
        {
            /** @param  list<string>  $allowedExtensions */
            public function __construct(
                private readonly array $allowedExtensions,
                private readonly string $contextLabel
            ) {}

            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                if (empty($value)) {
                    return;
                }

                $items = is_array($value) ? $value : [$value];
                foreach ($items as $item) {
                    $name = $item instanceof UploadedFile ? $item->getClientOriginalName() : (string) $item;
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    if ($ext === '') {
                        continue;
                    }

                    if (in_array($ext, FlexibleDocumentReader::BLOCKED_EXTENSIONS, true)
                        || ! in_array($ext, $this->allowedExtensions, true)) {
                        $fail("Format berkas .{$ext} tidak diizinkan untuk {$this->contextLabel}.");

                        return;
                    }
                }
            }
        };
    }

    /**
     * @return array{path: string, name: string}|null
     */
    public static function resolveUploadedFile(mixed $state, string $disk = 'local'): ?array
    {
        if (empty($state)) {
            return null;
        }

        if (is_array($state)) {
            $state = Arr::first($state);
        }

        if ($state instanceof UploadedFile) {
            $realPath = $state->getRealPath();
            if (! $realPath || ! is_file($realPath)) {
                return null;
            }

            return [
                'path' => $realPath,
                'name' => $state->getClientOriginalName(),
            ];
        }

        if (is_string($state)) {
            if (is_file($state)) {
                return [
                    'path' => $state,
                    'name' => basename($state),
                ];
            }

            if (Storage::disk($disk)->exists($state)) {
                return [
                    'path' => Storage::disk($disk)->path($state),
                    'name' => basename($state),
                ];
            }
        }

        return null;
    }

    public function detectKind(string $filePath, ?string $originalName = null): string
    {
        $ext = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));

        if (! is_file($filePath) || filesize($filePath) === 0) {
            return $ext ?: 'unknown';
        }

        $handle = @fopen($filePath, 'rb');
        $head = $handle ? (string) fread($handle, 4096) : '';
        if ($handle) {
            fclose($handle);
        }

        if (str_starts_with($head, '%PDF-')) {
            return 'pdf';
        }

        if (str_starts_with($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) {
            return 'ole2_legacy';
        }

        if (str_starts_with($head, "PK\x03\x04")) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                $hasXlsx = $zip->locateName('xl/workbook.xml') !== false;
                $hasDocx = $zip->locateName('word/document.xml') !== false;
                $mime = $zip->getFromName('mimetype');
                $zip->close();

                if ($hasXlsx) {
                    return 'xlsx';
                }
                if ($hasDocx) {
                    return 'docx';
                }
                if (is_string($mime)) {
                    if (str_contains($mime, 'spreadsheet')) {
                        return 'ods';
                    }
                    if (str_contains($mime, 'text')) {
                        return 'odt';
                    }
                }
            }
        }

        $trimmed = ltrim($this->stripUtf8Bom($head));
        if (str_starts_with(strtolower($trimmed), '{\\rtf')) {
            return 'rtf';
        }

        if ((str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) && $this->decodeJson($filePath) !== null) {
            return 'json';
        }

        if (str_starts_with($trimmed, '<')) {
            $lowerHead = strtolower($trimmed);
            if (str_contains($lowerHead, '<workbook')) {
                return 'xml_spreadsheet';
            }
            if (str_contains($lowerHead, '<table') || str_contains($lowerHead, '<html')) {
                return 'html_table';
            }
        }

        return match ($ext) {
            'xltx', 'xlsm' => 'xlsx',
            'dotx', 'dotm' => 'docx',
            'tsv' => 'tsv',
            'csv' => 'csv',
            'md', 'markdown' => 'md',
            default => $ext ?: 'txt',
        };
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    public function extractTabularSheets(string $filePath, ?string $originalName = null): array
    {
        $kind = $this->detectKind($filePath, $originalName);

        return match ($kind) {
            'xlsx' => $this->readXlsxSheets($filePath),
            'ods' => $this->readOdsSheets($filePath),
            'docx' => $this->readDocxTables($filePath),
            'odt' => $this->readOdtTables($filePath),
            'xml_spreadsheet' => $this->readXmlSpreadsheetSheets($filePath),
            'html_table' => $this->readHtmlTables($filePath),
            'csv', 'tsv', 'txt' => $this->readDelimitedSheet($filePath, $kind === 'tsv' ? "\t" : null),
            default => [],
        };
    }

    /**
     * @return list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>
     */
    public function extractWordOrTextLines(string $filePath, ?string $originalName = null): array
    {
        $kind = $this->detectKind($filePath, $originalName);

        return match ($kind) {
            'docx' => $this->readDocxParagraphs($filePath),
            'odt' => $this->readOdtParagraphs($filePath),
            'rtf' => $this->linesFromPlainText($this->stripRtf((string) @file_get_contents($filePath))),
            'txt', 'md', 'csv', 'tsv' => $this->linesFromPlainText((string) @file_get_contents($filePath)),
            default => [],
        };
    }

    public function decodeJson(string $filePath): ?array
    {
        if (! is_file($filePath)) {
            return null;
        }

        $raw = $this->normalizeUtf8($this->stripUtf8Bom((string) @file_get_contents($filePath)));
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    private function readXlsxSheets(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        try {
            $sharedStrings = $this->parseXlsxSharedStrings($zip->getFromName('xl/sharedStrings.xml'));
            $sheetTargets = $this->resolveXlsxSheetTargets($zip);
            $sheets = [];

            foreach ($sheetTargets as $sheetName => $entryPath) {
                $sheetXmlStr = $zip->getFromName($entryPath);
                if (! is_string($sheetXmlStr) || $sheetXmlStr === '') {
                    continue;
                }

                $sheetXml = $this->loadSimpleXml($sheetXmlStr);
                if (! $sheetXml || ! isset($sheetXml->sheetData->row)) {
                    continue;
                }

                $rawRows = [];
                $maxCols = 0;

                foreach ($sheetXml->sheetData->row as $row) {
                    $rowCells = [];
                    $fallbackCol = 0;

                    foreach ($row->c as $c) {
                        $cellRef = (string) $c['r'];
                        if (preg_match('/^([A-Z]+)/i', $cellRef, $m)) {
                            $colIdx = $this->columnLettersToIndex(strtoupper($m[1]));
                            $fallbackCol = $colIdx + 1;
                        } else {
                            $colIdx = $fallbackCol++;
                        }

                        $val = $this->extractXlsxCellValue($c, $sharedStrings);
                        $rowCells[$colIdx] = $val;
                        $maxCols = max($maxCols, $colIdx + 1);
                    }

                    if (! empty(array_filter($rowCells, fn (string $v): bool => $v !== ''))) {
                        $rawRows[] = $rowCells;
                    }
                }

                if (empty($rawRows) || $maxCols === 0) {
                    continue;
                }

                $normalizedRows = [];
                foreach ($rawRows as $sparseRow) {
                    $denseRow = [];
                    for ($i = 0; $i < $maxCols; $i++) {
                        $denseRow[] = $sparseRow[$i] ?? '';
                    }
                    $normalizedRows[] = $denseRow;
                }

                $sheets[] = [
                    'name' => $sheetName,
                    'rows' => $normalizedRows,
                ];
            }

            return $sheets;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<string>
     */
    private function parseXlsxSharedStrings(string|false $sstXml): array
    {
        if (! is_string($sstXml) || $sstXml === '') {
            return [];
        }

        $xml = $this->loadSimpleXml($sstXml);
        if (! $xml || ! isset($xml->si)) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $si) {
            $text = '';
            if (isset($si->t)) {
                $text = (string) $si->t;
            } elseif (isset($si->r)) {
                foreach ($si->r as $r) {
                    $text .= (string) $r->t;
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @return array<string, string>
     */
    private function resolveXlsxSheetTargets(ZipArchive $zip): array
    {
        $wbXmlStr = $zip->getFromName('xl/workbook.xml');
        $relsXmlStr = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (! is_string($wbXmlStr) || ! is_string($relsXmlStr)) {
            return $zip->locateName('xl/worksheets/sheet1.xml') !== false
                ? ['Sheet1' => 'xl/worksheets/sheet1.xml']
                : [];
        }

        $wbXml = $this->loadSimpleXml($wbXmlStr);
        $relsXml = $this->loadSimpleXml($relsXmlStr);
        if (! $wbXml || ! $relsXml || ! isset($wbXml->sheets->sheet)) {
            return [];
        }

        $relMap = [];
        foreach ($relsXml->Relationship as $rel) {
            $relMap[(string) $rel['Id']] = (string) $rel['Target'];
        }

        $targets = [];
        foreach ($wbXml->sheets->sheet as $sheet) {
            $name = (string) $sheet['name'] ?: 'Sheet';
            $rAttrs = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $rId = (string) ($rAttrs['id'] ?? '');
            $target = ltrim((string) ($relMap[$rId] ?? ''), '/');
            if ($target === '') {
                continue;
            }
            if (! str_starts_with($target, 'xl/')) {
                $target = 'xl/' . $target;
            }
            $targets[$name] = $target;
        }

        return $targets;
    }

    /**
     * @param  list<string>  $sharedStrings
     */
    private function extractXlsxCellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            return trim($sharedStrings[(int) ((string) $cell->v)] ?? '');
        }

        if ($type === 'inlineStr') {
            $text = '';
            if (isset($cell->is->t)) {
                $text = (string) $cell->is->t;
            } elseif (isset($cell->is->r)) {
                foreach ($cell->is->r as $r) {
                    $text .= (string) $r->t;
                }
            }

            return trim($text);
        }

        if ($type === 'b') {
            return ((string) $cell->v) === '1' ? 'TRUE' : 'FALSE';
        }

        return trim((string) $cell->v);
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    private function readOdsSheets(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        try {
            $contentXml = $zip->getFromName('content.xml');
            if (! is_string($contentXml) || $contentXml === '') {
                return [];
            }

            $dom = $this->loadDomXml($contentXml);
            if (! $dom) {
                return [];
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0');
            $xpath->registerNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');

            $sheets = [];
            foreach ($xpath->query('//table:table') ?: [] as $tableNode) {
                if (! $tableNode instanceof DOMElement) {
                    continue;
                }

                $sheetName = $tableNode->getAttribute('table:name') ?: 'Sheet';
                $rows = [];
                $maxCols = 0;

                foreach ($xpath->query('./table:table-row', $tableNode) ?: [] as $rowNode) {
                    $cells = [];
                    foreach ($xpath->query('./table:table-cell | ./table:covered-table-cell', $rowNode) ?: [] as $cellNode) {
                        if (! $cellNode instanceof DOMElement) {
                            continue;
                        }

                        $repeat = (int) ($cellNode->getAttribute('table:number-columns-repeated') ?: 1);
                        $paragraphs = [];
                        foreach ($xpath->query('.//text:p', $cellNode) ?: [] as $pNode) {
                            $paragraphs[] = trim((string) $pNode->textContent);
                        }
                        $val = trim(implode("\n", array_filter($paragraphs, fn (string $v): bool => $v !== '')));

                        if ($val === '' && $repeat > 20) {
                            continue;
                        }

                        $repeatCount = min($repeat, 50);
                        for ($r = 0; $r < $repeatCount; $r++) {
                            $cells[] = $val;
                        }
                    }

                    // Trim trailing empty cells before storing
                    while (! empty($cells) && end($cells) === '') {
                        array_pop($cells);
                    }

                    if (! empty(array_filter($cells, fn (string $v): bool => $v !== ''))) {
                        $rows[] = $cells;
                        $maxCols = max($maxCols, count($cells));
                    }
                }

                if (! empty($rows)) {
                    $rows = array_map(fn (array $r): array => array_pad($r, $maxCols, ''), $rows);
                    $sheets[] = ['name' => $sheetName, 'rows' => $rows];
                }
            }

            return $sheets;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    private function readDocxTables(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        try {
            $docXml = $zip->getFromName('word/document.xml');
            if (! is_string($docXml) || $docXml === '') {
                return [];
            }

            $dom = $this->loadDomXml($docXml);
            if (! $dom) {
                return [];
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $sheets = [];
            $tableIndex = 1;
            foreach ($xpath->query('//w:body//w:tbl') ?: [] as $tblNode) {
                $rows = [];
                $maxCols = 0;

                foreach ($xpath->query('./w:tr', $tblNode) ?: [] as $trNode) {
                    $cells = [];
                    foreach ($xpath->query('./w:tc', $trNode) ?: [] as $tcNode) {
                        $paraLines = [];
                        foreach ($xpath->query('./w:p', $tcNode) ?: [] as $pNode) {
                            $line = trim($this->extractDocxParagraphText($xpath, $pNode));
                            if ($line !== '') {
                                $paraLines[] = $line;
                            }
                        }
                        $cells[] = trim(implode("\n", $paraLines));
                    }

                    if (! empty(array_filter($cells, fn (string $v): bool => $v !== ''))) {
                        $rows[] = $cells;
                        $maxCols = max($maxCols, count($cells));
                    }
                }

                if (! empty($rows) && $maxCols >= 2) {
                    $rows = array_map(fn (array $r): array => array_pad($r, $maxCols, ''), $rows);
                    $sheets[] = [
                        'name' => 'Tabel Word ' . $tableIndex++,
                        'rows' => $rows,
                    ];
                }
            }

            return $sheets;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    private function readOdtTables(string $filePath): array
    {
        return $this->readOdsSheets($filePath);
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    private function readXmlSpreadsheetSheets(string $filePath): array
    {
        $raw = (string) @file_get_contents($filePath);
        $xml = $this->loadSimpleXml($raw);
        if (! $xml) {
            return [];
        }

        $sheets = [];
        $ns = $xml->getNamespaces(true);
        $ssNs = $ns['ss'] ?? 'urn:schemas-microsoft-com:office:spreadsheet';

        foreach ($xml->Worksheet ?? [] as $ws) {
            $wsAttrs = $ws->attributes($ssNs);
            $name = (string) ($wsAttrs['Name'] ?? 'Sheet');
            $rows = [];
            $maxCols = 0;

            foreach ($ws->Table->Row ?? [] as $row) {
                $cells = [];
                $colIdx = 0;
                foreach ($row->Cell as $cell) {
                    $cellAttrs = $cell->attributes($ssNs);
                    if (isset($cellAttrs['Index'])) {
                        $colIdx = max(0, ((int) $cellAttrs['Index']) - 1);
                    }
                    $val = trim((string) ($cell->Data ?? ''));
                    $cells[$colIdx] = $val;
                    $colIdx++;
                    $maxCols = max($maxCols, $colIdx);
                }
                if (! empty(array_filter($cells, fn (string $v): bool => $v !== ''))) {
                    $dense = [];
                    for ($i = 0; $i < $maxCols; $i++) {
                        $dense[] = $cells[$i] ?? '';
                    }
                    $rows[] = $dense;
                }
            }

            if (! empty($rows)) {
                $sheets[] = ['name' => $name, 'rows' => array_map(fn ($r) => array_pad($r, $maxCols, ''), $rows)];
            }
        }

        return $sheets;
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    private function readHtmlTables(string $filePath): array
    {
        $raw = $this->normalizeUtf8((string) @file_get_contents($filePath));
        if ($raw === '') {
            return [];
        }

        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $raw, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $sheets = [];
        $index = 1;
        foreach ($dom->getElementsByTagName('table') as $table) {
            $rows = [];
            $maxCols = 0;

            foreach ($table->getElementsByTagName('tr') as $tr) {
                $cells = [];
                foreach ($tr->childNodes as $child) {
                    if ($child instanceof DOMElement && in_array(strtolower($child->tagName), ['td', 'th'], true)) {
                        $cells[] = trim(preg_replace('/\s+/', ' ', (string) $child->textContent) ?? '');
                    }
                }
                if (! empty(array_filter($cells, fn (string $v): bool => $v !== ''))) {
                    $rows[] = $cells;
                    $maxCols = max($maxCols, count($cells));
                }
            }

            if (! empty($rows) && $maxCols >= 2) {
                $rows = array_map(fn (array $r): array => array_pad($r, $maxCols, ''), $rows);
                $sheets[] = ['name' => 'Tabel HTML ' . $index++, 'rows' => $rows];
            }
        }

        return $sheets;
    }

    /**
     * @return list<array{name: string, rows: list<list<string>>}>
     */
    private function readDelimitedSheet(string $filePath, ?string $forcedDelimiter = null): array
    {
        $raw = @file_get_contents($filePath);
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $text = $this->normalizeUtf8($this->stripUtf8Bom($raw));
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $delimiter = $forcedDelimiter ?? $this->detectCsvDelimiter($text);

        $stream = fopen('php://temp', 'r+');
        if (! $stream) {
            return [];
        }

        fwrite($stream, $text);
        rewind($stream);

        $rows = [];
        $maxCols = 0;
        while (($row = fgetcsv($stream, 0, $delimiter)) !== false) {
            $cleaned = array_map(fn ($v): string => trim((string) $v), $row);
            if (! empty(array_filter($cleaned, fn (string $v): bool => $v !== ''))) {
                $rows[] = $cleaned;
                $maxCols = max($maxCols, count($cleaned));
            }
        }
        fclose($stream);

        if (empty($rows) || $maxCols < 2) {
            return [];
        }

        $rows = array_map(fn (array $r): array => array_pad($r, $maxCols, ''), $rows);

        return [['name' => 'CSV', 'rows' => $rows]];
    }

    /**
     * @return list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>
     */
    private function readDocxParagraphs(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        try {
            $docXml = $zip->getFromName('word/document.xml');
            if (! is_string($docXml) || $docXml === '') {
                return [];
            }

            $dom = $this->loadDomXml($docXml);
            if (! $dom) {
                return [];
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $lines = [];
            foreach ($xpath->query('//w:body//w:p') ?: [] as $pNode) {
                $styleNodes = $xpath->query('./w:pPr/w:pStyle/@w:val', $pNode);
                $styleVal = $styleNodes && $styleNodes->length > 0 ? strtolower((string) $styleNodes->item(0)?->nodeValue) : '';
                $isHeading = str_contains($styleVal, 'heading') || str_contains($styleVal, 'title');

                $text = '';
                $boldChars = 0;
                $underlineChars = 0;
                $highlightChars = 0;
                $totalNonSpaceChars = 0;

                foreach ($xpath->query('.//w:r', $pNode) ?: [] as $rNode) {
                    $runText = $this->extractDocxRunText($xpath, $rNode);
                    $nonSpaceLen = mb_strlen(preg_replace('/\s+/u', '', $runText) ?? '');

                    if ($nonSpaceLen > 0) {
                        $totalNonSpaceChars += $nonSpaceLen;

                        if ($this->hasDocxFormattingFlag($xpath, $rNode, 'w:b')) {
                            $boldChars += $nonSpaceLen;
                        }
                        if ($this->hasDocxUnderlineFlag($xpath, $rNode)) {
                            $underlineChars += $nonSpaceLen;
                        }
                        if ($xpath->query('./w:rPr/w:highlight', $rNode)->length > 0) {
                            $highlightChars += $nonSpaceLen;
                        }
                    }

                    $text .= $runText;
                }

                // Split soft line breaks inside single Word paragraphs into individual lines cleanly
                $subLines = preg_split("/\r\n|\r|\n/", $text) ?: [$text];
                foreach ($subLines as $sub) {
                    $cleanSub = trim($sub);
                    $lines[] = [
                        'text' => $cleanSub,
                        'is_bold' => $totalNonSpaceChars > 0 && ($boldChars / $totalNonSpaceChars) >= 0.6,
                        'is_underlined' => $totalNonSpaceChars > 0 && ($underlineChars / $totalNonSpaceChars) >= 0.5,
                        'is_highlighted' => $totalNonSpaceChars > 0 && ($highlightChars / $totalNonSpaceChars) >= 0.4,
                        'is_heading' => $isHeading,
                    ];
                }
            }

            return $lines;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>
     */
    private function readOdtParagraphs(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        try {
            $contentXml = $zip->getFromName('content.xml');
            if (! is_string($contentXml) || $contentXml === '') {
                return [];
            }

            $dom = $this->loadDomXml($contentXml);
            if (! $dom) {
                return [];
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');

            $lines = [];
            foreach ($xpath->query('//text:h | //text:p') ?: [] as $node) {
                $isHeading = $node instanceof DOMElement && strtolower($node->localName) === 'h';
                $lines[] = [
                    'text' => trim((string) $node->textContent),
                    'is_bold' => false,
                    'is_underlined' => false,
                    'is_highlighted' => false,
                    'is_heading' => $isHeading,
                ];
            }

            return $lines;
        } finally {
            $zip->close();
        }
    }

    private function extractDocxParagraphText(DOMXPath $xpath, \DOMNode $pNode): string
    {
        $text = '';
        foreach ($xpath->query('.//w:r', $pNode) ?: [] as $rNode) {
            $text .= $this->extractDocxRunText($xpath, $rNode);
        }

        return $text;
    }

    private function extractDocxRunText(DOMXPath $xpath, \DOMNode $rNode): string
    {
        $text = '';
        foreach ($rNode->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            $name = $child->localName;
            if ($name === 't') {
                $text .= $child->textContent;
            } elseif ($name === 'tab') {
                $text .= "\t";
            } elseif ($name === 'br' || $name === 'cr') {
                $text .= "\n";
            }
        }

        return $text;
    }

    private function hasDocxFormattingFlag(DOMXPath $xpath, \DOMNode $rNode, string $tag): bool
    {
        $nodes = $xpath->query("./w:rPr/{$tag}", $rNode);
        if (! $nodes || $nodes->length === 0) {
            return false;
        }

        $el = $nodes->item(0);
        if ($el instanceof DOMElement && $el->hasAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'val')) {
            $val = strtolower($el->getAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'val'));

            return ! in_array($val, ['0', 'false', 'off', 'none'], true);
        }

        return true;
    }

    private function hasDocxUnderlineFlag(DOMXPath $xpath, \DOMNode $rNode): bool
    {
        $nodes = $xpath->query('./w:rPr/w:u', $rNode);
        if (! $nodes || $nodes->length === 0) {
            return false;
        }

        $el = $nodes->item(0);
        if ($el instanceof DOMElement) {
            $val = strtolower($el->getAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'val'));
            if ($val === '' && $el->hasAttribute('w:val')) {
                $val = strtolower($el->getAttribute('w:val'));
            }

            return $val !== 'none';
        }

        return true;
    }

    /**
     * @return list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>
     */
    private function linesFromPlainText(string $raw): array
    {
        $text = $this->normalizeUtf8($this->stripUtf8Bom($raw));
        $split = preg_split("/\r\n|\r|\n/", $text) ?: [];

        return array_map(fn (string $line): array => [
            'text' => trim($line),
            'is_bold' => false,
            'is_underlined' => false,
            'is_highlighted' => false,
            'is_heading' => false,
        ], $split);
    }

    private function stripRtf(string $rtf): string
    {
        $rtf = preg_replace('/\{\\\\(fonttbl|colortbl|stylesheet|info|pict|object)[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/si', '', $rtf) ?? $rtf;
        $rtf = preg_replace('/\\\\(par|line)\b\s?/', "\n", $rtf) ?? $rtf;
        $rtf = preg_replace('/\\\\tab\b\s?/', "\t", $rtf) ?? $rtf;
        $rtf = preg_replace_callback('/\\\\\'([0-9a-fA-F]{2})/', fn (array $m): string => chr((int) hexdec($m[1])), $rtf) ?? $rtf;
        $rtf = preg_replace('/\\\\u(-?\d+)\??/', '', $rtf) ?? $rtf;
        $rtf = preg_replace('/\\\\[a-zA-Z]+-?\d*\s?/', '', $rtf) ?? $rtf;
        $rtf = str_replace(['{', '}'], '', $rtf);

        return trim($rtf);
    }

    private function detectCsvDelimiter(string $text): string
    {
        $lines = array_values(array_filter(
            array_slice(explode("\n", $text), 0, 8),
            fn (string $l): bool => trim($l) !== ''
        ));

        $candidates = [';', ',', "\t", '|'];
        $bestDelimiter = ';';
        $bestScore = 0;

        foreach ($candidates as $cand) {
            $counts = array_map(fn (string $line): int => count(str_getcsv($line, $cand)), $lines);
            $multiColLines = count(array_filter($counts, fn (int $c): bool => $c > 1));
            $maxCols = ! empty($counts) ? max($counts) : 1;
            $score = ($multiColLines * 100) + $maxCols;

            if ($multiColLines > 0 && $score > $bestScore) {
                $bestScore = $score;
                $bestDelimiter = $cand;
            }
        }

        return $bestDelimiter;
    }

    private function stripUtf8Bom(string $text): string
    {
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            return substr($text, 3);
        }

        return $text;
    }

    private function normalizeUtf8(string $text): string
    {
        if ($text === '' || mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        $converted = @mb_convert_encoding($text, 'UTF-8', 'Windows-1252, ISO-8859-1');

        return is_string($converted) ? $converted : $text;
    }

    private function columnLettersToIndex(string $letters): int
    {
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - ord('A') + 1);
        }

        return max(0, $index - 1);
    }

    private function loadSimpleXml(string $xmlString): ?SimpleXMLElement
    {
        $prev = libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $xml instanceof SimpleXMLElement ? $xml : null;
    }

    private function loadDomXml(string $xmlString): ?DOMDocument
    {
        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $loaded = @$dom->loadXML($xmlString, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $loaded ? $dom : null;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    public static function buildCsv(array $headers, array $rows, string $delimiter = ';'): string
    {
        $stream = fopen('php://temp', 'r+');
        fputs($stream, "\xEF\xBB\xBF");
        fputcsv($stream, $headers, $delimiter);
        foreach ($rows as $row) {
            fputcsv($stream, array_map(fn ($v): string => (string) ($v ?? ''), $row), $delimiter);
        }
        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    /**
     * Membangun berkas Excel (.xlsx) asli multi-sheet tanpa dependensi eksternal.
     *
     * @param  list<array{name: string, rows: list<list<string|int|float|null>>}>  $sheets
     */
    public static function buildXlsx(array $sheets): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_tpl_');
        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Gagal membuat berkas Excel (.xlsx).');
        }

        try {
            $sheetOverrides = '';
            $wbSheetsXml = '';
            $wbRelsXml = '<Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

            foreach ($sheets as $idx => $sheet) {
                $sheetNumber = $idx + 1;
                $safeName = self::xmlText(mb_substr(preg_replace('/[:\\\\\/\?\*\[\]]/', '', (string) $sheet['name']) ?: "Sheet{$sheetNumber}", 0, 31));
                $sheetOverrides .= "<Override PartName=\"/xl/worksheets/sheet{$sheetNumber}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
                $wbSheetsXml .= "<sheet name=\"{$safeName}\" sheetId=\"{$sheetNumber}\" r:id=\"rId{$sheetNumber}\"/>";
                $wbRelsXml .= "<Relationship Id=\"rId{$sheetNumber}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$sheetNumber}.xml\"/>";

                $rowsXml = '';
                foreach ($sheet['rows'] as $rowIdx => $row) {
                    $excelRowNum = $rowIdx + 1;
                    $styleAttr = $rowIdx === 0 ? ' s="1"' : '';
                    $cellsXml = '';

                    foreach ($row as $colIdx => $cellVal) {
                        $colLetters = self::indexToColumnLetters($colIdx);
                        $ref = $colLetters . $excelRowNum;
                        $strVal = self::xmlText((string) ($cellVal ?? ''));
                        $cellsXml .= "<c r=\"{$ref}\" t=\"inlineStr\"{$styleAttr}><is><t xml:space=\"preserve\">{$strVal}</t></is></c>";
                    }

                    $rowsXml .= "<row r=\"{$excelRowNum}\">{$cellsXml}</row>";
                }

                $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                    . "<sheetData>{$rowsXml}</sheetData>"
                    . '</worksheet>';

                $zip->addFromString("xl/worksheets/sheet{$sheetNumber}.xml", $sheetXml);
            }

            $zip->addFromString(
                '[Content_Types].xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . $sheetOverrides
                . '</Types>'
            );

            $zip->addFromString(
                '_rels/.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>'
            );

            $zip->addFromString(
                'xl/workbook.xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . "<sheets>{$wbSheetsXml}</sheets>"
                . '</workbook>'
            );

            $zip->addFromString(
                'xl/_rels/workbook.xml.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . $wbRelsXml
                . '</Relationships>'
            );

            $zip->addFromString(
                'xl/styles.xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
                . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1E3A5F"/></patternFill></fill></fills>'
                . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
                . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
                . '</styleSheet>'
            );

            $zip->close();

            return (string) file_get_contents($tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * Membangun berkas Word (.docx) asli dengan paragraf & tabel opsional.
     *
     * @param  list<string>  $paragraphs
     * @param  list<list<string>>  $tableRows
     */
    public static function buildDocx(string $title, array $paragraphs = [], array $tableRows = []): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'docx_tpl_');
        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Gagal membuat berkas Word (.docx).');
        }

        try {
            $bodyXml = '<w:p><w:r><w:rPr><w:b/><w:sz w:val="28"/></w:rPr><w:t xml:space="preserve">'
                . self::xmlText($title)
                . '</w:t></w:r></w:p>';

            foreach ($paragraphs as $line) {
                $bodyXml .= '<w:p><w:r><w:t xml:space="preserve">' . self::xmlText($line) . '</w:t></w:r></w:p>';
            }

            if (! empty($tableRows)) {
                $bodyXml .= '<w:tbl><w:tblPr><w:tblBorders>'
                    . '<w:top w:val="single" w:sz="4" w:space="0" w:color="94A3B8"/>'
                    . '<w:left w:val="single" w:sz="4" w:space="0" w:color="94A3B8"/>'
                    . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="94A3B8"/>'
                    . '<w:right w:val="single" w:sz="4" w:space="0" w:color="94A3B8"/>'
                    . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>'
                    . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>'
                    . '</w:tblBorders></w:tblPr>';

                foreach ($tableRows as $rIdx => $row) {
                    $bodyXml .= '<w:tr>';
                    foreach ($row as $cell) {
                        $boldXml = $rIdx === 0 ? '<w:rPr><w:b/></w:rPr>' : '';
                        $bodyXml .= '<w:tc><w:p><w:r>' . $boldXml . '<w:t xml:space="preserve">' . self::xmlText((string) $cell) . '</w:t></w:r></w:p></w:tc>';
                    }
                    $bodyXml .= '</w:tr>';
                }
                $bodyXml .= '</w:tbl>';
            }

            $zip->addFromString(
                '[Content_Types].xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
                . '</Types>'
            );

            $zip->addFromString(
                '_rels/.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
                . '</Relationships>'
            );

            $zip->addFromString(
                'word/document.xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                . '<w:body>' . $bodyXml . '</w:body>'
                . '</w:document>'
            );

            $zip->close();

            return (string) file_get_contents($tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    private static function indexToColumnLetters(int $index): string
    {
        $letters = '';
        $num = $index + 1;
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $num = intdiv($num - 1, 26);
        }

        return $letters;
    }

    private static function xmlText(string $value): string
    {
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';

        return htmlspecialchars($clean, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
