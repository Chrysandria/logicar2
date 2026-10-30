<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Employe;
use App\Models\Expedition;
use App\Models\Planning;
use App\Models\PlanningItem;
use App\Models\Trajet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpeditionCarScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_cars_endpoint_excludes_vehicles_with_conflicting_expeditions(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $availableCar = Car::create(['immatriculation' => 'AB-123-CD']);
        $scheduledCar = Car::create(['immatriculation' => 'EF-456-GH']);
        Car::create(['immatriculation' => 'IJ-789-KL', 'is_available' => false]);
        $trip = Trajet::create(['designation' => 'Centre - Depot']);
        Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '11:30',
            'trajet_id' => $trip->id,
            'car_id' => $scheduledCar->id,
        ]);

        $this->getJson('/expeditions/available-cars?date=2026-10-30&hdr=09:00')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $availableCar->id);
    }

    public function test_available_cars_endpoint_requires_a_valid_date_and_hour(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));

        $this->getJson('/expeditions/available-cars?date=not-a-date&hdr=9am')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date', 'hdr']);
    }

    public function test_unavailable_car_cannot_be_assigned_to_an_expedition(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $car = Car::create(['immatriculation' => 'AB-123-CD', 'is_available' => false]);
        $trip = Trajet::create(['designation' => 'Centre - Depot']);
        $expedition = $this->createUnassignedExpedition($trip);

        $this->putJson("/expeditions/{$expedition->id}", [
            'car_id' => $car->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Ce véhicule est actuellement indisponible.');

        $this->assertDatabaseHas('expeditions', ['id' => $expedition->id, 'car_id' => null]);
    }

    public function test_unassigned_employees_from_the_same_planning_can_be_added_to_an_existing_expedition(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $trip = Trajet::create(['designation' => 'Centre - Depot']);
        $expedition = Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '09:00',
            'trajet_id' => $trip->id,
        ]);
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '11:00']);
        $existingItem = $this->createPlanningItem($planning, 'EMP-001', $expedition->id);
        $newItem = $this->createPlanningItem($planning, 'EMP-002');

        $this->putJson("/expeditions/{$expedition->id}", [
            'planning_id' => $planning->id,
            'add_list' => [['planning_item_id' => $newItem->id]],
        ])->assertOk();

        $this->assertDatabaseHas('planning_items', [
            'id' => $existingItem->id,
            'expedition_id' => $expedition->id,
        ]);
        $this->assertDatabaseHas('planning_items', [
            'id' => $newItem->id,
            'expedition_id' => $expedition->id,
            'ok' => false,
        ]);
    }

    public function test_employees_from_another_planning_cannot_be_added_to_an_expedition(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $trip = Trajet::create(['designation' => 'Centre - Depot']);
        $expedition = Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '09:00',
            'trajet_id' => $trip->id,
        ]);
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '11:00']);
        $otherPlanning = Planning::create(['dps' => '2026-10-30', 'hps' => '12:00']);
        $otherPlanningItem = $this->createPlanningItem($otherPlanning, 'EMP-003');

        $this->putJson("/expeditions/{$expedition->id}", [
            'planning_id' => $planning->id,
            'add_list' => [['planning_item_id' => $otherPlanningItem->id]],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Certains employés ne sont plus disponibles pour cette expédition.');

        $this->assertDatabaseHas('planning_items', [
            'id' => $otherPlanningItem->id,
            'expedition_id' => null,
        ]);
    }

    public function test_legacy_update_list_still_marks_assigned_employees_as_absent(): void
    {
        [$car, $trip] = $this->authenticateAndCreateCarAndTrip();
        $expedition = $this->createUnassignedExpedition($trip);
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '11:00']);
        $planningItem = $this->createPlanningItem($planning, 'EMP-004', $expedition->id);

        $this->putJson("/expeditions/{$expedition->id}", [
            'car_id' => $car->id,
            'list' => [['planning_item_id' => $planningItem->id]],
        ])->assertOk();

        $this->assertDatabaseHas('expeditions', [
            'id' => $expedition->id,
            'car_id' => $car->id,
        ]);
        $this->assertDatabaseHas('planning_items', [
            'id' => $planningItem->id,
            'expedition_id' => $expedition->id,
            'ok' => false,
        ]);
    }

    public function test_car_cannot_be_assigned_to_expeditions_less_than_three_hours_apart(): void
    {
        [$car, $trip] = $this->authenticateAndCreateCarAndTrip();
        $existingExpedition = Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '09:00',
            'trajet_id' => $trip->id,
            'car_id' => $car->id,
        ]);
        $newExpedition = $this->createUnassignedExpedition($trip);

        $response = $this->putJson("/expeditions/{$newExpedition->id}", [
            'car_id' => $car->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Ce véhicule a déjà une expédition prévue dans les trois heures autour de cet horaire.');
        $this->assertDatabaseHas('expeditions', ['id' => $existingExpedition->id, 'car_id' => $car->id]);
        $this->assertDatabaseHas('expeditions', ['id' => $newExpedition->id, 'car_id' => null]);
    }

    public function test_car_schedule_conflict_is_checked_across_midnight(): void
    {
        [$car, $trip] = $this->authenticateAndCreateCarAndTrip();
        Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '23:30',
            'trajet_id' => $trip->id,
            'car_id' => $car->id,
        ]);
        $newExpedition = $this->createUnassignedExpedition($trip, '2026-10-31', '01:00');

        $this->putJson("/expeditions/{$newExpedition->id}", [
            'car_id' => $car->id,
        ])->assertUnprocessable();

        $this->assertDatabaseHas('expeditions', ['id' => $newExpedition->id, 'car_id' => null]);
    }

    public function test_car_cannot_be_assigned_when_another_expedition_follows_within_three_hours(): void
    {
        [$car, $trip] = $this->authenticateAndCreateCarAndTrip();
        Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '12:00',
            'trajet_id' => $trip->id,
            'car_id' => $car->id,
        ]);
        $newExpedition = $this->createUnassignedExpedition($trip, '2026-10-30', '09:01');

        $this->putJson("/expeditions/{$newExpedition->id}", [
            'car_id' => $car->id,
        ])->assertUnprocessable();

        $this->assertDatabaseHas('expeditions', ['id' => $newExpedition->id, 'car_id' => null]);
    }

    public function test_car_can_be_assigned_when_another_expedition_is_exactly_three_hours_apart(): void
    {
        [$car, $trip] = $this->authenticateAndCreateCarAndTrip();
        Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '09:00',
            'trajet_id' => $trip->id,
            'car_id' => $car->id,
        ]);
        $newExpedition = $this->createUnassignedExpedition($trip, '2026-10-30', '12:00');

        $this->putJson("/expeditions/{$newExpedition->id}", [
            'car_id' => $car->id,
        ])->assertOk();

        $this->assertDatabaseHas('expeditions', ['id' => $newExpedition->id, 'car_id' => $car->id]);
    }

    private function authenticateAndCreateCarAndTrip(): array
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));

        return [
            Car::create(['immatriculation' => 'AB-123-CD']),
            Trajet::create(['designation' => 'Centre - Depot']),
        ];
    }

    private function createUnassignedExpedition(Trajet $trip, string $date = '2026-10-30', string $hour = '11:00'): Expedition
    {
        return Expedition::create([
            'date' => $date,
            'hdr' => $hour,
            'trajet_id' => $trip->id,
        ]);
    }

    private function createPlanningItem(Planning $planning, string $matricule, ?int $expeditionId = null): PlanningItem
    {
        $employe = Employe::create([
            'matricule' => $matricule,
            'service' => 'SNR',
            'nom' => "Employé {$matricule}",
            'telephone' => '00000000',
            'lieu_ramassage' => 'Centre',
            'pnt_adm' => 'Centre',
            'pnt_dec' => 'Depot',
        ]);

        return PlanningItem::create([
            'employe_id' => $employe->id,
            'planning_id' => $planning->id,
            'expedition_id' => $expeditionId,
        ]);
    }
}
