@props([
    'title' => null,
    'subtitle' => null,
    'back' => null,
])<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $title ? $title.' - ' : '' }}{{ config('app.name') }}</title>

    <meta name="theme-color" content="#047857" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#020617" media="(prefers-color-scheme: dark)">
    <meta name="color-scheme" content="light dark">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="e-Raport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/eraport-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/eraport-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        (function() {
            const samakanWarnaTema = () => {
                const gelap = document.documentElement.classList.contains('dark');

                document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => meta.remove());

                const meta = document.createElement('meta');
                meta.setAttribute('name', 'theme-color');
                meta.setAttribute('content', gelap ? '#020617' : '#047857');
                document.head.appendChild(meta);
            };

            const terapkan = (mode) => {
                if (mode === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (mode === 'light') {
                    document.documentElement.classList.remove('dark');
                } else {
                    document.documentElement.classList.toggle('dark', window.matchMedia(
                        '(prefers-color-scheme: dark)').matches);
                }

                samakanWarnaTema();
            };

            window.pwaAppearance = () => window.localStorage.getItem('appearance') || 'system';

            window.pwaSetAppearance = (mode) => {
                if (mode === 'system') {
                    window.localStorage.removeItem('appearance');
                } else {
                    window.localStorage.setItem('appearance', mode);
                }

                terapkan(mode);
            };

            terapkan(window.pwaAppearance());

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (window.pwaAppearance() === 'system') {
                    terapkan('system');
                }
            });
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Pra-muat halaman aplikasi agar perpindahan menu terasa instan --}}
    <script type="speculationrules">
        {
            "prefetch": [
                {
                    "where": {
                        "href_matches": "/guru-app*"
                    },
                    "eagerness": "moderate"
                }
            ]
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        html {
            -webkit-tap-highlight-color: transparent;
        }

        .safe-atas {
            padding-top: env(safe-area-inset-top);
        }

        .safe-bawah {
            padding-bottom: env(safe-area-inset-bottom);
        }

        input[type='number']::-webkit-outer-spin-button,
        input[type='number']::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        @media (display-mode: standalone) {
            .hanya-peramban {
                display: none !important;
            }
        }
    </style>
</head>

<body class="min-h-dvh bg-slate-100 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div x-data="{
        offline: !navigator.onLine,
        tema: window.pwaAppearance(),
        promptEvent: null,
        terinstal: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
        panduan: false,
        bannerTampil: false,
        platform: /iPad|iPhone|iPod/.test(navigator.userAgent) ? 'ios' : (/Android/.test(navigator.userAgent) ? 'android' : 'desktop'),
        init() {
            const ditutupPada = Number(window.localStorage.getItem('pwa-banner-ditutup') || 0);
            const masihBaru = ditutupPada > 0 && (Date.now() - ditutupPada) < 7 * 24 * 60 * 60 * 1000;
    
            this.bannerTampil = !this.terinstal && !masihBaru;
    
            window.addEventListener('appinstalled', () => {
                this.terinstal = true;
                this.bannerTampil = false;
            });
        },
        putarTema() {
            const sedangGelap = document.documentElement.classList.contains('dark');
            this.tema = sedangGelap ? 'light' : 'dark';
            window.pwaSetAppearance(this.tema);
        },
        tutupBanner() {
            this.bannerTampil = false;
            window.localStorage.setItem('pwa-banner-ditutup', String(Date.now()));
        },
        async pasang() {
            if (this.promptEvent) {
                this.promptEvent.prompt();
                const hasil = await this.promptEvent.userChoice;
                if (hasil.outcome === 'accepted') {
                    this.terinstal = true;
                    this.bannerTampil = false;
                }
                return;
            }
            this.panduan = true;
        }
    }"
        @beforeinstallprompt.window.prevent="promptEvent = $event; bannerTampil = !terinstal" @online.window="offline = false"
        @offline.window="offline = true" class="flex min-h-dvh flex-col">
        {{-- Indikator offline --}}
        <div x-cloak x-show="offline" x-transition
            class="safe-atas fixed inset-x-0 top-0 z-40 bg-amber-500 px-4 py-2 text-center text-xs font-semibold text-white shadow">
            {{ __('Tidak ada koneksi — nilai belum bisa disimpan.') }}
        </div>

        {{-- Header aplikasi --}}
        <header
            class="safe-atas sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
            <div class="flex min-h-16 items-center gap-2 px-3 py-2">
                @if ($back)
                    <a href="{{ $back }}" aria-label="{{ __('Kembali') }}"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-slate-600 transition hover:bg-slate-100 active:scale-95 dark:text-slate-300 dark:hover:bg-slate-800">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                @else
                    <img src="{{ asset('images/eraport-icon.png') }}" alt="e-Raport"
                        class="h-10 w-10 shrink-0 rounded-xl">
                @endif

                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-semibold leading-tight">{{ $title ?? __('Aplikasi Guru') }}</p>
                    @if ($subtitle)
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                </div>

                <button type="button" @click="putarTema()" x-cloak
                    :aria-label="tema === 'dark' ? '{{ __('Ganti ke tema terang') }}' : '{{ __('Ganti ke tema gelap') }}'"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-slate-600 transition hover:bg-slate-100 active:scale-95 dark:text-slate-300 dark:hover:bg-slate-800">
                    <i class="fas fa-moon text-lg" x-show="tema !== 'dark'"></i>
                    <i class="fas fa-sun text-lg" x-show="tema === 'dark'"></i>
                </button>

                <button type="button" @click="pasang()" x-cloak x-show="!terinstal"
                    aria-label="{{ __('Pasang aplikasi') }}"
                    class="hanya-peramban flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-emerald-600 transition hover:bg-emerald-50 active:scale-95 dark:text-emerald-400 dark:hover:bg-emerald-950/50">
                    <i class="fas fa-download text-lg"></i>
                </button>
            </div>
        </header>

        <main class="flex-1 px-4 pb-32 pt-4">
            <x-pwa.flash />
            <x-pwa.install-banner />

            {{ $slot }}
        </main>

        {{-- Navigasi bawah --}}
        <nav
            class="safe-bawah fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
            <div class="mx-auto grid max-w-lg grid-cols-5">
                @php
                    $menu = [
                        [
                            'label' => __('Beranda'),
                            'ikon' => 'fa-house',
                            'url' => route('guru.pwa.beranda'),
                            'aktif' => request()->routeIs('guru.pwa.beranda'),
                        ],
                        [
                            'label' => __('Nilai'),
                            'ikon' => 'fa-pen-to-square',
                            'url' => route('guru.pwa.nilai'),
                            'aktif' => request()->routeIs('guru.pwa.nilai*'),
                        ],
                        [
                            'label' => __('Wali'),
                            'ikon' => 'fa-user-graduate',
                            'url' => route('guru.pwa.wali'),
                            'aktif' => request()->routeIs('guru.pwa.wali'),
                        ],
                        [
                            'label' => __('Ekskul'),
                            'ikon' => 'fa-medal',
                            'url' => route('guru.pwa.ekskul'),
                            'aktif' => request()->routeIs('guru.pwa.ekskul*'),
                        ],
                        [
                            'label' => __('Akun'),
                            'ikon' => 'fa-user-gear',
                            'url' => route('guru.pwa.akun'),
                            'aktif' => request()->routeIs('guru.pwa.akun') || request()->routeIs('settings.*'),
                        ],
                    ];
                @endphp

                @foreach ($menu as $item)
                    <a href="{{ $item['url'] }}"
                        class="flex flex-col items-center gap-1 px-1 pb-2 pt-2.5 text-[11px] font-medium transition {{ $item['aktif'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
                        <span
                            class="flex h-9 w-14 items-center justify-center rounded-2xl transition {{ $item['aktif'] ? 'bg-emerald-50 dark:bg-emerald-950/60' : '' }}">
                            <i class="fas {{ $item['ikon'] }} text-lg"></i>
                        </span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        {{-- Panduan pasang aplikasi manual (iOS / peramban tanpa prompt) --}}
        <div x-cloak x-show="panduan" x-transition.opacity
            class="fixed inset-0 z-50 flex items-end bg-slate-900/60 p-4 sm:items-center sm:justify-center"
            @click.self="panduan = false">
            <div class="w-full max-w-sm rounded-3xl bg-white p-5 shadow-xl dark:bg-slate-900">
                <div class="mb-3 flex items-center gap-3">
                    <img src="{{ asset('images/pwa-192.png') }}" alt="" class="h-12 w-12 rounded-2xl">
                    <div>
                        <p class="text-sm font-semibold">{{ __('Pasang e-Raport di layar utama') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Akses lebih cepat, seperti aplikasi.') }}</p>
                    </div>
                </div>

                <ol class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <template x-if="platform === 'ios'">
                        <li class="flex gap-2">
                            <span class="font-semibold">1.</span>
                            <span>{!! __('Ketuk ikon <strong>Bagikan</strong> di Safari, lalu pilih <strong>Tambahkan ke Layar Utama</strong>.') !!}</span>
                        </li>
                    </template>
                    <template x-if="platform === 'android'">
                        <li class="flex gap-2">
                            <span class="font-semibold">1.</span>
                            <span>{!! __('Buka menu <strong>⋮</strong> di peramban, lalu pilih <strong>Instal aplikasi</strong> / <strong>Tambahkan ke layar utama</strong>.') !!}</span>
                        </li>
                    </template>
                    <template x-if="platform === 'desktop'">
                        <li class="flex gap-2">
                            <span class="font-semibold">1.</span>
                            <span>{!! __('Klik ikon <strong>instal</strong> di kanan address bar, atau menu peramban → <strong>Instal e-Raport</strong>.') !!}</span>
                        </li>
                    </template>
                    <li class="flex gap-2">
                        <span class="font-semibold">2.</span>
                        <span>{!! __('Beri nama <strong>e-Raport</strong>, lalu simpan.') !!}</span>
                    </li>
                </ol>

                <button type="button" @click="panduan = false"
                    class="mt-4 w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 active:scale-[0.99]">
                    {{ __('Mengerti') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>

</html>
