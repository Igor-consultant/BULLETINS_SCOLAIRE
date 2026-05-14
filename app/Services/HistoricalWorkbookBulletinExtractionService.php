<?php

namespace App\Services;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportBulletin;
use App\Models\HistoricalImportBulletinLine;
use App\Models\HistoricalImportPanel;
use App\Models\HistoricalImportRoster;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalWorkbookBulletinExtractionService
{
    public function extract(int $batchId, ?array $onlySheets = null): HistoricalImportBatch
    {
        $batch = HistoricalImportBatch::with('sheets')->findOrFail($batchId);
        $sheetNames = $onlySheets !== null && $onlySheets !== [] ? $onlySheets : $batch->sheets->pluck('sheet_name')->all();

        DB::table('historical_import_bulletin_lines')->where('batch_id', $batchId)->delete();
        DB::table('historical_import_bulletins')->where('batch_id', $batchId)->delete();

        foreach ($sheetNames as $sheetName) {
            $sheet = $batch->sheets->firstWhere('sheet_name', $sheetName);

            if (! $sheet) {
                throw new RuntimeException("Onglet non stage pour ce batch: {$sheetName}");
            }

            $panels = HistoricalImportPanel::query()
                ->where('batch_id', $batchId)
                ->where('sheet_id', $sheet->id)
                ->orderBy('panel_index')
                ->get();

            foreach ($panels as $panel) {
                $this->extractPanelBulletins($batch->id, $sheet->id, $sheetName, $panel);
            }
        }

        return $batch->fresh();
    }

    protected function extractPanelBulletins(int $batchId, int $sheetId, string $sheetName, HistoricalImportPanel $panel): void
    {
        $anchorRows = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereBetween('column_index', [$panel->start_column_index, $panel->end_column_index])
            ->where('display_value', 'BULLETIN DE NOTES')
            ->orderBy('row_index')
            ->get(['row_index', 'cell_reference']);

        $sheetRoster = HistoricalImportRoster::query()
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->orderByRaw('COALESCE(best_student_number, 9999)')
            ->orderBy('first_row_index')
            ->orderBy('student_name')
            ->get();

        foreach ($anchorRows as $anchorIndex => $anchor) {
            $anchorRow = (int) $anchor->row_index;

            $studentName = $this->findStudentNameAroundAnchor($batchId, $sheetId, $panel, $anchorRow);
            $rawStudentName = $studentName;
            $trimesterLabel = $this->findTrimesterLabelAroundAnchor($batchId, $sheetId, $panel, $anchorRow);
            $classCode = $this->findClassCodeAroundAnchor($batchId, $sheetId, $panel, $anchorRow);
            $classLabel = $this->findClassLabelAroundAnchor($batchId, $sheetId, $panel, $anchorRow);
            $academicYearLabel = $this->findNearbyAcademicYear($batchId, $sheetId, $panel, $anchorRow);
            $studentNumber = $this->findStudentNumberAroundAnchor($batchId, $sheetId, $panel, $anchorRow);
            $roster = $studentName !== null
                ? $sheetRoster->firstWhere('student_name', $studentName)
                : null;

            if (! $this->looksLikeStudentName($studentName)) {
                $roster = $this->inferRosterFromAnchorSequence($sheetRoster, $anchorIndex);
                $studentName = $roster?->student_name;
                $studentNumber ??= $roster?->best_student_number;
            }

            if (! $this->looksLikeStudentName($studentName)) {
                continue;
            }

            if ($roster?->best_student_number !== null) {
                $studentNumber = $roster->best_student_number;
            }

            $bulletin = HistoricalImportBulletin::create([
                'batch_id' => $batchId,
                'sheet_id' => $sheetId,
                'panel_id' => $panel->id,
                'roster_id' => $roster?->id,
                'sheet_name' => $sheetName,
                'panel_index' => $panel->panel_index,
                'anchor_row_index' => $anchorRow,
                'anchor_cell' => $anchor->cell_reference,
                'trimester_label' => $trimesterLabel,
                'student_name' => $studentName,
                'student_number' => $studentNumber,
                'class_code' => $classCode,
                'class_label' => $classLabel,
                'academic_year_label' => $academicYearLabel,
                'metadata' => [
                    'panel_start' => $panel->start_column_letters,
                    'panel_end' => $panel->end_column_letters,
                    'fallback_roster' => $roster !== null && ! $this->looksLikeStudentName($rawStudentName),
                ],
            ]);

            $lineCount = 0;

            foreach (range($anchorRow + 9, $anchorRow + 30) as $lineRow) {
                $subjectLabel = $this->findSubjectLabel($batchId, $sheetId, $panel, $lineRow);

                if ($subjectLabel === null) {
                    continue;
                }

                HistoricalImportBulletinLine::create([
                    'batch_id' => $batchId,
                    'bulletin_id' => $bulletin->id,
                    'sheet_name' => $sheetName,
                    'panel_index' => $panel->panel_index,
                    'line_row_index' => $lineRow,
                    'subject_label' => $subjectLabel,
                    'note_classe' => $this->parseNullableDecimal($this->readCellValue($batchId, $sheetId, $panel->start_column_index + 1, $lineRow)),
                    'composition' => $this->parseNullableDecimal($this->readCellValue($batchId, $sheetId, $panel->start_column_index + 2, $lineRow)),
                    'moyenne_sur_20' => $this->parseNullableDecimal($this->readCellValue($batchId, $sheetId, $panel->start_column_index + 3, $lineRow)),
                    'coefficient' => $this->parseNullableDecimal($this->readCellValue($batchId, $sheetId, $panel->start_column_index + 4, $lineRow)),
                    'points' => $this->parseNullableDecimal($this->readCellValue($batchId, $sheetId, $panel->start_column_index + 5, $lineRow)),
                    'rang' => $this->parseNullableInt($this->readCellValue($batchId, $sheetId, $panel->start_column_index + 6, $lineRow)),
                    'teacher_name' => $this->readCellValue($batchId, $sheetId, $panel->start_column_index + 7, $lineRow),
                    'appreciation' => $this->readCellValue($batchId, $sheetId, $panel->start_column_index + 8, $lineRow),
                    'metadata' => [
                        'source_row' => $lineRow,
                    ],
                ]);

                $lineCount++;
            }

            $bulletin->update([
                'subject_line_count' => $lineCount,
            ]);
        }
    }

    protected function findNearbyAcademicYear(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $anchorRow): ?string
    {
        $value = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereBetween('row_index', [$anchorRow, $anchorRow + 60])
            ->whereBetween('column_index', [$panel->start_column_index, $panel->end_column_index])
            ->where('display_value', 'like', 'Année Scolaire %')
            ->orderBy('row_index')
            ->value('display_value');

        return $value !== null ? (string) $value : null;
    }

    protected function findStudentNameAroundAnchor(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $anchorRow): ?string
    {
        $cells = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereBetween('row_index', [$anchorRow + 2, $anchorRow + 6])
            ->whereBetween('column_index', [$panel->start_column_index + 1, $panel->start_column_index + 3])
            ->orderBy('row_index')
            ->orderBy('column_index')
            ->pluck('display_value');

        foreach ($cells as $value) {
            $text = trim((string) $value);

            if ($this->looksLikeStudentName($text)) {
                return $text;
            }
        }

        return null;
    }

    protected function findTrimesterLabelAroundAnchor(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $anchorRow): ?string
    {
        $cells = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereBetween('row_index', [$anchorRow, $anchorRow + 3])
            ->whereBetween('column_index', [$panel->start_column_index, $panel->start_column_index + 3])
            ->orderBy('row_index')
            ->orderBy('column_index')
            ->pluck('display_value');

        foreach ($cells as $value) {
            $text = trim((string) $value);

            if (str_starts_with($text, 'Du ')) {
                return $text;
            }
        }

        return null;
    }

    protected function findStudentNumberAroundAnchor(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $anchorRow): ?int
    {
        $cells = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereBetween('row_index', [$anchorRow + 2, $anchorRow + 5])
            ->whereBetween('column_index', [$panel->start_column_index + 3, $panel->start_column_index + 5])
            ->orderBy('row_index')
            ->orderBy('column_index')
            ->pluck('display_value');

        foreach ($cells as $value) {
            $int = $this->parseNullableInt($value !== null ? (string) $value : null);

            if ($int !== null && $int > 0 && $int < 1000) {
                return $int;
            }
        }

        return null;
    }

    protected function findClassCodeAroundAnchor(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $anchorRow): ?string
    {
        return $this->findNearbyToken($batchId, $sheetId, $panel, $anchorRow, ['STA', 'PF2', 'PF3', 'TF2', 'TE']);
    }

    protected function findClassLabelAroundAnchor(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $anchorRow): ?string
    {
        $cells = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereBetween('row_index', [$anchorRow + 4, $anchorRow + 8])
            ->whereBetween('column_index', [$panel->start_column_index + 4, $panel->start_column_index + 8])
            ->orderBy('row_index')
            ->orderBy('column_index')
            ->pluck('display_value');

        foreach ($cells as $value) {
            $text = trim((string) $value);

            if ($text !== '' && str_contains($text, 'TRONC COMMUN')) {
                return $text;
            }
        }

        return null;
    }

    protected function findNearbyToken(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $anchorRow, array $allowed): ?string
    {
        $cells = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereBetween('row_index', [$anchorRow + 3, $anchorRow + 8])
            ->whereBetween('column_index', [$panel->start_column_index, $panel->start_column_index + 8])
            ->orderBy('row_index')
            ->orderBy('column_index')
            ->pluck('display_value');

        foreach ($cells as $value) {
            $text = trim((string) $value);

            if (in_array($text, $allowed, true)) {
                return $text;
            }
        }

        return null;
    }

    protected function findSubjectLabel(int $batchId, int $sheetId, HistoricalImportPanel $panel, int $rowIndex): ?string
    {
        $cells = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->where('row_index', $rowIndex)
            ->whereBetween('column_index', [$panel->start_column_index + 9, min($panel->end_column_index, $panel->start_column_index + 25)])
            ->orderBy('column_index')
            ->pluck('display_value')
            ->filter()
            ->values();

        foreach ($cells as $value) {
            $text = trim((string) $value);

            if ($text === '' || $this->isExcludedSubjectLabel($text) || is_numeric($text)) {
                continue;
            }

            return $text;
        }

        return null;
    }

    protected function isExcludedSubjectLabel(string $value): bool
    {
        $upper = mb_strtoupper($value, 'UTF-8');

        foreach ([
            'NOTE DE CLASSE',
            'COMPOS',
            'MOYENNE',
            'COEF',
            'RANG',
            'APPRECIATION',
            'PROFESSEUR',
            'TRONC COMMUN',
            'BULLETIN DE NOTES',
            'ANNEE SCOLAIRE',
            'À POINTE-NOIRE',
        ] as $excluded) {
            if (str_contains($upper, $excluded)) {
                return true;
            }
        }

        return false;
    }

    protected function looksLikeStudentName(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $value = trim($value);
        $upper = mb_strtoupper($value, 'UTF-8');

        if ($value === '' || preg_match('/\d/', $value) === 1) {
            return false;
        }

        foreach ([
            'GENIE INDUSTRIEL',
            'GENIE ELECTRONIQUE',
            'TRONC COMMUN',
            '#REF!',
        ] as $excluded) {
            if (str_contains($upper, $excluded)) {
                return false;
            }
        }

        return preg_match('/[A-Za-zÀ-ÿ]/u', $value) === 1;
    }

    protected function readCellValue(int $batchId, int $sheetId, int $columnIndex, int $rowIndex): ?string
    {
        $value = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->where('column_index', $columnIndex)
            ->where('row_index', $rowIndex)
            ->value('display_value');

        return $value !== null ? trim((string) $value) : null;
    }

    protected function parseNullableDecimal(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) ? (string) $normalized : null;
    }

    protected function parseNullableInt(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) round((float) $value) : null;
    }

    protected function inferRosterFromAnchorSequence($sheetRoster, int $anchorIndex): ?HistoricalImportRoster
    {
        $rosterCount = $sheetRoster->count();

        if ($rosterCount === 0) {
            return null;
        }

        return $sheetRoster->values()->get($anchorIndex % $rosterCount);
    }
}
