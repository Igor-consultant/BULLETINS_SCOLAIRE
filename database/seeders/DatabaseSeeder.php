<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ScolaireSeeder::class,
            MatieresSeeder::class,
            ElevesSeeder::class,
            EvaluationsSeeder::class,
            PaiementsStatutsSeeder::class,
            ParentsPortailSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@bulletins-scolaire.test'],
            [
                'name' => 'Administrateur I3P',
                'password' => bcrypt('password'),
            ]
        );

        if (! $admin->hasRole('administration')) {
            $admin->assignRole('administration');
        }
    }
}
