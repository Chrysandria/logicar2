<?php

namespace App\Http\Controllers;

use App\Models\Mouvement;
use App\Models\Car;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MouvementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $daily = Car::with("mouvements")
        ->where("mouvements.date",today())
        ->get(["cars.id","cars.immatriculation","mouvements.km"]);

        $monthly = Car::with("mouvements")
        ->whereMonth("mouvements.date",date("m"))
        ->whereYear("mouvements.date",date("Y"))
        ->get(["cars.matricule", DB::raw('SUM(mouvements.km) as total')]);

        return response()->json([
            "daily" => $daily,
            "monthly" => $monthly
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            "car_id" => "required|integer",
            "km" => "required|decimal"
        ]);

        Mouvement::create($validate);

        return response()->json($validate,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mouvement $mouvement)
    {
        $mouvement->update([
            "km" => $request->km
        ]);

        return response()->json($mouvement,200);
    }
}
