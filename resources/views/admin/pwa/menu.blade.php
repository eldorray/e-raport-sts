<x-layouts.pwa :title="__('Semua Menu')" :daftar-tahun="$daftarTahun">
    <p class="mb-4 flex items-start gap-2 px-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
        <i class="fas fa-circle-info mt-0.5 text-slate-400"></i>
        <span>{{ __('Menu ini membuka halaman versi web yang sudah menyesuaikan layar HP. Untuk kembali, pilih "Aplikasi Admin (HP)" di menu samping halaman web.') }}</span>
    </p>

    <div class="space-y-5">
        @foreach ($grupMenu as $grup)
            <section>
                <h2 class="mb-1.5 px-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ $grup['judul'] }}</h2>

                <ul
                    class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
                    @foreach ($grup['item'] as $item)
                        <li>
                            <a href="{{ $item['url'] }}"
                                class="flex min-h-14 items-center gap-3 px-3 py-2.5 transition active:bg-slate-50 dark:active:bg-slate-800/60">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-300">
                                    <i class="fas {{ $item['ikon'] }}"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold">{{ $item['label'] }}</span>
                                    <span class="block truncate text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ $item['keterangan'] }}</span>
                                </span>
                                <i class="fas fa-chevron-right shrink-0 text-xs text-slate-300 dark:text-slate-600"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.pwa>
