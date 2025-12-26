<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eraport STS</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
        }
    </style>
</head>

@php
    $school = \App\Models\SchoolProfile::first();
    $logoUrl = null;
    if ($school?->logo) {
        $logoUrl = filter_var($school->logo, FILTER_VALIDATE_URL) ? $school->logo : asset('storage/' . $school->logo);
    }
@endphp

<body class="min-h-screen bg-amber-50/50">
    <div class="min-h-screen flex items-center justify-center p-6">
        {{-- Main Card --}}
        <div class="w-full max-w-5xl rounded-3xl overflow-hidden shadow-2xl"
            style="background: linear-gradient(135deg, #f5ebe0 0%, #e8d5c4 25%, #d5c4a1 50%, #c9b896 75%, #b8a67e 100%);">

            {{-- Header --}}
            <div class="flex items-center justify-between px-8 py-4">
                <div class="flex items-center gap-3">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo"
                            class="w-10 h-10 rounded-full object-cover bg-white/50">
                    @else
                        <div class="w-10 h-10 rounded-full bg-white/30"></div>
                    @endif
                    <span class="text-amber-900 font-semibold uppercase tracking-wide text-sm">
                        {{ $school?->name ?? 'SEKOLAH' }}
                    </span>
                </div>
                <a href="{{ route('login') }}"
                    class="bg-amber-700 hover:bg-amber-800 text-white px-5 py-2 rounded-full text-sm font-medium transition">
                    MASUK
                </a>
            </div>

            {{-- Content --}}
            <div class="grid md:grid-cols-2 gap-8 px-8 py-12 min-h-[400px]">
                {{-- Left: Login Form --}}
                <div class="flex flex-col justify-center">
                    <form method="POST" action="{{ route('login') }}" class="space-y-4 max-w-sm">
                        @csrf
                        <div>
                            <input type="text" name="login" placeholder="USERNAME / NIP / EMAIL"
                                class="w-full px-4 py-3 rounded-full border border-amber-300/50 bg-white/40 text-amber-900 placeholder-amber-700/60 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white/60 transition"
                                required>
                        </div>
                        <div>
                            <input type="password" name="password" placeholder="PASSWORD"
                                class="w-full px-4 py-3 rounded-full border border-amber-300/50 bg-white/40 text-amber-900 placeholder-amber-700/60 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white/60 transition"
                                required>
                        </div>
                        <div>
                            <button type="submit"
                                class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-full font-semibold tracking-wide transition shadow-lg">
                                LOGIN
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-amber-800 cursor-pointer">
                                <input type="checkbox" name="remember"
                                    class="rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                                Ingat Saya
                            </label>
                        </div>
                    </form>
                </div>

                {{-- Right: Welcome Text --}}
                <div class="flex flex-col justify-center text-right">
                    <h1 class="text-4xl md:text-5xl font-bold text-amber-900" style="font-family: Georgia, serif;">
                        Selamat Datang.
                    </h1>
                    <p class="mt-4 text-amber-800/80 leading-relaxed">
                        Sistem Penilaian Rapor Digital<br>
                        {{ $school?->name ?? 'Sekolah' }}
                    </p>
                    @if ($school?->address)
                        <p class="mt-2 text-sm text-amber-700/70">
                            {{ $school->address }}, {{ $school->district }}, {{ $school->city }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>
