<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\ClasseMatiere;
use App\Models\Eleve;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\Trimestre;
use Illuminate\Database\Seeder;

class EvaluationsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $annee = AnneeScolaire::query()
            ->where('statut', 'active')
            ->latest('date_debut')
            ->first();

        if (! $annee) {
            return;
        }

        $trimestre = Trimestre::query()
            ->where('annee_scolaire_id', $annee->id)
            ->orderBy('ordre')
            ->first();

        if (! $trimestre) {
            return;
        }

        $classes = Classe::query()
            ->where('annee_scolaire_id', $annee->id)
            ->get()
            ->keyBy('code');

        $eleves = Eleve::query()
            ->get()
            ->keyBy('matricule');

        $evaluations = [
            [
                'classe_code' => 'STA',
                'matiere_code' => 'MATH',
                'libelle' => 'Devoir 1 - Mathematiques',
                'type' => 'devoir',
                'date_evaluation' => '2025-10-14',
                'note_sur' => 20,
                'statut' => 'validee',
                'notes' => [
                    'I3P-2025-001' => ['note' => 14.5, 'absence' => false],
                    'I3P-2025-006' => ['note' => 16.0, 'absence' => false],
                ],
            ],
            [
                'classe_code' => 'STA',
                'matiere_code' => 'MATH',
                'libelle' => 'Composition - Mathematiques',
                'type' => 'composition',
                'date_evaluation' => '2025-11-03',
                'note_sur' => 20,
                'statut' => 'validee',
                'notes' => [
                    'I3P-2025-001' => ['note' => 12.0, 'absence' => false],
                    'I3P-2025-006' => ['note' => 15.0, 'absence' => false],
                ],
            ],
            [
                'classe_code' => 'STA',
                'matiere_code' => 'TECH',
                'libelle' => 'Devoir 1 - Technologie',
                'type' => 'devoir',
                'date_evaluation' => '2025-10-21',
                'note_sur' => 20,
                'statut' => 'validee',
                'notes' => [
                    'I3P-2025-001' => ['note' => 13.0, 'absence' => false],
                    'I3P-2025-006' => ['note' => null, 'absence' => true, 'observation' => 'Absence justifiee'],
                ],
            ],
            [
                'classe_code' => 'PF2',
                'matiere_code' => 'ELN',
                'libelle' => 'Composition - Electronique appliquee',
                'type' => 'composition',
                'date_evaluation' => '2025-11-06',
                'note_sur' => 20,
                'statut' => 'validee',
                'notes' => [
                    'I3P-2025-002' => ['note' => 17.0, 'absence' => false],
                ],
            ],
            [
                'classe_code' => 'TE',
                'matiere_code' => 'ELEC',
                'libelle' => 'Devoir 1 - Electrotechnique',
                'type' => 'devoir',
                'date_evaluation' => '2025-10-18',
                'note_sur' => 20,
                'statut' => 'validee',
                'notes' => [
                    'I3P-2025-005' => ['note' => 11.5, 'absence' => false],
                ],
            ],
            [
                'classe_code' => 'TE',
                'matiere_code' => 'ELEC',
                'libelle' => 'Composition - Electrotechnique',
                'type' => 'composition',
                'date_evaluation' => '2025-11-08',
                'note_sur' => 20,
                'statut' => 'validee',
                'notes' => [
                    'I3P-2025-005' => ['note' => 13.0, 'absence' => false],
                ],
            ],
        ];

        foreach ($evaluations as $payload) {
            $classe = $classes->get($payload['classe_code']);

            if (! $classe) {
                continue;
            }

            $classeMatiere = ClasseMatiere::query()
                ->where('classe_id', $classe->id)
                ->whereHas('matiere', fn ($query) => $query->where('code', $payload['matiere_code']))
                ->first();

            if (! $classeMatiere) {
                continue;
            }

            $evaluation = Evaluation::updateOrCreate(
                [
                    'classe_matiere_id' => $classeMatiere->id,
                    'trimestre_id' => $trimestre->id,
                    'libelle' => $payload['libelle'],
                ],
                [
                    'type' => $payload['type'],
                    'date_evaluation' => $payload['date_evaluation'],
                    'note_sur' => $payload['note_sur'],
                    'coefficient_local' => null,
                    'statut' => $payload['statut'],
                ]
            );

            foreach ($payload['notes'] as $matricule => $noteData) {
                $eleve = $eleves->get($matricule);

                if (! $eleve) {
                    continue;
                }

                Note::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'eleve_id' => $eleve->id,
                    ],
                    [
                        'note' => $noteData['note'],
                        'absence' => $noteData['absence'],
                        'observation' => $noteData['observation'] ?? null,
                    ]
                );
            }
        }
    }
}
