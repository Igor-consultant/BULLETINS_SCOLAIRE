<?php

namespace App\Services;

use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportSheet;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use XMLReader;
use ZipArchive;

class HistoricalWorkbookStagingService
{
    public function stage(string $path, ?array $onlySheets = null, ?int $maxRows = null, ?string $label = null): HistoricalImportBatch
    {
        if (! is_file($path)) {
            throw new RuntimeException("Fichier introuvable: {$path}");
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Impossible d'ouvrir le classeur XLSX: {$path}");
        }

        try {
            $sheetMap = $this->readWorkbookSheetMap($zip);
            $selectedSheetMap = $this->filterSheets($sheetMap, $onlySheets);
            $sharedStrings = $this->readSharedStrings($zip);

            $batch = HistoricalImportBatch::create([
                'label' => $label,
                'source_path' => $path,
                'source_filename' => basename($path),
                'source_hash' => hash_file('sha256', $path),
                'status' => 'processing',
                'sheet_count' => count($selectedSheetMap),
                'metadata' => [
                    'available_sheets' => array_keys($sheetMap),
                    'selected_sheets' => array_keys($selectedSheetMap),
                    'max_rows' => $maxRows,
                ],
            ]);

            $totalRows = 0;
            $totalCells = 0;
            $totalFormulas = 0;

            foreach ($selectedSheetMap as $sheetName => $worksheetPath) {
                $sheet = HistoricalImportSheet::create([
                    'batch_id' => $batch->id,
                    'sheet_name' => $sheetName,
                    'worksheet_path' => $worksheetPath,
                ]);

                $summary = $this->stageWorksheet(
                    $zip,
                    $worksheetPath,
                    $sharedStrings,
                    $batch->id,
                    $sheet,
                    $maxRows
                );

                $sheet->update([
                    'dimension_ref' => $summary['dimension_ref'],
                    'row_count' => $summary['row_count'],
                    'non_empty_cell_count' => $summary['cell_count'],
                    'formula_cell_count' => $summary['formula_count'],
                    'metadata' => [
                        'sample_rows' => $summary['sample_rows'],
                    ],
                ]);

                $totalRows += $summary['row_count'];
                $totalCells += $summary['cell_count'];
                $totalFormulas += $summary['formula_count'];
            }

            $batch->update([
                'status' => 'completed',
                'row_count' => $totalRows,
                'cell_count' => $totalCells,
                'formula_count' => $totalFormulas,
                'imported_at' => now(),
            ]);

            return $batch->fresh(['sheets']);
        } catch (\Throwable $exception) {
            if (isset($batch)) {
                $batch->update([
                    'status' => 'failed',
                    'metadata' => array_merge($batch->metadata ?? [], [
                        'error' => $exception->getMessage(),
                    ]),
                ]);
            }

            throw $exception;
        } finally {
            $zip->close();
        }
    }

    protected function readWorkbookSheetMap(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('Structure XLSX invalide: workbook.xml manquant.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);

        if (! $workbook || ! $rels) {
            throw new RuntimeException('Impossible de lire les metadonnees du classeur.');
        }

        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $rels->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relationshipMap = [];

        foreach ($rels->xpath('//rel:Relationship') ?: [] as $relationship) {
            $relationshipMap[(string) $relationship['Id']] = $this->normalizeWorksheetPath((string) $relationship['Target']);
        }

        $sheetMap = [];

        foreach ($workbook->xpath('//main:sheets/main:sheet') ?: [] as $sheet) {
            $attributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $relationshipId = (string) ($attributes['id'] ?? '');
            $sheetName = (string) $sheet['name'];

            if ($sheetName !== '' && isset($relationshipMap[$relationshipId])) {
                $sheetMap[$sheetName] = $relationshipMap[$relationshipId];
            }
        }

        if ($sheetMap === []) {
            throw new RuntimeException('Aucun onglet exploitable n a ete detecte dans le classeur.');
        }

        return $sheetMap;
    }

    protected function normalizeWorksheetPath(string $target): string
    {
        $target = ltrim($target, '/');

        while (str_starts_with($target, '../')) {
            $target = substr($target, 3);
        }

        return str_starts_with($target, 'xl/')
            ? $target
            : 'xl/'.$target;
    }

    protected function filterSheets(array $sheetMap, ?array $onlySheets): array
    {
        if ($onlySheets === null || $onlySheets === []) {
            return $sheetMap;
        }

        $selected = [];

        foreach ($onlySheets as $sheetName) {
            if (! isset($sheetMap[$sheetName])) {
                throw new RuntimeException("Onglet introuvable dans le classeur: {$sheetName}");
            }

            $selected[$sheetName] = $sheetMap[$sheetName];
        }

        return $selected;
    }

    protected function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $reader = new XMLReader();
        $reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT);

        $sharedStrings = [];

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'si') {
                $sharedStrings[] = $this->normalizeTextValue($this->readInlineText($reader, 'si'));
            }
        }

        $reader->close();

        return $sharedStrings;
    }

    protected function stageWorksheet(
        ZipArchive $zip,
        string $worksheetPath,
        array $sharedStrings,
        int $batchId,
        HistoricalImportSheet $sheet,
        ?int $maxRows
    ): array {
        $xml = $zip->getFromName($worksheetPath);

        if ($xml === false) {
            throw new RuntimeException("Feuille introuvable dans l archive XLSX: {$worksheetPath}");
        }

        $reader = new XMLReader();
        $reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT);

        $cellBuffer = [];
        $rowIndexes = [];
        $cellCount = 0;
        $formulaCount = 0;
        $dimensionRef = null;
        $sampleRows = [];
        $sampleRowsMap = [];

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            if ($reader->localName === 'dimension' && $dimensionRef === null) {
                $dimensionRef = $reader->getAttribute('ref');
                continue;
            }

            if ($reader->localName !== 'row') {
                continue;
            }

            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'row') {
                    break;
                }

                if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'c') {
                    continue;
                }

                $cell = $this->readCell($reader, $sharedStrings);

                if ($cell['display_value'] === null && $cell['formula'] === null) {
                    continue;
                }

                $rowIndex = $cell['row_index'];

                if ($maxRows !== null && $rowIndex > $maxRows) {
                    continue;
                }

                $rowIndexes[$rowIndex] = true;

                $cellBuffer[] = [
                    'batch_id' => $batchId,
                    'sheet_id' => $sheet->id,
                    'sheet_name' => $sheet->sheet_name,
                    'row_index' => $rowIndex,
                    'column_index' => $this->columnLettersToIndex($cell['column_letters']),
                    'cell_reference' => $cell['reference'],
                    'cell_type' => $cell['cell_type'],
                    'raw_value' => $cell['raw_value'],
                    'display_value' => $cell['display_value'],
                    'formula' => $cell['formula'],
                    'is_formula' => $cell['formula'] !== null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $cellCount++;

                if ($cell['formula'] !== null) {
                    $formulaCount++;
                }

                if (! isset($sampleRowsMap[$rowIndex])) {
                    $sampleRowsMap[$rowIndex] = [];
                }

                if (count($sampleRowsMap[$rowIndex]) < 12) {
                    $sampleRowsMap[$rowIndex][] = [
                        'cell' => $cell['reference'],
                        'value' => $cell['display_value'],
                    ];
                }

                if (count($cellBuffer) >= 500) {
                    DB::table('historical_import_cells')->insert($cellBuffer);
                    $cellBuffer = [];
                }
            }
        }

        if ($cellBuffer !== []) {
            DB::table('historical_import_cells')->insert($cellBuffer);
        }

        $reader->close();

        ksort($sampleRowsMap);

        foreach ($sampleRowsMap as $rowIndex => $values) {
            if (count($sampleRows) >= 8) {
                break;
            }

            $sampleRows[] = [
                'row' => $rowIndex,
                'values' => $values,
            ];
        }

        return [
            'dimension_ref' => $dimensionRef,
            'row_count' => count($rowIndexes),
            'cell_count' => $cellCount,
            'formula_count' => $formulaCount,
            'sample_rows' => $sampleRows,
        ];
    }

    protected function readCell(XMLReader $reader, array $sharedStrings): array
    {
        $reference = $reader->getAttribute('r') ?? '';
        $type = $reader->getAttribute('t') ?? '';
        $rawValue = null;
        $displayValue = null;
        $formula = null;
        $columnLetters = preg_replace('/\d+/', '', $reference) ?: 'A';

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'c') {
                break;
            }

            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            if ($reader->localName === 'f') {
                $formula = $reader->readString();
                continue;
            }

            if ($reader->localName === 'v') {
                $rawValue = $reader->readString();
                continue;
            }

            if ($reader->localName === 'is') {
                $displayValue = $this->normalizeTextValue($this->readInlineText($reader, 'is'));
            }
        }

        $cellType = match ($type) {
            's' => 'shared_string',
            'b' => 'boolean',
            'inlineStr' => 'inline_string',
            'str' => 'string',
            default => $formula !== null ? 'formula' : 'numeric',
        };

        if ($type === 's' && $rawValue !== null) {
            $displayValue = $sharedStrings[(int) $rawValue] ?? $rawValue;
        } elseif ($type === 'b' && $rawValue !== null) {
            $displayValue = $rawValue === '1' ? 'TRUE' : 'FALSE';
        } elseif ($displayValue === null) {
            $displayValue = $rawValue;
        }

        $displayValue = $this->normalizeTextValue($displayValue);

        return [
            'reference' => $reference,
            'column_letters' => $columnLetters,
            'row_index' => $this->rowNumberFromReference($reference),
            'cell_type' => $cellType,
            'raw_value' => $rawValue,
            'display_value' => $displayValue,
            'formula' => $formula,
        ];
    }

    protected function readInlineText(XMLReader $reader, string $endElement): string
    {
        $value = '';

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === $endElement) {
                break;
            }

            if (in_array($reader->nodeType, [XMLReader::TEXT, XMLReader::CDATA, XMLReader::SIGNIFICANT_WHITESPACE], true)) {
                $value .= $reader->value;
            }
        }

        return $value;
    }

    protected function normalizeTextValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return strtr($value, [
            '‚' => 'é',
            'ƒ' => 'ê',
            '„' => 'ä',
            '…' => 'à',
            '‡' => 'ç',
            'ˆ' => 'è',
            '‰' => 'ë',
            'Š' => 'è',
            '‹' => 'ï',
            'Œ' => 'Œ',
            'œ' => 'œ',
            '㌢' => 'ç',
        ]);
    }

    protected function columnLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;

        foreach (str_split($letters) as $character) {
            $index = ($index * 26) + (ord($character) - 64);
        }

        return max(1, $index);
    }

    protected function rowNumberFromReference(string $reference): int
    {
        if (preg_match('/(\d+)$/', $reference, $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }
}
