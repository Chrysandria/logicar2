<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeController extends Controller
{
    public function index(){
        $user = Auth::user();
        $emps = Employe::where("service",$user->service)->get();

        return response()->json($emps);
    }

    public function services(){
        $services = Employe::distinct("service")->get("service");
        return response()->json($services);
    }
}
