<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan akun yang masih memakai kata sandi awal di halaman ganti kata sandi.
 */
class WajibGantiSandi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null
            && $user->must_change_password === true
            && ! $request->routeIs('settings.password.*', 'logout')) {
            return redirect()->route('settings.password.edit')
                ->with('warning', __('Ganti kata sandi awal Anda sebelum melanjutkan.'));
        }

        return $next($request);
    }
}
