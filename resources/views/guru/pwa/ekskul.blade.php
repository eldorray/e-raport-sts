<x-layouts.pwa :title="__('Ekskul Saya')" :subtitle="$tahunAjaran ? $tahunAjaran->nama.' • '.($semester ?: '-') : null" :back="route('guru.pwa.beranda')">
    @if (! $guru)
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200">
            <p class="font-semibold">{{ __('Akun ini belum tertaut ke data guru.') }}</p>
            <p class="mt-1 leading-relaxed">
                {{ __('Minta admin menautkan akun Anda di menu Data Guru agar ekskul yang Anda ampu muncul di sini.') }}
            </p>
        </div>
    @elseif ($daftar->isEmpty())
        <div class="rounded-3xl border border-slate-200 bg-white p-5 text-center text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <i class="fas fa-medal mb-2 text-2xl text-slate-300 dark:text-slate-600"></i>
            <p class="font-semibold">{{ __('Belum ada ekskul yang Anda ampu.') }}</p>
            <p class="mt-1 leading-relaxed text-slate-500 dark:text-slate-400">
                {{ __('Hubungi admin bila Anda seharusnya menjadi pembina ekskul.') }}
            </p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($daftar as $item)
                @php
                    $peserta = (int) ($progres[$item->id]['peserta'] ?? 0);
                    $dinilai = (int) ($progres[$item->id]['dinilai'] ?? 0);
                    $persenItem = $peserta > 0 ? (int) round(($dinilai / $peserta) * 100) : 0;
                    $keadaan = $peserta > 0 && $dinilai >= $peserta ? 'lengkap' : ($dinilai > 0 ? 'sebagian' : 'kosong');
                @endphp

                <a href="{{ route('guru.pwa.ekskul.form', $item) }}"
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-300">
                        <i class="fas fa-medal text-lg"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ $item->nama }}</p>

                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full {{ $keadaan === 'lengkap' ? 'bg-emerald-500' : ($keadaan === 'sebagian' ? 'bg-amber-400' : 'bg-slate-300') }}"
                                style="width: {{ $persenItem }}%"></div>
                        </div>

                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            {{ __(':dinilai dari :peserta peserta sudah dinilai', ['dinilai' => $dinilai, 'peserta' => $peserta]) }}
                        </p>
                    </div>

                    <i class="fas fa-chevron-right shrink-0 text-slate-300 dark:text-slate-600"></i>
                </a>
            @endforeach
        </div>

        @if ($tahunAjaran && ! $tahunAjaran->is_active)
            <p class="mt-4 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                <i class="fas fa-lock"></i>
                {{ __('Tahun ajaran tidak aktif — nilai ekskul hanya bisa dilihat.') }}
            </p>
        @endif
    @endif
</x-layouts.pwa>
