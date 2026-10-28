<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Block inactive users
        if (! auth()->user()->is_active) {
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'login.blocked_inactive',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Auth::logout();

            return back()->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        // Log successful login
        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'login.success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Only log a logout when someone was actually logged in
        if (auth()->check()) {
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}