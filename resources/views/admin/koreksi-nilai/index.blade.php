<x-layouts.app :title="__('Koreksi Nilai')">
    <div class="mb-6 flex flex-col gap-1">
        <p class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-400">{{ __('Penilaian') }}</p>
        <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Koreksi Nilai') }}</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Lihat dan betulkan nilai Sumatif/STS per kelas dan mapel. Koreksi disimpan atas nama guru pengampu dan dicatat atas nama Anda.') }}
        </p>
    </div>

    @if (!$tahunAjaran)
        <div
            class="bg-yellow-50 p-4 text-sm text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-100 rounded-2xl">
            {{ __('Pilih tahun ajaran terlebih dahulu di dashboard.') }}
        </div>
    @else
        @unless ($tahunAjaran->is_active)
            <div
                class="mb-4 flex items-start gap-2 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-200 rounded-2xl">
                <i class="fas fa-lock mt-0.5" aria-hidden="true"></i>
                <span>{{ __('Tahun ajaran :tahun sudah ditutup. Koreksi tetap bisa disimpan dan akan tercatat atas nama Anda.', ['tahun' => $tahunAjaran->label()]) }}</span>
            </div>
        @endunless

        <form method="GET" action="{{ route('koreksi-nilai.index') }}"
            class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end">
            <div class="flex flex-1 flex-col gap-1 sm:max-w-xs">
                <label for="filter-kelas" class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                    {{ __('Kelas') }} • {{ $tahunAjaran->label() }}
                </label>
                <select id="filter-kelas" name="kelas" onchange="this.form.submit()"
                    class="isian h-11 w-full px-3 text-base sm:text-sm">
                    <option value="">{{ __('Semua kelas') }}</option>
                    @foreach ($semuaKelas as $kelas)
                        <option value="{{ $kelas->id }}" @selected($kelasFilter === $kelas->id)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </div>
            <noscript>
                <button type="submit"
                    class="tombol tombol-aksen h-11 px-4 text-sm font-semibold">{{ __('Tampilkan') }}</button>
            </noscript>
        </form>

        @if ($kelasTampil->isEmpty())
            <div
                class="kartu p-6 text-center text-sm text-zinc-600 dark:text-zinc-300">
                {{ $kelasFilter ? __('Kelas tidak ditemukan pada tahun ajaran ini.') : __('Belum ada kelas pada tahun ajaran ini.') }}
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($kelasTampil as $kelas)
                    @php
                        $daftarMengajar = $mengajarPerKelas->get($kelas->id, collect());
                        $totalSiswa = (int) $kelas->siswas_count;
                    @endphp
                    {{-- min-w-0: nama mapel panjang (truncate) tidak boleh melebarkan kolom grid melewati layar HP --}}
                    <section
                        class="kartu min-w-0">
                        <header
                            class="flex items-center justify-between gap-3 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                            <h2 class="text-base font-semibold text-zinc-800 dark:text-zinc-100">{{ $kelas->nama }}</h2>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ __(':jumlah siswa', ['jumlah' => $totalSiswa]) }}
                            </span>
                        </header>

                        @if ($daftarMengajar->isEmpty())
                            <p class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Belum ada jadwal mengajar di kelas ini.') }}
                            </p>
                        @else
                            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($daftarMengajar as $item)
                                    @php
                                        $lengkap = (int) $lengkapPerMengajar->get($item->id, 0);
                                        $persen = $totalSiswa > 0 ? min(100, (int) round(($lengkap / $totalSiswa) * 100)) : 0;
                                        $warnaBar = match (true) {
                                            $totalSiswa > 0 && $lengkap >= $totalSiswa => 'bg-emerald-500',
                                            $lengkap > 0 => 'bg-accent',
                                            default => 'bg-zinc-300 dark:bg-zinc-600',
                                        };
                                    @endphp
                                    <li>
                                        <a href="{{ route('koreksi-nilai.show', $item) }}"
                                            class="flex min-h-14 items-center gap-3 px-4 py-3 transition-colors hover:bg-zinc-50 focus:bg-zinc-50 focus:outline-none dark:hover:bg-zinc-700/40 dark:focus:bg-zinc-700/40">
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                                    {{ $item->mataPelajaran->nama_mapel ?? '—' }}
                                                </p>
                                                @if ($item->guru)
                                                    <p class="truncate text-xs text-zinc-600 dark:text-zinc-400">
                                                        {{ $item->guru->nama }}</p>
                                                @else
                                                    <p class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                                                        {{ __('Belum ada guru') }}</p>
                                                @endif
                                                <div class="mt-2 flex items-center gap-2">
                                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700"
                                                        role="progressbar" aria-valuemin="0"
                                                        aria-valuemax="{{ $totalSiswa }}" aria-valuenow="{{ $lengkap }}"
                                                        aria-label="{{ __('Nilai lengkap') }}">
                                                        <div class="h-full rounded-full {{ $warnaBar }}"
                                                            style="width: {{ $persen }}%"></div>
                                                    </div>
                                                    <span
                                                        class="shrink-0 text-xs tabular-nums text-zinc-600 dark:text-zinc-300">
                                                        <span class="font-semibold">{{ $lengkap }}/{{ $totalSiswa }}</span>
                                                        {{ __('lengkap') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <span
                                                class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-accent-strong dark:text-blue-300">
                                                <span class="hidden sm:inline">{{ __('Koreksi') }}</span>
                                                <i class="fas fa-chevron-right text-[11px]" aria-hidden="true"></i>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    @endif
</x-layouts.app>
