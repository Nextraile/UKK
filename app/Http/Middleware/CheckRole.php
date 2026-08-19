<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Middleware to check if the authenticated user has one of the required roles.
 *
 * Usage in routes: `role:admin` or `role:user,admin`
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): mixed  $next
     * @param  string  ...$roles  The roles allowed to access the route.
     *
     * @throws HttpException
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
