{{--
    Bagian "Tahun ajaran & semester" di halaman Akun. Tombolnya membuka pemilih
    tahun ajaran milik layout PWA, jadi layout harus menerima daftar-tahun.
--}}
@props([
    'tahunAjaran' => null,
    'semester' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="flex items-center gap-2">
        <i class="fas fa-calendar-days text-emerald-600 dark:text-emerald-400"></i>
        <h2 class="text-sm font-bold">{{ __('Tahun ajaran & semester') }}</h2>
    </div>
    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
        {{ __('Data yang tampil di aplikasi mengikuti pilihan ini.') }}
    </p>

    <div class="mt-3 flex items-center gap-3 rounded-2xl bg-slate-50 px-3 py-3 dark:bg-slate-800/60">
        <span
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-300">
            <i class="fas fa-calendar-day"></i>
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold">{{ $tahunAjaran?->nama ?? __('Belum dipilih') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ $tahunAjaran ? __('Semester :semester', ['semester' => $semester ?: $tahunAjaran->semester]) : __('Pilih tahun ajaran untuk mulai.') }}
            </p>
        </div>
        @if ($tahunAjaran?->is_active)
            <span
                class="shrink-0 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white">{{ __('Aktif') }}</span>
        @endif
    </div>

    <button type="button" @click="bukaTahun()" aria-haspopup="dialog"
        class="mt-3 flex h-12 w-full items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 text-sm font-semibold text-emerald-700 transition active:scale-[0.99] dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
        <i class="fas fa-arrows-rotate"></i>{{ __('Ganti tahun ajaran') }}
    </button>
</section>
