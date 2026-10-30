<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SnrRouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_snr_user_receives_403_on_snr_routes(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));

        $this->getJson('/cars')
            ->assertForbidden()
            ->assertJsonPath('message', 'Accès réservé au service SNR.');
    }

    public function test_snr_user_receives_success_on_snr_routes(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));

        $this->getJson('/cars')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_service_user_can_still_access_shared_planning_routes(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));

        $this->getJson('/plannings?dps=2026-10-30')
            ->assertOk()
            ->assertExactJson([]);
    }
}
