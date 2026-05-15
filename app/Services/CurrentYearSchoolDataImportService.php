<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\ClasseMatiere;
use App\Models\Eleve;
use App\Models\Filiere;
use App\Models\Inscription;
use App\Models\Matiere;
use App\Models\Paiement;
use App\Models\PaiementStatut;
use App\Models\Resultat;
use App\Models\Trimestre;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CurrentYearSchoolDataImportService
{
    public function __construct(
        private readonly HistoricalWorkbookStagingService $staging,
        private readonly HistoricalWorkbookNormalizationService $normalization,
        private readonly HistoricalWorkbookBulletinExtractionService $extraction,
        private readonly HistoricalWorkbookValidationService $validation,
        private readonly HistoricalValidatedDataImportService $historicalImport,
    ) {
    }

    public function importWorkbook(UploadedFile $file, AnneeScolaire $anneeActive): array
    {
        $storedPath = $file->storeAs(
            'imports/current-year',
            now()->format('Ymd_His').'-'.$file->getClientOriginalName()
        );

        if (! $storedPath) {
            throw new RuntimeException("Impossible d'enregistrer le fichier importe.");
        }

        $absolutePath = storage_path('app/'.$storedPath);
        $label = 'Import admin annee active '.$anneeActive->libelle;

        $batch = $this->staging->stage($absolutePath, null, null, $label);
        $this->normalization->detectPanelsAndStudents($batch->id);
        $this->extraction->extract($batch->id);
        $this->validation->validate($batch->id);
        $this->historicalImport->importIntoAcademicYear($batch->id, $anneeActive->libelle);

        return [
            'mode' => 'xlsx_bulletins',
            'annee' => $anneeActive->libelle,
            'batch_id' => $batch->id,
            'source_filename' => $file->getClientOriginalName(),
        ];
    }

    public function importJsonPack(UploadedFile $file, AnneeScolaire $anneeActive): array
    {
        $json = file_get_contents($file->getRealPath());

        if ($json === false) {
            throw new RuntimeException("Impossible de lire le fichier JSON importe.");
        }

        $payload = json_decode($json, true);

        if (! is_array($payload)) {
            throw new RuntimeException("Le fichier JSON importe est invalide.");
        }

        $summary = DB::transaction(function () use ($payload, $anneeActive) {
            return $this->importJsonPayload($payload, $anneeActive);
        });

        return array_merge($summary, [
            'mode' => 'json_pack',
            'annee' => $anneeActive->libelle,
            'source_filename' => $file->getClientOriginalName(),
        ]);
    }

    private function importJsonPayload(array $payload, AnneeScolaire $anneeActive): array
    {
        $this->ensureCurrentYearTrimestres($anneeActive);

        $filieres = 0;
        foreach ($payload['filieres'] ?? [] as $item) {
            if (! isset($item['code'], $item['nom'])) {
                continue;
            }

            Filiere::updateOrCreate(
                ['code' => (string) $item['code']],
                [
                    'nom' => (string) $item['nom'],
                    'description' => $item['description'] ?? null,
                    'actif' => (bool) ($item['actif'] ?? true),
                ]
            );
            $filieres++;
        }

        $classes = 0;
        foreach ($payload['classes'] ?? [] as $item) {
            if (! isset($item['code'], $item['nom'], $item['filiere_code'])) {
                continue;
            }

            $filiere = Filiere::query()->where('code', (string) $item['filiere_code'])->first();

            if (! $filiere) {
                continue;
            }

            Classe::updateOrCreate(
                [
                    'code' => (string) $item['code'],
                    'annee_scolaire_id' => $anneeActive->id,
                ],
                [
                    'nom' => (string) $item['nom'],
                    'filiere_id' => $filiere->id,
                    'actif' => (bool) ($item['actif'] ?? true),
                ]
            );
            $classes++;
        }

        $matieres = 0;
        foreach ($payload['matieres'] ?? [] as $item) {
            if (! isset($item['code'], $item['libelle'])) {
                continue;
            }

            Matiere::updateOrCreate(
                ['code' => (string) $item['code']],
                [
                    'libelle' => (string) $item['libelle'],
                    'actif' => (bool) ($item['actif'] ?? true),
                ]
            );
            $matieres++;
        }

        $classeMatieres = 0;
        foreach ($payload['classe_matieres'] ?? [] as $item) {
            if (! isset($item['classe_code'], $item['matiere_code'])) {
                continue;
            }

            $classe = Classe::query()
                ->where('code', (string) $item['classe_code'])
                ->where('annee_scolaire_id', $anneeActive->id)
                ->first();
            $matiere = Matiere::query()->where('code', (string) $item['matiere_code'])->first();

            if (! $classe || ! $matiere) {
                continue;
            }

            ClasseMatiere::updateOrCreate(
                [
                    'classe_id' => $classe->id,
                    'matiere_id' => $matiere->id,
                ],
                [
                    'coefficient' => (float) ($item['coefficient'] ?? 0),
                    'enseignant_nom' => $item['enseignant_nom'] ?? null,
                    'actif' => (bool) ($item['actif'] ?? true),
                ]
            );
            $classeMatieres++;
        }

        $eleves = 0;
        foreach ($payload['eleves'] ?? [] as $item) {
            if (! isset($item['matricule'], $item['nom'], $item['prenoms'])) {
                continue;
            }

            Eleve::updateOrCreate(
                ['matricule' => (string) $item['matricule']],
                [
                    'nom' => (string) $item['nom'],
                    'prenoms' => (string) $item['prenoms'],
                    'sexe' => $item['sexe'] ?? null,
                    'date_naissance' => $item['date_naissance'] ?? null,
                    'lieu_naissance' => $item['lieu_naissance'] ?? null,
                    'contact_principal' => $item['contact_principal'] ?? null,
                    'nom_parent' => $item['nom_parent'] ?? null,
                    'contact_parent' => $item['contact_parent'] ?? null,
                    'adresse' => $item['adresse'] ?? null,
                    'actif' => (bool) ($item['actif'] ?? true),
                ]
            );
            $eleves++;
        }

        $inscriptions = 0;
        foreach ($payload['inscriptions'] ?? [] as $item) {
            if (! isset($item['matricule'], $item['classe_code'])) {
                continue;
            }

            $eleve = Eleve::query()->where('matricule', (string) $item['matricule'])->first();
            $classe = Classe::query()
                ->where('code', (string) $item['classe_code'])
                ->where('annee_scolaire_id', $anneeActive->id)
                ->first();

            if (! $eleve || ! $classe) {
                continue;
            }

            Inscription::updateOrCreate(
                [
                    'eleve_id' => $eleve->id,
                    'annee_scolaire_id' => $anneeActive->id,
                ],
                [
                    'classe_id' => $classe->id,
                    'statut' => $item['statut'] ?? 'inscrit',
                    'date_inscription' => $item['date_inscription'] ?? $anneeActive->date_debut,
                ]
            );
            $inscriptions++;
        }

        $trimestres = $this->ensureCurrentYearTrimestres($anneeActive)->keyBy('ordre');
        $resultats = 0;
        foreach ($payload['resultats'] ?? [] as $item) {
            if (! isset($item['matricule'], $item['classe_code'], $item['trimestre'], $item['matiere_code'])) {
                continue;
            }

            $eleve = Eleve::query()->where('matricule', (string) $item['matricule'])->first();
            $classe = Classe::query()
                ->where('code', (string) $item['classe_code'])
                ->where('annee_scolaire_id', $anneeActive->id)
                ->first();
            $matiere = Matiere::query()->where('code', (string) $item['matiere_code'])->first();
            $trimestre = $trimestres->get((int) $item['trimestre']);

            if (! $eleve || ! $classe || ! $matiere || ! $trimestre) {
                continue;
            }

            Resultat::updateOrCreate(
                [
                    'eleve_id' => $eleve->id,
                    'classe_id' => $classe->id,
                    'trimestre_id' => $trimestre->id,
                    'matiere_id' => $matiere->id,
                ],
                [
                    'coefficient' => (float) ($item['coefficient'] ?? 0),
                    'moyenne_devoirs' => $item['moyenne_devoirs'] ?? null,
                    'composition' => $item['composition'] ?? null,
                    'moyenne_matiere' => $item['moyenne_matiere'] ?? null,
                    'points' => $item['points'] ?? null,
                    'rang' => $item['rang'] ?? null,
                    'statut_calcul' => $item['statut_calcul'] ?? 'importe_admin',
                ]
            );
            $resultats++;
        }

        $statuts = 0;
        foreach ($payload['paiement_statuts'] ?? [] as $item) {
            if (! isset($item['matricule'])) {
                continue;
            }

            $eleve = Eleve::query()->where('matricule', (string) $item['matricule'])->first();

            if (! $eleve) {
                continue;
            }

            PaiementStatut::updateOrCreate(
                [
                    'eleve_id' => $eleve->id,
                    'annee_scolaire_id' => $anneeActive->id,
                ],
                [
                    'statut' => $item['statut'] ?? 'a_jour',
                    'montant_attendu' => $item['montant_attendu'] ?? null,
                    'montant_paye' => $item['montant_paye'] ?? null,
                    'date_dernier_paiement' => $item['date_dernier_paiement'] ?? null,
                    'autorise_acces_bulletin' => (bool) ($item['autorise_acces_bulletin'] ?? true),
                    'observation' => $item['observation'] ?? null,
                ]
            );
            $statuts++;
        }

        $paiements = 0;
        foreach ($payload['paiements'] ?? [] as $item) {
            if (! isset($item['matricule'], $item['date_paiement'], $item['montant'])) {
                continue;
            }

            $eleve = Eleve::query()->where('matricule', (string) $item['matricule'])->first();
            $statut = $eleve
                ? PaiementStatut::query()
                    ->where('eleve_id', $eleve->id)
                    ->where('annee_scolaire_id', $anneeActive->id)
                    ->first()
                : null;

            if (! $statut) {
                continue;
            }

            Paiement::updateOrCreate(
                [
                    'paiement_statut_id' => $statut->id,
                    'date_paiement' => $item['date_paiement'],
                    'montant' => $item['montant'],
                ],
                [
                    'mode_paiement' => $item['mode_paiement'] ?? null,
                    'reference' => $item['reference'] ?? null,
                    'observation' => $item['observation'] ?? null,
                ]
            );
            $paiements++;
        }

        return [
            'filieres' => $filieres,
            'classes' => $classes,
            'matieres' => $matieres,
            'classe_matieres' => $classeMatieres,
            'eleves' => $eleves,
            'inscriptions' => $inscriptions,
            'resultats' => $resultats,
            'paiement_statuts' => $statuts,
            'paiements' => $paiements,
        ];
    }

    private function ensureCurrentYearTrimestres(AnneeScolaire $anneeActive)
    {
        $existing = Trimestre::query()
            ->where('annee_scolaire_id', $anneeActive->id)
            ->orderBy('ordre')
            ->get();

        if ($existing->count() >= 3) {
            return $existing;
        }

        $startYear = (int) ($anneeActive->date_debut?->format('Y') ?? now()->year);
        $endYear = (int) ($anneeActive->date_fin?->format('Y') ?? ($startYear + 1));

        $definitions = [
            1 => ['libelle' => 'Trimestre 1', 'date_debut' => sprintf('%04d-10-01', $startYear), 'date_fin' => sprintf('%04d-12-31', $startYear)],
            2 => ['libelle' => 'Trimestre 2', 'date_debut' => sprintf('%04d-01-01', $endYear), 'date_fin' => sprintf('%04d-03-31', $endYear)],
            3 => ['libelle' => 'Trimestre 3', 'date_debut' => sprintf('%04d-04-01', $endYear), 'date_fin' => sprintf('%04d-06-30', $endYear)],
        ];

        foreach ($definitions as $ordre => $definition) {
            Trimestre::updateOrCreate(
                [
                    'annee_scolaire_id' => $anneeActive->id,
                    'ordre' => $ordre,
                ],
                [
                    'libelle' => $definition['libelle'],
                    'statut' => $anneeActive->statut,
                    'date_debut' => $definition['date_debut'],
                    'date_fin' => $definition['date_fin'],
                ]
            );
        }

        return Trimestre::query()
            ->where('annee_scolaire_id', $anneeActive->id)
            ->orderBy('ordre')
            ->get();
    }
}
