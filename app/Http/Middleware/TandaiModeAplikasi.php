<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menandai sesi sedang dipakai lewat aplikasi HP (PWA).
 *
 * Selama tanda ini ada, halaman web memakai bingkai aplikasi (lihat layouts/app.blade.php),
 * sehingga menu yang belum punya versi khusus HP tidak "lari" ke tampilan web.
 * Tanda dihapus lewat tombol "Versi Web" (rute app.versi-web).
 */
class TandaiModeAplikasi
{
    public const KUNCI_SESI = 'mode_aplikasi';

    public function handle(Request $request, Closure $next): Response
    {
        $request->session()->put(self::KUNCI_SESI, true);

        return $next($request);
    }
}
