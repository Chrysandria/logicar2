<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\PlanningItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $service = Auth::user()->service;
        $plannings = Planning::with('plannings_items.employe')
            ->where('plannings.dps', today());

        if($service !== "SNR"){
            $plannings = $plannings->where("employes.service",$service);
        }

        $plannings = $plannings->groupBy("planning.hps")->get();

        return response()->json($plannings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "dps" => "required|date",
            "hps" => "required|date_format:H:i",
            "list" => "requires|array"
        ]);

        $planning = Planning::firstOrCreate($request->only(["dps","hps"]));

        foreach($request->list as $item){
            PlanningItem::create([
                "employe_id" => $item->employe_id,
                "planning_id" => $planning->id
            ]);
        }

        return response()->json("Planning enregistré.",200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Planning $planning)
    {
        $response = $planning->
        with(["planning_items.employe","planning_items.expedition"])
        ->groupBy("expeditions.id")->get();

        return response()->json($response,200);
    }
}
