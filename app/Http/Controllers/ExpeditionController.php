<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Expedition;
use App\Models\Planning;
use App\Models\PlanningItem;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpeditionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $expeditions = Expedition::query()->with([
            'planning_items' => fn ($query) => $query->orderBy('employe_id'),
            'planning_items.employe',
            'trajet.trajet_items',
        ])->whereDate('date', $request->date)
            ->orderBy('hdr')
            ->get();

        return response()->json($expeditions);
    }

    public function availableCars(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'hdr' => ['required', 'date_format:H:i'],
        ]);

        $plannedAt = CarbonImmutable::parse($validated['date'])
            ->setTimeFromTimeString($validated['hdr']);
        $windowStart = $plannedAt->subHours(3);
        $windowEnd = $plannedAt->addHours(3);
        $cars = Car::query()->where('is_available', true)->get();
        $expeditionsByCar = Expedition::query()
            ->whereNotNull('car_id')
            ->whereBetween('date', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->get(['id', 'car_id', 'date', 'hdr'])
            ->groupBy('car_id');

        $availableCars = $cars
            ->reject(fn ($car): bool => $this->hasScheduleConflict(
                $plannedAt,
                $expeditionsByCar->get($car->id, []),
            ))
            ->values();

        return response()->json($availableCars);
    }

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
        $validated = $request->validate([
            'car_id' => ['sometimes', 'required', 'integer', 'exists:cars,id'],
            'planning_id' => ['required_with:add_list', 'integer', 'exists:plannings,id'],
            'list' => ['nullable', 'array'],
            'list.*.planning_item_id' => ['required', 'integer', 'distinct', 'exists:planning_items,id'],
            'add_list' => ['sometimes', 'array', 'min:1', 'max:20'],
            'add_list.*.planning_item_id' => ['required', 'integer', 'distinct', 'exists:planning_items,id'],
        ]);

        DB::transaction(function () use ($validated, $expedition): void {
            if (isset($validated['add_list'])) {
                $expedition = Expedition::query()
                    ->whereKey($expedition->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            if (isset($validated['car_id'])) {
                $car = Car::query()
                    ->whereKey($validated['car_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                if (! $car->is_available) {
                    abort(422, 'Ce véhicule est actuellement indisponible.');
                }

                $plannedAt = CarbonImmutable::parse($expedition->date)
                    ->setTimeFromTimeString($expedition->hdr);
                $windowStart = $plannedAt->subHours(3);
                $windowEnd = $plannedAt->addHours(3);

                $conflictingExpeditions = Expedition::query()
                    ->where('car_id', $car->id)
                    ->whereBetween('date', [$windowStart->toDateString(), $windowEnd->toDateString()])
                    ->get(['id', 'date', 'hdr']);

                if ($this->hasScheduleConflict($plannedAt, $conflictingExpeditions, $expedition->id)) {
                    abort(422, 'Ce véhicule a déjà une expédition prévue dans les trois heures autour de cet horaire.');
                }

                $expedition->update([
                    'car_id' => $car->id,
                ]);
            }

            if (isset($validated['add_list'])) {
                $planning = Planning::query()
                    ->whereKey($validated['planning_id'])
                    ->whereDate('dps', $expedition->date)
                    ->first();
                if ($planning === null) {
                    abort(422, 'Le planning ne correspond pas à la date de cette expédition.');
                }

                if ($expedition->planning_items()->where('planning_id', '!=', $planning->id)->exists()) {
                    abort(422, 'Cette expédition contient déjà des employés d’un autre planning ou service.');
                }

                $service = Auth::user()->service;
                if ($service !== 'SNR' && $expedition->planning_items()->whereHas(
                    'employe',
                    fn ($query) => $query->where('service', '!=', $service),
                )->exists()) {
                    abort(422, 'Cette expédition contient déjà des employés d’un autre planning ou service.');
                }

                $currentPassengerCount = $expedition->planning_items()->count();
                if ($currentPassengerCount + count($validated['add_list']) > 20) {
                    abort(422, 'Cette expédition ne peut pas contenir plus de 20 employés.');
                }

                $planningItemsToAdd = PlanningItem::query()
                    ->where('planning_id', $planning->id)
                    ->whereNull('expedition_id')
                    ->whereIn('id', array_column($validated['add_list'], 'planning_item_id'))
                    ->when($service !== 'SNR', function ($query) use ($service): void {
                        $query->whereHas('employe', fn ($query) => $query->where('service', $service));
                    })
                    ->lockForUpdate()
                    ->get();
                if ($planningItemsToAdd->count() !== count($validated['add_list'])) {
                    abort(422, 'Certains employés ne sont plus disponibles pour cette expédition.');
                }

                PlanningItem::query()
                    ->whereIn('id', array_column($validated['add_list'], 'planning_item_id'))
                    ->update([
                        'expedition_id' => $expedition->id,
                        'ok' => false,
                    ]);
            }

            if (isset($validated['list'])) {
                foreach ($validated['list'] as $item) {
                    PlanningItem::findOrFail($item['planning_item_id'])->update([
                        'ok' => false,
                    ]);
                }
            }
        });

        return response()->json('mis à jour effectue', 200);
    }

    private function hasScheduleConflict(
        CarbonImmutable $plannedAt,
        iterable $expeditions,
        ?int $ignoredExpeditionId = null,
    ): bool {
        foreach ($expeditions as $otherExpedition) {
            if ($otherExpedition->id === $ignoredExpeditionId) {
                continue;
            }

            $otherPlannedAt = CarbonImmutable::parse($otherExpedition->date)
                ->setTimeFromTimeString($otherExpedition->hdr);

            if (abs($plannedAt->getTimestamp() - $otherPlannedAt->getTimestamp()) < 3 * 60 * 60) {
                return true;
            }
        }

        return false;
    }
}
