<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\PlanningItem;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'dps' => 'required|date_format:Y-m-d',
        ]);

        $service = Auth::user()->service;
        $planning = Planning::with([
            'planning_items' => function ($query) {
                $query->orderBy('employe_id', 'asc');
            },
            'planning_items.employe',
        ])->whereDate('dps', $request->dps)
            ->when($service !== 'SNR', function ($query) use ($service) {
                $query->whereHas('planning_items.employe', function ($q) use ($service) {
                    $q->where('service', $service);
                });
            })->orderBy('hps')->get();

        return response()->json($planning);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dps' => 'required|date',
            'hps' => 'required|date_format:H:i',
            'list' => 'required|array',
        ]);

        $dps = new DateTime($request->dps);
        $today = new DateTime(today());

        $diff = $dps->diff($today);

        if ($diff->days < 4) {
            return response()->json([
                'message' => "L'édition du planning doit se faire au minimum 4 jours plutôt.",
            ], 403);
        }

        $planning = Planning::firstOrCreate($request->only(['dps', 'hps']));

        foreach ($request->list as $item) {
            $trigger = Planning::join('planning_items', 'planning_items.planning_id', '=', 'plannings.id')
                ->where([
                    'plannings.dps' => $request->dps,
                    'planning_items.employe_id' => $item['id'],
                ])
                ->select('plannings.*')
                ->first();

            if ($trigger === null) {
                PlanningItem::firstOrCreate([
                    'employe_id' => $item['id'],
                    'planning_id' => $planning->id,
                ]);
            }
        }

        return response()->json('Planning enregistré.', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Planning $planning)
    {
        $response = $planning->
        with(['planning_items.employe', 'planning_items.expedition'])
            ->groupBy('expeditions.id')->get();

        return response()->json($response, 200);
    }
}
