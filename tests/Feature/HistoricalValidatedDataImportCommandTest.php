<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportFinalization;
use App\Models\HistoricalImportValidatedBulletin;
use App\Models\HistoricalImportValidatedResult;
use App\Models\HistoricalImportResultMapping;
use App\Models\Inscription;
use App\Models\Matiere;
use App\Models\Resultat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class HistoricalValidatedDataImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_command_creates_final_referentials_and_results(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins-import.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'import test',
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

        $this->artisan('bulletins:import-historical-results', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $annee = AnneeScolaire::where('libelle', '2021-2022')->first();
        $classe = Classe::where('code', 'STA')->where('annee_scolaire_id', $annee?->id)->first();
        $eleve = Eleve::first();
        $resultat = Resultat::first();
        $matiere = Matiere::where('libelle', 'Mathematiques')->first();

        $this->assertNotNull($annee);
        $this->assertNotNull($classe);
        $this->assertNotNull($eleve);
        $this->assertNotNull($matiere);
        $this->assertNotNull($resultat);
        $this->assertSame('Alice', $eleve->nom);
        $this->assertSame('Mbemba', $eleve->prenoms);
        $this->assertSame('historique_importe', $resultat->statut_calcul);
        $this->assertCount(1, HistoricalImportFinalization::all());
        $this->assertCount(1, HistoricalImportResultMapping::all());
    }

    public function test_import_command_is_idempotent_for_students_and_results(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins-import-idempotent.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'import idempotent test',
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

        $this->artisan('bulletins:import-historical-results', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $firstEleves = Eleve::count();
        $firstInscriptions = Inscription::count();
        $firstResultats = Resultat::count();

        $this->artisan('bulletins:import-historical-results', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $this->assertSame($firstEleves, Eleve::count());
        $this->assertSame($firstInscriptions, Inscription::count());
        $this->assertSame($firstResultats, Resultat::count());
    }

    public function test_import_command_splits_finalization_by_academic_year_on_same_sheet(): void
    {
        $path = storage_path('framework/testing/minimal-bulletins-import-multi-year.xlsx');
        $this->createMinimalWorkbook($path);

        $this->artisan('bulletins:stage-historical-workbook', [
            'path' => $path,
            '--sheet' => ['STA'],
            '--label' => 'import multi year test',
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

        $validatedBulletin = HistoricalImportValidatedBulletin::firstOrFail();
        $validatedResult = HistoricalImportValidatedResult::firstOrFail();

        $validatedBulletin->update([
            'academic_year_label' => 'Année Scolaire 2020 - 2021',
        ]);

        $secondBulletin = HistoricalImportValidatedBulletin::create([
            'batch_id' => $validatedBulletin->batch_id,
            'sheet_id' => $validatedBulletin->sheet_id,
            'roster_id' => null,
            'source_bulletin_id' => $validatedBulletin->source_bulletin_id,
            'sheet_name' => $validatedBulletin->sheet_name,
            'trimester_label' => 'Du 2ème Trimestre',
            'student_name' => 'Bob Mbemba',
            'student_number' => 2,
            'class_code' => $validatedBulletin->class_code,
            'class_label' => $validatedBulletin->class_label,
            'academic_year_label' => 'Année Scolaire 2021 - 2022',
            'source_subject_line_count' => $validatedBulletin->source_subject_line_count,
            'metadata' => [],
        ]);

        HistoricalImportValidatedResult::create([
            'batch_id' => $validatedResult->batch_id,
            'validated_bulletin_id' => $secondBulletin->id,
            'source_line_id' => $validatedResult->source_line_id,
            'sheet_name' => $validatedResult->sheet_name,
            'trimester_label' => 'Du 2ème Trimestre',
            'student_name' => 'Bob Mbemba',
            'student_number' => 2,
            'subject_label_original' => $validatedResult->subject_label_original,
            'subject_label_normalized' => $validatedResult->subject_label_normalized,
            'note_classe' => $validatedResult->note_classe,
            'composition' => $validatedResult->composition,
            'moyenne_sur_20' => $validatedResult->moyenne_sur_20,
            'coefficient' => $validatedResult->coefficient,
            'points' => $validatedResult->points,
            'rang' => $validatedResult->rang,
            'teacher_name' => $validatedResult->teacher_name,
            'appreciation' => $validatedResult->appreciation,
            'metadata' => [],
        ]);

        $this->artisan('bulletins:import-historical-results', [
            'batch_id' => $batch->id,
            '--sheet' => ['STA'],
        ])->assertSuccessful();

        $this->assertCount(2, HistoricalImportFinalization::all());
        $this->assertSame(
            ['2020-2021', '2021-2022'],
            HistoricalImportFinalization::query()->orderBy('academic_year_label')->pluck('academic_year_label')->all()
        );
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
      <c r="Q175" t="s"><v>9</v></c>
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
  </sheetData>
</worksheet>
XML);

        $zip->close();
    }
}
