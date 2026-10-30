<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PlanningReportController extends Controller
{
    public function __invoke(Planning $planning): Response
    {
        abort_unless(Auth::user()->service === 'SNR', 403);

        $planning->load([
            'planning_items.employe',
            'planning_items.expedition.trajet',
            'planning_items.expedition.trajet.trajet_items' => fn ($query) => $query->orderBy('id'),
            'planning_items.expedition.car',
        ]);

        abort_if(
            $planning->planning_items->isEmpty()
                || $planning->planning_items->contains(fn ($item): bool => $item->expedition_id === null),
            422,
            'Le récapitulatif PDF est disponible uniquement lorsque tous les employés du planning sont affectés à une expédition.',
        );

        $expeditions = $planning->planning_items
            ->groupBy('expedition_id')
            ->map(fn ($items): array => [
                'expedition' => $items->first()->expedition,
                'items' => $items,
            ])
            ->sortBy(fn (array $group): string => $group['expedition']->hdr)
            ->values();

        $planningDate = CarbonImmutable::parse($planning->dps)->format('d/m/Y');

        return Pdf::loadView('pdf.planning-report', compact('planning', 'planningDate', 'expeditions'))
            ->setPaper('a4', 'landscape')
            ->stream("planning-{$planning->id}-recapitulatif.pdf");
    }
}
