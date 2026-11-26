<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-400">Rapor</p>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">Raport Tahfidz Al-Qur'an</h1>
            @if ($guru)
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Selamat datang, <span
                        class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $guru->nama }}</span>
                </p>
            @endif
        </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Kelas</label>
            <form id="kelasForm" method="GET" class="contents">
                <select name="kelas_id" onchange="document.getElementById('kelasForm').submit()"
                    class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected((string) $kelasId === (string) $kelas->id)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>


    @if (!$canEdit)
        <div class="mb-4 bg-amber-50 p-4 dark:bg-amber-900/30 rounded-2xl">
            <div class="flex items-center gap-2 text-sm font-semibold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __('Tahun ajaran yang dipilih tidak aktif. Anda hanya dapat melihat data, tidak dapat mengubah atau menambah data.') }}
            </div>
        </div>
    @endif

    {{-- Daftar kartu versi HP; mulai md tetap tabel --}}
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
                <label for="tahfidz-cari-siswa" class="sr-only">{{ __('Cari siswa') }}</label>
                <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-zinc-400"
                    aria-hidden="true"></i>
                <input id="tahfidz-cari-siswa" type="search" x-model="cari" autocomplete="off"
                    placeholder="{{ __('Cari nama atau NISN…') }}"
                    class="isian h-11 w-full pl-10 pr-3 text-base">
            </div>
        @endif

        @if ($siswas->isEmpty())
            <div
                class="kartu p-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                @if ($kelasId)
                    Belum ada siswa di kelas ini.
                @else
                    Pilih kelas terlebih dahulu.
                @endif
            </div>
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
                            </div>
                        </div>

                        <dl class="mt-3 grid grid-cols-2 gap-3">
                            @foreach ([
                                ['label' => 'Juz 30', 'jumlah' => $siswa->jumlah_surah_30, 'total' => 38, 'bar' => 'bg-green-500'],
                                ['label' => 'Juz 29', 'jumlah' => $siswa->jumlah_surah_29, 'total' => 11, 'bar' => 'bg-purple-500'],
                            ] as $capaian)
                                <div>
                                    <div class="flex items-baseline justify-between gap-2 text-xs">
                                        <dt class="font-semibold text-zinc-600 dark:text-zinc-300">{{ $capaian['label'] }}</dt>
                                        <dd class="tabular-nums text-zinc-700 dark:text-zinc-200">
                                            <span class="font-semibold">{{ $capaian['jumlah'] }}</span>/{{ $capaian['total'] }}</dd>
                                    </div>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700" aria-hidden="true">
                                        <div class="h-full rounded-full {{ $capaian['bar'] }}"
                                            style="width: {{ min(100, (int) round(($capaian['jumlah'] / $capaian['total']) * 100)) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </dl>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ([30 => 'amber', 29 => 'purple'] as $juzKartu => $warnaKartu)
                                <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => $juzKartu]) }}"
                                    @class([
                                        'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-3 text-sm font-semibold',
                                        'bg-amber-50 text-amber-700 active:bg-amber-100 dark:bg-amber-900/40 dark:text-amber-100' => $canEdit && $warnaKartu === 'amber',
                                        'bg-purple-50 text-purple-700 active:bg-purple-100 dark:bg-purple-900/40 dark:text-purple-100' => $canEdit && $warnaKartu === 'purple',
                                        'bg-zinc-50 text-zinc-600 active:bg-zinc-100 dark:bg-zinc-700 dark:text-zinc-300' => ! $canEdit,
                                    ])>
                                    <i class="fas {{ $canEdit ? 'fa-edit' : 'fa-eye' }} text-xs" aria-hidden="true"></i>
                                    {{ $canEdit ? 'Isi' : 'Lihat' }} Juz {{ $juzKartu }}
                                </a>
                            @endforeach
                            @if ($siswa->tahfidz)
                                <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester, 'juz' => 30]) }}"
                                    target="_blank"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-accent/10 px-3 text-sm font-semibold text-accent-strong active:bg-accent/10 dark:bg-blue-900/40 dark:text-blue-100">
                                    <i class="fas fa-print text-xs" aria-hidden="true"></i> Cetak 30
                                </a>
                                <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester, 'juz' => 29]) }}"
                                    target="_blank"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-indigo-50 px-3 text-sm font-semibold text-indigo-700 active:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-100">
                                    <i class="fas fa-print text-xs" aria-hidden="true"></i> Cetak 29
                                </a>
                            @endif
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
        class="kartu overflow-hidden max-md:hidden">
        <div class="overflow-x-auto">
            <table
                class="tabel p-6 min-w-full text-sm text-zinc-700 dark:text-zinc-200">
                <thead
                   >
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">NISN</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">L/P</th>
                        <th class="px-3 py-3 text-center">Juz 30</th>
                        <th class="px-3 py-3 text-center">Juz 29</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($siswas as $index => $siswa)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $siswa->nisn ?? '—' }}</td>
                            <td class="px-3 py-3 font-medium">{{ $siswa->nama }}</td>
                            <td class="px-3 py-3">{{ strtoupper(substr($siswa->jenis_kelamin ?? '-', 0, 1)) }}</td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $siswa->jumlah_surah_30 > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                    {{ $siswa->jumlah_surah_30 }} / 38
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $siswa->jumlah_surah_29 > 0 ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                    {{ $siswa->jumlah_surah_29 }} / 11
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-1.5 whitespace-nowrap">
                                    {{-- Juz 30 buttons --}}
                                    @if ($canEdit)
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 30]) }}"
                                            class="pil-aksi">
                                            <i class="fas fa-edit text-[11px]"></i> Juz 30
                                        </a>
                                    @else
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 30]) }}"
                                            class="pil-aksi">
                                            <i class="fas fa-eye text-[11px]"></i> Juz 30
                                        </a>
                                    @endif

                                    {{-- Juz 29 buttons --}}
                                    @if ($canEdit)
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 29]) }}"
                                            class="pil-aksi">
                                            <i class="fas fa-edit text-[11px]"></i> Juz 29
                                        </a>
                                    @else
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 29]) }}"
                                            class="pil-aksi">
                                            <i class="fas fa-eye text-[11px]"></i> Juz 29
                                        </a>
                                    @endif

                                    {{-- Print buttons --}}
                                    @if ($siswa->tahfidz)
                                        <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester, 'juz' => 30]) }}"
                                            target="_blank"
                                            class="pil-aksi">
                                            <i class="fas fa-print text-[11px]"></i> Cetak 30
                                        </a>
                                        <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester, 'juz' => 29]) }}"
                                            target="_blank"
                                            class="pil-aksi">
                                            <i class="fas fa-print text-[11px]"></i> Cetak 29
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                @if ($kelasId)
                                    Belum ada siswa di kelas ini.
                                @else
                                    Pilih kelas terlebih dahulu.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
