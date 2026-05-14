<?php

namespace App\Services;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportPanel;
use App\Models\HistoricalImportRoster;
use App\Models\HistoricalImportStudentCandidate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalWorkbookNormalizationService
{
    public function detectPanelsAndStudents(int $batchId, ?array $onlySheets = null): HistoricalImportBatch
    {
        $batch = HistoricalImportBatch::with('sheets')->findOrFail($batchId);
        $sheetNames = $onlySheets !== null && $onlySheets !== [] ? $onlySheets : $batch->sheets->pluck('sheet_name')->all();

        DB::table('historical_import_student_candidates')->where('batch_id', $batchId)->delete();
        DB::table('historical_import_panels')->where('batch_id', $batchId)->delete();
        DB::table('historical_import_rosters')->where('batch_id', $batchId)->delete();

        foreach ($sheetNames as $sheetName) {
            $sheet = $batch->sheets->firstWhere('sheet_name', $sheetName);

            if (! $sheet) {
                throw new RuntimeException("Onglet non stage pour ce batch: {$sheetName}");
            }

            $this->detectSheetPanels($batch->id, $sheet->id, $sheetName);
            $this->buildSheetRoster($batch->id, $sheet->id, $sheetName);
        }

        return $batch->fresh();
    }

    protected function buildSheetRoster(int $batchId, int $sheetId, string $sheetName): void
    {
        $candidates = HistoricalImportStudentCandidate::query()
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->get();

        $grouped = $candidates->groupBy('student_name');
        $rosters = collect();

        foreach ($grouped as $studentName => $rows) {
            $rosters->push([
                'student_name' => $studentName,
                'candidate_occurrences' => $rows->count(),
                'panel_presence_count' => $rows->pluck('panel_index')->unique()->count(),
                'first_row_index' => $rows->min('excel_row_index'),
                'last_row_index' => $rows->max('excel_row_index'),
                'best_student_number' => $this->resolvePreferredStudentNumber($rows),
                'metadata' => [
                    'panels' => $rows->pluck('panel_index')->unique()->values()->all(),
                    'source_rows' => $rows->pluck('excel_row_index')->unique()->values()->all(),
                ],
            ]);
        }

        $resolvedRosters = $this->fillSequentialStudentNumbers(
            $rosters->sortBy('first_row_index')->values()
        );

        foreach ($resolvedRosters as $roster) {
            HistoricalImportRoster::create([
                'batch_id' => $batchId,
                'sheet_id' => $sheetId,
                'sheet_name' => $sheetName,
                'student_name' => $roster['student_name'],
                'candidate_occurrences' => $roster['candidate_occurrences'],
                'panel_presence_count' => $roster['panel_presence_count'],
                'first_row_index' => $roster['first_row_index'],
                'last_row_index' => $roster['last_row_index'],
                'best_student_number' => $roster['best_student_number'],
                'metadata' => $roster['metadata'],
            ]);
        }
    }

    protected function resolvePreferredStudentNumber(Collection $rows): ?int
    {
        $preferred = $rows
            ->sortBy([
                ['panel_index', 'asc'],
                ['excel_row_index', 'asc'],
            ])
            ->pluck('student_number')
            ->first(fn ($value) => $value !== null && $value > 0);

        if ($preferred !== null) {
            return (int) $preferred;
        }

        $fallback = $rows
            ->pluck('student_number')
            ->filter(fn ($value) => $value !== null && $value > 0)
            ->min();

        return $fallback !== null ? (int) $fallback : null;
    }

    protected function fillSequentialStudentNumbers(Collection $rosters): Collection
    {
        $resolved = $rosters->map(fn (array $roster) => $roster)->values()->all();

        foreach ($resolved as $index => $roster) {
            $previous = $resolved[$index - 1] ?? null;
            $next = $resolved[$index + 1] ?? null;

            if (! $previous || ! $next) {
                continue;
            }

            $previousNumber = $previous['best_student_number'] ?? null;
            $nextNumber = $next['best_student_number'] ?? null;

            if ($previousNumber === null || $nextNumber === null) {
                continue;
            }

            if (($nextNumber - $previousNumber) === 2 && $roster['best_student_number'] !== ($previousNumber + 1)) {
                $resolved[$index]['best_student_number'] = $previousNumber + 1;
            }
        }

        return collect($resolved);
    }

    protected function detectSheetPanels(int $batchId, int $sheetId, string $sheetName): void
    {
        $headers = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->where('display_value', 'NOMS ET PRENOMS')
            ->orderBy('column_index')
            ->get([
                'id',
                'cell_reference',
                'column_index',
                'row_index',
            ]);

        if ($headers->isEmpty()) {
            return;
        }

        $maxColumn = (int) DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->max('column_index');

        foreach ($headers->values() as $index => $header) {
            $startColumn = (int) $header->column_index;
            $nextStartColumn = isset($headers[$index + 1]) ? (int) $headers[$index + 1]->column_index : ($maxColumn + 1);
            $endColumn = $nextStartColumn - 1;
            $startLetters = preg_replace('/\d+$/', '', $header->cell_reference) ?: 'A';
            $endLetters = $this->columnIndexToLetters($endColumn);
            $studentNumberColumn = $startColumn > 1 ? ($startColumn - 1) : null;

            $studentCandidates = $this->collectStudentCandidates(
                $batchId,
                $sheetId,
                $sheetName,
                $startColumn,
                $studentNumberColumn,
            );

            $bulletinCount = (int) DB::table('historical_import_cells')
                ->where('batch_id', $batchId)
                ->where('sheet_id', $sheetId)
                ->whereBetween('column_index', [$startColumn, min($endColumn, $startColumn + 12)])
                ->where('display_value', 'BULLETIN DE NOTES')
                ->count();

            $panel = HistoricalImportPanel::create([
                'batch_id' => $batchId,
                'sheet_id' => $sheetId,
                'sheet_name' => $sheetName,
                'panel_index' => $index + 1,
                'header_row_index' => (int) $header->row_index,
                'start_column_index' => $startColumn,
                'end_column_index' => $endColumn,
                'start_column_letters' => $startLetters,
                'end_column_letters' => $endLetters,
                'name_header_cell' => $header->cell_reference,
                'student_name_column_index' => $startColumn,
                'student_number_column_index' => $studentNumberColumn,
                'detected_student_count' => $studentCandidates->count(),
                'detected_bulletin_count' => $bulletinCount,
                'metadata' => [
                    'header_cell' => $header->cell_reference,
                    'header_row_index' => (int) $header->row_index,
                ],
            ]);

            foreach ($studentCandidates as $student) {
                HistoricalImportStudentCandidate::create([
                    'batch_id' => $batchId,
                    'sheet_id' => $sheetId,
                    'panel_id' => $panel->id,
                    'sheet_name' => $sheetName,
                    'excel_row_index' => $student['excel_row_index'],
                    'panel_index' => $index + 1,
                    'source_name_cell' => $student['source_name_cell'],
                    'source_number_cell' => $student['source_number_cell'],
                    'student_number' => $student['student_number'],
                    'student_name' => $student['student_name'],
                    'metadata' => $student['metadata'],
                ]);
            }
        }
    }

    protected function collectStudentCandidates(
        int $batchId,
        int $sheetId,
        string $sheetName,
        int $nameColumn,
        ?int $studentNumberColumn
    ): Collection {
        $nameCells = DB::table('historical_import_cells')
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->where('column_index', $nameColumn)
            ->orderBy('row_index')
            ->get([
                'row_index',
                'cell_reference',
                'display_value',
            ]);

        $numberMap = [];

        if ($studentNumberColumn !== null) {
            $numberMap = DB::table('historical_import_cells')
                ->where('batch_id', $batchId)
                ->where('sheet_id', $sheetId)
                ->where('column_index', $studentNumberColumn)
                ->pluck('display_value', 'row_index')
                ->all();
        }

        return $nameCells
            ->filter(function ($cell) use ($numberMap) {
                $value = trim((string) $cell->display_value);
                $rowIndex = (int) $cell->row_index;
                $hasStudentNumber = isset($numberMap[$rowIndex]) && is_numeric($numberMap[$rowIndex]);

                if ($value === '' || ! $this->looksLikeStudentName($value, $hasStudentNumber)) {
                    return false;
                }

                return true;
            })
            ->map(function ($cell) use ($numberMap, $studentNumberColumn, $sheetName) {
                $rowIndex = (int) $cell->row_index;
                $studentNumber = isset($numberMap[$rowIndex]) && is_numeric($numberMap[$rowIndex])
                    ? (int) $numberMap[$rowIndex]
                    : null;

                return [
                    'excel_row_index' => $rowIndex,
                    'source_name_cell' => $cell->cell_reference,
                    'source_number_cell' => $studentNumberColumn !== null
                        ? $this->columnIndexToLetters($studentNumberColumn).$rowIndex
                        : null,
                    'student_number' => $studentNumber,
                    'student_name' => trim((string) $cell->display_value),
                    'metadata' => [
                        'sheet_name' => $sheetName,
                    ],
                ];
            })
            ->unique(fn (array $student) => $student['excel_row_index'].'|'.$student['student_name'])
            ->values();
    }

    protected function looksLikeStudentName(string $value, bool $hasStudentNumber = false): bool
    {
        $upper = mb_strtoupper($value, 'UTF-8');
        $excluded = [
            'NOMS ET PRENOMS',
            'BULLETIN DE NOTES',
            'DU 2EME TRIMESTRE',
            'DU 1ER TRIMESTRE',
            'DU 3EME TRIMESTRE',
            'CONCEPTION',
            'NOTE DE CLASSE',
        ];

        foreach ($excluded as $item) {
            if (str_contains($upper, $item)) {
                return false;
            }
        }

        if (preg_match('/\d/', $value) === 1) {
            return false;
        }

        if (str_contains($value, ':') || str_contains($value, '?')) {
            return false;
        }

        if (! $hasStudentNumber && preg_match('/[a-zà-ÿ]/u', $value) !== 1) {
            return false;
        }

        $tokens = preg_split('/\s+/', trim($value)) ?: [];

        if (count(array_filter($tokens)) < 2) {
            return false;
        }

        return preg_match('/[A-Za-zÀ-ÿ]/u', $value) === 1;
    }

    protected function columnIndexToLetters(int $index): string
    {
        $letters = '';

        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letters = chr(65 + $remainder).$letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }
}
