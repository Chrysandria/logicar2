<?php

namespace Tests\Feature;

use App\Models\Employe;
use App\Models\Expedition;
use App\Models\Planning;
use App\Models\PlanningItem;
use App\Models\Trajet;
use App\Models\TrajetItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_snr_can_download_a_pdf_for_a_fully_treated_planning(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $firstTrip = Trajet::create(['designation' => 'Centre - Gare']);
        $secondTrip = Trajet::create(['designation' => 'Depot - Port']);
        $firstExpedition = Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '06:00',
            'trajet_id' => $firstTrip->id,
        ]);
        $secondExpedition = Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '06:30',
            'trajet_id' => $secondTrip->id,
        ]);
        $this->createPlanningItem($planning, 'EMP-001', 'Employé 1', $firstExpedition);
        $this->createPlanningItem($planning, 'EMP-002', 'Employé 2', $secondExpedition);

        $response = $this->get("/plannings/{$planning->id}/report");

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page\b/', $response->getContent()));
    }

    public function test_report_view_displays_route_passage_points_in_order_and_employee_pickup_points(): void
    {
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $trajet = Trajet::create(['designation' => 'Centre - Gare']);
        TrajetItem::create(['trajet_id' => $trajet->id, 'point' => 'Point A']);
        TrajetItem::create(['trajet_id' => $trajet->id, 'point' => 'Point B']);
        $expedition = Expedition::create([
            'date' => '2026-10-30',
            'hdr' => '06:00',
            'trajet_id' => $trajet->id,
        ]);
        $expedition->load('trajet.trajet_items');
        $employee = Employe::create([
            'matricule' => 'EMP-POINTS',
            'service' => 'CUISINE',
            'nom' => 'Employé Points',
            'telephone' => '00000000',
            'lieu_ramassage' => 'Centre',
            'pnt_adm' => 'ADM-POINT',
            'pnt_dec' => 'DEC-POINT',
        ]);
        $planningItem = PlanningItem::create([
            'employe_id' => $employee->id,
            'planning_id' => $planning->id,
            'expedition_id' => $expedition->id,
        ]);
        $expeditions = collect([[
            'expedition' => $expedition,
            'items' => collect([$planningItem->load('employe')]),
        ]]);
        $planningDate = '30/10/2026';

        $this->view('pdf.planning-report', compact('planning', 'planningDate', 'expeditions'))
            ->assertSee('Point ADM')
            ->assertSee('Point décalé')
            ->assertSee('ADM-POINT')
            ->assertSee('DEC-POINT')
            ->assertSee('Trajet : Point A - Point B');
    }

    public function test_planning_report_returns_unprocessable_until_every_employee_has_an_expedition(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);
        $this->createPlanningItem($planning, 'EMP-003', 'Employé en attente');

        $this->getJson("/plannings/{$planning->id}/report")
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Le récapitulatif PDF est disponible uniquement lorsque tous les employés du planning sont affectés à une expédition.',
            );
    }

    public function test_empty_planning_report_returns_unprocessable(): void
    {
        $this->actingAs(User::create([
            'name' => 'dispatcher',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);

        $this->getJson("/plannings/{$planning->id}/report")
            ->assertUnprocessable();
    }

    public function test_service_users_cannot_download_snr_planning_reports(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);

        $this->getJson("/plannings/{$planning->id}/report")
            ->assertForbidden();
    }

    public function test_unauthenticated_users_cannot_download_planning_reports(): void
    {
        $planning = Planning::create(['dps' => '2026-10-30', 'hps' => '08:00']);

        $this->getJson("/plannings/{$planning->id}/report")
            ->assertUnauthorized();
    }

    private function createPlanningItem(
        Planning $planning,
        string $matricule,
        string $name,
        ?Expedition $expedition = null,
    ): PlanningItem {
        $employee = Employe::create([
            'matricule' => $matricule,
            'service' => 'CUISINE',
            'nom' => $name,
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
