<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // If user requires password reset and is not already on profile page
        if ($user->password_reset_required && !$request->routeIs('profile.edit')) {
            return redirect()->route('profile.edit')
                ->with('warning', 'For security reasons, you must change your default password before continuing.');
        }

        return $next($request);
    }
}
