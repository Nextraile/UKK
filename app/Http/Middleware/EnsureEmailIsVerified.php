<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to ensure the authenticated user has verified their email address.
 *
 * This is a custom middleware that overrides Laravel's default `verified`
 * middleware to provide a localized redirect message.
 * Email verification is NOT globally required — only for specific features like
 * creating a rental.
 *
 * The user is sent back to
 * the page they were on with the `verify_email_prompt=true` flash flag. The
 * layout renders this as a modal popup (views/components/verify-email-modal.
 * blade.php) whose CTA links to route('verification.notice'). `back()` keeps
 * the user on the same page so the modal appears as an overlay.
 */
class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): mixed  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->user() || ! $request->user()->hasVerifiedEmail()) {
            return redirect()->back()->with([
                'error' => 'Anda harus memverifikasi email terlebih dahulu untuk mengakses fitur ini.',
                'verify_email_prompt' => true,
            ]);
        }

        return $next($request);
    }
}
