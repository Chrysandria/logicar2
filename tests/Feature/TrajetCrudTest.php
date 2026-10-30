<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Expedition;
use App\Models\Trajet;
use App\Models\TrajetItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrajetCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_trip_saves_its_ordered_stops(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret',
        ]));

        $response = $this->postJson('/trajets', [
            'designation' => 'Centre — Dépôt',
            'trajet_items' => [
                ['point' => 'Centre'],
                ['point' => 'Gare'],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('trajets', ['designation' => 'Centre — Dépôt']);
        $this->assertDatabaseHas('trajet_items', ['point' => 'Centre']);
        $this->assertDatabaseHas('trajet_items', ['point' => 'Gare']);

        $this->getJson('/trajets')
            ->assertOk()
            ->assertJsonPath('0.trajet_items.0.point', 'Centre')
            ->assertJsonPath('0.trajet_items.1.point', 'Gare');
    }

    public function test_updating_a_trip_changes_its_name_and_stops(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret',
        ]));
        $trip = Trajet::create(['designation' => 'Ancien trajet']);
        TrajetItem::create(['trajet_id' => $trip->id, 'point' => 'Ancien point']);

        $response = $this->putJson("/trajets/{$trip->id}", [
            'designation' => 'Trajet modifié',
            'trajet_items' => [
                ['point' => 'Nouveau point'],
                ['point' => 'Dernier arrêt'],
            ],
        ]);

        $response->assertOk()->assertJsonPath('message', 'Trajet mis à jour.');
        $this->assertDatabaseHas('trajets', ['id' => $trip->id, 'designation' => 'Trajet modifié']);
        $this->assertDatabaseHas('trajet_items', ['trajet_id' => $trip->id, 'point' => 'Nouveau point']);
        $this->assertDatabaseMissing('trajet_items', ['trajet_id' => $trip->id, 'point' => 'Ancien point']);
    }

    public function test_deleting_a_trip_with_expeditions_returns_conflict(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret',
        ]));
        $trip = Trajet::create(['designation' => 'Trajet utilisé']);
        $car = Car::create(['immatriculation' => 'AB-123-CD']);
        Expedition::create([
            'date' => '2026-11-10',
            'hdr' => '06:00',
            'trajet_id' => $trip->id,
            'car_id' => $car->id,
        ]);

        $response = $this->deleteJson("/trajets/{$trip->id}");

        $response->assertConflict()->assertJsonPath(
            'message',
            'Ce trajet est déjà associé à une expédition et ne peut pas être supprimé.',
        );
        $this->assertModelExists($trip);
    }

    public function test_deleting_an_unused_trip_removes_it(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret',
        ]));
        $trip = Trajet::create(['designation' => 'Trajet inutilisé']);
        TrajetItem::create(['trajet_id' => $trip->id, 'point' => 'Arrêt']);

        $response = $this->deleteJson("/trajets/{$trip->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('trajets', ['id' => $trip->id]);
        $this->assertDatabaseMissing('trajet_items', ['trajet_id' => $trip->id]);
    }
}
