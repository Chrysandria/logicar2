<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSnr
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->service !== 'SNR') {
            return response()->json([
                'message' => 'Accès réservé au service SNR.',
            ], 403);
        }

        return $next($request);
    }
}
