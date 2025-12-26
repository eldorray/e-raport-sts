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

<body class="min-h-screen bg-white">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo Sekolah"
                        class="w-12 h-12 rounded-lg object-contain bg-gray-50 p-1">
                @else
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                @endif
                <div>
                    <p class="font-bold text-gray-900">{{ $school?->name ?? 'Nama Sekolah' }}</p>
                    <p class="text-xs text-gray-500">{{ $school?->city ?? '' }}</p>
                </div>
            </div>
            <a href="{{ route('login') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                Masuk
            </a>
        </div>
    </header>

    {{-- Hero Section --}}
    <main class="bg-gradient-to-b from-blue-50 to-white">
        <div class="max-w-7xl mx-auto px-6 py-16 md:py-24">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Left Content --}}
                <div class="text-center lg:text-left">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight">
                        Selamat Datang di <span class="text-blue-600">Eraport</span>
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 leading-relaxed">
                        Akses Laporan Hasil Belajar Siswa Secara Mudah dan Transparan
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl text-base font-semibold transition shadow-lg shadow-blue-600/25">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Masuk ke Portal
                        </a>
                    </div>

                    {{-- Features --}}
                    <div class="mt-12 grid sm:grid-cols-3 gap-6">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Mudah Diakses</p>
                                <p class="text-xs text-gray-500">Kapan saja, dimana saja</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Aman & Terpercaya</p>
                                <p class="text-xs text-gray-500">Data terenkripsi</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Cepat & Efisien</p>
                                <p class="text-xs text-gray-500">Proses otomatis</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Illustration --}}
                <div class="flex justify-center lg:justify-end">
                    <div class="relative">
                        {{-- Background Circle --}}
                        <div class="absolute inset-0 bg-blue-100 rounded-full transform scale-90 -z-10"></div>

                        {{-- Illustration SVG --}}
                        <svg class="w-full max-w-md h-auto" viewBox="0 0 400 350" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            {{-- Background elements --}}
                            <circle cx="200" cy="175" r="150" fill="#DBEAFE" opacity="0.5" />
                            <circle cx="200" cy="175" r="120" fill="#BFDBFE" opacity="0.3" />

                            {{-- Book --}}
                            <rect x="80" y="200" width="80" height="100" rx="4" fill="#3B82F6" />
                            <rect x="85" y="205" width="70" height="90" rx="2" fill="#60A5FA" />
                            <rect x="95" y="220" width="50" height="4" fill="white" opacity="0.8" />
                            <rect x="95" y="230" width="40" height="3" fill="white" opacity="0.6" />
                            <rect x="95" y="240" width="45" height="3" fill="white" opacity="0.6" />

                            {{-- Student 1 --}}
                            <circle cx="180" cy="120" r="30" fill="#FDE68A" />
                            <circle cx="180" cy="115" r="25" fill="#F59E0B" />
                            <circle cx="180" cy="100" r="20" fill="#FCD34D" />
                            <ellipse cx="180" cy="95" rx="18" ry="16" fill="#78350F" />
                            <circle cx="173" cy="98" r="3" fill="white" />
                            <circle cx="187" cy="98" r="3" fill="white" />
                            <path d="M175 108 Q180 113 185 108" stroke="#78350F" stroke-width="2" fill="none" />
                            {{-- Student 1 body --}}
                            <rect x="160" y="130" width="40" height="50" rx="8" fill="#3B82F6" />
                            {{-- Tablet in hand --}}
                            <rect x="155" y="155" width="25" height="35" rx="3" fill="#1F2937" />
                            <rect x="158" y="158" width="19" height="29" rx="1" fill="#60A5FA" />

                            {{-- Student 2 --}}
                            <circle cx="260" cy="130" r="28" fill="#FDE68A" />
                            <circle cx="260" cy="125" r="23" fill="#EC4899" />
                            <circle cx="260" cy="112" r="18" fill="#FCD34D" />
                            <ellipse cx="260" cy="107" rx="16" ry="14" fill="#1F2937" />
                            <circle cx="254" cy="110" r="2.5" fill="white" />
                            <circle cx="266" cy="110" r="2.5" fill="white" />
                            <path d="M255 118 Q260 122 265 118" stroke="#1F2937" stroke-width="2" fill="none" />
                            {{-- Student 2 body --}}
                            <rect x="242" y="138" width="36" height="45" rx="8" fill="#EC4899" />
                            {{-- Book in hand --}}
                            <rect x="275" y="155" width="30" height="40" rx="2" fill="#10B981" />
                            <rect x="278" y="160" width="24" height="3" fill="white" opacity="0.8" />
                            <rect x="278" y="167" width="20" height="2" fill="white" opacity="0.6" />

                            {{-- Teacher --}}
                            <circle cx="320" cy="180" r="25" fill="#FDE68A" />
                            <circle cx="320" cy="175" r="20" fill="#6366F1" />
                            <circle cx="320" cy="165" r="17" fill="#FCD34D" />
                            <ellipse cx="320" cy="161" rx="15" ry="12" fill="#4B5563" />
                            <rect x="313" cy="158" width="3" height="6" fill="#9CA3AF" />
                            <circle cx="314" cy="163" r="2" fill="white" />
                            <circle cx="326" cy="163" r="2" fill="white" />
                            <path d="M316 170 Q320 173 324 170" stroke="#4B5563" stroke-width="1.5" fill="none" />
                            {{-- Teacher body --}}
                            <rect x="305" y="190" width="30" height="55" rx="6" fill="#6366F1" />
                            {{-- Clipboard --}}
                            <rect x="285" y="205" width="22" height="30" rx="2" fill="#F3F4F6" />
                            <rect x="292" y="200" width="8" height="6" rx="1" fill="#9CA3AF" />
                            <rect x="288" y="212" width="16" height="2" fill="#3B82F6" />
                            <rect x="288" y="218" width="14" height="2" fill="#3B82F6" />
                            <rect x="288" y="224" width="12" height="2" fill="#3B82F6" />

                            {{-- Decorative elements --}}
                            <circle cx="120" cy="100" r="8" fill="#FCD34D" />
                            <circle cx="330" cy="100" r="6" fill="#34D399" />
                            <circle cx="100" cy="250" r="5" fill="#F472B6" />
                            <path d="M350 150 L360 160 L350 170" stroke="#3B82F6" stroke-width="3" fill="none" />
                            <path d="M60 180 L70 190 L60 200" stroke="#10B981" stroke-width="3" fill="none" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm text-gray-500">
            © {{ date('Y') }} Eraport STS • Dikembangkan oleh Fahmie Al Khudhorie
        </div>
    </footer>
</body>

</html>
