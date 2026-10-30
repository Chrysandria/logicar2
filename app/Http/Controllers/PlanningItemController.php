<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use Illuminate\Http\Request;

class PlanningItemController extends Controller
{
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
