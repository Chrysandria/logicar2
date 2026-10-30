<?php

namespace App\Http\Controllers;

use App\Models\Trajet;
use App\Models\TrajetItem;
use Illuminate\Http\Request;

class TrajetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Trajet::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'designation' => 'required|string',
            'trajet_items' => 'required|array',
        ]);

        $trajet = Trajet::create([
            'designation' => $request->designation,
        ]);

        foreach ($request->trajet_items as $item) {
            TrajetItem::create([
                'trajet_id' => $trajet->id,
                'point' => $item['point'] ?? $item->point,
            ]);
        }

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
    public function update(Request $request, Trajet $trajet)
    {
        $request->validate([
            'trajet_items' => 'required|array',
        ]);

        TrajetItem::where('trajet_id', $trajet->id)->delete();

        foreach ($request->trajet_items as $item) {
            TrajetItem::create([
                'trajet_id' => $trajet->id,
                'point' => $item['point'] ?? $item->point,
            ]);
        }

        return response()->json('Mis à jour effectué', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trajet $trajet)
    {
        $trajet->delete();

        return response()->noContent();
    }
}
