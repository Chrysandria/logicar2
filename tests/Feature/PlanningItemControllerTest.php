<?php

namespace Tests\Feature;

use App\Models\Employe;
use App\Models\Expedition;
use App\Models\Planning;
use App\Models\PlanningItem;
use App\Models\Trajet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_snr_can_move_an_employee_to_another_expedition_on_the_same_planning_date(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $currentExpedition = $this->createExpedition('2026-10-30', '06:00');
        $targetExpedition = $this->createExpedition('2026-10-30', '06:30');
        $planningItem = $this->createPlanningItem($planning, 'EMP-001', $currentExpedition);

        $this->putJson("/planning-items/{$planningItem->id}/expedition", [
            'expedition_id' => $targetExpedition->id,
        ])
            ->assertOk()
            ->assertJsonPath('expedition_id', $targetExpedition->id);

        $this->assertDatabaseHas('planning_items', [
            'id' => $planningItem->id,
            'expedition_id' => $targetExpedition->id,
        ]);
    }

    public function test_snr_can_return_an_employee_to_the_unassigned_list(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $expedition = $this->createExpedition('2026-10-30', '06:00');
        $planningItem = $this->createPlanningItem($planning, 'EMP-002', $expedition);

        $this->putJson("/planning-items/{$planningItem->id}/expedition", [
            'expedition_id' => null,
        ])
            ->assertOk()
            ->assertJsonPath('expedition_id', null);

        $this->assertDatabaseHas('planning_items', [
            'id' => $planningItem->id,
            'expedition_id' => null,
        ]);
    }

    public function test_snr_cannot_assign_an_employee_to_an_expedition_from_another_date(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $currentExpedition = $this->createExpedition('2026-10-30', '06:00');
        $otherDateExpedition = $this->createExpedition('2026-10-31', '06:00');
        $planningItem = $this->createPlanningItem($planning, 'EMP-003', $currentExpedition);

        $this->putJson("/planning-items/{$planningItem->id}/expedition", [
            'expedition_id' => $otherDateExpedition->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Cette expédition n’appartient pas à la date du planning.');

        $this->assertDatabaseHas('planning_items', [
            'id' => $planningItem->id,
            'expedition_id' => $currentExpedition->id,
        ]);
    }

    public function test_service_users_cannot_change_employee_expedition_assignments(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $expedition = $this->createExpedition('2026-10-30', '06:00');
        $planningItem = $this->createPlanningItem($planning, 'EMP-004', $expedition);

        $this->putJson("/planning-items/{$planningItem->id}/expedition", [
            'expedition_id' => null,
        ])->assertForbidden();

        $this->assertDatabaseHas('planning_items', [
            'id' => $planningItem->id,
            'expedition_id' => $expedition->id,
        ]);
    }

    public function test_unauthenticated_users_cannot_change_employee_expedition_assignments(): void
    {
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $expedition = $this->createExpedition('2026-10-30', '06:00');
        $planningItem = $this->createPlanningItem($planning, 'EMP-006', $expedition);

        $this->putJson("/planning-items/{$planningItem->id}/expedition", [
            'expedition_id' => null,
        ])->assertUnauthorized();

        $this->assertDatabaseHas('planning_items', [
            'id' => $planningItem->id,
            'expedition_id' => $expedition->id,
        ]);
    }

    public function test_snr_cannot_assign_an_employee_to_a_full_expedition(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $currentExpedition = $this->createExpedition('2026-10-30', '06:00');
        $fullExpedition = $this->createExpedition('2026-10-30', '06:30');
        $planningItem = $this->createPlanningItem($planning, 'EMP-005', $currentExpedition);

        for ($index = 1; $index <= 20; $index++) {
            $this->createPlanningItem($planning, sprintf('FULL-%03d', $index), $fullExpedition);
        }

        $this->putJson("/planning-items/{$planningItem->id}/expedition", [
            'expedition_id' => $fullExpedition->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Cette expédition ne peut pas contenir plus de 20 employés.');

        $this->assertDatabaseHas('planning_items', [
            'id' => $planningItem->id,
            'expedition_id' => $currentExpedition->id,
        ]);
    }

    private function createExpedition(string $date, string $time): Expedition
    {
        return Expedition::create([
            'date' => $date,
            'hdr' => $time,
            'trajet_id' => Trajet::create(['designation' => "Trajet {$date} {$time}"])->id,
        ]);
    }

    private function createPlanningItem(
        Planning $planning,
        string $matricule,
        ?Expedition $expedition = null,
    ): PlanningItem {
        $employee = Employe::create([
            'matricule' => $matricule,
            'service' => 'CUISINE',
            'nom' => "Employé {$matricule}",
            'telephone' => '00000000',
            'lieu_ramassage' => 'Centre',
            'pnt_adm' => 'ADM',
            'pnt_dec' => 'DEC',
        ]);

        return PlanningItem::create([
            'employe_id' => $employee->id,
            'planning_id' => $planning->id,
            'expedition_id' => $expedition?->id,
        ]);
    }
}
