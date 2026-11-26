<x-layouts.app>
    @php
        // Kelas yang boleh dicetak sekaligus: kelas terpilih (admin) atau kelas wali (guru); $kelasList sudah dibatasi sesuai peran
        $kelasCetak = $kelasId ? $kelasList->firstWhere('id', $kelasId) : null;
    @endphp
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-400">Master Data</p>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">Cetak Rapor Siswa</h1>
        </div>
        @if ($kelasCetak)
            <a href="{{ route('rapor.print-kelas', $kelasCetak) }}" target="_blank" rel="noopener"
                class="tombol tombol-aksen gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11">
                <i class="fas fa-print text-xs"></i> {{ __('Cetak rapor satu kelas') }} ({{ $kelasCetak->nama }})
            </a>
        @elseif (! ($isGuru ?? false) && $kelasList->isNotEmpty())
            <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Pilih kelas untuk mencetak rapor seluruh siswanya sekaligus.') }}</p>
        @endif
    </div>

    <div class="mb-4 grid gap-3 max-md:grid-cols-2 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex min-w-0 flex-col gap-1">
            <label class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Tingkat</label>
            <form id="filterForm" method="GET" class="contents">
                <select name="tingkat" onchange="document.getElementById('filterForm').submit()"
                    class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base"
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
            <label class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Kelas</label>
            <form id="kelasForm" method="GET" class="contents">
                <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                <select name="kelas_id" onchange="document.getElementById('kelasForm').submit()"
                    class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base"
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
                <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-zinc-400"
                    aria-hidden="true"></i>
                <input id="rapor-cari-siswa" type="search" x-model="cari" autocomplete="off"
                    placeholder="{{ __('Cari nama atau NISN…') }}"
                    class="isian h-11 w-full pl-10 pr-3 text-base">
            </div>
        @endif

        @if ($siswas->isEmpty())
            <div
                class="kartu p-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Belum ada data.</div>
        @else
            <ul class="space-y-3">
                @foreach ($siswas as $index => $siswa)
                    <li x-show="cocok($el)" data-cari="{{ \Illuminate\Support\Str::lower($siswa->nama . ' ' . $siswa->nisn) }}"
                        class="kartu p-4">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $siswa->nama }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ __('NISN') }} {{ $siswa->nisn ?? '—' }} •
                                    {{ strtoupper(substr($siswa->jenis_kelamin ?? '-', 0, 1)) }}
                                </p>
                                @if ($siswa->tempat_lahir || $siswa->tanggal_lahir)
                                    <p class="truncate text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                                target="_blank"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-accent/10 px-3 text-sm font-semibold text-accent-strong active:bg-accent/10 dark:bg-blue-900/40 dark:text-blue-100">
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
                class="rounded-2xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                {{ __('Tidak ada siswa yang cocok.') }}</p>
        @endif
    </div>

    <div
        class="kartu px-6 pb-6overflow-hidden max-md:hidden">
        <div class="overflow-x-auto">
            <table id="rapor-table"
                class="tabel p-6 min-w-full text-sm text-zinc-700 dark:text-zinc-200">
                <thead
                   >
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">NISN</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">L/P</th>
                        <th class="px-3 py-3">TTL</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
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
                                <div class="flex items-center gap-1.5 whitespace-nowrap">

                                    <a href="{{ route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                                        target="_blank"
                                        class="pil-aksi">
                                        <i class="fas fa-file-lines text-[11px]"></i> Rapor
                                    </a>
                                    <a href="{{ $siswa->kelas_id ? route('rapor.ledger', ['kelas' => $siswa->kelas_id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) : '#' }}"
                                        target="_blank"
                                        class="pil-aksi">
                                        <i class="fas fa-table-list text-[11px]"></i> Ledger
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
