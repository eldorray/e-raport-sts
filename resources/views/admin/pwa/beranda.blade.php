@php
    $namaAdmin = auth()->user()->name;
    $persen = $ringkasan['persen'];
@endphp

<x-layouts.pwa :title="__('Aplikasi Admin')" :daftar-tahun="$daftarTahun">
    {{-- Kartu pembuka --}}
    <section
        class="rounded-3xl bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 p-5 text-white shadow-lg shadow-emerald-900/20">
        <p class="text-xs font-medium uppercase tracking-wide text-emerald-100/80">{{ __('Assalamualaikum') }}</p>
        <h1 class="mt-1 text-xl font-semibold leading-tight">{{ $namaAdmin }}</h1>
        <p class="mt-1 text-sm text-emerald-50/90">
            {{ __('Pantau kelengkapan nilai seluruh kelas langsung dari ponsel.') }}
        </p>

        @if ($tahunAjaran)
            <button type="button" @click="bukaTahun()" aria-haspopup="dialog"
                aria-label="{{ __('Ganti tahun ajaran') }}"
                class="mt-4 flex flex-wrap items-center gap-2 text-left transition active:scale-[0.99]">
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                    <i class="fas fa-calendar-day mr-1"></i>{{ $tahunAjaran->nama }}
                </span>
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                    <i class="fas fa-clock mr-1"></i>{{ $semester ?: '-' }}
                    <i class="fas fa-chevron-down ml-1 text-[9px]"></i>
                </span>
                @if (! $tahunAjaran->is_active)
                    <span class="rounded-full bg-amber-400/90 px-3 py-1 text-xs font-semibold text-amber-950">
                        <i class="fas fa-lock mr-1"></i>{{ __('Tahun ajaran tidak aktif') }}
                    </span>
                @endif
            </button>
        @endif

        <div class="mt-5 grid grid-cols-2 gap-2">
            <a href="{{ route('koreksi-nilai.index') }}"
                class="flex h-12 items-center justify-center gap-2 rounded-2xl bg-white px-3 text-sm font-bold text-emerald-700 shadow-sm transition active:scale-[0.99]">
                <i class="fas fa-pen-to-square"></i>{{ __('Koreksi Nilai') }}
            </a>
            <a href="{{ route('rapor.index') }}"
                class="flex h-12 items-center justify-center gap-2 rounded-2xl bg-white/15 px-3 text-sm font-bold text-white transition active:scale-[0.99]">
                <i class="fas fa-file-lines"></i>{{ __('Cetak Rapor') }}
            </a>
        </div>
    </section>

    @if (! $tahunAjaran)
        <x-pwa.tahun-kosong class="mt-4" :pesan="__('Pilih tahun ajaran dan semester untuk melihat kelengkapan nilai.')" />
    @else
        {{-- Kelengkapan keseluruhan --}}
        <section
            class="mt-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('Kelengkapan nilai') }}</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums">
                        {{ number_format($ringkasan['lengkap'], 0, ',', '.') }}<span
                            class="text-base font-medium text-slate-400">/{{ number_format($ringkasan['target'], 0, ',', '.') }}</span>
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __('nilai siswa sudah lengkap sumatif + STS') }}</p>
                </div>
                <div class="relative h-16 w-16 shrink-0">
                    <svg viewBox="0 0 36 36" class="h-16 w-16 -rotate-90" aria-hidden="true">
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
                    <p class="text-lg font-bold tabular-nums">{{ $ringkasan['kelas'] }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Kelas') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-2 py-3 dark:bg-slate-800/60">
                    <p class="text-lg font-bold tabular-nums">{{ $ringkasan['penugasan'] }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Penugasan') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-2 py-3 dark:bg-slate-800/60">
                    <p class="text-lg font-bold tabular-nums">{{ $ringkasan['siswa'] }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Siswa') }}</p>
                </div>
            </div>
        </section>

        {{-- Guru dengan nilai belum lengkap --}}
        @if ($guruBelumLengkap->isNotEmpty())
            <section class="mt-4">
                <div class="mb-2 flex items-baseline justify-between px-1">
                    <h2 class="text-sm font-semibold">{{ __('Guru dengan nilai belum lengkap') }}</h2>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __(':jumlah guru', ['jumlah' => $jumlahGuruBelumLengkap]) }}</span>
                </div>

                <ul
                    class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
                    @foreach ($guruBelumLengkap as $baris)
                        <li class="flex items-center gap-3 px-3 py-2.5">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-300">
                                <i class="fas fa-user-clock text-sm"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $baris['nama'] }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ __(':belum dari :total penugasan belum lengkap', ['belum' => $baris['belum'], 'total' => $baris['penugasan']]) }}
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold tabular-nums text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">{{ $baris['belum'] }}</span>
                        </li>
                    @endforeach
                </ul>

                @if ($jumlahGuruBelumLengkap > $guruBelumLengkap->count())
                    <p class="mt-2 px-1 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ __('dan :jumlah guru lainnya — lihat per kelas di bawah.', ['jumlah' => $jumlahGuruBelumLengkap - $guruBelumLengkap->count()]) }}
                    </p>
                @endif
            </section>
        @elseif ($ringkasan['target'] > 0)
            <div
                class="mt-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                <i class="fas fa-circle-check text-lg"></i>
                {{ __('Semua guru sudah melengkapi nilai.') }}
            </div>
        @endif

        {{-- Progres per kelas --}}
        <section class="mt-4">
            <div class="mb-2 flex items-baseline justify-between px-1">
                <h2 class="text-sm font-semibold">{{ __('Progres per kelas') }}</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Ketuk untuk koreksi') }}</span>
            </div>

            @if ($perKelas->isEmpty())
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 text-center text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <i class="fas fa-layer-group mb-2 text-2xl text-slate-300 dark:text-slate-600"></i>
                    <p class="font-semibold">{{ __('Belum ada kelas pada tahun ajaran ini.') }}</p>
                    <a href="{{ route('kelas.index') }}"
                        class="mt-3 inline-flex h-11 items-center gap-2 rounded-2xl bg-emerald-600 px-4 text-sm font-semibold text-white transition active:scale-[0.99]">
                        <i class="fas fa-plus"></i>{{ __('Kelola Kelas') }}
                    </a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($perKelas as $baris)
                        {{-- Kelas tanpa penugasan tidak punya nilai untuk dikoreksi, jadi tidak ditautkan --}}
                        @if ($baris['penugasan'] > 0)
                            <a href="{{ route('koreksi-nilai.index', ['kelas' => $baris['kelas']->id]) }}"
                                class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                                <x-pwa.admin-baris-kelas :baris="$baris" />
                                <i class="fas fa-chevron-right shrink-0 text-slate-300 dark:text-slate-600"></i>
                            </a>
                        @else
                            <div
                                class="flex items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white/60 p-3 dark:border-slate-800 dark:bg-slate-900/60">
                                <x-pwa.admin-baris-kelas :baris="$baris" />
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Pintasan --}}
    <section class="mt-4 grid grid-cols-2 gap-2">
        <a href="{{ route('tahun-ajaran-baru.create') }}"
            class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
            <i class="fas fa-forward text-lg text-blue-500"></i>
            {{ __('Tahun Ajaran Baru') }}
        </a>
        <a href="{{ route('mengajar.index') }}"
            class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
            <i class="fas fa-person-chalkboard text-lg text-emerald-500"></i>
            {{ __('Mengajar') }}
        </a>
        <a href="{{ route('siswa.index') }}"
            class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
            <i class="fas fa-user-graduate text-lg text-amber-500"></i>
            {{ __('Siswa') }}
        </a>
        <a href="{{ route('admin.pwa.menu') }}"
            class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
            <i class="fas fa-grip text-lg text-slate-500"></i>
            {{ __('Semua Menu') }}
        </a>
    </section>
</x-layouts.pwa>
