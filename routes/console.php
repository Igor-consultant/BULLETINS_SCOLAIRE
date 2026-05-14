<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Services\HistoricalWorkbookBulletinExtractionService;
use App\Services\HistoricalWorkbookNormalizationService;
use App\Services\HistoricalWorkbookStagingService;
use App\Services\HistoricalWorkbookValidationService;
use App\Services\HistoricalValidatedDataImportService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bulletins:stage-historical-workbook {path?} {--sheet=*} {--max-rows=} {--label=}', function (HistoricalWorkbookStagingService $service) {
    $path = $this->argument('path') ?: 'C:\\BULLETINS_SCOLAIRE\\BULLETINS AMENAGES 2021 - 2024.xlsx';
    $sheets = $this->option('sheet');
    $maxRows = $this->option('max-rows');
    $label = $this->option('label');

    $batch = $service->stage(
        $path,
        $sheets !== [] ? $sheets : null,
        $maxRows !== null ? (int) $maxRows : null,
        $label ?: null,
    );

    $this->info('Batch cree: #'.$batch->id);
    $this->line('Source: '.$batch->source_filename);
    $this->line('Onglets: '.$batch->sheet_count);
    $this->line('Lignes stagees: '.$batch->row_count);
    $this->line('Cellules stagees: '.$batch->cell_count);
    $this->line('Formules detectees: '.$batch->formula_count);

    foreach ($batch->sheets as $sheet) {
        $this->line(sprintf(
            '- %s: %d lignes, %d cellules, %d formules',
            $sheet->sheet_name,
            $sheet->row_count,
            $sheet->non_empty_cell_count,
            $sheet->formula_cell_count,
        ));
    }
})->purpose('Stage le classeur historique Excel des bulletins dans les tables de migration');

Artisan::command('bulletins:normalize-historical-workbook {batch_id} {--sheet=*}', function (HistoricalWorkbookNormalizationService $service) {
    $batchId = (int) $this->argument('batch_id');
    $sheets = $this->option('sheet');

    $service->detectPanelsAndStudents($batchId, $sheets !== [] ? $sheets : null);

    $panels = DB::table('historical_import_panels')
        ->where('batch_id', $batchId)
        ->orderBy('sheet_name')
        ->orderBy('panel_index')
        ->get();

    $this->info('Normalisation de structure terminee pour le batch #'.$batchId);

    foreach ($panels as $panel) {
        $this->line(sprintf(
            '- %s panneau %d [%s:%s] : %d eleves candidats, %d bulletins detectes',
            $panel->sheet_name,
            $panel->panel_index,
            $panel->start_column_letters,
            $panel->end_column_letters,
            $panel->detected_student_count,
            $panel->detected_bulletin_count,
        ));
    }

    $rosters = DB::table('historical_import_rosters')
        ->where('batch_id', $batchId)
        ->select('sheet_name', DB::raw('COUNT(*) AS unique_students'))
        ->groupBy('sheet_name')
        ->get();

    foreach ($rosters as $roster) {
        $this->line(sprintf(
            '  > roster %s : %d eleves uniques',
            $roster->sheet_name,
            $roster->unique_students,
        ));
    }
})->purpose('Detecte les panneaux et les eleves candidats dans le staging historique');

Artisan::command('bulletins:extract-historical-bulletins {batch_id} {--sheet=*}', function (HistoricalWorkbookBulletinExtractionService $service) {
    $batchId = (int) $this->argument('batch_id');
    $sheets = $this->option('sheet');

    $service->extract($batchId, $sheets !== [] ? $sheets : null);

    $bulletins = DB::table('historical_import_bulletins')
        ->where('batch_id', $batchId)
        ->select('sheet_name', DB::raw('COUNT(*) AS bulletin_count'), DB::raw('SUM(subject_line_count) AS subject_lines'))
        ->groupBy('sheet_name')
        ->get();

    foreach ($bulletins as $bulletin) {
        $this->line(sprintf(
            '%s : %d bulletins extraits, %d lignes matieres',
            $bulletin->sheet_name,
            $bulletin->bulletin_count,
            $bulletin->subject_lines,
        ));
    }
})->purpose('Extrait les bulletins historiques et leurs lignes de resultats depuis le staging');

Artisan::command('bulletins:validate-historical-results {batch_id} {--sheet=*}', function (HistoricalWorkbookValidationService $service) {
    $batchId = (int) $this->argument('batch_id');
    $sheets = $this->option('sheet');

    $service->validate($batchId, $sheets !== [] ? $sheets : null);

    $summary = DB::table('historical_import_validated_bulletins')
        ->where('batch_id', $batchId)
        ->select('sheet_name', DB::raw('COUNT(*) AS bulletins'))
        ->groupBy('sheet_name')
        ->get();

    foreach ($summary as $row) {
        $lineCount = DB::table('historical_import_validated_results')
            ->where('batch_id', $batchId)
            ->where('sheet_name', $row->sheet_name)
            ->count();

        $this->line(sprintf(
            '%s : %d bulletins valides, %d lignes validees',
            $row->sheet_name,
            $row->bulletins,
            $lineCount,
        ));
    }
})->purpose('Construit un jeu valide dedoublonne eleve plus trimestre plus matiere a partir des bulletins historiques');

Artisan::command('bulletins:import-historical-results {batch_id} {--sheet=*}', function (HistoricalValidatedDataImportService $service) {
    $batchId = (int) $this->argument('batch_id');
    $sheets = $this->option('sheet');

    $service->import($batchId, $sheets !== [] ? $sheets : null);

    $summary = DB::table('historical_import_finalizations')
        ->where('batch_id', $batchId)
        ->select(
            'sheet_name',
            'academic_year_label',
            'class_code',
            'imported_student_count',
            'imported_bulletin_count',
            'imported_result_count'
        )
        ->orderBy('sheet_name')
        ->get();

    foreach ($summary as $row) {
        $this->line(sprintf(
            '%s [%s / %s] : %d eleves, %d bulletins, %d resultats importes',
            $row->sheet_name,
            $row->academic_year_label ?? 'annee inconnue',
            $row->class_code ?? 'classe inconnue',
            $row->imported_student_count,
            $row->imported_bulletin_count,
            $row->imported_result_count,
        ));
    }
})->purpose('Injecte le jeu valide historique dans les tables metier avec tracabilite');
