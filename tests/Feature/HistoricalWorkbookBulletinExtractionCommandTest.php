<?php

namespace Tests\Feature;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportBulletin;
use App\Models\HistoricalImportBulletinLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class HistoricalWorkbookBulletinExtractionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulletin_extraction_command_creates_bulletin_and_lines(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins-extract.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'extract test',
        ])->assertSuccessful();

        $batch = HistoricalImportBatch::firstOrFail();

        $this->artisan('bulletins:normalize-historical-workbook', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $this->artisan('bulletins:extract-historical-bulletins', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $bulletin = HistoricalImportBulletin::first();
        $line = HistoricalImportBulletinLine::first();

        $this->assertNotNull($bulletin);
        $this->assertSame('Alice Mbemba', $bulletin->student_name);
        $this->assertSame('Du 1er Trimestre', $bulletin->trimester_label);

        $this->assertNotNull($line);
        $this->assertSame('MATHEMATIQUES', $line->subject_label);
        $this->assertSame('12.5000', (string) $line->moyenne_sur_20);
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
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="13" uniqueCount="13">
  <si><t>NOMS ET PRENOMS</t></si>
  <si><t>Alice Mbemba</t></si>
  <si><t>BULLETIN DE NOTES</t></si>
  <si><t>Du 1er Trimestre</t></si>
  <si><t>STA</t></si>
  <si><t>TRONC COMMUN INDUSTRIEL</t></si>
  <si><t>MATHEMATIQUES</t></si>
  <si><t>M. Prof</t></si>
  <si><t>Assez bien</t></si>
  <si><t>Année Scolaire 2021 - 2022</t></si>
  <si><t>NOTE DE CLASSE</t></si>
  <si><t>COMPOS.</t></si>
  <si><t>MOYENNE                                                            SUR 20</t></si>
</sst>
XML);

        $zip->addFromString('xl/worksheets/sheet1.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <dimension ref="A8:Q205"/>
  <sheetData>
    <row r="8">
      <c r="B8" t="s"><v>0</v></c>
    </row>
    <row r="9">
      <c r="A9"><v>1</v></c>
      <c r="B9" t="s"><v>1</v></c>
    </row>
    <row r="170">
      <c r="B170" t="s"><v>2</v></c>
    </row>
    <row r="171">
      <c r="B171" t="s"><v>3</v></c>
    </row>
    <row r="172">
      <c r="F172"><v>1</v></c>
    </row>
    <row r="173">
      <c r="C173" t="s"><v>1</v></c>
    </row>
    <row r="175">
      <c r="F175" t="s"><v>4</v></c>
      <c r="H175" t="s"><v>5</v></c>
    </row>
    <row r="178">
      <c r="C178" t="s"><v>10</v></c>
      <c r="D178" t="s"><v>11</v></c>
      <c r="E178" t="s"><v>12</v></c>
    </row>
    <row r="179">
      <c r="C179"><v>14</v></c>
      <c r="D179"><v>11</v></c>
      <c r="E179"><v>12.5</v></c>
      <c r="F179"><v>2</v></c>
      <c r="G179"><v>25</v></c>
      <c r="H179"><v>3</v></c>
      <c r="I179" t="s"><v>7</v></c>
      <c r="J179" t="s"><v>8</v></c>
      <c r="Q179" t="s"><v>6</v></c>
    </row>
    <row r="204">
      <c r="J204" t="s"><v>9</v></c>
    </row>
  </sheetData>
</worksheet>
XML);

        $zip->close();
    }
}
