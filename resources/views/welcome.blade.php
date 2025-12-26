<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eraport STS</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(3deg);
            }
        }

        @keyframes float-slow {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(-2deg);
            }
        }

        @keyframes pulse-soft {

            0%,
            100% {
                opacity: 0.6;
                transform: scale(1);
            }

            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }

        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-slow {
            animation: float-slow 8s ease-in-out infinite;
        }

        .animate-float-delay {
            animation: float 6s ease-in-out infinite 2s;
        }

        .animate-pulse-soft {
            animation: pulse-soft 4s ease-in-out infinite;
        }

        .gradient-animate {
            background-size: 200% 200%;
            animation: gradient-shift 8s ease infinite;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
    </style>
</head>

<body class="min-h-screen overflow-x-hidden">
    {{-- Background --}}
    <div class="fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-emerald-50"></div>
        {{-- Decorative Blobs --}}
        <div
            class="absolute top-0 -left-40 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse-soft">
        </div>
        <div class="absolute top-0 -right-40 w-96 h-96 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse-soft"
            style="animation-delay: 2s"></div>
        <div class="absolute -bottom-40 left-1/2 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-pulse-soft"
            style="animation-delay: 4s"></div>
        {{-- Grid Pattern --}}
        <div class="absolute inset-0"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,0.03) 1px, transparent 0); background-size: 40px 40px;">
        </div>
    </div>

    <div class="min-h-screen flex flex-col relative">
        {{-- Header --}}
        <header class="py-6 px-6 relative z-10">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/eraport-logo.png') }}" alt="eraport" class="h-10">
                </div>
                <a href="{{ route('login') }}"
                    class="group inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:shadow-xl hover:shadow-blue-600/40 hover:-translate-y-0.5">
                    Masuk
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </header>

        {{-- Hero Section --}}
        <main class="flex-1 flex items-center relative z-10">
            <div class="max-w-6xl mx-auto px-6 py-12 w-full">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    {{-- Left Content --}}
                    <div class="relative">
                        {{-- Badge --}}
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-500/10 to-emerald-500/10 border border-blue-200/50 px-4 py-2 mb-6">
                            <span class="flex h-2 w-2 relative">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-sm font-medium text-gray-700">Sistem Penilaian Modern</span>
                        </div>

                        {{-- Heading --}}
                        <h1
                            class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-[1.1] tracking-tight">
                            Kelola Nilai
                            <span class="relative">
                                <span
                                    class="bg-gradient-to-r from-blue-600 via-blue-500 to-emerald-500 bg-clip-text text-transparent gradient-animate">Siswa</span>
                                <svg class="absolute -bottom-2 left-0 w-full h-3 text-blue-500/30" viewBox="0 0 200 9"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 8C31 2 61 2 101 5C141 8 171 6 199 2" stroke="currentColor"
                                        stroke-width="3" fill="none" stroke-linecap="round" />
                                </svg>
                            </span>
                            <br>dengan Mudah
                        </h1>

                        {{-- Description --}}
                        <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-lg">
                            Masukkan nilai sumatif dan STS, dapatkan deskripsi capaian otomatis. Terintegrasi penuh
                            dengan data guru, kelas, dan siswa.
                        </p>

                        {{-- CTA Buttons --}}
                        <div class="mt-10 flex flex-wrap gap-4">
                            <a href="{{ route('login') }}"
                                class="group inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-4 text-base font-semibold text-white shadow-xl shadow-blue-600/30 transition-all hover:shadow-2xl hover:shadow-blue-600/40 hover:-translate-y-1 active:translate-y-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Mulai Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>

                        {{-- Stats --}}
                        <div class="mt-12 flex items-center gap-8">
                            <div>
                                <p class="text-3xl font-bold text-gray-900">100%</p>
                                <p class="text-sm text-gray-500">Otomatis</p>
                            </div>
                            <div class="w-px h-12 bg-gray-200"></div>
                            <div>
                                <p class="text-3xl font-bold text-gray-900">Real-time</p>
                                <p class="text-sm text-gray-500">Sync Data</p>
                            </div>
                            <div class="w-px h-12 bg-gray-200"></div>
                            <div>
                                <p class="text-3xl font-bold text-gray-900">Cepat</p>
                                <p class="text-sm text-gray-500">& Mudah</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right Content - Feature Cards --}}
                    <div class="relative">
                        {{-- Decorative elements --}}
                        <div
                            class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-blue-400 to-blue-600 rounded-3xl opacity-10 animate-float">
                        </div>
                        <div
                            class="absolute -bottom-10 -left-10 w-32 h-32 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl opacity-10 animate-float-delay">
                        </div>

                        <div class="space-y-5 relative">
                            {{-- Card 1 --}}
                            <div class="glass rounded-3xl p-6 border border-white/50 shadow-xl shadow-gray-200/50 hover:shadow-2xl transition-all hover:-translate-y-1 cursor-default animate-float-slow"
                                style="animation-delay: 0s">
                                <div class="flex items-start gap-5">
                                    <div
                                        class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">Penilaian Real-time</h3>
                                        <p class="mt-1 text-gray-600">Nilai sumatif, STS, dan rata-rata rapor dihitung
                                            secara otomatis saat Anda menginput.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 2 --}}
                            <div class="glass rounded-3xl p-6 border border-white/50 shadow-xl shadow-gray-200/50 hover:shadow-2xl transition-all hover:-translate-y-1 cursor-default animate-float-slow"
                                style="animation-delay: 0.5s">
                                <div class="flex items-start gap-5">
                                    <div
                                        class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">Deskripsi Otomatis</h3>
                                        <p class="mt-1 text-gray-600">Predikat dan kalimat capaian kompetensi otomatis
                                            terisi berdasarkan rentang nilai.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 3 --}}
                            <div class="glass rounded-3xl p-6 border border-white/50 shadow-xl shadow-gray-200/50 hover:shadow-2xl transition-all hover:-translate-y-1 cursor-default animate-float-slow"
                                style="animation-delay: 1s">
                                <div class="flex items-start gap-5">
                                    <div
                                        class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg shadow-purple-500/30">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">Terintegrasi Penuh</h3>
                                        <p class="mt-1 text-gray-600">Data guru, kelas, siswa, dan mata pelajaran
                                            tersinkron dalam satu sistem.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="py-6 px-6 relative z-10">
            <div class="max-w-6xl mx-auto text-center text-sm text-gray-500">
                © {{ date('Y') }} Eraport STS • Dikembangkan oleh <span class="font-medium text-gray-700">Fahmie Al
                    Khudhorie</span>
            </div>
        </footer>
    </div>
</body>

</html>
