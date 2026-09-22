@props([
    'compact' => false,
])

<div x-cloak x-show="bannerTampil" x-transition
    {{ $attributes->merge(['class' => 'hanya-peramban mb-4 flex items-center gap-3 rounded-3xl border border-emerald-200 bg-emerald-50/80 p-3 shadow-sm dark:border-emerald-900/70 dark:bg-emerald-950/40']) }}>
    <img src="{{ asset('images/pwa-192.png') }}" alt="" class="h-11 w-11 shrink-0 rounded-2xl">

    <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold">{{ __('Pasang e-Raport di HP Anda') }}</p>
        <p class="text-[11px] leading-snug text-slate-600 dark:text-slate-300">
            {{ $compact ? __('Akses lebih cepat, seperti aplikasi.') : __('Buka tanpa mengetik alamat, langsung dari ikon di layar utama.') }}
        </p>
    </div>

    <button type="button" @click="pasang()"
        class="shrink-0 rounded-2xl bg-emerald-600 px-3.5 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700 active:scale-95">
        {{ __('Pasang') }}
    </button>

    <button type="button" @click="tutupBanner()" aria-label="{{ __('Tutup ajakan pasang') }}"
        class="shrink-0 rounded-xl p-1.5 text-slate-400 transition hover:bg-white/60 hover:text-slate-600 dark:hover:bg-slate-800/60">
        <i class="fas fa-xmark"></i>
    </button>
</div>
