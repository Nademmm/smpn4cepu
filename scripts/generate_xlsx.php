<?php

$csvPath = __DIR__ . '/../storage/app/data_semua_akun_smpn4cepu.csv';
$xlsxPath = __DIR__ . '/../data_semua_akun_smpn4cepu.xlsx';

if (!file_exists($csvPath)) {
    die("CSV tidak ditemukan\n");
}

$rows = [];
if (($handle = fopen($csvPath, "r")) !== FALSE) {
    // Lewati BOM jika ada
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }
    while (($data = fgetcsv($handle)) !== FALSE) {
        $rows[] = $data;
    }
    fclose($handle);
}

$zip = new ZipArchive();
if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Gagal membuat file XLSX\n");
}

// 1. [Content_Types].xml
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStringTable+xml"/>
</Types>';
$zip->addFromString('[Content_Types].xml', $contentTypes);

// 2. _rels/.rels
$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
$zip->addFromString('_rels/.rels', $rels);

// 3. xl/_rels/workbook.xml.rels
$wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';
$zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

// 4. xl/workbook.xml
$wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Semua Akun SMPN 4 Cepu" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
$zip->addFromString('xl/workbook.xml', $wb);

// 5. Shared Strings & Sheet XML
$sharedStrings = [];
$stringIndexMap = [];

function getColLetter($index) {
    $letter = '';
    while ($index >= 0) {
        $letter = chr($index % 26 + 65) . $letter;
        $index = intdiv($index, 26) - 1;
    }
    return $letter;
}

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
$sheetXml .= '<sheetData>' . "\n";

foreach ($rows as $rIdx => $row) {
    $rowNum = $rIdx + 1;
    $sheetXml .= '<row r="' . $rowNum . '">' . "\n";
    foreach ($row as $cIdx => $val) {
        $cellRef = getColLetter($cIdx) . $rowNum;
        $val = trim((string)$val);
        if (str_starts_with($val, '="') && str_ends_with($val, '"')) {
            $val = substr($val, 2, -1);
        }
        
        if (!isset($stringIndexMap[$val])) {
            $idx = count($sharedStrings);
            $stringIndexMap[$val] = $idx;
            $sharedStrings[] = $val;
        } else {
            $idx = $stringIndexMap[$val];
        }
        
        $sheetXml .= '<c r="' . $cellRef . '" t="s"><v>' . $idx . '</v></c>' . "\n";
    }
    $sheetXml .= '</row>' . "\n";
}

$sheetXml .= '</sheetData>' . "\n";
$sheetXml .= '</worksheet>';

$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

// 6. xl/sharedStrings.xml
$sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$sstXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">' . "\n";
foreach ($sharedStrings as $str) {
    $cleanStr = htmlspecialchars($str, ENT_XML1, 'UTF-8');
    $sstXml .= '<si><t>' . $cleanStr . '</t></si>' . "\n";
}
$sstXml .= '</sst>';

$zip->addFromString('xl/sharedStrings.xml', $sstXml);
$zip->close();

echo "BERHASIL DIBUAT FILE EXCEL: {$xlsxPath}\n";
echo "Ukuran File: " . filesize($xlsxPath) . " bytes\n";
