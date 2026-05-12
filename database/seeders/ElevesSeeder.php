<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Inscription;
use Illuminate\Database\Seeder;

class ElevesSeeder extends Seeder
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

        $classes = Classe::query()
            ->where('annee_scolaire_id', $annee->id)
            ->get()
            ->keyBy('code');

        $eleves = [
            [
                'matricule' => 'I3P-2025-001',
                'nom' => 'MABIALA',
                'prenoms' => 'Kevin Armel',
                'sexe' => 'M',
                'date_naissance' => '2007-03-14',
                'lieu_naissance' => 'Pointe-Noire',
                'contact_principal' => '+242060000001',
                'nom_parent' => 'M. Mabiala',
                'contact_parent' => '+242050000001',
                'adresse' => 'Quartier Tie-Tie, Pointe-Noire',
                'classe_code' => 'STA',
            ],
            [
                'matricule' => 'I3P-2025-002',
                'nom' => 'OKEMBA',
                'prenoms' => 'Grace Naomi',
                'sexe' => 'F',
                'date_naissance' => '2006-11-22',
                'lieu_naissance' => 'Dolisie',
                'contact_principal' => '+242060000002',
                'nom_parent' => 'Mme Okemba',
                'contact_parent' => '+242050000002',
                'adresse' => 'Quartier Loandjili, Pointe-Noire',
                'classe_code' => 'PF2',
            ],
            [
                'matricule' => 'I3P-2025-003',
                'nom' => 'TCHICAYA',
                'prenoms' => 'Bryan Exauce',
                'sexe' => 'M',
                'date_naissance' => '2007-08-09',
                'lieu_naissance' => 'Brazzaville',
                'contact_principal' => '+242060000003',
                'nom_parent' => 'M. Tchicaya',
                'contact_parent' => '+242050000003',
                'adresse' => 'Quartier Mpita, Pointe-Noire',
                'classe_code' => 'PF3',
            ],
            [
                'matricule' => 'I3P-2025-004',
                'nom' => 'MOUKILA',
                'prenoms' => 'Diane Carine',
                'sexe' => 'F',
                'date_naissance' => '2006-05-30',
                'lieu_naissance' => 'Pointe-Noire',
                'contact_principal' => '+242060000004',
                'nom_parent' => 'Mme Moukila',
                'contact_parent' => '+242050000004',
                'adresse' => 'Quartier Mongo-Mpoukou, Pointe-Noire',
                'classe_code' => 'TF2',
            ],
            [
                'matricule' => 'I3P-2025-005',
                'nom' => 'KOUBEMBA',
                'prenoms' => 'Jordan Steve',
                'sexe' => 'M',
                'date_naissance' => '2005-12-17',
                'lieu_naissance' => 'Nkayi',
                'contact_principal' => '+242060000005',
                'nom_parent' => 'M. Koubemba',
                'contact_parent' => '+242050000005',
                'adresse' => 'Quartier Vindoulou, Pointe-Noire',
                'classe_code' => 'TE',
            ],
            [
                'matricule' => 'I3P-2025-006',
                'nom' => 'MBEMBA',
                'prenoms' => 'Jessica Ruth',
                'sexe' => 'F',
                'date_naissance' => '2007-01-08',
                'lieu_naissance' => 'Pointe-Noire',
                'contact_principal' => '+242060000006',
                'nom_parent' => 'Mme Mbemba',
                'contact_parent' => '+242050000006',
                'adresse' => 'Quartier Mvoumvou, Pointe-Noire',
                'classe_code' => 'STA',
            ],
        ];

        foreach ($eleves as $payload) {
            $classe = $classes->get($payload['classe_code']);

            if (! $classe) {
                continue;
            }

            $eleve = Eleve::updateOrCreate(
                ['matricule' => $payload['matricule']],
                [
                    'nom' => $payload['nom'],
                    'prenoms' => $payload['prenoms'],
                    'sexe' => $payload['sexe'],
                    'date_naissance' => $payload['date_naissance'],
                    'lieu_naissance' => $payload['lieu_naissance'],
                    'contact_principal' => $payload['contact_principal'],
                    'nom_parent' => $payload['nom_parent'],
                    'contact_parent' => $payload['contact_parent'],
                    'adresse' => $payload['adresse'],
                    'actif' => true,
                ]
            );

            Inscription::updateOrCreate(
                [
                    'eleve_id' => $eleve->id,
                    'annee_scolaire_id' => $annee->id,
                ],
                [
                    'classe_id' => $classe->id,
                    'statut' => 'inscrit',
                    'date_inscription' => $annee->date_debut,
                ]
            );
        }
    }
}
