<?php

namespace App\Http\Controllers;

use App\Models\Trajet;
use App\Models\TrajetItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TrajetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Trajet::with('trajet_items')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'designation' => ['required', 'string', 'max:255', 'unique:trajets,designation'],
            'trajet_items' => ['required', 'array', 'min:1'],
            'trajet_items.*.point' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            $trajet = Trajet::create([
                'designation' => $validated['designation'],
            ]);

            foreach ($validated['trajet_items'] as $item) {
                TrajetItem::create([
                    'trajet_id' => $trajet->id,
                    'point' => $item['point'],
                ]);
            }
        });

        return response()->json('Nouveau trajet enregistré', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trajet $trajet)
    {
        return response()->json($trajet->load('trajet_items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trajet $trajet): JsonResponse
    {
        $validated = $request->validate([
            'designation' => [
                'required',
                'string',
                'max:255',
                Rule::unique('trajets', 'designation')->ignore($trajet->id),
            ],
            'trajet_items' => ['required', 'array', 'min:1'],
            'trajet_items.*.point' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($trajet, $validated): void {
            $trajet->update([
                'designation' => $validated['designation'],
            ]);

            TrajetItem::where('trajet_id', $trajet->id)->delete();

            foreach ($validated['trajet_items'] as $item) {
                TrajetItem::create([
                    'trajet_id' => $trajet->id,
                    'point' => $item['point'],
                ]);
            }
        });

        return response()->json(['message' => 'Trajet mis à jour.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trajet $trajet)
    {
        if (DB::table('expeditions')->where('trajet_id', $trajet->id)->exists()) {
            return response()->json([
                'message' => 'Ce trajet est déjà associé à une expédition et ne peut pas être supprimé.',
            ], 409);
        }

        $trajet->delete();

        return response()->noContent();
    }
}
