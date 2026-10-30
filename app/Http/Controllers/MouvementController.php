<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Mouvement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MouvementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $daily = Car::query()
            ->join('mouvements', 'mouvements.car_id', '=', 'cars.id')
            ->whereDate('mouvements.date', today())
            ->get(['cars.id', 'cars.immatriculation', 'mouvements.km']);

        $monthly = Car::query()
            ->join('mouvements', 'mouvements.car_id', '=', 'cars.id')
            ->whereMonth('mouvements.date', date('m'))
            ->whereYear('mouvements.date', date('Y'))
            ->groupBy('cars.id', 'cars.immatriculation')
            ->select(['cars.id', 'cars.immatriculation', DB::raw('SUM(mouvements.km) as total')])
            ->get();

        return response()->json([
            'daily' => $daily,
            'monthly' => $monthly,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'car_id' => 'required|integer',
            'date' => 'required|date',
            'km' => 'required|numeric',
        ]);

        Mouvement::create($validate);

        return response()->json($validate, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mouvement $mouvement)
    {
        $validated = $request->validate([
            'km' => 'required|numeric',
        ]);

        $mouvement->update([
            'km' => $validated['km'],
        ]);

        return response()->json($mouvement, 200);
    }
}
