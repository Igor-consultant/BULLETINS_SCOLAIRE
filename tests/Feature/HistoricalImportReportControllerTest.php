<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalImportReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_direction_user_can_access_historical_import_report_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('direction');

        $response = $this->actingAs($user)->get(route('bulletins.historiques'));

        $response->assertOk();
    }
}
