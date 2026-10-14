<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Anomalie;
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
        Car::create($request->all());

        return response()->json([
            "message" => "Nouveau véhicule enregistrer."
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        $response = $car->with(["anomalies","mouvements"])->get();

        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        Anomalie::find($request->anomalie_id)->update([
            "end_at" => today()
        ]);

        $car->update([
            "is_available" => true
        ]);

        return response()->json([
            "message" => "Ce véhicule a été remis en service."
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        $car->delete();
        return response()->json("Suppression effectuée.",200);
    }
}
