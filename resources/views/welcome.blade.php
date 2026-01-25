<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'E-Raport STS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/eraport-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/eraport-icon.png') }}">

    @php
        $school = \App\Models\SchoolProfile::first();
        $schoolName = $school?->name ?? 'Sekolah';
        $logoUrl = $school?->logo ? asset('storage/' . $school->logo) : null;
        $totalSiswa = \App\Models\Siswa::count();
        $totalGuru = \App\Models\Guru::count();
        $totalKelas = \App\Models\Kelas::count();
    @endphp

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite(['resources/css/app.css'])

    <style>
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes float-delayed {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 0.4;
            }

            50% {
                opacity: 0.7;
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float-delayed 5s ease-in-out infinite;
            animation-delay: 1s;
        }

        .animate-pulse-slow {
            animation: pulse-slow 4s ease-in-out infinite;
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #06b6d4, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-8px);
        }

        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Light mode styles */
        html:not(.dark) .glass {
            background: rgba(255, 255, 255, 0.8);
        }

        html:not(.dark) .gradient-text {
            background: linear-gradient(135deg, #2563eb, #0891b2, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Theme toggle button */
        .theme-toggle {
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: rotate(15deg) scale(1.1);
        }
    </style>

    <script>
        // Check for saved theme preference or system preference
        if (localStorage.getItem('theme') === 'light' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body
    class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-slate-50 font-['Plus_Jakarta_Sans']">
    <div class="relative min-h-screen overflow-hidden">

        {{-- Animated Background --}}
        <div class="pointer-events-none absolute inset-0">
            <div
                class="animate-pulse-slow absolute -right-40 -top-40 h-[500px] w-[500px] rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-500/20">
            </div>
            <div class="animate-pulse-slow absolute -left-40 top-1/2 h-[400px] w-[400px] rounded-full bg-purple-500/10 blur-3xl dark:bg-purple-500/15"
                style="animation-delay: 2s;"></div>
            <div class="animate-pulse-slow absolute bottom-0 right-1/4 h-[300px] w-[300px] rounded-full bg-cyan-500/10 blur-3xl dark:bg-cyan-500/15"
                style="animation-delay: 4s;"></div>
        </div>

        {{-- Grid Pattern --}}
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(0,0,0,0.03)_1px,transparent_1px)] bg-[size:64px_64px] dark:bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)]">
        </div>

        {{-- Header --}}
        <header class="glass relative z-50 border-b border-slate-200 dark:border-white/10">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo Sekolah"
                            class="h-16 w-16 rounded-xl bg-slate-100 object-contain p-2 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/20">
                    @else
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 shadow-lg shadow-blue-500/30">
                            <i class="fa-solid fa-graduation-cap text-2xl text-white"></i>
                        </div>
                    @endif
                    <div>
                        <p class="text-xl font-bold text-slate-900 dark:text-white">
                            {{ $school?->name ?? 'E-Raport STS' }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $school?->city ?? 'Sumatif Tengah Semester' }}</p>
                    </div>
                </div>
                <nav class="flex items-center gap-3">
                    {{-- Theme Toggle --}}
                    <button id="themeToggle" type="button"
                        class="theme-toggle flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-200 dark:bg-white/10 dark:text-yellow-300 dark:ring-white/10 dark:hover:bg-white/20">
                        <i class="fa-solid fa-sun hidden dark:block"></i>
                        <i class="fa-solid fa-moon block dark:hidden"></i>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:shadow-blue-500/50">
                            <i class="fa-solid fa-arrow-right"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:shadow-blue-500/50">
                            <i class="fa-solid fa-right-to-bracket"></i> Masuk
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        {{-- Hero Section --}}
        <section class="relative z-10 px-6 py-16 lg:py-24">
            <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2">
                <div class="text-center lg:text-left">
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-4 py-2 text-sm font-medium text-blue-600 ring-1 ring-inset ring-blue-400/30 dark:text-blue-300">
                        <i class="fa-solid fa-sparkles"></i> Sistem Rapor Digital Modern
                    </div>

                    <h1
                        class="text-4xl font-extrabold leading-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                        Kelola Penilaian
                        <span class="gradient-text">Lebih Mudah</span>
                        & Efisien
                    </h1>

                    <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-300 lg:mx-0">
                        Aplikasi rapor digital berbasis web untuk mengelola nilai Sumatif Tengah Semester (STS) dengan
                        fitur lengkap dan mudah digunakan.
                    </p>

                    <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row lg:justify-start">
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-500 via-blue-600 to-cyan-500 px-8 py-4 text-base font-semibold text-white shadow-xl shadow-blue-500/30 transition hover:scale-105 hover:shadow-blue-500/50 sm:w-auto">
                                <i class="fa-solid fa-arrow-right"></i> Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-500 via-blue-600 to-cyan-500 px-8 py-4 text-base font-semibold text-white shadow-xl shadow-blue-500/30 transition hover:scale-105 hover:shadow-blue-500/50 sm:w-auto">
                                <i class="fa-solid fa-right-to-bracket"></i> Mulai Sekarang
                            </a>
                        @endauth
                        <a href="#features"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-8 py-4 text-base font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-200 dark:border-white/20 dark:bg-white/5 dark:text-white dark:hover:border-white/30 dark:hover:bg-white/10 sm:w-auto">
                            <i class="fa-solid fa-circle-info"></i> Pelajari Fitur
                        </a>
                    </div>

                    {{-- Stats --}}
                    <div class="mt-12 grid grid-cols-3 gap-6">
                        <div class="text-center lg:text-left">
                            <p class="text-3xl font-bold text-slate-900 dark:text-white">
                                {{ number_format($totalSiswa) }}+</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Siswa Aktif</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <p class="text-3xl font-bold text-slate-900 dark:text-white">
                                {{ number_format($totalGuru) }}+</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Guru Pengajar</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <p class="text-3xl font-bold text-slate-900 dark:text-white">
                                {{ number_format($totalKelas) }}+</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelas Aktif</p>
                        </div>
                    </div>
                </div>

                {{-- Right Illustration --}}
                <div class="relative hidden lg:block">
                    {{-- Floating Cards --}}
                    <div class="animate-float absolute -top-8 left-8">
                        <div
                            class="glass rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 p-4 shadow-2xl dark:border-white/10 dark:from-emerald-500/20 dark:to-emerald-600/10">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 shadow-lg shadow-emerald-500/30">
                                    <i class="fa-solid fa-check text-xl text-white"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">Nilai Tersimpan</p>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-300">25 siswa telah dinilai</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="animate-float-delayed absolute -bottom-4 right-8">
                        <div
                            class="glass rounded-2xl border border-slate-200 bg-gradient-to-br from-purple-500/10 to-purple-600/5 p-4 shadow-2xl dark:border-white/10 dark:from-purple-500/20 dark:to-purple-600/10">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500 shadow-lg shadow-purple-500/30">
                                    <i class="fa-solid fa-print text-xl text-white"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">Rapor Siap Cetak</p>
                                    <p class="text-xs text-purple-600 dark:text-purple-300">Kelas 6A - 30 siswa</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Card --}}
                    <div
                        class="glass relative ml-12 mt-8 rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-100/80 via-slate-50/50 to-transparent p-8 shadow-2xl dark:border-white/10 dark:from-white/10 dark:via-white/5">
                        <div
                            class="absolute -inset-px rounded-3xl bg-gradient-to-br from-blue-500/10 via-transparent to-purple-500/10 blur-xl dark:from-blue-500/20 dark:to-purple-500/20">
                        </div>
                        <div class="relative space-y-6">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-300">Alur
                                    Penggunaan</span>
                                <span
                                    class="rounded-full bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-600 ring-1 ring-blue-500/30 dark:bg-blue-500/20 dark:text-blue-300">E-Raport
                                    STS</span>
                            </div>

                            <div class="space-y-4">
                                <div
                                    class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-100/50 p-4 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30">
                                        <i class="fa-solid fa-book-open text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900 dark:text-white">1. Pilih Mata Pelajaran
                                        </p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Pilih mapel dan kelas
                                            yang diampu</p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-slate-400 dark:text-slate-500"></i>
                                </div>

                                <div
                                    class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-100/50 p-4 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30">
                                        <i class="fa-solid fa-pen-to-square text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900 dark:text-white">2. Input Nilai</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Masukkan nilai Sumatif &
                                            STS</p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-slate-400 dark:text-slate-500"></i>
                                </div>

                                <div
                                    class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-100/50 p-4 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg shadow-purple-500/30">
                                        <i class="fa-solid fa-file-lines text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900 dark:text-white">3. Cetak Rapor</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Deskripsi capaian
                                            otomatis</p>
                                    </div>
                                    <i class="fa-solid fa-check-circle text-emerald-500 dark:text-emerald-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section id="features"
            class="relative z-10 border-t border-slate-200 bg-slate-100/50 py-24 dark:border-white/10 dark:bg-slate-900/50">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-16 text-center">
                    <span
                        class="mb-4 inline-flex items-center gap-2 rounded-full bg-purple-500/10 px-4 py-2 text-sm font-medium text-purple-600 ring-1 ring-inset ring-purple-400/30 dark:text-purple-300">
                        <i class="fa-solid fa-sparkles"></i> Fitur Unggulan
                    </span>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">Semua yang Anda Butuhkan
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-400">Fitur lengkap untuk
                        mengelola penilaian dan rapor siswa dengan efisien</p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    {{-- Feature 1 --}}
                    <div
                        class="card-hover glass rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50/80 to-transparent p-8 dark:border-white/10 dark:from-white/5">
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30">
                            <i class="fa-solid fa-calculator text-xl text-white"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white">Kalkulasi Otomatis</h3>
                        <p class="leading-relaxed text-slate-600 dark:text-slate-400">Nilai rata-rata rapor dihitung
                            otomatis dari nilai Sumatif dan STS dengan bobot yang dapat dikonfigurasi.</p>
                    </div>

                    {{-- Feature 2 --}}
                    <div
                        class="card-hover glass rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50/80 to-transparent p-8 dark:border-white/10 dark:from-white/5">
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30">
                            <i class="fa-solid fa-wand-magic-sparkles text-xl text-white"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white">Deskripsi Otomatis</h3>
                        <p class="leading-relaxed text-slate-600 dark:text-slate-400">Predikat dan deskripsi capaian
                            pembelajaran dibuat otomatis berdasarkan rentang nilai yang ditentukan.</p>
                    </div>

                    {{-- Feature 3 --}}
                    <div
                        class="card-hover glass rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50/80 to-transparent p-8 dark:border-white/10 dark:from-white/5">
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg shadow-purple-500/30">
                            <i class="fa-solid fa-print text-xl text-white"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white">Cetak Rapor</h3>
                        <p class="leading-relaxed text-slate-600 dark:text-slate-400">Cetak rapor dalam format PDF yang
                            rapi dan sesuai dengan standar kurikulum terbaru.</p>
                    </div>

                    {{-- Feature 4 --}}
                    <div
                        class="card-hover glass rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50/80 to-transparent p-8 dark:border-white/10 dark:from-white/5">
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg shadow-orange-500/30">
                            <i class="fa-solid fa-users-gear text-xl text-white"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white">Manajemen Kelas</h3>
                        <p class="leading-relaxed text-slate-600 dark:text-slate-400">Kelola data siswa, guru, dan
                            kelas dengan mudah. Wali kelas dapat mengelola siswa di kelasnya.</p>
                    </div>

                    {{-- Feature 5 --}}
                    <div
                        class="card-hover glass rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50/80 to-transparent p-8 dark:border-white/10 dark:from-white/5">
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 shadow-lg shadow-cyan-500/30">
                            <i class="fa-solid fa-chart-line text-xl text-white"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white">Dashboard Analitik</h3>
                        <p class="leading-relaxed text-slate-600 dark:text-slate-400">Pantau progres pengisian nilai
                            dan statistik kelas melalui dashboard yang informatif.</p>
                    </div>

                    {{-- Feature 6 --}}
                    <div
                        class="card-hover glass rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50/80 to-transparent p-8 dark:border-white/10 dark:from-white/5">
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-pink-600 shadow-lg shadow-pink-500/30">
                            <i class="fa-solid fa-shield-halved text-xl text-white"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white">Aman & Terpercaya</h3>
                        <p class="leading-relaxed text-slate-600 dark:text-slate-400">Data tersimpan aman dengan sistem
                            backup dan restore untuk keamanan data sekolah.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA Section --}}
        <section class="relative z-10 py-24">
            <div class="mx-auto max-w-4xl px-6 text-center">
                <div
                    class="glass rounded-3xl border border-slate-200 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-cyan-500/5 p-12 dark:border-white/10 dark:from-blue-500/10 dark:via-purple-500/10 dark:to-cyan-500/10">
                    <h2 class="mb-4 text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">Siap Memulai?</h2>
                    <p class="mx-auto mb-8 max-w-xl text-lg text-slate-600 dark:text-slate-300">Akses sistem rapor
                        digital sekarang dan permudah proses penilaian di sekolah Anda.</p>
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-blue-500 via-blue-600 to-cyan-500 px-10 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition hover:scale-105 hover:shadow-blue-500/50">
                            <i class="fa-solid fa-arrow-right"></i> Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-blue-500 via-blue-600 to-cyan-500 px-10 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition hover:scale-105 hover:shadow-blue-500/50">
                            <i class="fa-solid fa-arrow-right"></i> Masuk Sekarang
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer
            class="relative z-10 border-t border-slate-200 bg-slate-100/80 py-8 dark:border-white/10 dark:bg-slate-900/80">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="flex items-center gap-3">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo"
                                class="h-14 w-14 rounded-lg bg-slate-200 object-contain p-1.5 dark:bg-white/10">
                        @else
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500">
                                <i class="fa-solid fa-graduation-cap text-xl text-white"></i>
                            </div>
                        @endif
                        <span class="text-lg font-semibold text-slate-900 dark:text-white">E-Raport STS</span>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        © {{ date('Y') }} <span class="text-slate-900 dark:text-white">{{ $schoolName }}</span>
                        • Dikembangkan oleh
                        <a href="https://github.com/eldorray" target="_blank"
                            class="text-blue-600 transition hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Fahmie
                            Al Khudhorie</a>
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');

        themeToggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');

            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>

</html>
