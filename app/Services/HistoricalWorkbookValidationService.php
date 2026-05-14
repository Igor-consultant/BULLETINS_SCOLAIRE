<?php

namespace App\Services;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportBulletin;
use App\Models\HistoricalImportBulletinLine;
use App\Models\HistoricalImportValidatedBulletin;
use App\Models\HistoricalImportValidatedResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalWorkbookValidationService
{
    public function validate(int $batchId, ?array $onlySheets = null): HistoricalImportBatch
    {
        $batch = HistoricalImportBatch::with('sheets')->findOrFail($batchId);
        $sheetNames = $onlySheets !== null && $onlySheets !== [] ? $onlySheets : $batch->sheets->pluck('sheet_name')->all();

        DB::table('historical_import_validated_results')->where('batch_id', $batchId)->delete();
        DB::table('historical_import_validated_bulletins')->where('batch_id', $batchId)->delete();

        foreach ($sheetNames as $sheetName) {
            $sheet = $batch->sheets->firstWhere('sheet_name', $sheetName);

            if (! $sheet) {
                throw new RuntimeException("Onglet non stage pour ce batch: {$sheetName}");
            }

            $this->validateSheet($batchId, $sheet->id, $sheetName);
        }

        return $batch->fresh();
    }

    protected function validateSheet(int $batchId, int $sheetId, string $sheetName): void
    {
        $bulletins = HistoricalImportBulletin::query()
            ->where('batch_id', $batchId)
            ->where('sheet_id', $sheetId)
            ->whereNotNull('roster_id')
            ->whereNotNull('trimester_label')
            ->get();

        $groups = $bulletins->groupBy(fn (HistoricalImportBulletin $bulletin) => $bulletin->student_name.'|'.$bulletin->trimester_label);

        foreach ($groups as $groupKey => $group) {
            $chosen = $group
                ->sortBy([
                    ['subject_line_count', 'desc'],
                    [fn (HistoricalImportBulletin $bulletin) => $bulletin->academic_year_label !== null ? 0 : 1, 'asc'],
                    [fn (HistoricalImportBulletin $bulletin) => $bulletin->student_number !== null ? 0 : 1, 'asc'],
                    ['panel_index', 'asc'],
                    ['anchor_row_index', 'asc'],
                ])
                ->first();

            if (! $chosen) {
                continue;
            }

            $validatedBulletin = HistoricalImportValidatedBulletin::create([
                'batch_id' => $batchId,
                'sheet_id' => $sheetId,
                'roster_id' => $chosen->roster_id,
                'source_bulletin_id' => $chosen->id,
                'sheet_name' => $sheetName,
                'trimester_label' => $chosen->trimester_label,
                'student_name' => $chosen->student_name,
                'student_number' => $chosen->student_number,
                'class_code' => $chosen->class_code,
                'class_label' => $chosen->class_label,
                'academic_year_label' => $chosen->academic_year_label,
                'source_subject_line_count' => $chosen->subject_line_count,
                'metadata' => [
                    'candidate_bulletin_ids' => $group->pluck('id')->values()->all(),
                    'candidate_count' => $group->count(),
                ],
            ]);

            $this->validateBulletinLines($batchId, $validatedBulletin, $chosen);
        }
    }

    protected function validateBulletinLines(
        int $batchId,
        HistoricalImportValidatedBulletin $validatedBulletin,
        HistoricalImportBulletin $sourceBulletin
    ): void {
        $lines = HistoricalImportBulletinLine::query()
            ->where('bulletin_id', $sourceBulletin->id)
            ->get();

        $grouped = $lines
            ->map(function (HistoricalImportBulletinLine $line) {
                $normalized = $this->normalizeSubjectLabel($line->subject_label);

                return [
                    'line' => $line,
                    'normalized' => $normalized,
                ];
            })
            ->filter(fn (array $item) => $item['normalized'] !== null)
            ->groupBy('normalized');

        foreach ($grouped as $normalized => $items) {
            $chosen = collect($items)
                ->sortByDesc(function (array $item) {
                    $line = $item['line'];

                    return
                        ($line->coefficient !== null ? (float) $line->coefficient : 0) * 1000000 +
                        ($line->points !== null ? (float) $line->points : 0) * 1000 +
                        ($line->moyenne_sur_20 !== null ? (float) $line->moyenne_sur_20 : 0);
                })
                ->first();

            if (! $chosen) {
                continue;
            }

            /** @var HistoricalImportBulletinLine $sourceLine */
            $sourceLine = $chosen['line'];

            HistoricalImportValidatedResult::create([
                'batch_id' => $batchId,
                'validated_bulletin_id' => $validatedBulletin->id,
                'source_line_id' => $sourceLine->id,
                'sheet_name' => $validatedBulletin->sheet_name,
                'trimester_label' => $validatedBulletin->trimester_label,
                'student_name' => $validatedBulletin->student_name,
                'student_number' => $validatedBulletin->student_number,
                'subject_label_original' => $sourceLine->subject_label,
                'subject_label_normalized' => $normalized,
                'note_classe' => $sourceLine->note_classe,
                'composition' => $sourceLine->composition,
                'moyenne_sur_20' => $sourceLine->moyenne_sur_20,
                'coefficient' => $sourceLine->coefficient,
                'points' => $sourceLine->points,
                'rang' => $sourceLine->rang,
                'teacher_name' => $sourceLine->teacher_name,
                'appreciation' => $sourceLine->appreciation,
                'metadata' => [
                    'source_subject_candidates' => collect($items)->pluck('line.id')->values()->all(),
                ],
            ]);
        }
    }

    protected function normalizeSubjectLabel(?string $label): ?string
    {
        if ($label === null) {
            return null;
        }

        $trimmed = trim($label);

        if ($trimmed === '') {
            return null;
        }

        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $trimmed);
        $upper = strtoupper($ascii !== false ? $ascii : $trimmed);
        $upper = preg_replace('/\s+/', ' ', $upper) ?? $upper;
        $upper = trim($upper);

        if (preg_match('/^DISCIPLINE\s+\d+$/', $upper) === 1) {
            return null;
        }

        return match ($upper) {
            'FRANCAIS' => 'Francais',
            'HISTOIRE-GEOGRAPHIE', 'HISTOIRE - GEOGRAPHIE' => 'Histoire-Geographie',
            'SCIENCES PHYSIQUES', 'SCIENCES - PHYSIQUES', 'SCIENCES-PHYSIQUES' => 'Sciences Physiques',
            'EDUCATION PHYSIQUE ET SPORTIVE' => 'Education Physique et Sportive',
            'EDUCATION CIVIQUE ET MORALE' => 'Education Civique et Morale',
            'BUREAU DES METHODES' => 'Bureau des Methodes',
            'DESSIN INDUSTRIEL' => 'Dessin Industriel',
            'TRAVAUX PRATIQUES', 'TRAVAUX PRATIQUES ' => 'Travaux Pratiques',
            'TECHNOLOGIE GENERALE' => 'Technologie Generale',
            'MATHEMATIQUES' => 'Mathematiques',
            'ANGLAIS' => 'Anglais',
            'AUTOMATISME' => 'Automatisme',
            'INFORMATIQUE' => 'Informatique',
            'PHILOSOPHIE' => 'Philosophie',
            default => $this->isAcceptableSubject($upper) ? $this->toTitleCaseSubject($upper) : null,
        };
    }

    protected function isAcceptableSubject(string $upper): bool
    {
        foreach ([
            'BULLETIN DE NOTES',
            'NOTE DE CLASSE',
            'COMPOS',
            'MOYENNE',
            'COEF',
            'RANG',
            'APPRECIATION',
            'TRONC COMMUN',
        ] as $excluded) {
            if (str_contains($upper, $excluded)) {
                return false;
            }
        }

        return preg_match('/[A-Z]/', $upper) === 1;
    }

    protected function toTitleCaseSubject(string $upper): string
    {
        $words = explode(' ', strtolower($upper));
        $words = array_map(fn (string $word) => ucfirst($word), $words);

        return implode(' ', $words);
    }
}
