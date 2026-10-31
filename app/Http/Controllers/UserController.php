<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->ensureSnr();

        $users = User::query()
            ->select(['id', 'name', 'service', 'created_at', 'updated_at'])
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureSnr();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'service' => ['required', 'string', 'max:255'],
        ]);

        $initialPassword = bin2hex(random_bytes(16));
        $user = User::create([
            'name' => $validated['name'],
            'service' => $validated['service'],
            'password' => $initialPassword,
            'must_change_password' => true,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'initial_password' => $initialPassword,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'service' => $user->service,
                'must_change_password' => $user->must_change_password,
            ],
        ], 201)->header('Cache-Control', 'no-store, private');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->ensureSnr();

        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé.',
        ]);
    }

    private function ensureSnr(): void
    {
        abort_unless(Auth::user()?->service === 'SNR', 403, 'Accès réservé au service SNR.');
    }
}
