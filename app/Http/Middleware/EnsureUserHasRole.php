<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): \Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();

        $allowed = collect($roles)
            ->flatMap(fn (string $r): array => preg_split('/[|,]/', $r) ?: [])
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
