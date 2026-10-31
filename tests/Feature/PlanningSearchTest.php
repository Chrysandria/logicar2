<?php

namespace Tests\Feature;

use App\Models\Employe;
use App\Models\Planning;
use App\Models\PlanningItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_search_its_plannings_for_a_date_without_exposing_other_services(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));

        $ownEmployee = Employe::create([
            'matricule' => 'CU-001',
            'service' => 'CUISINE',
            'nom' => 'Employé Cuisine',
            'telephone' => '00000000',
            'lieu_ramassage' => 'Centre',
            'pnt_adm' => 'ADM',
            'pnt_dec' => 'Décalé',
        ]);
        $otherEmployee = Employe::create([
            'matricule' => 'TR-001',
            'service' => 'TRANSPORT',
            'nom' => 'Employé Transport',
            'telephone' => '11111111',
            'lieu_ramassage' => 'Gare',
            'pnt_adm' => 'ADM',
            'pnt_dec' => 'Décalé',
        ]);
        $planning = Planning::create(['dps' => '2026-10-25', 'hps' => '06:00']);
        PlanningItem::create(['employe_id' => $ownEmployee->id, 'planning_id' => $planning->id]);
        PlanningItem::create(['employe_id' => $otherEmployee->id, 'planning_id' => $planning->id]);

        $response = $this->getJson('/plannings?dps=2026-10-25');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.planning_items.0.employe.matricule', 'CU-001')
            ->assertJsonMissing(['matricule' => 'TR-001']);
    }

    public function test_service_can_view_an_employees_planning_history(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));

        $employee = Employe::create([
            'matricule' => 'CU-002',
            'service' => 'CUISINE',
            'nom' => 'Employé Historique',
            'telephone' => '00000000',
            'lieu_ramassage' => 'Centre',
            'pnt_adm' => 'ADM',
            'pnt_dec' => 'Décalé',
        ]);
        $olderPlanning = Planning::create(['dps' => '2026-10-20', 'hps' => '06:00']);
        $newerPlanning = Planning::create(['dps' => '2026-10-25', 'hps' => '07:00']);
        PlanningItem::create(['employe_id' => $employee->id, 'planning_id' => $olderPlanning->id]);
        PlanningItem::create([
            'employe_id' => $employee->id,
            'planning_id' => $newerPlanning->id,
            'ok' => false,
            'remarque' => 'Absent',
        ]);

        $response = $this->getJson("/planning-history?employee_id={$employee->id}");

        $response->assertOk()
            ->assertJsonPath('data.0.planning.dps', '2026-10-25')
            ->assertJsonPath('data.0.ok', false)
            ->assertJsonPath('data.0.remarque', 'Absent')
            ->assertJsonPath('data.1.planning.dps', '2026-10-20');
    }

    public function test_service_cannot_view_another_services_employees_history(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));

        $otherEmployee = Employe::create([
            'matricule' => 'TR-002',
            'service' => 'TRANSPORT',
            'nom' => 'Employé Transport',
            'telephone' => '11111111',
            'lieu_ramassage' => 'Gare',
            'pnt_adm' => 'ADM',
            'pnt_dec' => 'Décalé',
        ]);

        $this->getJson("/planning-history?employee_id={$otherEmployee->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_employee_history_returns_422_when_employee_id_is_missing(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));

        $this->getJson('/planning-history')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_id']);
    }

    public function test_service_can_add_employees_to_an_existing_planning(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));
        $planning = Planning::create([
            'dps' => today()->addDays(10)->toDateString(),
            'hps' => '06:00',
        ]);
        $employee = $this->createEmployee('CU-003', 'CUISINE');

        $this->postJson("/plannings/{$planning->id}/employees", [
            'employee_ids' => [$employee->id],
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Employés ajoutés au planning.');

        $this->assertDatabaseHas('planning_items', [
            'planning_id' => $planning->id,
            'employe_id' => $employee->id,
            'expedition_id' => null,
        ]);
    }

    public function test_service_cannot_add_an_employee_from_another_service_to_a_planning(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));
        $planning = Planning::create([
            'dps' => today()->addDays(10)->toDateString(),
            'hps' => '06:00',
        ]);
        $employee = $this->createEmployee('TR-003', 'TRANSPORT');

        $this->postJson("/plannings/{$planning->id}/employees", [
            'employee_ids' => [$employee->id],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_ids.0']);

        $this->assertDatabaseMissing('planning_items', [
            'planning_id' => $planning->id,
            'employe_id' => $employee->id,
        ]);
    }

    public function test_service_cannot_add_an_employee_already_planned_for_that_date(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));
        $date = today()->addDays(10)->toDateString();
        $targetPlanning = Planning::create(['dps' => $date, 'hps' => '06:00']);
        $existingPlanning = Planning::create(['dps' => $date, 'hps' => '07:00']);
        $employee = $this->createEmployee('CU-004', 'CUISINE');
        PlanningItem::create([
            'employe_id' => $employee->id,
            'planning_id' => $existingPlanning->id,
        ]);

        $this->postJson("/plannings/{$targetPlanning->id}/employees", [
            'employee_ids' => [$employee->id],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Un ou plusieurs employés sont déjà inscrits à un planning pour cette date.');

        $this->assertDatabaseMissing('planning_items', [
            'planning_id' => $targetPlanning->id,
            'employe_id' => $employee->id,
        ]);
    }

    public function test_unauthenticated_users_cannot_add_employees_to_an_existing_planning(): void
    {
        $planning = Planning::create([
            'dps' => today()->addDays(10)->toDateString(),
            'hps' => '06:00',
        ]);
        $employee = $this->createEmployee('CU-005', 'CUISINE');

        $this->postJson("/plannings/{$planning->id}/employees", [
            'employee_ids' => [$employee->id],
        ])->assertUnauthorized();

        $this->assertDatabaseMissing('planning_items', [
            'planning_id' => $planning->id,
            'employe_id' => $employee->id,
        ]);
    }

    private function createEmployee(string $matricule, string $service): Employe
    {
        return Employe::create([
            'matricule' => $matricule,
            'service' => $service,
            'nom' => "Employé {$matricule}",
            'telephone' => '00000000',
            'lieu_ramassage' => 'Centre',
            'pnt_adm' => 'ADM',
            'pnt_dec' => 'Décalé',
        ]);
    }
}
