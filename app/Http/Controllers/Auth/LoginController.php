<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        $tahunAjaranOptions = TahunAjaran::orderByDesc('is_active')
            ->orderByDesc('tahun_mulai')
            ->get();

        return view('auth.login', compact('tahunAjaranOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajarans,id'],
            'semester' => ['required', 'string', 'max:20'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $loginValue = $request->string('login');
        $password = $request->string('password');
        $remember = $request->boolean('remember');

        $attempted = Auth::attempt(['email' => $loginValue, 'password' => $password], $remember)
            || Auth::attempt(['name' => $loginValue, 'password' => $password], $remember)
            || Auth::attempt(['nip' => $loginValue, 'password' => $password], $remember)
            || Auth::attempt(['nik' => $loginValue, 'password' => $password], $remember);

        if (! $attempted) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (! $user?->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'login' => __('Akun Anda nonaktif. Silakan hubungi admin.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        $selectedYearId = $request->integer('tahun_ajaran_id');
        $selectedSemester = $request->string('semester');

        $yearModel = TahunAjaran::find($selectedYearId);

        $request->session()->put([
            'selected_tahun_ajaran_id' => $selectedYearId,
            'selected_semester' => $selectedSemester,
            'selected_tahun_ajaran_is_active' => $yearModel?->is_active ?? false,
        ]);

        $redirect = match (auth()->user()->role) {
            'admin' => route('dashboard', absolute: false),
            default => route('dashboard', absolute: false),
        };

        return redirect()->intended($redirect);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('login')).'|'.$request->ip());
    }
}
