<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\Planning;
use App\Models\PlanningItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlanningItemController extends Controller
{
    public function updateExpedition(Request $request, PlanningItem $planningItem): JsonResponse
    {
        abort_unless(Auth::user()->service === 'SNR', 403);

        $validated = $request->validate([
            'expedition_id' => ['present', 'nullable', 'integer', 'exists:expeditions,id'],
        ]);

        $updatedExpeditionId = DB::transaction(function () use ($validated, $planningItem): ?int {
            $planningItem = PlanningItem::query()
                ->whereKey($planningItem->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['expedition_id'] === null) {
                $planningItem->update(['expedition_id' => null]);

                return null;
            }

            $expedition = Expedition::query()
                ->whereKey($validated['expedition_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $planningDate = Planning::query()
                ->whereKey($planningItem->planning_id)
                ->value('dps');

            if ($planningDate !== $expedition->date) {
                abort(422, 'Cette expédition n’appartient pas à la date du planning.');
            }

            if ($planningItem->expedition_id === $expedition->id) {
                return $expedition->id;
            }

            if ($expedition->planning_items()->count() >= 20) {
                abort(422, 'Cette expédition ne peut pas contenir plus de 20 employés.');
            }

            $planningItem->update(['expedition_id' => $expedition->id]);

            return $expedition->id;
        });

        return response()->json([
            'message' => 'Affectation de l’employé mise à jour.',
            'expedition_id' => $updatedExpeditionId,
        ]);
    }

    public function find(Request $request)
    {
        $request->validate([
            'dps' => 'required|date',
            'employe_id' => 'required|integer',
        ]);

        $trigger = Planning::join('planning_items', 'planning_items.planning_id', '=', 'plannings.id')
            ->where([
                'plannings.dps' => $request->dps,
                'planning_items.employe_id' => $request->employe_id,
            ])
            ->select(['plannings.hps', 'plannings.dps'])
            ->first();

        if ($trigger === null) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'planning' => $trigger,
        ]);
    }
}
