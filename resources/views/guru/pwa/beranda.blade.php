@php
    $namaGuru = $guru?->nama ?? auth()->user()->name;
    $persen = $ringkasan['siswa'] > 0 ? (int) round(($ringkasan['lengkap'] / $ringkasan['siswa']) * 100) : 0;
@endphp

<x-layouts.pwa :title="__('Aplikasi Guru')" :subtitle="$tahunAjaran ? $tahunAjaran->nama.' • '.($semester ?: '-') : null">
    {{-- Kartu pembuka --}}
    <section
        class="rounded-3xl bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 p-5 text-white shadow-lg shadow-emerald-900/20">
        <p class="text-xs font-medium uppercase tracking-wide text-emerald-100/80">{{ __('Assalamualaikum') }}</p>
        <h1 class="mt-1 text-xl font-semibold leading-tight">{{ $namaGuru }}</h1>
        <p class="mt-1 text-sm text-emerald-50/90">
            {{ __('Input dan pantau nilai siswa langsung dari ponsel.') }}
        </p>

        @if ($tahunAjaran)
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                    <i class="fas fa-calendar-day mr-1"></i>{{ $tahunAjaran->nama }}
                </span>
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                    <i class="fas fa-clock mr-1"></i>{{ $semester ?: '-' }}
                </span>
                @if (! $tahunAjaran->is_active)
                    <span class="rounded-full bg-amber-400/90 px-3 py-1 text-xs font-semibold text-amber-950">
                        <i class="fas fa-lock mr-1"></i>{{ __('Tahun ajaran tidak aktif — hanya baca') }}
                    </span>
                @endif
            </div>
        @endif

        @if ($kontekSiap = $tahunAjaran && $guru)
            <a href="{{ route('guru.pwa.nilai') }}"
                class="mt-5 flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-emerald-700 shadow-sm transition active:scale-[0.99]">
                <i class="fas fa-pen-to-square"></i>
                {{ __('Input Nilai Sekarang') }}
            </a>
        @endif
    </section>

    @if (! $guru)
        <div class="mt-4 rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200">
            <p class="font-semibold">{{ __('Akun ini belum tertaut ke data guru.') }}</p>
            <p class="mt-1 leading-relaxed">
                {{ __('Minta admin menautkan akun Anda pada menu Data Guru supaya penugasan mengajar dan penilaian dapat dibuka.') }}
            </p>
        </div>
    @elseif (! $tahunAjaran)
        <div class="mt-4 rounded-3xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-200">
            <p class="font-semibold">{{ __('Tahun ajaran belum dipilih.') }}</p>
            <p class="mt-1 leading-relaxed">
                {{ __('Pilih tahun ajaran dan semester di dashboard, lalu kembali ke aplikasi ini.') }}
            </p>
            <a href="{{ route('dashboard') }}"
                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">
                <i class="fas fa-arrow-right"></i>{{ __('Buka Dashboard') }}
            </a>
        </div>
    @endif

    @if ($guru && $tahunAjaran)
        {{-- Ringkasan progres --}}
        <section class="mt-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('Progres nilai') }}</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums">
                        {{ $ringkasan['lengkap'] }}<span class="text-base font-medium text-slate-400">/{{ $ringkasan['siswa'] }}</span>
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa sudah lengkap sumatif + STS') }}</p>
                </div>
                <div class="relative h-16 w-16 shrink-0">
                    <svg viewBox="0 0 36 36" class="h-16 w-16 -rotate-90">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3.5"
                            class="text-slate-200 dark:text-slate-800" />
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3.5"
                            stroke-linecap="round" class="text-emerald-500"
                            stroke-dasharray="{{ $persen }} {{ 100 - $persen }}" />
                    </svg>
                    <span
                        class="absolute inset-0 flex items-center justify-center text-sm font-bold tabular-nums">{{ $persen }}%</span>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-2xl bg-slate-50 px-2 py-3 dark:bg-slate-800/60">
                    <p class="text-lg font-bold tabular-nums">{{ $ringkasan['penugasan'] }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Penugasan') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-2 py-3 dark:bg-slate-800/60">
                    <p class="text-lg font-bold tabular-nums">{{ $ringkasan['kelas'] }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Kelas') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-2 py-3 dark:bg-slate-800/60">
                    <p class="text-lg font-bold tabular-nums">{{ $ringkasan['terisi'] }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Siswa terisi') }}</p>
                </div>
            </div>
        </section>

        {{-- Perlu dilanjutkan --}}
        @if ($lanjutkan->isNotEmpty())
            <section class="mt-4">
                <div class="mb-2 flex items-center justify-between px-1">
                    <h2 class="text-sm font-semibold">{{ __('Lanjutkan pengisian') }}</h2>
                    <a href="{{ route('guru.pwa.nilai') }}"
                        class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Lihat semua') }}</a>
                </div>

                <div class="space-y-2">
                    @foreach ($lanjutkan as $baris)
                        @php
                            $item = $baris['mengajar'];
                            $jumlahSiswa = (int) $baris['jumlahSiswa'];
                            $lengkap = (int) $baris['lengkap'];
                            $persenItem = $jumlahSiswa > 0 ? (int) round(($lengkap / $jumlahSiswa) * 100) : 0;
                        @endphp
                        <a href="{{ route('guru.pwa.nilai.form', $item) }}"
                            class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                {{ $item->kelas?->nama ?? '—' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $item->mataPelajaran?->nama_mapel ?? '—' }}</p>
                                <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $persenItem }}%"></div>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ __(':lengkap dari :total siswa lengkap', ['lengkap' => $lengkap, 'total' => $jumlahSiswa]) }}
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-slate-300 dark:text-slate-600"></i>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Pintasan --}}
        <section class="mt-4 grid grid-cols-2 gap-2">
            <a href="{{ route('guru.pwa.ekskul') }}"
                class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                <i class="fas fa-medal text-lg text-amber-500"></i>
                {{ __('Nilai Ekskul') }}
            </a>
            <a href="{{ route('guru.pwa.akun') }}#bobot"
                class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                <i class="fas fa-scale-balanced text-lg text-blue-500"></i>
                {{ __('Bobot Nilai') }}
            </a>
            <a href="{{ route('guru.pelajaran') }}"
                class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                <i class="fas fa-chalkboard-user text-lg text-emerald-500"></i>
                {{ __('Pelajaran Saya') }}
            </a>
            <button type="button" @click="pasang()"
                class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-left text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                <i class="fas fa-mobile-screen-button text-lg text-slate-500"></i>
                {{ __('Pasang Aplikasi') }}
            </button>
        </section>
    @endif
</x-layouts.pwa>
