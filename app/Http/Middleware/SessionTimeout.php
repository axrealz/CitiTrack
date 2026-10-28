<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Inactivity timeout in minutes.
     * Store computer left unattended → auto logout.
     */
    protected int $timeoutMinutes = 30;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $lastActivity = session('last_activity_at');

            if ($lastActivity && now()->diffInMinutes($lastActivity) >= $this->timeoutMinutes) {
                AuditLog::create([
                    'user_id'    => $request->user()->id,
                    'action'     => 'logout.session_timeout',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Session expired due to inactivity. Please log in again.']);
            }

            session(['last_activity_at' => now()]);
        }

        return $next($request);
    }
}