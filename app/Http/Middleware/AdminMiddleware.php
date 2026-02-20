<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'message' => 'Acceso no autorizado. Solo ADMIN.'
            ], 403);
        }

        return $next($request);
    }
}
