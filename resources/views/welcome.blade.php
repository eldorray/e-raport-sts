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
            background: #f8fafc;
            color: #1e293b;
        }
    </style>
</head>

<body>
    <div class="min-h-screen flex flex-col">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-100">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <img src="{{ asset('images/eraport-logo.png') }}" alt="eraport" class="h-9">
                <a href="{{ route('login') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                    Masuk
                </a>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 flex items-center">
            <div class="max-w-5xl mx-auto px-6 py-16 w-full">
                <div class="text-center max-w-2xl mx-auto">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight">
                        Sistem Penilaian Rapor Digital
                    </h1>
                    <p class="mt-4 text-gray-600 text-lg">
                        Kelola nilai siswa dengan mudah. Input nilai sumatif dan STS, dapatkan deskripsi capaian
                        otomatis.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition">
                            Mulai Sekarang
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Features --}}
                <div class="mt-20 grid sm:grid-cols-3 gap-8">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">Penilaian Otomatis</h3>
                        <p class="mt-2 text-sm text-gray-600">Rata-rata rapor dihitung secara otomatis dari nilai
                            sumatif dan STS.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">Deskripsi Capaian</h3>
                        <p class="mt-2 text-sm text-gray-600">Predikat dan kalimat capaian terisi otomatis sesuai
                            rentang nilai.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900">Data Terintegrasi</h3>
                        <p class="mt-2 text-sm text-gray-600">Guru, kelas, dan siswa tersinkron dalam satu sistem.</p>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="bg-white border-t border-gray-100 py-6">
            <div class="max-w-5xl mx-auto px-6 text-center text-sm text-gray-500">
                © {{ date('Y') }} Eraport STS • Dikembangkan oleh Fahmie Al Khudhorie
            </div>
        </footer>
    </div>
</body>

</html>
