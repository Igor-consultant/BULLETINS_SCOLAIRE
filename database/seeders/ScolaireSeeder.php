<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Filiere;
use App\Models\Trimestre;
use Illuminate\Database\Seeder;

class ScolaireSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $annee = AnneeScolaire::updateOrCreate(
            ['libelle' => '2025-2026'],
            [
                'date_debut' => '2025-10-01',
                'date_fin' => '2026-07-31',
                'statut' => 'active',
            ]
        );

        foreach ([
            ['ordre' => 1, 'libelle' => 'Trimestre 1', 'statut' => 'active', 'date_debut' => '2025-10-01', 'date_fin' => '2025-12-31'],
            ['ordre' => 2, 'libelle' => 'Trimestre 2', 'statut' => 'brouillon', 'date_debut' => '2026-01-01', 'date_fin' => '2026-03-31'],
            ['ordre' => 3, 'libelle' => 'Trimestre 3', 'statut' => 'brouillon', 'date_debut' => '2026-04-01', 'date_fin' => '2026-06-30'],
        ] as $trimestre) {
            Trimestre::updateOrCreate(
                [
                    'annee_scolaire_id' => $annee->id,
                    'ordre' => $trimestre['ordre'],
                ],
                [
                    'libelle' => $trimestre['libelle'],
                    'statut' => $trimestre['statut'],
                    'date_debut' => $trimestre['date_debut'],
                    'date_fin' => $trimestre['date_fin'],
                ]
            );
        }

        $filieres = [
            ['code' => 'GI', 'nom' => 'Genie industriel', 'description' => 'Parcours oriente procedes, production et maintenance.'],
            ['code' => 'ELN', 'nom' => 'Electronique', 'description' => 'Parcours oriente systemes electroniques et instrumentation.'],
            ['code' => 'ELT', 'nom' => 'Electrotechnique', 'description' => 'Parcours oriente installations electriques et automatismes.'],
            ['code' => 'SIN', 'nom' => 'Systeme d information et du numerique', 'description' => 'Parcours oriente informatique, reseaux et systemes numeriques.'],
            ['code' => 'GC', 'nom' => 'Genie civil', 'description' => 'Parcours oriente batiments, topographie, metrage et chantiers.'],
            ['code' => 'GM', 'nom' => 'Genie mecanique', 'description' => 'Parcours oriente mecanique, fabrication et maintenance.'],
            ['code' => 'RTC', 'nom' => 'Reseaux et telecommunication', 'description' => 'Parcours oriente telecoms, systemes et infrastructures reseau.'],
        ];

        $filieresMap = [];

        foreach ($filieres as $data) {
            $filieresMap[$data['code']] = Filiere::updateOrCreate(
                ['code' => $data['code']],
                [
                    'nom' => $data['nom'],
                    'description' => $data['description'],
                    'actif' => true,
                ]
            );
        }

        $classes = [
            ['code' => 'STA', 'nom' => 'Tronc commun industriel', 'filiere' => 'GI'],
            ['code' => 'SSIN', 'nom' => 'Seconde SIN', 'filiere' => 'SIN'],
            ['code' => 'SF4', 'nom' => 'Seconde F4', 'filiere' => 'GC'],
            ['code' => 'PE', 'nom' => 'Premiere E', 'filiere' => 'GI'],
            ['code' => 'PF1', 'nom' => 'Premiere F1', 'filiere' => 'GM'],
            ['code' => 'PF2', 'nom' => 'Premiere F2', 'filiere' => 'ELN'],
            ['code' => 'PF3', 'nom' => 'Premiere F3', 'filiere' => 'ELT'],
            ['code' => 'PF4', 'nom' => 'Premiere F4', 'filiere' => 'GC'],
            ['code' => 'PH5', 'nom' => 'Premiere H5', 'filiere' => 'RTC'],
            ['code' => 'TF1', 'nom' => 'Terminale F1', 'filiere' => 'GM'],
            ['code' => 'TF2', 'nom' => 'Terminale F2', 'filiere' => 'ELN'],
            ['code' => 'TF3', 'nom' => 'Terminale F3', 'filiere' => 'ELT'],
            ['code' => 'TF4', 'nom' => 'Terminale F4', 'filiere' => 'GC'],
            ['code' => 'TE', 'nom' => 'Terminale E', 'filiere' => 'GI'],
            ['code' => 'TH5', 'nom' => 'Terminale H5', 'filiere' => 'RTC'],
        ];

        foreach ($classes as $data) {
            Classe::updateOrCreate(
                [
                    'code' => $data['code'],
                    'annee_scolaire_id' => $annee->id,
                ],
                [
                    'nom' => $data['nom'],
                    'filiere_id' => $filieresMap[$data['filiere']]->id,
                    'actif' => true,
                ]
            );
        }
    }
}
