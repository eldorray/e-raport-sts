<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheHeaders
{
    /**
     * Handle an incoming request.
     * Prevents browser from caching authenticated pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Use headers->add() to support all response types including StreamedResponse
        $response->headers->add([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);

        return $response;
    }
}
