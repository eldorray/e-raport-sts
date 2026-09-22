@php
    $inisial = collect(explode(' ', (string) $user->name))
        ->filter()
        ->take(2)
        ->map(fn (string $kata): string => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('') ?: 'G';
@endphp

<x-layouts.pwa :title="__('Akun')" :subtitle="$guru?->nama ?? $user->name">
    <div x-data="akunGuru({{ Illuminate\Support\Js::from([
        'bobotSumatif' => $bobotSumatif,
        'bobotSts' => $bobotSts,
        'sandiTerbuka' => $errors->has('current_password') || $errors->has('password'),
    ]) }})" x-init="siap()">
        {{-- Identitas --}}
        <section
            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-3">
                <span
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-lg font-bold text-white">{{ $inisial }}</span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-semibold">{{ $guru?->nama ?? $user->name }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        {{ $guru?->nip ? __('NIP :nip', ['nip' => $guru->nip]) : $user->email }}
                    </p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span
                            class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                            <i class="fas fa-chalkboard-user mr-1"></i>{{ ucfirst((string) $user->role) }}
                        </span>
                        @if ($tahunAjaran)
                            <span
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {{ $tahunAjaran->nama }} • {{ $semester ?: '-' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if (! $guru)
                <p
                    class="mt-3 flex items-start gap-2 rounded-2xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                    <i class="fas fa-triangle-exclamation mt-0.5"></i>
                    {{ __('Akun ini belum tertaut ke data guru, jadi penugasan mengajar belum bisa dibuka.') }}
                </p>
            @endif

            <a href="{{ route('settings.profile.edit') }}"
                class="mt-3 flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition active:scale-[0.99] dark:border-slate-700 dark:text-slate-300">
                <i class="fas fa-id-card"></i>{{ __('Profil lengkap') }}
            </a>
        </section>

        {{-- Bobot nilai --}}
        <section id="bobot"
            class="mt-3 scroll-mt-20 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2">
                <i class="fas fa-scale-balanced text-emerald-600 dark:text-emerald-400"></i>
                <h2 class="text-sm font-bold">{{ __('Bobot nilai rapor') }}</h2>
            </div>
            <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                {{ __('Persentase sumatif dan STS saat menghitung nilai akhir pada input nilai.') }}
            </p>

            <form method="POST" action="{{ route('penilaian.bobot.update') }}" class="mt-3">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span
                            class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Sumatif (%)') }}</span>
                        <input type="number" name="bobot_sumatif" step="0.01" min="0" max="100" inputmode="decimal"
                            x-model.number="bobotSumatif" @input="hitungTotal()"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 text-center text-lg font-bold tabular-nums outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950" />
                    </label>

                    <label class="block">
                        <span
                            class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('STS (%)') }}</span>
                        <input type="number" name="bobot_sts" step="0.01" min="0" max="100" inputmode="decimal"
                            x-model.number="bobotSts" @input="hitungTotal()"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 text-center text-lg font-bold tabular-nums outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950" />
                    </label>
                </div>

                <p class="mt-2 flex items-center gap-2 text-[11px] font-semibold"
                    :class="total === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                    <i class="fas" :class="total === 100 ? 'fa-circle-check' : 'fa-triangle-exclamation'"></i>
                    <span x-text="`{{ __('Total') }}: ${total}%`"></span>
                    <span x-show="total !== 100" x-cloak>{{ __('— sebaiknya berjumlah 100%') }}</span>
                </p>

                <button type="submit"
                    class="mt-3 h-12 w-full rounded-2xl bg-emerald-600 text-sm font-bold text-white transition hover:bg-emerald-700 active:scale-[0.99]">
                    {{ __('Simpan bobot') }}
                </button>
            </form>
        </section>

        {{-- Tampilan --}}
        <section
            class="mt-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2">
                <i class="fas fa-palette text-emerald-600 dark:text-emerald-400"></i>
                <h2 class="text-sm font-bold">{{ __('Tampilan') }}</h2>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-2">
                @foreach ([['mode' => 'light', 'label' => __('Terang'), 'ikon' => 'fa-sun'], ['mode' => 'dark', 'label' => __('Gelap'), 'ikon' => 'fa-moon'], ['mode' => 'system', 'label' => __('Sistem'), 'ikon' => 'fa-circle-half-stroke']] as $pilihan)
                    <button type="button" @click="pilihTema('{{ $pilihan['mode'] }}')"
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

        {{-- Keamanan --}}
        <section
            class="mt-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <button type="button" @click="sandiTerbuka = !sandiTerbuka" class="flex w-full items-center gap-2">
                <i class="fas fa-lock text-emerald-600 dark:text-emerald-400"></i>
                <h2 class="flex-1 text-left text-sm font-bold">{{ __('Ubah kata sandi') }}</h2>
                <i class="fas text-slate-400" :class="sandiTerbuka ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <form x-cloak x-show="sandiTerbuka" x-transition method="POST" action="{{ route('settings.password.update') }}"
                class="mt-3 space-y-3">
                @csrf
                @method('put')

                <label class="block">
                    <span
                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Sandi sekarang') }}</span>
                    <input type="password" name="current_password" autocomplete="current-password" required
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950" />
                </label>

                <label class="block">
                    <span
                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Sandi baru') }}</span>
                    <input type="password" name="password" autocomplete="new-password" required
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950" />
                </label>

                <label class="block">
                    <span
                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Ulangi sandi baru') }}</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" required
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950" />
                </label>

                <button type="submit"
                    class="h-12 w-full rounded-2xl bg-slate-800 text-sm font-bold text-white transition hover:bg-slate-900 active:scale-[0.99] dark:bg-slate-700 dark:hover:bg-slate-600">
                    {{ __('Simpan sandi baru') }}
                </button>
            </form>
        </section>

        {{-- Aplikasi --}}
        <section
            class="mt-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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

                <a href="{{ route('guru.pwa.beranda') }}"
                    class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-3 py-3 text-sm font-semibold text-slate-600 transition active:scale-[0.98] dark:border-slate-700 dark:text-slate-300">
                    <i class="fas fa-house"></i>{{ __('Beranda') }}
                </a>
            </div>

            <dl class="mt-3 space-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                <div class="flex justify-between gap-2">
                    <dt>{{ __('Tahun ajaran aktif') }}</dt>
                    <dd class="font-semibold">{{ $tahunAjaran?->nama ?? __('belum dipilih') }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt>{{ __('Semester') }}</dt>
                    <dd class="font-semibold">{{ $semester ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt>{{ __('Aplikasi') }}</dt>
                    <dd class="font-semibold">{{ config('app.name') }}</dd>
                </div>
            </dl>
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
            {{ __('e-Raport Kurikulum Merdeka — aplikasi guru') }}
        </p>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('akunGuru', (konfigurasi) => ({
                bobotSumatif: konfigurasi.bobotSumatif,
                bobotSts: konfigurasi.bobotSts,
                total: 0,
                tema: window.pwaAppearance(),
                sandiTerbuka: konfigurasi.sandiTerbuka,

                siap() {
                    this.hitungTotal();
                },

                hitungTotal() {
                    this.total = Number(this.bobotSumatif || 0) + Number(this.bobotSts || 0);
                },

                pilihTema(mode) {
                    this.tema = mode;
                    window.pwaSetAppearance(mode);
                },
            }));
        });
    </script>
</x-layouts.pwa>
