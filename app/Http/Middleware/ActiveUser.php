<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware to prevent soft-deleted (inactive) users from using the application.
 *
 * Per FR-013, users with a non-null `deleted_at` are considered inactive and
 * should be logged out immediately.
 */
class ActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): mixed  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->trashed()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Akun Anda sudah tidak aktif.');
        }

        return $next($request);
    }
}
