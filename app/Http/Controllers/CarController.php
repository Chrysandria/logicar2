<?php

namespace App\Http\Controllers;

use App\Models\Anomalie;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Car::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'immatriculation' => 'required|string|unique:cars,immatriculation',
            'is_available' => 'sometimes|boolean',
        ]);

        Car::create($request->all());

        return response()->json([
            'message' => 'Nouveau véhicule enregistré.',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        $response = $car->load(['anomalies', 'mouvements']);

        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        if ($request->filled('anomalie_id')) {
            Anomalie::findOrFail($request->anomalie_id)->update([
                'end_at' => today(),
            ]);
        }

        $car->update([
            'is_available' => true,
        ]);

        return response()->json([
            'message' => 'Ce véhicule a été remis en service.',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        $car->delete();

        return response()->json('Suppression effectuée.', 200);
    }
}
