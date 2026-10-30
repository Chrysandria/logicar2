<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\PlanningItem;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'dps' => 'required|date_format:Y-m-d',
        ]);

        $service = Auth::user()->service;
        $planningItemsQuery = function (HasMany $query) use ($service): void {
            $query->orderBy('employe_id')
                ->when($service !== 'SNR', function (Builder $query) use ($service): void {
                    $query->whereHas('employe', function (Builder $query) use ($service): void {
                        $query->where('service', $service);
                    });
                });
        };

        $planning = Planning::with([
            'planning_items' => $planningItemsQuery,
            'planning_items.employe',
            'planning_items.expedition',
        ])->whereDate('dps', $request->dps)
            ->when($service !== 'SNR', function ($query) use ($service) {
                $query->whereHas('planning_items.employe', function ($q) use ($service) {
                    $q->where('service', $service);
                });
            })->orderBy('hps')->get();

        return response()->json($planning);
    }

    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employes,id'],
        ]);
        $service = Auth::user()->service;

        $history = PlanningItem::query()
            ->join('plannings', 'planning_items.planning_id', '=', 'plannings.id')
            ->join('employes', 'planning_items.employe_id', '=', 'employes.id')
            ->where('planning_items.employe_id', $validated['employee_id'])
            ->when($service !== 'SNR', function (Builder $query) use ($service): void {
                $query->where('employes.service', $service);
            })
            ->with([
                'planning:id,dps,hps',
                'employe:id,matricule,nom,service',
            ])
            ->select('planning_items.*')
            ->orderByDesc('plannings.dps')
            ->orderByDesc('plannings.hps')
            ->paginate(25);

        return response()->json($history);
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
            $employeId = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
            if ($employeId === null) {
                continue;
            }

            $trigger = Planning::join('planning_items', 'planning_items.planning_id', '=', 'plannings.id')
                ->where([
                    'plannings.dps' => $request->dps,
                    'planning_items.employe_id' => $employeId,
                ])
                ->select('plannings.*')
                ->first();

            if ($trigger === null) {
                PlanningItem::firstOrCreate([
                    'employe_id' => $employeId,
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
        $response = $planning->load(['planning_items.employe', 'planning_items.expedition']);

        return response()->json($response, 200);
    }
}
