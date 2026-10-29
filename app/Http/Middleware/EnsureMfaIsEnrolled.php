<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaIsEnrolled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Password authentication is still required.
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Allow local development without MFA when explicitly configured.
        if (
            app()->environment('local')
            && config('fortify.mfa_enabled', true) === false
        ) {
            return $next($request);
        }

        $isEnrolled = filled($user->two_factor_secret)
            && $user->two_factor_confirmed_at !== null;

        if (! $isEnrolled) {
            $message = 'Set up and confirm two-factor authentication '
                . 'before accessing the toolkit.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'setup_url' => route('account.security'),
                ], 403);
            }

            return redirect()
                ->route('account.security')
                ->with('mfa_required', $message);
        }

        return $next($request);
    }
}