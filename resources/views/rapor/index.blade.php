<x-layouts.app>
    @php
        // Kelas yang boleh dicetak sekaligus: kelas terpilih (admin) atau kelas wali (guru); $kelasList sudah dibatasi sesuai peran
        $kelasCetak = $kelasId ? $kelasList->firstWhere('id', $kelasId) : null;
    @endphp
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Master Data</p>
            <h1 class="text-2xl font-bold text-gray-800 max-md:text-xl dark:text-gray-100">Cetak Rapor Siswa</h1>
        </div>
        @if ($kelasCetak)
            <a href="{{ route('rapor.print-kelas', $kelasCetak) }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 max-md:min-h-11 max-md:justify-center">
                <i class="fas fa-print text-xs"></i> {{ __('Cetak rapor satu kelas') }} ({{ $kelasCetak->nama }})
            </a>
        @elseif (! ($isGuru ?? false) && $kelasList->isNotEmpty())
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Pilih kelas untuk mencetak rapor seluruh siswanya sekaligus.') }}</p>
        @endif
    </div>

    <div class="mb-4 grid gap-3 max-md:grid-cols-2 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex min-w-0 flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Tingkat</label>
            <form id="filterForm" method="GET" class="contents">
                <select name="tingkat" onchange="document.getElementById('filterForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm max-md:h-11 max-md:bg-white max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @if ($isGuru ?? false) disabled @endif>
                    @unless ($isGuru ?? false)
                        <option value="">Semua</option>
                    @endunless
                    @foreach ($tingkatOptions as $opt)
                        @if (($isGuru ?? false) && $kelasList->where('tingkat', $opt)->isEmpty())
                            @continue
                        @endif
                        <option value="{{ $opt }}" @selected($tingkat == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @if ($isGuru ?? false)
                    <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                @endif
            </form>
        </div>
        <div class="flex min-w-0 flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</label>
            <form id="kelasForm" method="GET" class="contents">
                <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                <select name="kelas_id" onchange="document.getElementById('kelasForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm max-md:h-11 max-md:bg-white max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @if ($isGuru ?? false) disabled @endif>
                    @unless ($isGuru ?? false)
                        <option value="">Semua</option>
                    @endunless
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected((string) $kelasId === (string) $kelas->id)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
                @if ($isGuru ?? false)
                    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                @endif
            </form>
        </div>
    </div>

    {{-- Daftar kartu versi HP; mulai md tetap tabel (DataTables) --}}
    <div class="md:hidden" x-data="{
        cari: '',
        cocok(el) {
            const kata = this.cari.trim().toLowerCase();
            return kata === '' || el.dataset.cari.includes(kata);
        },
        get kosong() {
            const kata = this.cari.trim().toLowerCase();
            return kata !== '' && ![...this.$root.querySelectorAll('[data-cari]')].some((el) => el.dataset.cari.includes(kata));
        },
    }">
        @if ($siswas->count() > 8)
            <div class="relative mb-3">
                <label for="rapor-cari-siswa" class="sr-only">{{ __('Cari siswa') }}</label>
                <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"
                    aria-hidden="true"></i>
                <input id="rapor-cari-siswa" type="search" x-model="cari" autocomplete="off"
                    placeholder="{{ __('Cari nama atau NISN…') }}"
                    class="h-11 w-full rounded-xl border border-gray-200 bg-white pl-10 pr-3 text-base text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        @endif

        @if ($siswas->isEmpty())
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Belum ada data.</div>
        @else
            <ul class="space-y-3">
                @foreach ($siswas as $index => $siswa)
                    <li x-show="cocok($el)" data-cari="{{ \Illuminate\Support\Str::lower($siswa->nama . ' ' . $siswa->nisn) }}"
                        class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->nama }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('NISN') }} {{ $siswa->nisn ?? '—' }} •
                                    {{ strtoupper(substr($siswa->jenis_kelamin ?? '-', 0, 1)) }}
                                </p>
                                @if ($siswa->tempat_lahir || $siswa->tanggal_lahir)
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                                target="_blank"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-50 px-3 text-sm font-semibold text-blue-700 active:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-100">
                                <i class="fas fa-file-lines text-xs" aria-hidden="true"></i> Rapor
                            </a>
                            <a href="{{ $siswa->kelas_id ? route('rapor.ledger', ['kelas' => $siswa->kelas_id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) : '#' }}"
                                target="_blank"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-50 px-3 text-sm font-semibold text-emerald-700 active:bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-100">
                                <i class="fas fa-table-list text-xs" aria-hidden="true"></i> Ledger
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
            <p style="display: none" x-show="kosong"
                class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                {{ __('Tidak ada siswa yang cocok.') }}</p>
        @endif
    </div>

    <div
        class="px-6 pb-6overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm max-md:hidden dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table id="rapor-table"
                class="p-6 min-w-full divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">NISN</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">L/P</th>
                        <th class="px-3 py-3">TTL</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($siswas as $index => $siswa)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $siswa->nisn ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $siswa->nama }}</td>
                            <td class="px-3 py-3">{{ strtoupper(substr($siswa->jenis_kelamin ?? '-', 0, 1)) }}</td>
                            <td class="px-3 py-3 text-xs">
                                {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">

                                    <a href="{{ route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">
                                        <i class="fas fa-file-lines text-[11px]"></i> Rapor
                                    </a>
                                    <a href="{{ $siswa->kelas_id ? route('rapor.ledger', ['kelas' => $siswa->kelas_id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) : '#' }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100">
                                        <i class="fas fa-table-list text-[11px]"></i> Ledger
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
