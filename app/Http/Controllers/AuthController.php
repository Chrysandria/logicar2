<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($validate, $request->remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            return response()->json([
                "message" => "Connection effectué",
                "user" => $user
            ],200);
        };

        return response()->json([
            "message" => "Les informations saisies sont incorrectes."
        ],403);
    }

    public function logout(Request $request){
        if(Auth::logout()){
            $request->session()->invalidate();
            return response()->json([
                "message" => "Déconnexion effectué"
            ],200);
        }

        return response()->json([
            "message" => "Une erreur s'est produite."
        ],501);

    }

    public function user(){
        return response()->json([
            "user" => Auth::user()
        ]);
    }
}
