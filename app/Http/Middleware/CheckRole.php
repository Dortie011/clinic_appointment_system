<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if the user is currently authenticated
        if (!auth()->check()) {
            return redirect('/login');
        }

        // 2. Check if the user's role is in the allowed list passed to the route
        if (in_array(auth()->user()->role, $roles)) {
            return $next($request);
        }

        // 3. Directly abort with an explicit 403 response view to kill any infinite redirect loops
        return response()->view('errors.403', [], 403);
    }
}