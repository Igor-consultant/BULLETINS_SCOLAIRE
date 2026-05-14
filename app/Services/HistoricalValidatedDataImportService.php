<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\ClasseMatiere;
use App\Models\Eleve;
use App\Models\Filiere;
use App\Models\HistoricalImportBatch;
use App\Models\HistoricalImportFinalization;
use App\Models\HistoricalImportResultMapping;
use App\Models\HistoricalImportValidatedBulletin;
use App\Models\Inscription;
use App\Models\Matiere;
use App\Models\Resultat;
use App\Models\Trimestre;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalValidatedDataImportService
{
    public function import(int $batchId, ?array $onlySheets = null): HistoricalImportBatch
    {
        $batch = HistoricalImportBatch::with('sheets')->findOrFail($batchId);
        $sheetNames = $onlySheets !== null && $onlySheets !== [] ? $onlySheets : $batch->sheets->pluck('sheet_name')->all();

        foreach ($sheetNames as $sheetName) {
            $sheet = $batch->sheets->firstWhere('sheet_name', $sheetName);

            if (! $sheet) {
                throw new RuntimeException("Onglet non stage pour ce batch: {$sheetName}");
            }

            DB::transaction(function () use ($batchId, $sheetName) {
                $this->importSheet($batchId, $sheetName);
            });
        }

        return $batch->fresh();
    }

    protected function importSheet(int $batchId, string $sheetName): HistoricalImportFinalization
    {
        $validatedBulletins = HistoricalImportValidatedBulletin::query()
            ->with(['results'])
            ->where('batch_id', $batchId)
            ->where('sheet_name', $sheetName)
            ->orderBy('student_name')
            ->orderBy('trimester_label')
            ->get();

        if ($validatedBulletins->isEmpty()) {
            throw new RuntimeException("Aucun bulletin valide a importer pour l'onglet {$sheetName} du batch #{$batchId}.");
        }

        $groups = $validatedBulletins->groupBy(fn (HistoricalImportValidatedBulletin $bulletin) => $this->buildFinalizationKey($bulletin, $sheetName));
        $lastFinalization = null;

        foreach ($groups as $groupBulletins) {
            $groupCollection = $groupBulletins instanceof Collection ? $groupBulletins : collect($groupBulletins);
            $lastFinalization = $this->importBulletinGroup($batchId, $sheetName, $groupCollection);
        }

        if (! $lastFinalization) {
            throw new RuntimeException("Impossible de finaliser l'import pour l'onglet {$sheetName} du batch #{$batchId}.");
        }

        return $lastFinalization;
    }

    protected function importBulletinGroup(int $batchId, string $sheetName, Collection $validatedBulletins): HistoricalImportFinalization
    {
        $academicYearLabel = $this->resolveAcademicYearLabel($validatedBulletins);
        $anneeScolaire = $this->resolveAnneeScolaire($academicYearLabel);
        $trimestres = $this->ensureTrimestres($anneeScolaire);
        $classCode = $validatedBulletins->pluck('class_code')->filter()->mode()[0] ?? $sheetName;
        $classLabel = $validatedBulletins->pluck('class_label')->filter()->mode()[0] ?? $classCode;
        $classe = $this->resolveClasse($classCode, $classLabel, $anneeScolaire);

        $finalization = HistoricalImportFinalization::updateOrCreate(
            [
                'batch_id' => $batchId,
                'sheet_name' => $sheetName,
                'academic_year_label' => $academicYearLabel,
                'class_code' => $classCode,
            ],
            [
                'annee_scolaire_id' => $anneeScolaire->id,
                'classe_id' => $classe->id,
                'metadata' => null,
            ]
        );

        HistoricalImportResultMapping::query()
            ->where('finalization_id', $finalization->id)
            ->delete();

        $subjectMetadata = $this->prepareMatieres($classe, $validatedBulletins);
        $studentGroups = $validatedBulletins->groupBy(fn (HistoricalImportValidatedBulletin $bulletin) => $this->buildStudentKey($bulletin));
        $importedResultCount = 0;

        foreach ($studentGroups as $studentGroup) {
            $studentBulletins = $studentGroup instanceof Collection ? $studentGroup : collect($studentGroup);
            $representative = $studentBulletins->first();

            if (! $representative) {
                continue;
            }

            $eleve = $this->resolveEleve($representative, $anneeScolaire, $classCode);
            $inscription = Inscription::updateOrCreate(
                [
                    'eleve_id' => $eleve->id,
                    'annee_scolaire_id' => $anneeScolaire->id,
                ],
                [
                    'classe_id' => $classe->id,
                    'statut' => 'historique_importe',
                    'date_inscription' => $anneeScolaire->date_debut,
                ]
            );

            foreach ($studentBulletins as $bulletin) {
                $trimestre = $this->resolveTrimestreFromLabel($trimestres, $bulletin->trimester_label);

                if (! $trimestre) {
                    continue;
                }

                foreach ($bulletin->results as $validatedResult) {
                    $matiere = $subjectMetadata['matieres'][$validatedResult->subject_label_normalized] ?? null;

                    if (! $matiere) {
                        continue;
                    }

                    $resultat = Resultat::updateOrCreate(
                        [
                            'eleve_id' => $eleve->id,
                            'classe_id' => $classe->id,
                            'trimestre_id' => $trimestre->id,
                            'matiere_id' => $matiere->id,
                        ],
                        [
                            'coefficient' => $validatedResult->coefficient ?? 0,
                            'moyenne_devoirs' => $validatedResult->note_classe,
                            'composition' => $validatedResult->composition,
                            'moyenne_matiere' => $validatedResult->moyenne_sur_20,
                            'points' => $validatedResult->points,
                            'rang' => $validatedResult->rang,
                            'statut_calcul' => 'historique_importe',
                        ]
                    );

                    HistoricalImportResultMapping::updateOrCreate(
                        [
                            'validated_result_id' => $validatedResult->id,
                        ],
                        [
                            'finalization_id' => $finalization->id,
                            'validated_bulletin_id' => $bulletin->id,
                            'eleve_id' => $eleve->id,
                            'inscription_id' => $inscription->id,
                            'annee_scolaire_id' => $anneeScolaire->id,
                            'trimestre_id' => $trimestre->id,
                            'classe_id' => $classe->id,
                            'matiere_id' => $matiere->id,
                            'resultat_id' => $resultat->id,
                        ]
                    );

                    $importedResultCount++;
                }
            }
        }

        $finalization->update([
            'imported_student_count' => $studentGroups->count(),
            'imported_bulletin_count' => $validatedBulletins->count(),
            'imported_result_count' => $importedResultCount,
            'metadata' => [
                'resolved_academic_year_label' => $academicYearLabel,
                'subject_count' => count($subjectMetadata['matieres']),
                'subject_codes' => $subjectMetadata['codes'],
            ],
        ]);

        return $finalization->fresh();
    }

    protected function resolveAcademicYearLabel(Collection $validatedBulletins): string
    {
        $rawLabel = $validatedBulletins->pluck('academic_year_label')->filter()->mode()[0] ?? null;

        if ($rawLabel !== null && preg_match('/(20\d{2})\s*-\s*(20\d{2})/', $rawLabel, $matches) === 1) {
            return $matches[1].'-'.$matches[2];
        }

        return '2021-2022';
    }

    protected function resolveAnneeScolaire(string $label): AnneeScolaire
    {
        preg_match('/(20\d{2})-(20\d{2})/', $label, $matches);
        $startYear = isset($matches[1]) ? (int) $matches[1] : 2021;
        $endYear = isset($matches[2]) ? (int) $matches[2] : $startYear + 1;

        return AnneeScolaire::updateOrCreate(
            ['libelle' => $label],
            [
                'date_debut' => sprintf('%04d-10-01', $startYear),
                'date_fin' => sprintf('%04d-07-31', $endYear),
                'statut' => 'archive',
            ]
        );
    }

    protected function ensureTrimestres(AnneeScolaire $anneeScolaire): Collection
    {
        $definitions = [
            1 => ['libelle' => 'Trimestre 1', 'date_debut' => $anneeScolaire->date_debut?->format('Y-m-d') ?? substr((string) $anneeScolaire->date_debut, 0, 10), 'date_fin' => substr($anneeScolaire->libelle, 0, 4).'-12-31'],
            2 => ['libelle' => 'Trimestre 2', 'date_debut' => substr($anneeScolaire->libelle, 5, 4).'-01-01', 'date_fin' => substr($anneeScolaire->libelle, 5, 4).'-03-31'],
            3 => ['libelle' => 'Trimestre 3', 'date_debut' => substr($anneeScolaire->libelle, 5, 4).'-04-01', 'date_fin' => substr($anneeScolaire->libelle, 5, 4).'-06-30'],
        ];

        $trimestres = collect();

        foreach ($definitions as $ordre => $definition) {
            $trimestres->put($ordre, Trimestre::updateOrCreate(
                [
                    'annee_scolaire_id' => $anneeScolaire->id,
                    'ordre' => $ordre,
                ],
                [
                    'libelle' => $definition['libelle'],
                    'statut' => 'archive',
                    'date_debut' => $definition['date_debut'],
                    'date_fin' => $definition['date_fin'],
                ]
            ));
        }

        return $trimestres;
    }

    protected function resolveClasse(string $classCode, string $classLabel, AnneeScolaire $anneeScolaire): Classe
    {
        $filiereMap = [
            'STA' => ['code' => 'GI', 'nom' => 'Genie industriel'],
            'PF2' => ['code' => 'ELN', 'nom' => 'Electronique'],
            'PF3' => ['code' => 'ELN', 'nom' => 'Electronique'],
            'TF2' => ['code' => 'ELT', 'nom' => 'Electrotechnique'],
            'TE' => ['code' => 'ELT', 'nom' => 'Electrotechnique'],
        ];

        $filiereData = $filiereMap[$classCode] ?? ['code' => 'HIST', 'nom' => 'Historique'];

        $filiere = Filiere::updateOrCreate(
            ['code' => $filiereData['code']],
            [
                'nom' => $filiereData['nom'],
                'description' => 'Filiere referencee par import historique.',
                'actif' => true,
            ]
        );

        return Classe::updateOrCreate(
            [
                'code' => $classCode,
                'annee_scolaire_id' => $anneeScolaire->id,
            ],
            [
                'nom' => $classLabel,
                'filiere_id' => $filiere->id,
                'actif' => true,
            ]
        );
    }

    protected function prepareMatieres(Classe $classe, Collection $validatedBulletins): array
    {
        $allResults = $validatedBulletins->flatMap(fn (HistoricalImportValidatedBulletin $bulletin) => $bulletin->results);
        $bySubject = $allResults->groupBy('subject_label_normalized');
        $matieres = [];
        $codes = [];

        foreach ($bySubject as $subjectLabel => $results) {
            if ($subjectLabel === null) {
                continue;
            }

            $matiere = Matiere::query()
                ->whereRaw('LOWER(libelle) = ?', [mb_strtolower($subjectLabel)])
                ->first();

            if (! $matiere) {
                $code = $this->generateMatiereCode($subjectLabel);
                $matiere = Matiere::create([
                    'code' => $code,
                    'libelle' => $subjectLabel,
                    'actif' => true,
                ]);
            }

            $matieres[$subjectLabel] = $matiere;
            $codes[$subjectLabel] = $matiere->code;

            $teacherName = $results->pluck('teacher_name')->filter()->mode()[0] ?? null;
            $coefficient = (float) ($results->pluck('coefficient')->filter()->mode()[0] ?? 0);

            ClasseMatiere::updateOrCreate(
                [
                    'classe_id' => $classe->id,
                    'matiere_id' => $matiere->id,
                ],
                [
                    'coefficient' => $coefficient,
                    'enseignant_nom' => $teacherName,
                    'actif' => true,
                ]
            );
        }

        return [
            'matieres' => $matieres,
            'codes' => $codes,
        ];
    }

    protected function resolveEleve(HistoricalImportValidatedBulletin $bulletin, AnneeScolaire $anneeScolaire, string $classCode): Eleve
    {
        [$nom, $prenoms] = $this->splitStudentName($bulletin->student_name);
        $startYear = preg_match('/^(20\d{2})-/', $anneeScolaire->libelle, $matches) === 1 ? $matches[1] : '2021';
        $matricule = $this->buildMatricule($bulletin, $startYear, $classCode);

        return Eleve::updateOrCreate(
            ['matricule' => $matricule],
            [
                'nom' => $nom,
                'prenoms' => $prenoms,
                'actif' => true,
            ]
        );
    }

    protected function buildMatricule(HistoricalImportValidatedBulletin $bulletin, string $startYear, string $classCode): string
    {
        $normalizedName = $this->normalizeStudentKey($bulletin->student_name);
        $suffix = strtoupper(substr(sha1($startYear.'|'.$classCode.'|'.$normalizedName), 0, 8));

        return sprintf('HIST-%s-%s-%s', $startYear, strtoupper($classCode), $suffix);
    }

    protected function splitStudentName(string $studentName): array
    {
        $clean = preg_replace('/\s+/', ' ', trim($studentName)) ?? trim($studentName);
        $parts = array_values(array_filter(explode(' ', $clean)));

        if ($parts === []) {
            return ['INCONNU', ''];
        }

        $surnameParts = [];

        foreach ($parts as $part) {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $part);
            $probe = $ascii !== false ? $ascii : $part;
            $lettersOnly = preg_replace('/[^A-Za-z-]/', '', $probe) ?? $probe;

            if ($lettersOnly !== '' && strtoupper($lettersOnly) === $lettersOnly) {
                $surnameParts[] = $part;
                continue;
            }

            break;
        }

        if ($surnameParts !== []) {
            $nom = implode(' ', $surnameParts);
            $prenoms = trim(substr($clean, strlen($nom))) ?: '-';

            return [$nom, $prenoms];
        }

        $nom = array_shift($parts) ?? 'INCONNU';

        return [$nom, $parts !== [] ? implode(' ', $parts) : '-'];
    }

    protected function resolveTrimestreFromLabel(Collection $trimestres, ?string $label): ?Trimestre
    {
        if ($label === null) {
            return null;
        }

        if (preg_match('/1/', $label) === 1) {
            return $trimestres->get(1);
        }

        if (preg_match('/2/', $label) === 1) {
            return $trimestres->get(2);
        }

        if (preg_match('/3/', $label) === 1) {
            return $trimestres->get(3);
        }

        return null;
    }

    protected function buildStudentKey(HistoricalImportValidatedBulletin $bulletin): string
    {
        if ($bulletin->roster_id !== null) {
            return 'roster:'.$bulletin->roster_id;
        }

        return 'name:'.mb_strtolower($bulletin->student_name);
    }

    protected function buildFinalizationKey(HistoricalImportValidatedBulletin $bulletin, string $defaultSheetName): string
    {
        $sheetName = $bulletin->sheet_name ?: $defaultSheetName;
        $academicYear = $this->resolveAcademicYearLabel(collect([$bulletin]));
        $classCode = $bulletin->class_code ?: $defaultSheetName;

        return implode('|', [$sheetName, $academicYear, $classCode]);
    }

    protected function generateMatiereCode(string $label): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $label);
        $base = strtoupper($ascii !== false ? $ascii : $label);
        $base = preg_replace('/[^A-Z0-9]+/', '-', $base) ?? $base;
        $base = trim($base, '-');
        $base = substr($base, 0, 24);
        $candidate = $base !== '' ? $base : 'HIST-MATIERE';
        $suffix = 1;

        while (Matiere::query()->where('code', $candidate)->exists()) {
            $candidate = substr($base !== '' ? $base : 'HIST-MATIERE', 0, 20).'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    protected function normalizeStudentKey(string $studentName): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $studentName);
        $normalized = strtoupper($ascii !== false ? $ascii : $studentName);
        $normalized = preg_replace('/[^A-Z0-9]+/', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
