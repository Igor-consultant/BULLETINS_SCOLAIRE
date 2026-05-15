<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\PaiementStatut;
use App\Models\Resultat;
use App\Models\Trimestre;
use Illuminate\Support\Collection;

class BulletinWorkflowService
{
    public function __construct(
        private readonly BulletinSettingsService $settings,
    ) {
    }

    public function buildTrimestrielData(): array
    {
        $anneeActive = AnneeScolaire::query()
            ->where('statut', 'active')
            ->latest('date_debut')
            ->first();

        $trimestre = Trimestre::query()
            ->where('annee_scolaire_id', $anneeActive?->id)
            ->orderBy('ordre')
            ->first();

        $classes = Classe::query()
            ->with([
                'filiere',
                'classeMatieres.matiere',
                'classeMatieres.evaluations' => fn ($query) => $query
                    ->where('trimestre_id', $trimestre?->id)
                    ->with(['notes']),
                'inscriptions.eleve',
            ])
            ->where('annee_scolaire_id', $anneeActive?->id)
            ->orderBy('code')
            ->get();

        $resultatsParClasse = $classes->map(function (Classe $classe) {
            $eleves = $classe->inscriptions
                ->sortBy(fn ($inscription) => ($inscription->eleve?->nom ?? '').' '.($inscription->eleve?->prenoms ?? ''))
                ->map(function ($inscription) use ($classe) {
                    $eleve = $inscription->eleve;

                    $matieres = $classe->classeMatieres
                        ->map(function ($classeMatiere) use ($eleve) {
                            $devoirNotes = [];
                            $compositionNotes = [];

                            foreach ($classeMatiere->evaluations as $evaluation) {
                                $note = $evaluation->notes->firstWhere('eleve_id', $eleve?->id);

                                if (! $note || $note->absence || $note->note === null) {
                                    continue;
                                }

                                $valeur = (float) $note->note;

                                if ($evaluation->type === 'devoir') {
                                    $devoirNotes[] = $valeur;
                                }

                                if ($evaluation->type === 'composition') {
                                    $compositionNotes[] = $valeur;
                                }
                            }

                            $moyenneDevoirs = count($devoirNotes) > 0
                                ? array_sum($devoirNotes) / count($devoirNotes)
                                : null;

                            $composition = count($compositionNotes) > 0
                                ? array_sum($compositionNotes) / count($compositionNotes)
                                : null;

                            $moyenneMatiere = $this->settings->moyenneMatiere($moyenneDevoirs, $composition);

                            $coefficient = (float) $classeMatiere->coefficient;

                            return [
                                'matiere_id' => $classeMatiere->matiere?->id,
                                'matiere' => $classeMatiere->matiere?->libelle,
                                'coefficient' => $coefficient,
                                'moyenne_devoirs' => $moyenneDevoirs,
                                'composition' => $composition,
                                'moyenne_matiere' => $moyenneMatiere,
                                'points' => $moyenneMatiere !== null ? $moyenneMatiere * $coefficient : null,
                                'rang' => null,
                                'appreciation' => $this->appreciationCourte($moyenneMatiere),
                            ];
                        })
                        ->filter(fn ($matiere) => $matiere['moyenne_matiere'] !== null)
                        ->values();

                    $totalPoints = $matieres->sum(fn ($matiere) => $matiere['points'] ?? 0);
                    $totalCoefficients = $matieres->sum(fn ($matiere) => $matiere['coefficient'] ?? 0);
                    $moyenneGenerale = $totalCoefficients > 0
                        ? $totalPoints / $totalCoefficients
                        : null;

                    return [
                        'eleve' => $eleve,
                        'matieres' => $matieres,
                        'total_points' => $totalPoints,
                        'total_coefficients' => $totalCoefficients,
                        'moyenne_generale' => $moyenneGenerale,
                        'rang' => null,
                    ];
                })
                ->filter(fn ($ligne) => $ligne['eleve'] !== null)
                ->values();

            $eleves = $this->assignMatiereRanks($eleves);
            $eleves = $this->sortStudentRowsByAverage($eleves)
                ->values()
                ->map(function (array $ligne, int $index) {
                    if ($ligne['moyenne_generale'] === null) {
                        $ligne['rang'] = null;

                        return $ligne;
                    }

                    $ligne['rang'] = $index + 1;

                    return $ligne;
                });

            return [
                'classe' => $classe,
                'eleves' => $eleves,
            ];
        })->filter(fn ($bloc) => $bloc['eleves']->isNotEmpty())->values();

        return [
            'anneeActive' => $anneeActive,
            'trimestre' => $trimestre,
            'resultatsParClasse' => $resultatsParClasse,
            'settings' => $this->settings->all(),
            'stats' => [
                'classes' => $resultatsParClasse->count(),
                'eleves' => $resultatsParClasse->sum(fn ($bloc) => $bloc['eleves']->count()),
                'matieres_calculees' => $resultatsParClasse->sum(
                    fn ($bloc) => $bloc['eleves']->sum(fn ($ligne) => $ligne['matieres']->count())
                ),
            ],
        ];
    }

    public function findPaiementStatutForTrimestre(Eleve $eleve, Trimestre $trimestre): ?PaiementStatut
    {
        return PaiementStatut::query()
            ->where('eleve_id', $eleve->id)
            ->where('annee_scolaire_id', $trimestre->annee_scolaire_id)
            ->latest('id')
            ->first();
    }

    public function buildBulletinData(Eleve $eleve, Trimestre $trimestre): array
    {
        $resultats = Resultat::query()
            ->with([
                'classe.filiere',
                'classe.classeMatieres',
                'matiere',
                'trimestre.anneeScolaire',
            ])
            ->where('eleve_id', $eleve->id)
            ->where('trimestre_id', $trimestre->id)
            ->orderBy('matiere_id')
            ->get();

        abort_if($resultats->isEmpty(), 404);

        $classe = $resultats->first()->classe;
        $annee = $resultats->first()->trimestre?->anneeScolaire;
        $teacherMap = $classe?->classeMatieres?->keyBy('matiere_id') ?? collect();
        $totalPoints = $resultats->sum(fn ($resultat) => (float) $resultat->points);
        $totalCoefficients = $resultats->sum(fn ($resultat) => (float) $resultat->coefficient);
        $moyenneGenerale = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : null;
        $classeStats = $classe ? $this->computeClasseStats($classe->id, $trimestre->id) : [
            'rang' => null,
            'premier' => null,
            'dernier' => null,
            'effectif' => 0,
        ];
        $matiereRanks = $classe ? $this->computeMatiereRanks($classe->id, $trimestre->id) : [];
        $rang = $classeStats['rang'][$eleve->id] ?? null;
        $lignes = $resultats->map(function (Resultat $resultat) use ($teacherMap, $eleve, $matiereRanks) {
            $teacher = optional($teacherMap->get($resultat->matiere_id))->enseignant_nom;

            return [
                'matiere' => $resultat->matiere?->libelle,
                'coefficient' => (float) $resultat->coefficient,
                'moyenne_devoirs' => $resultat->moyenne_devoirs !== null ? (float) $resultat->moyenne_devoirs : null,
                'composition' => $resultat->composition !== null ? (float) $resultat->composition : null,
                'moyenne_matiere' => $resultat->moyenne_matiere !== null ? (float) $resultat->moyenne_matiere : null,
                'points' => $resultat->points !== null ? (float) $resultat->points : null,
                'rang' => $matiereRanks[$resultat->matiere_id][$eleve->id] ?? null,
                'professeur' => $teacher,
                'appreciation' => $this->appreciationCourte($resultat->moyenne_matiere !== null ? (float) $resultat->moyenne_matiere : null),
            ];
        });

        return [
            'eleve' => $eleve,
            'trimestre' => $trimestre,
            'annee' => $annee,
            'classe' => $classe,
            'resultats' => $resultats,
            'lignes' => $lignes,
            'paiementStatut' => $this->findPaiementStatutForTrimestre($eleve, $trimestre),
            'bulletinAccessGateEnabled' => $this->settings->paymentGateEnabled(),
            'header' => $this->settings->header(),
            'dateEmission' => now(),
            'synthese' => [
                'total_points' => $totalPoints,
                'total_coefficients' => $totalCoefficients,
                'moyenne_generale' => $moyenneGenerale,
                'rang' => $rang,
                'premier' => $classeStats['premier'],
                'dernier' => $classeStats['dernier'],
                'effectif' => $classeStats['effectif'],
                'appreciation_generale' => $this->appreciationLongue($moyenneGenerale),
                'sanction' => $this->sanctionAcademique($moyenneGenerale),
            ],
        ];
    }

    public function appreciationCourte(?float $moyenne): ?string
    {
        if ($moyenne === null) {
            return null;
        }

        return $this->settings->shortAppreciation($moyenne);
    }

    public function appreciationLongue(?float $moyenne): string
    {
        return $this->settings->generalAppreciation($moyenne);
    }

    public function sanctionAcademique(?float $moyenne): string
    {
        return $this->settings->sanction($moyenne);
    }

    public function computeClasseStats(int $classeId, int $trimestreId): array
    {
        $resultats = Resultat::query()
            ->with('eleve')
            ->where('classe_id', $classeId)
            ->where('trimestre_id', $trimestreId)
            ->get()
            ->groupBy('eleve_id')
            ->map(function (Collection $rows, int $eleveId) {
                $points = $rows->sum(fn (Resultat $resultat) => (float) $resultat->points);
                $coefficients = $rows->sum(fn (Resultat $resultat) => (float) $resultat->coefficient);
                $moyenne = $coefficients > 0 ? $points / $coefficients : null;

                return [
                    'eleve_id' => $eleveId,
                    'eleve' => $rows->first()?->eleve,
                    'moyenne' => $moyenne,
                ];
            })
            ->filter(fn (array $row) => $row['moyenne'] !== null)
            ->sort(function (array $a, array $b) {
                if (abs($a['moyenne'] - $b['moyenne']) >= 0.00001) {
                    return $a['moyenne'] < $b['moyenne'] ? 1 : -1;
                }

                return $this->compareStudentNames($a['eleve'], $b['eleve']);
            })
            ->values();

        $ranks = [];

        foreach ($resultats as $index => $row) {
            $ranks[$row['eleve_id']] = $index + 1;
        }

        return [
            'rang' => $ranks,
            'premier' => $resultats->first()['moyenne'] ?? null,
            'dernier' => $resultats->last()['moyenne'] ?? null,
            'effectif' => $resultats->count(),
        ];
    }

    public function bulletinAccessAllowed(Eleve $eleve, Trimestre $trimestre): bool
    {
        if (! $this->settings->paymentGateEnabled()) {
            return true;
        }

        $paiementStatut = $this->findPaiementStatutForTrimestre($eleve, $trimestre);

        return $paiementStatut?->autorise_acces_bulletin ?? true;
    }

    private function assignMatiereRanks(Collection $eleves): Collection
    {
        $rankMap = [];

        $matiereRows = $eleves
            ->flatMap(function (array $ligne) {
                return $ligne['matieres']->map(function (array $matiere) use ($ligne) {
                    return [
                        'matiere_id' => $matiere['matiere_id'],
                        'eleve' => $ligne['eleve'],
                        'eleve_id' => $ligne['eleve']->id,
                        'moyenne_matiere' => $matiere['moyenne_matiere'],
                    ];
                });
            })
            ->filter(fn (array $row) => $row['matiere_id'] && $row['moyenne_matiere'] !== null)
            ->groupBy('matiere_id');

        foreach ($matiereRows as $matiereId => $rows) {
            $sortedRows = $rows->sort(function (array $a, array $b) {
                if (abs($a['moyenne_matiere'] - $b['moyenne_matiere']) >= 0.00001) {
                    return $a['moyenne_matiere'] < $b['moyenne_matiere'] ? 1 : -1;
                }

                return $this->compareStudentNames($a['eleve'], $b['eleve']);
            })->values();

            foreach ($sortedRows as $index => $row) {
                $rankMap[$matiereId][$row['eleve_id']] = $index + 1;
            }
        }

        return $eleves->map(function (array $ligne) use ($rankMap) {
            $ligne['matieres'] = $ligne['matieres']->map(function (array $matiere) use ($ligne, $rankMap) {
                $matiere['rang'] = $rankMap[$matiere['matiere_id']][$ligne['eleve']->id] ?? null;

                return $matiere;
            });

            return $ligne;
        });
    }

    private function sortStudentRowsByAverage(Collection $eleves): Collection
    {
        return $eleves->sort(function (array $a, array $b) {
            $moyenneA = $a['moyenne_generale'];
            $moyenneB = $b['moyenne_generale'];

            if ($moyenneA === null && $moyenneB === null) {
                return $this->compareStudentNames($a['eleve'], $b['eleve']);
            }

            if ($moyenneA === null) {
                return 1;
            }

            if ($moyenneB === null) {
                return -1;
            }

            if (abs($moyenneA - $moyenneB) >= 0.00001) {
                return $moyenneA < $moyenneB ? 1 : -1;
            }

            return $this->compareStudentNames($a['eleve'], $b['eleve']);
        });
    }

    private function compareStudentNames($eleveA, $eleveB): int
    {
        $nameA = mb_strtolower(trim(($eleveA?->nom ?? '').' '.($eleveA?->prenoms ?? '')));
        $nameB = mb_strtolower(trim(($eleveB?->nom ?? '').' '.($eleveB?->prenoms ?? '')));

        return strcmp($nameA, $nameB);
    }

    private function computeMatiereRanks(int $classeId, int $trimestreId): array
    {
        $rankMap = [];
        $rows = Resultat::query()
            ->with('eleve')
            ->where('classe_id', $classeId)
            ->where('trimestre_id', $trimestreId)
            ->get()
            ->filter(fn (Resultat $row) => $row->matiere_id && $row->moyenne_matiere !== null)
            ->groupBy('matiere_id');

        foreach ($rows as $matiereId => $matiereRows) {
            $sortedRows = $matiereRows->sort(function (Resultat $a, Resultat $b) {
                $moyenneA = (float) $a->moyenne_matiere;
                $moyenneB = (float) $b->moyenne_matiere;

                if (abs($moyenneA - $moyenneB) >= 0.00001) {
                    return $moyenneA < $moyenneB ? 1 : -1;
                }

                return $this->compareStudentNames($a->eleve, $b->eleve);
            })->values();

            foreach ($sortedRows as $index => $row) {
                $rankMap[$matiereId][$row->eleve_id] = $index + 1;
            }
        }

        return $rankMap;
    }
}
