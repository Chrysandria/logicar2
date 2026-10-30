<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\PlanningItem;
use Illuminate\Http\Request;

class ExpeditionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'hdr' => 'required|date_format:H:i',
            'trajet_id' => 'required|integer',
            'list' => 'required|array|max:20',
        ]);

        $expedition = Expedition::create($request->only(['date', 'hdr', 'trajet_id']));

        foreach ($request->list as $item) {
            $planningItemId = is_array($item) ? ($item['planning_item_id'] ?? null) : ($item->planning_item_id ?? null);
            if ($planningItemId === null) {
                continue;
            }

            PlanningItem::findOrFail($planningItemId)->update([
                'expedition_id' => $expedition->id,
            ]);
        }

        return response()->json($expedition, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expedition $expedition)
    {
        $request->validate([
            'car_id' => 'required|integer',
            'list' => 'nullable|array',
        ]);

        $expedition->update([
            'car_id' => $request->car_id,
        ]);

        if ($request->list !== null) {
            foreach ($request->list as $item) {
                $planningItemId = is_array($item) ? ($item['planning_item_id'] ?? null) : ($item->planning_item_id ?? null);
                if ($planningItemId === null) {
                    continue;
                }

                PlanningItem::findOrFail($planningItemId)->update([
                    'ok' => false,
                ]);
            }
        }

        return response()->json('mis à jour effectue', 200);
    }
}
