<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireFreshSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        // Resolve authentication before checking how it was restored.
        $guard->user();

        if ($guard->viaRemember()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please sign in again.',
                ], 401);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
