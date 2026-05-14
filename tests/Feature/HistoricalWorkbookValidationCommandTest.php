<?php

namespace Tests\Feature;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportValidatedBulletin;
use App\Models\HistoricalImportValidatedResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class HistoricalWorkbookValidationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_command_deduplicates_and_filters_placeholder_subjects(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins-validate.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'validate test',
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

        $this->artisan('bulletins:validate-historical-results', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $validatedBulletin = HistoricalImportValidatedBulletin::first();
        $results = HistoricalImportValidatedResult::query()
            ->orderBy('subject_label_normalized')
            ->get();

        $this->assertNotNull($validatedBulletin);
        $this->assertSame('Alice Mbemba', $validatedBulletin->student_name);
        $this->assertCount(1, HistoricalImportValidatedBulletin::all());
        $this->assertCount(1, $results);
        $this->assertSame('Mathematiques', $results->first()->subject_label_normalized);
    }

    public function test_validation_prefers_candidate_with_academic_year_when_duplicates_exist(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins-validate-prefers-year.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'validate prefer year test',
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

        $source = \App\Models\HistoricalImportBulletin::firstOrFail();
        $source->update([
            'academic_year_label' => 'AnnÃ©e Scolaire 2021 - 2022',
        ]);

        \App\Models\HistoricalImportBulletin::create([
            'batch_id' => $source->batch_id,
            'sheet_id' => $source->sheet_id,
            'panel_id' => $source->panel_id,
            'roster_id' => $source->roster_id,
            'sheet_name' => $source->sheet_name,
            'panel_index' => $source->panel_index,
            'anchor_row_index' => $source->anchor_row_index + 100,
            'anchor_cell' => 'B999',
            'trimester_label' => $source->trimester_label,
            'student_name' => $source->student_name,
            'student_number' => $source->student_number,
            'class_code' => $source->class_code,
            'class_label' => $source->class_label,
            'academic_year_label' => null,
            'subject_line_count' => $source->subject_line_count,
            'metadata' => [],
        ]);

        $this->artisan('bulletins:validate-historical-results', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $validatedBulletin = HistoricalImportValidatedBulletin::firstOrFail();

        $this->assertSame('AnnÃ©e Scolaire 2021 - 2022', $validatedBulletin->academic_year_label);
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
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="14" uniqueCount="14">
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
  <si><t>DISCIPLINE 15</t></si>
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
    <row r="180">
      <c r="C180"><v>0</v></c>
      <c r="D180"><v>0</v></c>
      <c r="E180"><v>0</v></c>
      <c r="F180"><v>0</v></c>
      <c r="G180"><v>0</v></c>
      <c r="H180"><v>1</v></c>
      <c r="I180"><v>0</v></c>
      <c r="J180" t="s"><v>8</v></c>
      <c r="Q180" t="s"><v>13</v></c>
    </row>
  </sheetData>
</worksheet>
XML);

        $zip->close();
    }
}
