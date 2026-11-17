<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\Support\FlexibleDocumentReader;
use App\Support\SubjectPermissionFormHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionImportService
{
    public const FIELD_LABELS = [
        'ignore' => '-- Abaikan Kolom Ini --',
        'order_index' => 'Nomor / Urutan Soal',
        'question_text' => 'Teks Pertanyaan / Soal *',
        'option_a' => 'Pilihan / Opsi A *',
        'option_b' => 'Pilihan / Opsi B *',
        'option_c' => 'Pilihan / Opsi C',
        'option_d' => 'Pilihan / Opsi D',
        'option_e' => 'Pilihan / Opsi E',
        'correct_answer' => 'Kunci Jawaban *',
        'points' => 'Bobot Poin',
        'explanation' => 'Pembahasan Jawaban',
    ];

    public function __construct(
        protected FlexibleDocumentReader $reader = new FlexibleDocumentReader()
    ) {}

    /**
     * Memeriksa dan mem-parsing berkas template soal dari format apapun tanpa menyimpan ke DB terlebih dahulu.
     *
     * @param  array<int|string, string>  $columnOverrides  Peta opsional [colIndex => fieldKey]
     * @return array{
     *     success: bool,
     *     format_label: string,
     *     is_tabular: bool,
     *     detected_headers: array<int, string>,
     *     column_map: array<int, string>,
     *     sample_rows: array<int, array<int, string>>,
     *     questions: list<array<string, mixed>>,
     *     total_found: int,
     *     skipped_count: int,
     *     warnings: list<string>,
     *     errors: list<string>
     * }
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
                'Berkas biner Office lama (.xls / .doc) belum bisa diparsing langsung. Buka di Excel/Word lalu Save As menjadi .xlsx, .docx, atau .csv.'
            );
        }

        if ($kind === 'pdf') {
            return $this->emptyResult(
                'Berkas PDF belum mendukung ekstraksi otomatis soal pilihan ganda karena tata letak PDF beragam. Gunakan Excel (.xlsx/.csv), Word (.docx/.dotx), atau TXT (Aiken/bernomor).'
            );
        }

        // 1. Format JSON
        if ($kind === 'json') {
            return $this->parseJsonQuestions($filePath);
        }

        // 2. Format Tabular (Excel .xlsx/.xltx/.ods, CSV/TSV, HTML/XML .xls, Tabel di Word .docx/.odt)
        $sheets = $this->reader->extractTabularSheets($filePath, $name);
        if (! empty($sheets)) {
            $tabularResult = $this->parseTabularSheets($sheets, $kind, $columnOverrides);
            if ($tabularResult['total_found'] > 0 || ! in_array($kind, ['docx', 'odt', 'txt', 'md'], true)) {
                return $tabularResult;
            }
        }

        // 3. Tabel vertikal key-value di Word (.docx) (contoh: 1 tabel 2 kolom per soal)
        if ($kind === 'docx' && ! empty($sheets)) {
            $verticalResult = $this->parseVerticalDocxTables($sheets);
            if ($verticalResult['total_found'] > 0) {
                return $verticalResult;
            }
        }

        // 4. Format Teks / Paragraf Word (.docx/.dotx/.odt/.txt/.md/.rtf)
        $lines = $this->reader->extractWordOrTextLines($filePath, $name);
        if (! empty($lines)) {
            return $this->parseDocumentLines($lines, $kind);
        }

        return $this->emptyResult('Format berkas tidak mengandung butir soal yang dikenali. Pastikan tersedia pertanyaan, minimal 2 opsi jawaban, dan kunci jawaban.');
    }

    /**
     * Membuat Bank Soal baru lengkap dengan izin mapel/rombel guru lalu mengimpor seluruh butir soal.
     *
     * @param  array<string, mixed>  $bankData
     * @param  array<int|string, string>  $columnOverrides
     * @return array<string, mixed>
     */
    public function createBankAndImport(
        array $bankData,
        string|UploadedFile $file,
        User $actor,
        array $columnOverrides = [],
        ?string $originalName = null
    ): array {
        $preview = $this->inspect($file, $columnOverrides, $originalName);

        if (empty($preview['questions'])) {
            return [
                'success' => false,
                'bank' => null,
                'imported_questions' => 0,
                'skipped_count' => $preview['skipped_count'],
                'format_label' => $preview['format_label'],
                'warnings' => $preview['warnings'],
                'errors' => ! empty($preview['errors']) ? $preview['errors'] : ['Tidak ada butir soal valid yang berhasil dibaca dari template.'],
            ];
        }

        $normalized = SubjectPermissionFormHelper::validateAndNormalizePayload($bankData, isCreate: true, actor: $actor);
        $title = trim((string) ($normalized['title'] ?? ''));
        if ($title === '') {
            $baseName = pathinfo($originalName ?: ($file instanceof UploadedFile ? $file->getClientOriginalName() : (string) $file), PATHINFO_FILENAME);
            $title = Str::headline($baseName) ?: 'Paket Soal Impor';
        }

        $accessCode = trim((string) ($normalized['access_code'] ?? ''));
        if ($accessCode === '') {
            $accessCode = 'SOAL-' . Str::upper(Str::random(5));
        }

        $bank = DB::transaction(function () use ($normalized, $title, $accessCode, $actor, $preview): QuestionBank {
            $bank = QuestionBank::create([
                'subject_id' => (int) $normalized['subject_id'],
                'grade_level' => (int) $normalized['grade_level'],
                'target_classes' => $normalized['target_classes'] ?? null,
                'title' => $title,
                'description' => $normalized['description'] ?? null,
                'duration_minutes' => max(5, (int) ($normalized['duration_minutes'] ?? 30)),
                'passing_grade' => max(0, min(100, (int) ($normalized['passing_grade'] ?? 75))),
                'access_code' => $accessCode,
                'is_active' => (bool) ($normalized['is_active'] ?? true),
                'allow_retry' => (bool) ($normalized['allow_retry'] ?? true),
                'max_attempts' => max(0, (int) ($normalized['max_attempts'] ?? 0)),
                'created_by' => $normalized['created_by'] ?? $actor->id,
            ]);

            $this->persistQuestions($bank, $preview['questions'], replaceExisting: false);

            return $bank;
        });

        $this->flushCaches($bank->id);

        return [
            'success' => true,
            'bank' => $bank->fresh(['questions.options']),
            'imported_questions' => count($preview['questions']),
            'skipped_count' => $preview['skipped_count'],
            'format_label' => $preview['format_label'],
            'warnings' => $preview['warnings'],
            'errors' => [],
        ];
    }

    /**
     * Mengimpor butir soal dari template ke dalam Bank Soal yang sudah ada.
     *
     * @param  array<int|string, string>  $columnOverrides
     * @return array<string, mixed>
     */
    public function importIntoBank(
        QuestionBank $bank,
        string|UploadedFile $file,
        bool $replaceExisting = false,
        array $columnOverrides = [],
        ?string $originalName = null
    ): array {
        $preview = $this->inspect($file, $columnOverrides, $originalName);

        if (empty($preview['questions'])) {
            return [
                'success' => false,
                'imported_questions' => 0,
                'skipped_count' => $preview['skipped_count'],
                'format_label' => $preview['format_label'],
                'warnings' => $preview['warnings'],
                'errors' => ! empty($preview['errors']) ? $preview['errors'] : ['Tidak ada butir soal valid yang berhasil dibaca dari berkas.'],
            ];
        }

        $count = DB::transaction(fn (): int => $this->persistQuestions($bank, $preview['questions'], $replaceExisting));
        $this->flushCaches($bank->id);

        return [
            'success' => true,
            'imported_questions' => $count,
            'skipped_count' => $preview['skipped_count'],
            'format_label' => $preview['format_label'],
            'warnings' => $preview['warnings'],
            'errors' => [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $parsedQuestions
     */
    private function persistQuestions(QuestionBank $bank, array $parsedQuestions, bool $replaceExisting): int
    {
        if ($replaceExisting) {
            $bank->questions()->delete();
            $nextOrder = 1;
        } else {
            $nextOrder = ((int) $bank->questions()->max('order_index')) + 1;
        }

        $inserted = 0;
        foreach ($parsedQuestions as $item) {
            $question = Question::create([
                'bank_id' => $bank->id,
                'question_text' => $item['question_text'],
                'explanation' => $item['explanation'] ?? null,
                'points' => max(1, min(255, (int) ($item['points'] ?? 10))),
                'order_index' => $nextOrder++,
            ]);

            foreach ($item['options'] as $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_label' => $opt['option_label'],
                    'option_text' => $opt['option_text'],
                    'is_correct' => (bool) $opt['is_correct'],
                ]);
            }

            $inserted++;
        }

        return $inserted;
    }

    private function flushCaches(int $bankId): void
    {
        Cache::forget('public_active_question_banks_v1');
        Cache::forget('quiz_bank_full_' . $bankId);
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

        $allQuestions = [];
        $warnings = [];
        $skipped = 0;
        $bestHeaders = [];
        $bestColumnMap = [];
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

            $headerResult = $this->locateHeaderRow($rows, $normalizedOverrides);
            $headerRowIdx = $headerResult['header_row'];
            $columnMap = $headerResult['column_map'];
            $headerNames = $headerResult['headers'];

            if (empty($columnMap) && ! empty($normalizedOverrides)) {
                $headerRowIdx = -1;
                $columnMap = $normalizedOverrides;
                $headerNames = array_map(fn (int $i): string => 'Kolom ' . ($i + 1), array_keys($rows[0]));
            }

            // Fallback headerless (mis. file tanpa baris header, langsung soal di baris 1)
            if (empty($columnMap)) {
                $inferred = $this->inferHeaderlessColumnMap($rows);
                if (! empty($inferred)) {
                    $headerRowIdx = -1;
                    $columnMap = $inferred;
                    $headerNames = array_map(fn (int $i): string => 'Kolom ' . ($i + 1), array_keys($rows[0]));
                }
            }

            if (empty($columnMap) || ! in_array('question_text', $columnMap, true)) {
                continue;
            }

            if (empty($bestHeaders)) {
                $bestHeaders = $headerNames;
                $bestColumnMap = $columnMap;
            }

            $dataStart = $headerRowIdx >= 0 ? $headerRowIdx + 1 : 0;
            $dataRows = array_slice($rows, $dataStart);

            if (empty($bestSampleRows)) {
                $bestSampleRows = array_slice($dataRows, 0, 2);
            }

            foreach ($dataRows as $offset => $row) {
                $displayRow = $dataStart + $offset + 1;
                $mapped = $this->mapRowWithColumnMap($row, $columnMap);
                $built = $this->buildNormalizedQuestion($mapped, $displayRow, "Sheet {$sheet['name']} Baris {$displayRow}");

                if ($built['warning'] !== null) {
                    $warnings[] = $built['warning'];
                }

                if ($built['question'] === null) {
                    if ($built['count_as_skipped']) {
                        $skipped++;
                    }
                    continue;
                }

                $allQuestions[] = $built['question'];
            }
        }

        $formatName = match ($kind) {
            'xlsx' => 'Excel (.xlsx)',
            'ods' => 'OpenDocument Spreadsheet (.ods)',
            'docx' => 'Word (.docx) - Format Tabel',
            'odt' => 'OpenDocument (.odt) - Format Tabel',
            'xml_spreadsheet', 'html_table' => 'Excel Web/XML (.xls)',
            'tsv' => 'TSV (Pemisah Tab)',
            default => 'CSV / Teks Terstruktur',
        };

        $errors = [];
        if (empty($allQuestions)) {
            $errors[] = empty($bestHeaders)
                ? 'Kolom pertanyaan, pilihan jawaban, dan kunci belum terdeteksi otomatis. Gunakan opsi Pemetaan Kolom atau cek struktur berkas.'
                : (! empty($warnings) ? $warnings[0] : 'Tidak ada baris soal yang memiliki minimal 2 opsi jawaban dan kunci yang valid.');
        }

        return [
            'success' => ! empty($allQuestions),
            'format_label' => $formatName,
            'is_tabular' => ! empty($bestHeaders),
            'detected_headers' => $bestHeaders,
            'column_map' => $bestColumnMap,
            'sample_rows' => $bestSampleRows,
            'questions' => $allQuestions,
            'total_found' => count($allQuestions),
            'skipped_count' => $skipped,
            'warnings' => array_slice($warnings, 0, 15),
            'errors' => $errors,
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

            $hasQuestion = in_array('question_text', $map, true);
            $optCount = count(array_filter($map, fn (string $f): bool => str_starts_with($f, 'option_')));
            $hasKey = in_array('correct_answer', $map, true);
            $score = count($map) + ($hasQuestion ? 10 : 0) + ($optCount >= 2 ? 8 : 0) + ($hasKey ? 5 : 0);

            if ($hasQuestion && ($optCount >= 2 || $hasKey) && $score > $bestScore) {
                $bestScore = $score;
                $bestIdx = $r;
                $bestMap = $map;
                $bestHeaders = array_values(array_map(fn ($v, $i): string => trim((string) $v) !== '' ? trim((string) $v) : 'Kolom ' . ($i + 1), $row, array_keys($row)));
            }
        }

        // Gabungkan override eksplisit jika ada header terpilih
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

        // Exact option letters or explicit numbered/lettered options FIRST so "jawaban a" != "kunci jawaban"
        $optionMap = [
            'option_a' => ['a', 'opsi a', 'pilihan a', 'jawaban a', 'option a', 'opt a', 'pil a', 'jwb a', 'choice a', 'opsi 1', 'pilihan 1', 'option 1', 'answer 1', 'choice 1', 'jawaban 1'],
            'option_b' => ['b', 'opsi b', 'pilihan b', 'jawaban b', 'option b', 'opt b', 'pil b', 'jwb b', 'choice b', 'opsi 2', 'pilihan 2', 'option 2', 'answer 2', 'choice 2', 'jawaban 2'],
            'option_c' => ['c', 'opsi c', 'pilihan c', 'jawaban c', 'option c', 'opt c', 'pil c', 'jwb c', 'choice c', 'opsi 3', 'pilihan 3', 'option 3', 'answer 3', 'choice 3', 'jawaban 3'],
            'option_d' => ['d', 'opsi d', 'pilihan d', 'jawaban d', 'option d', 'opt d', 'pil d', 'jwb d', 'choice d', 'opsi 4', 'pilihan 4', 'option 4', 'answer 4', 'choice 4', 'jawaban 4'],
            'option_e' => ['e', 'opsi e', 'pilihan e', 'jawaban e', 'option e', 'opt e', 'pil e', 'jwb e', 'choice e', 'opsi 5', 'pilihan 5', 'option 5', 'answer 5', 'choice 5', 'jawaban 5'],
        ];

        foreach ($optionMap as $field => $aliases) {
            foreach ($aliases as $alias) {
                if ($clean === $alias || (strlen($alias) > 1 && str_starts_with($clean, $alias . ' '))) {
                    return $field;
                }
            }
        }

        if (preg_match('/^(?:opsi|pilihan|jawaban|option|opt|pil|jwb|choice)\s+([a-e])\b/', $clean, $m)) {
            return 'option_' . $m[1];
        }

        // Correct answer
        foreach ([
            'kunci jawaban', 'kunci_jawaban', 'jawaban benar', 'kunci', 'correct answer', 'correct answer s',
            'answer key', 'kj', 'key', 'benarnya', 'jawaban tepat', 'correct option',
        ] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'correct_answer';
            }
        }
        if (in_array($clean, ['jawaban', 'answer', 'correct'], true)) {
            return 'correct_answer';
        }

        // Question text
        foreach ([
            'pertanyaan', 'butir soal', 'teks soal', 'teks pertanyaan', 'isi soal', 'kalimat soal',
            'kalimat pertanyaan', 'question text', 'question max 120 characters', 'naskah soal', 'prompt',
        ] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'question_text';
            }
        }
        if (in_array($clean, ['soal', 'question'], true)) {
            return 'question_text';
        }

        // Points
        foreach (['bobot poin', 'bobot nilai', 'bobot', 'poin', 'skor', 'points', 'point', 'score', 'weight', 'nilai'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'points';
            }
        }

        // Explanation
        foreach (['pembahasan', 'penjelasan', 'explanation', 'feedback', 'reason', 'uraian jawaban', 'keterangan'] as $kw) {
            if ($clean === $kw || str_contains($clean, $kw)) {
                return 'explanation';
            }
        }

        // Order index
        if (in_array($clean, ['no', 'nomor', 'urutan', 'urutan soal', 'order', 'order index', '#'], true)) {
            return 'order_index';
        }

        return null;
    }

    /**
     * @param  list<list<string>>  $rows
     * @return array<int, string>
     */
    private function inferHeaderlessColumnMap(array $rows): array
    {
        $first = $rows[0] ?? [];
        $colCount = count($first);
        if ($colCount < 4) {
            return [];
        }

        // Cek apakah kolom pertama berisi nomor urut 1, 2, ...
        $col0Numeric = preg_match('/^\d{1,3}\.?$/', trim($first[0] ?? '')) === 1;
        $startIdx = $col0Numeric ? 1 : 0;
        $remaining = $colCount - $startIdx;

        // Pola minimum: [Soal, A, B, C, D, Kunci] (6 kolom) atau [Soal, A, B, Kunci] (4 kolom)
        if ($remaining >= 6) {
            $map = [];
            if ($col0Numeric) {
                $map[0] = 'order_index';
            }
            $map[$startIdx] = 'question_text';
            $map[$startIdx + 1] = 'option_a';
            $map[$startIdx + 2] = 'option_b';
            $map[$startIdx + 3] = 'option_c';
            $map[$startIdx + 4] = 'option_d';

            $col5Key = $this->looksLikeKeyToken($first[$startIdx + 5] ?? '');
            $col6Key = isset($first[$startIdx + 6]) && $this->looksLikeKeyToken($first[$startIdx + 6]);

            if ($col5Key) {
                $map[$startIdx + 5] = 'correct_answer';
                if (isset($first[$startIdx + 6]) && is_numeric(trim($first[$startIdx + 6]))) {
                    $map[$startIdx + 6] = 'points';
                }
                if (isset($first[$startIdx + 7])) {
                    $map[$startIdx + 7] = 'explanation';
                }
            } elseif ($col6Key) {
                $map[$startIdx + 5] = 'option_e';
                $map[$startIdx + 6] = 'correct_answer';
                if (isset($first[$startIdx + 7]) && is_numeric(trim($first[$startIdx + 7]))) {
                    $map[$startIdx + 7] = 'points';
                }
                if (isset($first[$startIdx + 8])) {
                    $map[$startIdx + 8] = 'explanation';
                }
            } else {
                return [];
            }

            return $map;
        }

        return [];
    }

    private function looksLikeKeyToken(string $val): bool
    {
        $clean = strtoupper(trim($val));

        return preg_match('/^(?:OPSI|PILIHAN|JAWABAN)?\s*[A-E1-5][\.\)]?$/', $clean) === 1;
    }

    /**
     * @param  list<string>  $row
     * @param  array<int, string>  $columnMap
     * @return array<string, string>
     */
    private function mapRowWithColumnMap(array $row, array $columnMap): array
    {
        $mapped = [];
        foreach ($columnMap as $colIdx => $field) {
            if ($field !== 'ignore') {
                $mapped[$field] = trim((string) ($row[$colIdx] ?? ''));
            }
        }

        return $mapped;
    }

    /**
     * Mendukung tabel vertikal 2-kolom pada dokumen Word (contoh: Kolom 1 "Soal", Kolom 2 teks soal).
     *
     * @param  list<array{name: string, rows: list<list<string>>}>  $tables
     */
    private function parseVerticalDocxTables(array $tables): array
    {
        $questions = [];
        $warnings = [];
        $skipped = 0;

        foreach ($tables as $tIdx => $table) {
            $card = [];
            foreach ($table['rows'] as $row) {
                if (count($row) < 2) {
                    continue;
                }
                $field = $this->mapHeaderAlias($row[0]);
                if ($field !== null && $field !== 'ignore') {
                    $card[$field] = trim($row[1]);
                }
            }

            if (isset($card['question_text'])) {
                $built = $this->buildNormalizedQuestion($card, $tIdx + 1, "Tabel Word " . ($tIdx + 1));
                if ($built['warning'] !== null) {
                    $warnings[] = $built['warning'];
                }
                if ($built['question'] !== null) {
                    $questions[] = $built['question'];
                } elseif ($built['count_as_skipped']) {
                    $skipped++;
                }
            }
        }

        return [
            'success' => ! empty($questions),
            'format_label' => 'Word (.docx) - Kartu Tabel',
            'is_tabular' => false,
            'detected_headers' => [],
            'column_map' => [],
            'sample_rows' => [],
            'questions' => $questions,
            'total_found' => count($questions),
            'skipped_count' => $skipped,
            'warnings' => $warnings,
            'errors' => empty($questions) ? ['Tabel Word tidak berisi data soal yang lengkap.'] : [],
        ];
    }

    /**
     * Mem-parsing dokumen Word (.docx/.dotx/.odt) atau teks (.txt/.md/.rtf) berformat daftar bernomor maupun Aiken.
     *
     * @param  list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>  $lines
     */
    private function parseDocumentLines(array $lines, string $kind): array
    {
        [$contentLines, $answerKeyMap] = $this->extractEndOfDocAnswerKey($lines);

        $blocks = [];
        $current = null;
        $lastSeenSection = 'none'; // none | stem | option | meta

        foreach ($contentLines as $lineMeta) {
            $rawText = trim($lineMeta['text']);
            if ($rawText === '') {
                continue;
            }

            // Coba deteksi baris penanda eksplisit: "Soal 1:" / "Pertanyaan:"
            if (preg_match('/^(?:soal|pertanyaan)\s*(?:#|no\.?\s*)?(\d{1,3})?\s*[\:\.\)\-]\s*(.+)$/iu', $rawText, $m)) {
                if ($current !== null) {
                    $blocks[] = $current;
                }
                $current = $this->initQuestionBlock(trim($m[2]), $m[1] !== '' ? (int) $m[1] : null);
                $lastSeenSection = 'stem';
                continue;
            }

            // Cek apakah ini baris jawaban inline "Kunci: A", "ANSWER: B", "Jawaban Benar: C", "Bobot: 10", "Pembahasan: ..."
            if ($current !== null && $this->tryConsumeMetadataLine($current, $rawText)) {
                $lastSeenSection = 'meta';
                continue;
            }

            // Cek apakah ini baris opsi jawaban (termasuk opsi sejajar dlm 1 baris: "A. ...  B. ...")
            $inlineOptions = $this->splitInlineOptionSegments($rawText, $lineMeta);
            if ($inlineOptions !== null && $current !== null) {
                foreach ($inlineOptions as $opt) {
                    $current['options'][] = $opt;
                }
                $lastSeenSection = 'option';
                continue;
            }

            // Cek nomor soal baru: "1. ..." atau "1) ..."
            // Catatan penting: jika soal saat ini BELUM punya opsi & BELUM selesai meta, "1) ..." bisa jadi daftar bernomor DI DALAM stem soal!
            if (preg_match('/^(\d{1,3})\s*[\.\)]\s+(.+)$/u', $rawText, $m)) {
                $num = (int) $m[1];
                $shouldSplit = $current === null
                    || $lastSeenSection === 'meta'
                    || count($current['options']) >= 2
                    || ($current['number'] !== null && $num === $current['number'] + 1 && $lastSeenSection !== 'stem');

                if ($shouldSplit) {
                    if ($current !== null) {
                        $blocks[] = $current;
                    }
                    $current = $this->initQuestionBlock(trim($m[2]), $num);
                    $lastSeenSection = 'stem';
                    continue;
                }
            }

            // Format Aiken tanpa nomor soal: bila soal sebelumnya sudah punya >= 2 opsi atau meta kunci, baris teks baru memulai soal berikutnya
            if ($current === null || $lastSeenSection === 'meta' || count($current['options']) >= 2) {
                if ($current !== null) {
                    $blocks[] = $current;
                }
                $current = $this->initQuestionBlock($rawText, null);
                $lastSeenSection = 'stem';
                continue;
            }

            // Lanjutan stem multi-baris atau lanjutan opsi multi-baris
            if ($lastSeenSection === 'option' && ! empty($current['options'])) {
                $lastIdx = count($current['options']) - 1;
                $current['options'][$lastIdx]['option_text'] .= ' ' . $rawText;
            } elseif ($lastSeenSection === 'meta' && isset($current['explanation'])) {
                $current['explanation'] .= ' ' . $rawText;
            } else {
                $current['question_text'] .= "\n" . $rawText;
                $lastSeenSection = 'stem';
            }
        }

        if ($current !== null) {
            $blocks[] = $current;
        }

        $questions = [];
        $warnings = [];
        $skipped = 0;

        foreach ($blocks as $idx => $block) {
            $seq = $idx + 1;
            $qNum = $block['number'] ?? $seq;

            if ($block['correct_raw'] === '' && isset($answerKeyMap[$qNum])) {
                $block['correct_raw'] = $answerKeyMap[$qNum];
            }

            $built = $this->finalizeTextBlockQuestion($block, $seq);
            if ($built['warning'] !== null) {
                $warnings[] = $built['warning'];
            }

            if ($built['question'] !== null) {
                $questions[] = $built['question'];
            } elseif ($built['count_as_skipped']) {
                $skipped++;
            }
        }

        $label = match ($kind) {
            'docx' => 'Word (.docx) - Teks / Daftar Soal',
            'odt' => 'OpenDocument (.odt) - Teks / Daftar Soal',
            'rtf' => 'Rich Text (.rtf)',
            default => 'Teks (.txt / Aiken / Bernomor)',
        };

        $errors = [];
        if (empty($questions)) {
            $errors[] = ! empty($warnings)
                ? $warnings[0]
                : 'Tidak ada soal pilihan ganda terdeteksi. Contoh format yang didukung: "1. Pertanyaan", opsi "A. ... B. ...", lalu "Kunci: A" (atau cetak tebal/bintang pada opsi benar).';
        }

        return [
            'success' => ! empty($questions),
            'format_label' => $label,
            'is_tabular' => false,
            'detected_headers' => [],
            'column_map' => [],
            'sample_rows' => [],
            'questions' => $questions,
            'total_found' => count($questions),
            'skipped_count' => $skipped,
            'warnings' => array_slice($warnings, 0, 15),
            'errors' => $errors,
        ];
    }

    /**
     * @param  list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>  $lines
     * @return array{0: list<array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}>, 1: array<int, string>}
     */
    private function extractEndOfDocAnswerKey(array $lines): array
    {
        $keyStartIdx = null;
        $total = count($lines);

        for ($i = $total - 1; $i >= 0; $i--) {
            $t = trim($lines[$i]['text']);
            if ($t === '') {
                continue;
            }
            if (preg_match('/^(?:kunci\s*jawaban|daftar\s*kunci|answer\s*key)\s*[\:\-]?$/iu', $t)) {
                $keyStartIdx = $i;
                break;
            }
        }

        if ($keyStartIdx === null) {
            return [$lines, []];
        }

        $keyMap = [];
        for ($j = $keyStartIdx + 1; $j < $total; $j++) {
            $line = trim($lines[$j]['text']);
            if ($line === '') {
                continue;
            }
            if (preg_match_all('/(\d{1,3})\s*[\.\)\:\-\s]\s*([A-Ea-e])\b/u', $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $keyMap[(int) $m[1]] = strtoupper($m[2]);
                }
            }
        }

        if (empty($keyMap)) {
            return [$lines, []];
        }

        return [array_slice($lines, 0, $keyStartIdx), $keyMap];
    }

    /**
     * @return array<string, mixed>
     */
    private function initQuestionBlock(string $questionText, ?int $number): array
    {
        return [
            'number' => $number,
            'question_text' => $questionText,
            'options' => [],
            'correct_raw' => '',
            'points' => 10,
            'explanation' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function tryConsumeMetadataLine(array &$block, string $line): bool
    {
        if (preg_match('/^(?:kunci\s*jawaban|jawaban\s*benar|kunci|answer|correct(?:\s*answer)?|kj)\s*[\:\=\-]\s*(.+)$/iu', $line, $m)) {
            $block['correct_raw'] = trim($m[1]);

            return true;
        }

        if (preg_match('/^(?:poin|bobot(?:\s*poin|\s*nilai)?|skor|points?|score)\s*[\:\=\-]\s*(\d+)/iu', $line, $m)) {
            $block['points'] = max(1, min(255, (int) $m[1]));

            return true;
        }

        if (preg_match('/^(?:pembahasan|penjelasan|explanation|uraian)\s*[\:\=\-]\s*(.+)$/iu', $line, $m)) {
            $block['explanation'] = trim($m[1]);

            return true;
        }

        return false;
    }

    /**
     * Mendeteksi 1 atau beberapa opsi jawaban pada baris teks (mis. "A. ... B. ...").
     *
     * @param  array{text: string, is_bold: bool, is_underlined: bool, is_highlighted: bool, is_heading: bool}  $lineMeta
     * @return list<array<string, mixed>>|null
     */
    private function splitInlineOptionSegments(string $line, array $lineMeta): ?array
    {
        $pattern = '/^(?:\*|\[\s*[xX✓✔]\s*\]\s*)?(?:\(([A-Ea-e])\)|\[([A-Ea-e])\]|([A-Ea-e])[\.\)\:])\s+(.+)$/u';
        if (preg_match($pattern, $line) !== 1) {
            return null;
        }

        // Pisahkan opsi horizontal jika ada 2+ spasi atau tab diikuti B./C./D./E.
        $parts = preg_split(
            '/(?:\t+|\s{2,})(?=(?:\*|\[\s*[xX✓✔]\s*\]\s*)?(?:\([B-Eb-e]\)|\[[B-Eb-e]\]|[B-Eb-e][\.\)\:])\s+)/u',
            $line
        ) ?: [$line];

        $isSingleSegment = count($parts) === 1;
        $options = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (! preg_match($pattern, $part, $m)) {
                return null;
            }

            $letter = strtoupper($m[1] ?: ($m[2] ?: $m[3]));
            $rawOptionText = trim($m[4]);
            $hasTextMarker = str_starts_with($part, '*')
                || preg_match('/^\[\s*[xX✓✔]\s*\]/u', $part) === 1
                || $this->stripCorrectTextMarker($rawOptionText) !== $rawOptionText;

            $cleanText = $this->stripCorrectTextMarker($rawOptionText);
            $options[] = [
                'option_label' => $letter,
                'option_text' => $cleanText,
                'has_marker' => $hasTextMarker,
                'is_styled' => $isSingleSegment && ($lineMeta['is_bold'] || $lineMeta['is_highlighted'] || $lineMeta['is_underlined']),
            ];
        }

        return ! empty($options) ? $options : null;
    }

    private function parseJsonQuestions(string $filePath): array
    {
        $decoded = $this->reader->decodeJson($filePath);
        if (! is_array($decoded)) {
            return $this->emptyResult('Berkas JSON tidak valid.');
        }

        $items = isset($decoded['questions']) && is_array($decoded['questions'])
            ? $decoded['questions']
            : (array_is_list($decoded) ? $decoded : []);

        $questions = [];
        $warnings = [];
        $skipped = 0;

        foreach ($items as $idx => $item) {
            if (! is_array($item)) {
                continue;
            }
            $rowNum = $idx + 1;

            // Normalisasi format JSON nested options atau flat keys
            $flat = [];
            foreach ($item as $k => $v) {
                if ($k === 'options' && is_array($v)) {
                    $labels = ['A', 'B', 'C', 'D', 'E'];
                    foreach (array_values($v) as $optIdx => $optItem) {
                        if ($optIdx >= 5) {
                            break;
                        }
                        $fieldKey = 'option_' . strtolower($labels[$optIdx]);
                        if (is_array($optItem)) {
                            $flat[$fieldKey] = trim((string) ($optItem['option_text'] ?? $optItem['text'] ?? ''));
                            if (! empty($optItem['is_correct'])) {
                                $flat['correct_answer'] = $optItem['option_label'] ?? $labels[$optIdx];
                            }
                        } else {
                            $flat[$fieldKey] = trim((string) $optItem);
                        }
                    }
                    continue;
                }

                $alias = $this->mapHeaderAlias((string) $k);
                if ($alias !== null && ! is_array($v)) {
                    $flat[$alias] = trim((string) $v);
                } elseif (in_array((string) $k, ['question_text', 'explanation', 'points', 'order_index', 'correct_answer', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e'], true) && ! is_array($v)) {
                    $flat[(string) $k] = trim((string) $v);
                }
            }

            $built = $this->buildNormalizedQuestion($flat, $rowNum, "Item JSON #{$rowNum}");
            if ($built['warning'] !== null) {
                $warnings[] = $built['warning'];
            }
            if ($built['question'] !== null) {
                $questions[] = $built['question'];
            } elseif ($built['count_as_skipped']) {
                $skipped++;
            }
        }

        return [
            'success' => ! empty($questions),
            'format_label' => 'JSON Bank Soal',
            'is_tabular' => false,
            'detected_headers' => [],
            'column_map' => [],
            'sample_rows' => [],
            'questions' => $questions,
            'total_found' => count($questions),
            'skipped_count' => $skipped,
            'warnings' => $warnings,
            'errors' => empty($questions) ? ['Berkas JSON tidak memuat daftar soal yang valid.'] : [],
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @return array{question: array<string, mixed>|null, warning: string|null, count_as_skipped: bool}
     */
    private function buildNormalizedQuestion(array $row, int $defaultOrder, string $locationLabel): array
    {
        $rawQuestionText = trim((string) ($row['question_text'] ?? ''));
        if ($rawQuestionText === '') {
            return ['question' => null, 'warning' => null, 'count_as_skipped' => false];
        }

        $options = [];
        $markerCorrectIdx = null;
        $labels = ['A', 'B', 'C', 'D', 'E'];

        foreach ($labels as $idx => $label) {
            $rawOpt = trim((string) ($row['option_' . strtolower($label)] ?? ''));
            if ($rawOpt === '') {
                continue;
            }

            $cleanOpt = $this->stripCorrectTextMarker($rawOpt);
            if ($cleanOpt !== $rawOpt && $markerCorrectIdx === null) {
                $markerCorrectIdx = count($options);
            }

            $options[] = [
                'option_label' => $label,
                'option_text' => $cleanOpt,
                'is_correct' => false,
            ];
        }

        // Fallback: jika opsi ditulis langsung di dalam sel pertanyaan (A. ... B. ...)
        if (count($options) < 2) {
            [$extractedStem, $embeddedOptions] = $this->extractEmbeddedOptionsFromStem($rawQuestionText);
            if (count($embeddedOptions) >= 2) {
                $rawQuestionText = $extractedStem;
                $options = $embeddedOptions;
            }
        }

        if (count($options) < 2) {
            return [
                'question' => null,
                'warning' => "{$locationLabel}: dilewati karena jumlah pilihan jawaban kurang dari 2.",
                'count_as_skipped' => true,
            ];
        }

        // Re-index labels sequentially (A, B, C, ...) cleanly
        foreach ($options as $idx => &$opt) {
            $opt['option_label'] = $labels[$idx] ?? chr(65 + $idx);
        }
        unset($opt);

        $correctIdx = $this->resolveCorrectOptionIndex(
            trim((string) ($row['correct_answer'] ?? '')),
            $options,
            $markerCorrectIdx
        );

        if ($correctIdx === null) {
            return [
                'question' => null,
                'warning' => "{$locationLabel}: dilewati karena kunci jawaban tidak dikenali.",
                'count_as_skipped' => true,
            ];
        }

        $options[$correctIdx]['is_correct'] = true;

        $pointsRaw = trim((string) ($row['points'] ?? ''));
        $points = is_numeric($pointsRaw) ? max(1, min(255, (int) round((float) $pointsRaw))) : 10;

        $orderRaw = trim((string) ($row['order_index'] ?? ''));
        $order = is_numeric($orderRaw) ? max(1, (int) $orderRaw) : $defaultOrder;

        $explanation = trim((string) ($row['explanation'] ?? ''));

        return [
            'question' => [
                'question_text' => $rawQuestionText,
                'points' => $points,
                'order_index' => $order,
                'explanation' => $explanation !== '' ? $explanation : null,
                'options' => $options,
            ],
            'warning' => null,
            'count_as_skipped' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array{question: array<string, mixed>|null, warning: string|null, count_as_skipped: bool}
     */
    private function finalizeTextBlockQuestion(array $block, int $seq): array
    {
        $stem = trim((string) $block['question_text']);
        $rawOpts = $block['options'] ?? [];
        $qLabel = 'Soal #' . ($block['number'] ?? $seq);

        if ($stem === '' || count($rawOpts) < 2) {
            return [
                'question' => null,
                'warning' => $stem !== '' ? "{$qLabel}: dilewati karena pilihan jawaban kurang dari 2." : null,
                'count_as_skipped' => $stem !== '',
            ];
        }

        $markerIdx = null;
        $styledIndices = [];
        $labels = ['A', 'B', 'C', 'D', 'E'];
        $options = [];

        foreach (array_slice($rawOpts, 0, 5) as $idx => $opt) {
            if (! empty($opt['has_marker']) && $markerIdx === null) {
                $markerIdx = $idx;
            }
            if (! empty($opt['is_styled'])) {
                $styledIndices[] = $idx;
            }

            $options[] = [
                'option_label' => $opt['option_label'] ?: ($labels[$idx] ?? chr(65 + $idx)),
                'option_text' => trim((string) $opt['option_text']),
                'is_correct' => false,
            ];
        }

        // Jika tidak ada tulisan "Kunci:", pakai tanda * / [x], atau deteksi jika tepat 1 opsi dicetak tebal/highlight di Word
        $fallbackIdx = $markerIdx;
        if ($fallbackIdx === null && count($styledIndices) === 1 && count($options) >= 2) {
            $fallbackIdx = $styledIndices[0];
        }

        $correctIdx = $this->resolveCorrectOptionIndex((string) ($block['correct_raw'] ?? ''), $options, $fallbackIdx);
        if ($correctIdx === null) {
            return [
                'question' => null,
                'warning' => "{$qLabel}: dilewati karena kunci jawaban belum ditentukan.",
                'count_as_skipped' => true,
            ];
        }

        $options[$correctIdx]['is_correct'] = true;

        return [
            'question' => [
                'question_text' => $stem,
                'points' => max(1, min(255, (int) ($block['points'] ?? 10))),
                'order_index' => $block['number'] ?? $seq,
                'explanation' => $block['explanation'] ?? null,
                'options' => $options,
            ],
            'warning' => null,
            'count_as_skipped' => false,
        ];
    }

    /**
     * @param  list<array{option_label: string, option_text: string, is_correct: bool}>  $options
     */
    private function resolveCorrectOptionIndex(string $rawKey, array $options, ?int $fallbackIdx = null): ?int
    {
        $clean = trim($rawKey);

        if ($clean === '') {
            return $fallbackIdx;
        }

        // 1. Cek huruf tunggal/berawalan huruf: "A", "b.", "(C)", "Opsi B", "Pilihan D", "Jawaban A"
        $normalizedToken = strtoupper(trim(preg_replace('/^(?:OPSI|PILIHAN|JAWABAN|OPTION)\s*/i', '', $clean) ?? $clean));
        $normalizedToken = trim($normalizedToken, " \t\n\r\0\x0B.():[]");

        if (preg_match('/^[A-E]$/', $normalizedToken) === 1) {
            foreach ($options as $idx => $opt) {
                if (strtoupper($opt['option_label']) === $normalizedToken) {
                    return $idx;
                }
            }
            $pos = ord($normalizedToken) - ord('A');
            if (isset($options[$pos])) {
                return $pos;
            }
        }

        // 2. Cek angka 1..5 (format ekspor Quizizz / Kahoot / Google Forms)
        if (preg_match('/^[1-5]$/', $normalizedToken) === 1) {
            $pos = ((int) $normalizedToken) - 1;
            if (isset($options[$pos])) {
                return $pos;
            }
        }

        // 3. Cocokkan isi teks jawaban secara langsung (jika guru menulis isi teks jawaban benar di kolom Kunci)
        $targetText = mb_strtolower(preg_replace('/\s+/u', ' ', $clean) ?? $clean);
        foreach ($options as $idx => $opt) {
            $optNorm = mb_strtolower(preg_replace('/\s+/u', ' ', trim($opt['option_text'])) ?? '');
            if ($optNorm !== '' && $optNorm === $targetText) {
                return $idx;
            }
        }

        return $fallbackIdx;
    }

    private function stripCorrectTextMarker(string $text): string
    {
        $stripped = preg_replace('/^(?:\*\s*|\[\s*[xX✓✔]\s*\]\s*)/u', '', $text) ?? $text;
        $stripped = preg_replace('/\s*(?:\*|\(\s*benar\s*\)|\[\s*benar\s*\]|✓|✔)\s*$/iu', '', $stripped) ?? $stripped;

        return trim($stripped);
    }

    /**
     * @return array{0: string, 1: list<array<string, mixed>>}
     */
    private function extractEmbeddedOptionsFromStem(string $rawStem): array
    {
        $lines = preg_split("/\r\n|\r|\n/", $rawStem) ?: [];
        $stemLines = [];
        $options = [];
        $seenOption = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^(?:\*|\[\s*[xX✓✔]\s*\]\s*)?(?:\(([A-Ea-e])\)|([A-Ea-e])[\.\)])\s+(.+)$/u', $trimmed, $m)) {
                $seenOption = true;
                $letter = strtoupper($m[1] ?: $m[2]);
                $optText = $this->stripCorrectTextMarker($m[3]);
                $options[] = [
                    'option_label' => $letter,
                    'option_text' => $optText,
                    'is_correct' => false,
                ];
            } elseif (! $seenOption) {
                $stemLines[] = $trimmed;
            }
        }

        return [trim(implode("\n", $stemLines)), $options];
    }

    private function emptyResult(string $errorMessage): array
    {
        return [
            'success' => false,
            'format_label' => 'Tidak Dikenali',
            'is_tabular' => false,
            'detected_headers' => [],
            'column_map' => [],
            'sample_rows' => [],
            'questions' => [],
            'total_found' => 0,
            'skipped_count' => 0,
            'warnings' => [],
            'errors' => [$errorMessage],
        ];
    }

    /**
     * Contoh baris template standar soal pilihan ganda SMPN 4 Cepu.
     *
     * @return array{headers: list<string>, rows: list<list<string>>}
     */
    private static function sampleTemplateData(): array
    {
        $headers = [
            'No',
            'Pertanyaan',
            'Pilihan A',
            'Pilihan B',
            'Pilihan C',
            'Pilihan D',
            'Pilihan E (Opsional)',
            'Kunci Jawaban',
            'Bobot Poin',
            'Pembahasan',
        ];

        $rows = [
            [
                '1',
                'Kemampuan makhluk hidup untuk peka dan menanggapi rangsangan dari lingkungan disebut...',
                'Adaptasi',
                'Iritabilitas',
                'Respirasi',
                'Ekskresi',
                '',
                'B',
                '10',
                'Iritabilitas adalah kepekaan makhluk hidup terhadap rangsangan.',
            ],
            [
                '2',
                'Panjang sisi miring segitiga siku-siku dengan panjang sisi siku-siku 6 cm dan 8 cm adalah...',
                '9 cm',
                '10 cm',
                '12 cm',
                '14 cm',
                '',
                'B',
                '10',
                'Dengan Teorema Pythagoras: c = akar(6^2 + 8^2) = akar(100) = 10 cm.',
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
            ['PANDUAN IMPOR SOAL SMP NEGERI 4 CEPU'],
            ['1. Anda dapat menggunakan template resmi ini ATAU file Excel/CSV/Word/TXT milik Anda sendiri.'],
            ['2. Urutan maupun nama kolom bebas (contoh: Pertanyaan/Soal, Opsi A..E / Option 1..4, Kunci / Answer).'],
            ['3. Kunci Jawaban dapat diisi huruf (A/B/C/D/E), angka (1/2/3/4/5), atau isi teks jawaban langsung.'],
            ['4. Pilihan E dan Pembahasan bersifat opsional (boleh dikosongkan untuk soal SMP 4 opsi A-D).'],
        ];

        return FlexibleDocumentReader::buildXlsx([
            ['name' => 'Bank Soal', 'rows' => $rows],
            ['name' => 'Petunjuk', 'rows' => $guideRows],
        ]);
    }

    public static function getTemplateDocx(): string
    {
        $paragraphs = [
            'Anda boleh memakai format daftar bernomor seperti contoh di bawah INI, atau format tabel Word, atau berkas Word soal milik Anda sendiri:',
            '',
            '1. Kemampuan makhluk hidup untuk peka dan menanggapi rangsangan dari lingkungan disebut...',
            'A. Adaptasi',
            'B. Iritabilitas',
            'C. Respirasi',
            'D. Ekskresi',
            'Kunci: B',
            'Pembahasan: Iritabilitas adalah kemampuan menanggapi rangsangan.',
            '',
            '2. Berapakah hasil dari 3 x (4 + 2)?',
            'A. 14',
            'B. 18',
            'C. 20',
            'D. 24',
            'Kunci: B',
        ];

        return FlexibleDocumentReader::buildDocx('TEMPLATE IMPOR SOAL SMPN 4 CEPU', $paragraphs);
    }

    public static function getTemplateTxt(): string
    {
        return implode("\n", [
            '// Contoh Format Aiken / Daftar Bernomor (Guru bebas memilih gaya penulisan)',
            '1. Kemampuan makhluk hidup untuk peka dan menanggapi rangsangan disebut...',
            'A. Adaptasi',
            'B. Iritabilitas',
            'C. Respirasi',
            'D. Ekskresi',
            'Kunci: B',
            'Pembahasan: Iritabilitas adalah kepekaan makhluk hidup terhadap rangsangan.',
            '',
            'Hasil dari 3 x (4 + 2) adalah...',
            'A. 14',
            'B. 18',
            'C. 20',
            'D. 24',
            'ANSWER: B',
        ]);
    }

    public static function exportBankToCsv(QuestionBank $bank): string
    {
        $rows = self::buildBankExportRows($bank);
        $headers = array_shift($rows);

        return FlexibleDocumentReader::buildCsv($headers, $rows, ';');
    }

    public static function exportBankToXlsx(QuestionBank $bank): string
    {
        $rows = self::buildBankExportRows($bank);

        return FlexibleDocumentReader::buildXlsx([
            ['name' => 'Bank Soal', 'rows' => $rows],
        ]);
    }

    /**
     * @return list<list<string>>
     */
    private static function buildBankExportRows(QuestionBank $bank): array
    {
        $bank->loadMissing('questions.options');
        $rows = [self::sampleTemplateData()['headers']];
        $labels = ['A', 'B', 'C', 'D', 'E'];

        foreach ($bank->questions->sortBy('order_index')->values() as $idx => $q) {
            $opts = $q->options->values();
            $optTexts = ['', '', '', '', ''];
            $correctLabel = '';

            foreach ($opts as $optIdx => $opt) {
                if ($optIdx >= 5) {
                    break;
                }
                $optTexts[$optIdx] = (string) $opt->option_text;
                if ($opt->is_correct) {
                    $correctLabel = strtoupper((string) ($opt->option_label ?: $labels[$optIdx]));
                }
            }

            $rows[] = [
                (string) ($idx + 1),
                (string) $q->question_text,
                $optTexts[0],
                $optTexts[1],
                $optTexts[2],
                $optTexts[3],
                $optTexts[4],
                $correctLabel,
                (string) ($q->points ?? 10),
                (string) ($q->explanation ?? ''),
            ];
        }

        return $rows;
    }
}
