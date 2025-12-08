<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        $allowed = collect($roles)
            ->flatMap(fn ($r) => preg_split('/[|,]/', (string) $r))
            ->map(fn ($r) => strtolower(trim($r)))
            ->filter()
            ->values();

        $userRole = strtolower((string) optional($user)->role);

        if (! $user || ! $allowed->contains($userRole)) {
            throw new AccessDeniedHttpException('Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
