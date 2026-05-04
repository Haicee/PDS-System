<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class ExportService
{
    public function buildPdsSubmissionsXlsx(array $columns, array $rows, array $colWidths): string
    {
        return $this->buildXlsx(
            'PDS Submissions',
            $this->pdsSubmissionsStylesXml(),
            $this->pdsSubmissionsSheetXml($columns, $rows, $colWidths)
        );
    }

    public function buildEmployeesXlsx(array $columns, array $rows, array $colWidths): string
    {
        return $this->buildXlsx(
            'Employees',
            $this->employeesStylesXml(),
            $this->genericSheetXml($columns, $rows, $colWidths)
        );
    }

    public function buildPdsDetailsXlsx(array $columns, array $rows, array $colWidths): string
    {
        return $this->buildXlsx(
            'PDS Details',
            $this->pdsDetailsStylesXml(),
            $this->pdsDetailsSheetXml($columns, $rows, $colWidths)
        );
    }

    private function buildXlsx(string $sheetName, string $stylesXml, string $sheetXml): string
    {
        $tmp = $this->openZip($zip);

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml('xl/workbook.xml', 'spreadsheetml.sheet.main+xml'));
        $zip->addFromString('_rels/.rels', $this->rootRelsXml('xl/workbook.xml', 'officeDocument'));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetName));
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
        return $this->readAndDelete($tmp);
    }

    public function buildPdsDocx(array $submission): string
    {
        $tmp = $this->openZip($zip);

        $safe = fn ($v) => htmlspecialchars((string) $v, ENT_XML1);

        $zip->addFromString('[Content_Types].xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>
XML);
        $zip->addFromString('_rels/.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
XML);
        $zip->addFromString('word/_rels/document.xml.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>
XML);

        $name      = $safe($submission['name'] ?? '—');
        $dept      = $safe($submission['department'] ?? '—');
        $email     = $safe($submission['email'] ?? '—');
        $submitted = $safe($submission['submitted_at'] ?? '—');
        $status    = $safe($submission['status'] ?? '—');
        $type      = $safe($submission['type'] ?? '—');

        $zip->addFromString('word/document.xml', <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    <w:p><w:r><w:t>PDS Submission</w:t></w:r></w:p>
    <w:p><w:r><w:t>Name: {$name}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Department: {$dept}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Email: {$email}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Submitted: {$submitted}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Type: {$type}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Status: {$status}</w:t></w:r></w:p>
    <w:p><w:r><w:t xml:space="preserve">Preview: see attached image or system record.</w:t></w:r></w:p>
    <w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>
  </w:body>
</w:document>
XML);

        $zip->close();
        return $this->readAndDelete($tmp);
    }

    private function openZip(?ZipArchive &$zip): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create archive.');
        }
        return $tmp;
    }

    private function readAndDelete(string $tmp): string
    {
        $content = file_get_contents($tmp);
        @unlink($tmp);
        return $content;
    }

    private function contentTypesXml(string $partName, string $contentTypeSuffix): string
    {
        $base = 'application/vnd.openxmlformats-officedocument.spreadsheetml';
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/{$partName}" ContentType="{$base}.{$contentTypeSuffix}"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="{$base}.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="{$base}.styles+xml"/>
</Types>
XML;
    }

    private function rootRelsXml(string $target, string $type): string
    {
        $typeNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/' . $type;
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="{$typeNs}" Target="{$target}"/>
</Relationships>
XML;
    }

    private function workbookRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(string $sheetName): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="{$sheetName}" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    private function pdsSubmissionsStylesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="12"/><color theme="1"/><name val="Arial"/></font>
    <font><sz val="12"/><color rgb="FFFFFFFF"/><name val="Arial"/><b/></font>
  </fonts>
  <fills count="6">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF4F46E5"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF22C55E"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF59E0B"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEF4444"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="5">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="3" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="4" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="5" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }

    private function employeesStylesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="12"/><color theme="1"/><name val="Arial"/></font>
    <font><sz val="12"/><color rgb="FFFFFFFF"/><name val="Arial"/><b/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0fb3e4"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center" wrapText="1"/>
    </xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }

    private function pdsSubmissionsSheetXml(array $columns, array $rows, array $colWidths): string
    {
        $colsXml = $this->colsXml($colWidths);
        $sheetRows = [$this->headerRowXml($columns)];
        $rowIndex = 1;

        foreach ($rows as $row) {
            $rowIndex++;
            $cells = '';
            $values = [
                $row['name'] ?? '',
                $row['department'] ?? '',
                $row['email'] ?? '',
                $row['submitted_at'] ?? '',
                $row['type'] ?? '',
                $row['status'] ?? '',
            ];
            $status = strtolower(trim($row['status'] ?? ''));
            $statusStyle = match ($status) {
                'approved' => 2,
                'pending'  => 3,
                'rejected' => 4,
                default    => 0,
            };
            foreach ($values as $colIndex => $value) {
                $styleId = ($colIndex === 5) ? $statusStyle : 0;
                $cells .= '<c r="' . chr(65 + $colIndex) . $rowIndex . '" t="inlineStr" s="' . $styleId . '"><is><t>' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
            }
            $sheetRows[] = '<row r="' . $rowIndex . '">' . $cells . '</row>';
        }

        return $this->wrapSheet($colsXml, $sheetRows);
    }

    private function genericSheetXml(array $columns, array $rows, array $colWidths): string
    {
        $colsXml = $this->colsXml($colWidths);
        $sheetRows = [$this->headerRowXml($columns)];
        $rowIndex = 1;
        $totalColumns = count($columns);

        foreach ($rows as $row) {
            $rowIndex++;
            $cells = '';
            $values = array_values($row);
            for ($colIndex = 0; $colIndex < $totalColumns; $colIndex++) {
                $value = $values[$colIndex] ?? '';
                $cells .= '<c r="' . chr(65 + $colIndex) . $rowIndex . '" t="inlineStr" s="0"><is><t>' . htmlspecialchars((string) $value, ENT_XML1) . '</t></is></c>';
            }
            $sheetRows[] = '<row r="' . $rowIndex . '">' . $cells . '</row>';
        }

        return $this->wrapSheet($colsXml, $sheetRows);
    }

    private function headerRowXml(array $columns): string
    {
        $cells = '';
        foreach ($columns as $colIndex => $value) {
            $cells .= '<c r="' . chr(65 + $colIndex) . '1" t="inlineStr" s="1"><is><t>' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
        }
        return '<row r="1">' . $cells . '</row>';
    }

    private function colsXml(array $colWidths): string
    {
        $xml = '<cols>';
        foreach (array_values($colWidths) as $i => $width) {
            $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1" />';
        }
        return $xml . '</cols>';
    }

    private function wrapSheet(string $colsXml, array $sheetRows): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $colsXml
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '</worksheet>';
    }

    private function pdsDetailsStylesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>
    <font><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/><b/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFB4C7E7"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FF000000"/></left>
      <right style="thin"><color rgb="FF000000"/></right>
      <top style="thin"><color rgb="FF000000"/></top>
      <bottom style="thin"><color rgb="FF000000"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFill="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }

    private function pdsDetailsSheetXml(array $columns, array $rows, array $colWidths): string
    {
        $colsXml = $this->colsXml($colWidths);
        $sheetRows = [$this->pdsDetailsHeaderRowXml($columns)];
        $rowIndex = 1;
        $totalColumns = count($columns);

        foreach ($rows as $row) {
            $rowIndex++;
            $cells = '';
            $values = array_values($row);
            for ($colIndex = 0; $colIndex < $totalColumns; $colIndex++) {
                $value = (string) ($values[$colIndex] ?? '');
                $ref = $this->columnLetter($colIndex) . $rowIndex;
                $cells .= '<c r="' . $ref . '" t="inlineStr" s="2"><is><t xml:space="preserve">' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
            }
            $sheetRows[] = '<row r="' . $rowIndex . '">' . $cells . '</row>';
        }

        return $this->wrapSheet($colsXml, $sheetRows);
    }

    private function pdsDetailsHeaderRowXml(array $columns): string
    {
        $cells = '';
        foreach ($columns as $colIndex => $value) {
            $ref = $this->columnLetter($colIndex) . '1';
            $cells .= '<c r="' . $ref . '" t="inlineStr" s="1"><is><t>' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
        }
        return '<row r="1" ht="42" customHeight="1">' . $cells . '</row>';
    }

    private function columnLetter(int $index): string
    {
        $letters = '';
        $n = $index;
        do {
            $letters = chr(65 + ($n % 26)) . $letters;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);
        return $letters;
    }
}
