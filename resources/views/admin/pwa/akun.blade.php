@php
    $inisial = collect(explode(' ', (string) $user->name))
        ->filter()
        ->take(2)
        ->map(fn (string $kata): string => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('') ?: 'A';
@endphp

<x-layouts.pwa :title="__('Akun')" :daftar-tahun="$daftarTahun">
    {{-- Identitas --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-3">
            <span
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-lg font-bold text-white">{{ $inisial }}</span>

            <div class="min-w-0 flex-1">
                <p class="truncate text-base font-semibold">{{ $user->name }}</p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span
                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <i class="fas fa-user-shield mr-1"></i>{{ __('Admin') }}
                    </span>
                </div>
            </div>
        </div>

        <a href="{{ route('settings.profile.edit') }}"
            class="mt-3 flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition active:scale-[0.99] dark:border-slate-700 dark:text-slate-300">
            <i class="fas fa-id-card"></i>{{ __('Ubah profil') }}
        </a>
    </section>

    {{-- Tahun ajaran & semester --}}
    <x-pwa.kartu-tahun class="mt-3" :tahun-ajaran="$tahunAjaran" :semester="$semester" />

    {{-- Tampilan --}}
    <x-pwa.pilih-tema class="mt-3" />

    {{-- Keamanan --}}
    <a href="{{ route('settings.password.edit') }}"
        class="mt-3 flex items-center gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
        <i class="fas fa-lock text-emerald-600 dark:text-emerald-400"></i>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-bold">{{ __('Ubah kata sandi') }}</span>
            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ __('Dibuka di halaman pengaturan web') }}</span>
        </span>
        <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600"></i>
    </a>

    {{-- Aplikasi --}}
    <section class="mt-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-2">
            <i class="fas fa-mobile-screen-button text-emerald-600 dark:text-emerald-400"></i>
            <h2 class="text-sm font-bold">{{ __('Aplikasi') }}</h2>
        </div>

        <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
            <template x-if="terinstal">
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                    {{ __('Sudah dipasang di layar utama — terima kasih!') }}
                </span>
            </template>
            <template x-if="!terinstal">
                <span>{{ __('Pasang aplikasi ini agar bisa dibuka cepat seperti aplikasi biasa.') }}</span>
            </template>
        </p>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <button type="button" @click="pasang()" x-cloak x-show="!terinstal"
                class="flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-3 text-sm font-semibold text-emerald-700 transition active:scale-[0.98] dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                <i class="fas fa-download"></i>{{ __('Pasang') }}
            </button>

            <a href="{{ route('app.versi-web') }}"
                class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-3 py-3 text-sm font-semibold text-slate-600 transition active:scale-[0.98] dark:border-slate-700 dark:text-slate-300">
                <i class="fas fa-desktop"></i>{{ __('Versi Web') }}
            </a>
        </div>
    </section>

    {{-- Keluar --}}
    <form method="POST" action="{{ route('logout') }}" class="mt-3"
        onsubmit="return confirm('{{ __('Keluar dari aplikasi sekarang?') }}');">
        @csrf

        <button type="submit"
            class="flex h-12 w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 text-sm font-bold text-red-600 transition active:scale-[0.99] dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <i class="fas fa-right-from-bracket"></i>{{ __('Keluar') }}
        </button>
    </form>

    <p class="mt-6 text-center text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('e-Raport Kurikulum Merdeka — aplikasi admin') }}
    </p>
</x-layouts.pwa>
