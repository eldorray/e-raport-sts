{{-- Pilihan tema Terang / Gelap / Sistem; memakai state `tema` dan pilihTema() milik layout PWA. --}}
<section {{ $attributes->merge(['class' => 'rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="flex items-center gap-2">
        <i class="fas fa-palette text-emerald-600 dark:text-emerald-400"></i>
        <h2 class="text-sm font-bold">{{ __('Tampilan') }}</h2>
    </div>

    <div class="mt-3 grid grid-cols-3 gap-2">
        @foreach ([['mode' => 'light', 'label' => __('Terang'), 'ikon' => 'fa-sun'], ['mode' => 'dark', 'label' => __('Gelap'), 'ikon' => 'fa-moon'], ['mode' => 'system', 'label' => __('Sistem'), 'ikon' => 'fa-circle-half-stroke']] as $pilihan)
            <button type="button" @click="pilihTema('{{ $pilihan['mode'] }}')"
                :aria-pressed="tema === '{{ $pilihan['mode'] }}'"
                class="flex flex-col items-center gap-1.5 rounded-2xl border px-2 py-3 text-xs font-semibold transition active:scale-[0.98]"
                :class="tema === '{{ $pilihan['mode'] }}'
                    ?
                    'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' :
                    'border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-400'">
                <i class="fas {{ $pilihan['ikon'] }} text-base"></i>
                {{ $pilihan['label'] }}
            </button>
        @endforeach
    </div>
</section>
