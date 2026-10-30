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
}
