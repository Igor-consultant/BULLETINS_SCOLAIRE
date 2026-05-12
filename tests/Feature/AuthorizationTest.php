<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_cannot_access_internal_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $parent = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $parent->assignRole('parent');

        $response = $this->actingAs($parent)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_enseignant_cannot_access_direction_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $enseignant = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $enseignant->assignRole('enseignant');

        $response = $this->actingAs($enseignant)->get('/direction');

        $response->assertForbidden();
    }

    public function test_comptabilite_can_access_comptabilite_module(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $comptable = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $comptable->assignRole('comptabilite');

        $response = $this->actingAs($comptable)->get('/comptabilite/statuts');

        $response->assertOk();
    }
}
