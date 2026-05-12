<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\ClasseMatiere;
use App\Models\Matiere;
use Illuminate\Database\Seeder;

class MatieresSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $matieres = [
            ['code' => 'MATH', 'libelle' => 'Mathematiques'],
            ['code' => 'ANG', 'libelle' => 'Anglais'],
            ['code' => 'TECH', 'libelle' => 'Technologie'],
            ['code' => 'ELEC', 'libelle' => 'Electrotechnique appliquee'],
            ['code' => 'ELN', 'libelle' => 'Electronique appliquee'],
            ['code' => 'MAINT', 'libelle' => 'Maintenance industrielle'],
        ];

        $matieresMap = [];

        foreach ($matieres as $matiere) {
            $matieresMap[$matiere['code']] = Matiere::updateOrCreate(
                ['code' => $matiere['code']],
                [
                    'libelle' => $matiere['libelle'],
                    'actif' => true,
                ]
            );
        }

        $programmes = [
            'STA' => [
                ['code' => 'MATH', 'coefficient' => 4, 'enseignant_nom' => 'M. Ngoma'],
                ['code' => 'ANG', 'coefficient' => 2, 'enseignant_nom' => 'Mme Okemba'],
                ['code' => 'TECH', 'coefficient' => 5, 'enseignant_nom' => 'M. Tchibota'],
                ['code' => 'MAINT', 'coefficient' => 4, 'enseignant_nom' => 'M. Mbemba'],
            ],
            'PF2' => [
                ['code' => 'MATH', 'coefficient' => 4, 'enseignant_nom' => 'M. Ngoma'],
                ['code' => 'ANG', 'coefficient' => 2, 'enseignant_nom' => 'Mme Okemba'],
                ['code' => 'TECH', 'coefficient' => 4, 'enseignant_nom' => 'M. Tchicaya'],
                ['code' => 'ELN', 'coefficient' => 5, 'enseignant_nom' => 'M. Mavoungou'],
            ],
            'PF3' => [
                ['code' => 'MATH', 'coefficient' => 3, 'enseignant_nom' => 'M. Ngoma'],
                ['code' => 'ANG', 'coefficient' => 2, 'enseignant_nom' => 'Mme Okemba'],
                ['code' => 'ELN', 'coefficient' => 6, 'enseignant_nom' => 'M. Mavoungou'],
                ['code' => 'TECH', 'coefficient' => 4, 'enseignant_nom' => 'M. Tchicaya'],
            ],
            'TF2' => [
                ['code' => 'MATH', 'coefficient' => 3, 'enseignant_nom' => 'M. Ngoma'],
                ['code' => 'ANG', 'coefficient' => 2, 'enseignant_nom' => 'Mme Okemba'],
                ['code' => 'ELEC', 'coefficient' => 6, 'enseignant_nom' => 'M. Koubemba'],
                ['code' => 'TECH', 'coefficient' => 4, 'enseignant_nom' => 'Mme Moukila'],
            ],
            'TE' => [
                ['code' => 'MATH', 'coefficient' => 3, 'enseignant_nom' => 'M. Ngoma'],
                ['code' => 'ANG', 'coefficient' => 2, 'enseignant_nom' => 'Mme Okemba'],
                ['code' => 'ELEC', 'coefficient' => 5, 'enseignant_nom' => 'M. Koubemba'],
                ['code' => 'TECH', 'coefficient' => 5, 'enseignant_nom' => 'Mme Moukila'],
            ],
        ];

        $classes = Classe::query()->get()->keyBy('code');

        foreach ($programmes as $classeCode => $elements) {
            $classe = $classes->get($classeCode);

            if (! $classe) {
                continue;
            }

            foreach ($elements as $element) {
                ClasseMatiere::updateOrCreate(
                    [
                        'classe_id' => $classe->id,
                        'matiere_id' => $matieresMap[$element['code']]->id,
                    ],
                    [
                        'coefficient' => $element['coefficient'],
                        'enseignant_nom' => $element['enseignant_nom'],
                        'actif' => true,
                    ]
                );
            }
        }
    }
}
