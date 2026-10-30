<?php

namespace App\Http\Controllers;

use App\Models\Anomalie;
use App\Models\Car;
use Illuminate\Http\Request;

class AnomalieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $anomalies = Anomalie::query()->orderByDesc('created_at')->get();

        return response()->json($anomalies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'car_id' => 'required|integer',
            'description' => 'required|string',
        ]);

        Car::findOrFail($validate['car_id'])->update([
            'is_available' => false,
        ]);

        Anomalie::create($validate);

        return response()->json([
            'message' => 'Ce véhicule a été mis hors service',
        ]);
    }
}
