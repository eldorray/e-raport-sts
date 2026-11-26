<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memutus sesi akun yang dinonaktifkan.
 *
 * Status aktif dulu hanya dicek saat login dengan kata sandi, sehingga sesi yang sudah
 * berjalan dan cookie "Ingat saya" tetap diterima setelah akun dinonaktifkan.
 */
class PastikanAkunAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Perbandingan ketat: atribut yang belum dimuat (null) tidak dianggap nonaktif.
        if ($user !== null && $user->is_active === false) {
            // logout() juga memutar remember_token, jadi cookie "Ingat saya" ikut tidak berlaku.
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => __('Akun Anda nonaktif. Silakan hubungi admin.'),
            ]);
        }

        return $next($request);
    }
}
