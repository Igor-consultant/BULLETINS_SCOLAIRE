<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\PaiementStatut;
use App\Models\Resultat;
use App\Models\Trimestre;

class BulletinWorkflowService
{
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

                            $moyenneMatiere = $moyenneDevoirs !== null && $composition !== null
                                ? ($moyenneDevoirs + $composition) / 2
                                : null;

                            $coefficient = (float) $classeMatiere->coefficient;

                            return [
                                'matiere_id' => $classeMatiere->matiere?->id,
                                'matiere' => $classeMatiere->matiere?->libelle,
                                'coefficient' => $coefficient,
                                'moyenne_devoirs' => $moyenneDevoirs,
                                'composition' => $composition,
                                'moyenne_matiere' => $moyenneMatiere,
                                'points' => $moyenneMatiere !== null ? $moyenneMatiere * $coefficient : null,
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
                ->sort(function (array $a, array $b) {
                    $moyenneA = $a['moyenne_generale'];
                    $moyenneB = $b['moyenne_generale'];

                    if ($moyenneA === null && $moyenneB === null) {
                        return strcmp(
                            ($a['eleve']->nom ?? '').' '.($a['eleve']->prenoms ?? ''),
                            ($b['eleve']->nom ?? '').' '.($b['eleve']->prenoms ?? '')
                        );
                    }

                    if ($moyenneA === null) {
                        return 1;
                    }

                    if ($moyenneB === null) {
                        return -1;
                    }

                    if (abs($moyenneA - $moyenneB) < 0.00001) {
                        return strcmp(
                            ($a['eleve']->nom ?? '').' '.($a['eleve']->prenoms ?? ''),
                            ($b['eleve']->nom ?? '').' '.($b['eleve']->prenoms ?? '')
                        );
                    }

                    return $moyenneA < $moyenneB ? 1 : -1;
                })
                ->values();

            $previousAverage = null;
            $currentRank = 0;

            $eleves = $eleves->map(function (array $ligne, int $index) use (&$previousAverage, &$currentRank) {
                $moyenne = $ligne['moyenne_generale'];

                if ($moyenne === null) {
                    $ligne['rang'] = null;

                    return $ligne;
                }

                if ($previousAverage === null || abs($moyenne - $previousAverage) >= 0.00001) {
                    $currentRank = $index + 1;
                    $previousAverage = $moyenne;
                }

                $ligne['rang'] = $currentRank;

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
        $totalPoints = $resultats->sum(fn ($resultat) => (float) $resultat->points);
        $totalCoefficients = $resultats->sum(fn ($resultat) => (float) $resultat->coefficient);
        $moyenneGenerale = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : null;
        $rang = $resultats->pluck('rang')->filter(fn ($value) => $value !== null)->first();

        return [
            'eleve' => $eleve,
            'trimestre' => $trimestre,
            'annee' => $annee,
            'classe' => $classe,
            'resultats' => $resultats,
            'paiementStatut' => $this->findPaiementStatutForTrimestre($eleve, $trimestre),
            'dateEmission' => now(),
            'synthese' => [
                'total_points' => $totalPoints,
                'total_coefficients' => $totalCoefficients,
                'moyenne_generale' => $moyenneGenerale,
                'rang' => $rang,
            ],
        ];
    }
}
