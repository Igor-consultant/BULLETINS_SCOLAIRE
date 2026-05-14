<?php

namespace Tests\Feature;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportPanel;
use App\Models\HistoricalImportStudentCandidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class HistoricalWorkbookNormalizationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalization_command_detects_panel_and_students(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins-normalize.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'normalize test',
        ])->assertSuccessful();

        $batch = HistoricalImportBatch::firstOrFail();

        $this->artisan('bulletins:normalize-historical-workbook', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $panel = HistoricalImportPanel::first();
        $student = HistoricalImportStudentCandidate::query()->orderBy('excel_row_index')->first();

        $this->assertNotNull($panel);
        $this->assertSame('STA', $panel->sheet_name);
        $this->assertSame('B', $panel->start_column_letters);
        $this->assertSame(2, $panel->detected_student_count);

        $this->assertNotNull($student);
        $this->assertSame('Alice Mbemba', $student->student_name);
        $this->assertSame(1, $student->student_number);
    }

    protected function createMinimalWorkbook(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (is_file($path)) {
            unlink($path);
        }

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="STA" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/sharedStrings.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="7" uniqueCount="7">
  <si><t>NOMS ET PRENOMS</t></si>
  <si><t>Alice Mbemba</t></si>
  <si><t>Bob Ngoma</t></si>
  <si><t>BULLETIN DE NOTES</t></si>
  <si><t>Du 1er Trimestre</t></si>
  <si><t>Conception</t></si>
  <si><t>Professeur X</t></si>
</sst>
XML);

        $zip->addFromString('xl/worksheets/sheet1.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <dimension ref="A8:L11"/>
  <sheetData>
    <row r="8">
      <c r="B8" t="s"><v>0</v></c>
    </row>
    <row r="9">
      <c r="A9"><v>1</v></c>
      <c r="B9" t="s"><v>1</v></c>
      <c r="L9" t="s"><v>6</v></c>
    </row>
    <row r="10">
      <c r="A10"><v>2</v></c>
      <c r="B10" t="s"><v>2</v></c>
      <c r="L10" t="s"><v>6</v></c>
    </row>
    <row r="11">
      <c r="B11" t="s"><v>3</v></c>
      <c r="C11" t="s"><v>4</v></c>
      <c r="D11" t="s"><v>5</v></c>
    </row>
  </sheetData>
</worksheet>
XML);

        $zip->close();
    }
}
