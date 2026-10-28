<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,cashier')
     *   ->middleware('role:stocker')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Must be logged in
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // 2. Account must be active
        if (! $request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // 3. Role must match one of the allowed roles
        if (! in_array($request->user()->role, $roles, true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}