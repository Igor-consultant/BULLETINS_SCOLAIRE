<?php

namespace Tests\Feature;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportCell;
use App\Models\HistoricalImportSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class HistoricalWorkbookStagingCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_workbook_command_stages_selected_sheet(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'test import',
        ])->assertSuccessful();

        $batch = HistoricalImportBatch::first();

        $this->assertNotNull($batch);
        $this->assertSame('completed', $batch->status);
        $this->assertSame(1, $batch->sheet_count);

        $sheet = HistoricalImportSheet::first();

        $this->assertNotNull($sheet);
        $this->assertSame('STA', $sheet->sheet_name);
        $this->assertGreaterThan(0, $sheet->non_empty_cell_count);

        $cell = HistoricalImportCell::query()
            ->where('sheet_name', 'STA')
            ->where('cell_reference', 'A1')
            ->first();

        $this->assertNotNull($cell);
        $this->assertSame('BULLETIN DE NOTES', $cell->display_value);
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
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="3" uniqueCount="3">
  <si><t>BULLETIN DE NOTES</t></si>
  <si><t>STA</t></si>
  <si><t>Année Scolaire 2021 - 2022</t></si>
</sst>
XML);

        $zip->addFromString('xl/worksheets/sheet1.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <dimension ref="A1:C2"/>
  <sheetData>
    <row r="1">
      <c r="A1" t="s"><v>0</v></c>
      <c r="B1" t="s"><v>1</v></c>
      <c r="C1" t="s"><v>2</v></c>
    </row>
    <row r="2">
      <c r="A2"><v>15</v></c>
      <c r="B2"><f>A2*2</f><v>30</v></c>
    </row>
  </sheetData>
</worksheet>
XML);

        $zip->close();
    }
}
