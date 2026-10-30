<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
                'message' => 'Connection effectué',
                'user' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'Les informations saisies sont incorrectes.',
        ], 403);
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();

            return response()->json([
                'message' => 'Déconnexion effectué',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => "Une erreur s'est produite.",
            ], 501);
        }

    }

    public function user()
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
            ], 422);
        }

        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
            'user' => $user,
        ]);
    }
}
