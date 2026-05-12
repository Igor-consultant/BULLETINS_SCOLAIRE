<?php

namespace Database\Seeders;

use App\Models\Eleve;
use App\Models\ParentEleve;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParentsPortailSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $parent = User::firstOrCreate(
            ['email' => 'parent@bulletins-scolaire.test'],
            [
                'name' => 'Parent I3P Demo',
                'password' => bcrypt('password'),
            ]
        );

        if (! $parent->hasRole('parent')) {
            $parent->assignRole('parent');
        }

        $rattachements = [
            'I3P-2025-001' => 'Pere',
            'I3P-2025-004' => 'Pere',
            'I3P-2025-005' => 'Pere',
        ];

        foreach ($rattachements as $matricule => $lienParente) {
            $eleve = Eleve::query()
                ->where('matricule', $matricule)
                ->first();

            if (! $eleve) {
                continue;
            }

            ParentEleve::firstOrCreate(
                [
                    'user_id' => $parent->id,
                    'eleve_id' => $eleve->id,
                ],
                [
                    'lien_parente' => $lienParente,
                ]
            );
        }
    }
}
