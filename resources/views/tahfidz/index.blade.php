<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rapor</p>
            <h1 class="text-2xl font-bold text-gray-800 max-md:text-xl dark:text-gray-100">Raport Tahfidz Al-Qur'an</h1>
            @if ($guru)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Selamat datang, <span
                        class="font-semibold text-gray-800 dark:text-gray-200">{{ $guru->nama }}</span>
                </p>
            @endif
        </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</label>
            <form id="kelasForm" method="GET" class="contents">
                <select name="kelas_id" onchange="document.getElementById('kelasForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm max-md:h-11 max-md:bg-white max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected((string) $kelasId === (string) $kelas->id)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>


    @if (!$canEdit)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/30">
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
                <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"
                    aria-hidden="true"></i>
                <input id="tahfidz-cari-siswa" type="search" x-model="cari" autocomplete="off"
                    placeholder="{{ __('Cari nama atau NISN…') }}"
                    class="h-11 w-full rounded-xl border border-gray-200 bg-white pl-10 pr-3 text-base text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        @endif

        @if ($siswas->isEmpty())
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
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
                            </div>
                        </div>

                        <dl class="mt-3 grid grid-cols-2 gap-3">
                            @foreach ([
                                ['label' => 'Juz 30', 'jumlah' => $siswa->jumlah_surah_30, 'total' => 38, 'bar' => 'bg-green-500'],
                                ['label' => 'Juz 29', 'jumlah' => $siswa->jumlah_surah_29, 'total' => 11, 'bar' => 'bg-purple-500'],
                            ] as $capaian)
                                <div>
                                    <div class="flex items-baseline justify-between gap-2 text-xs">
                                        <dt class="font-semibold text-gray-600 dark:text-gray-300">{{ $capaian['label'] }}</dt>
                                        <dd class="tabular-nums text-gray-700 dark:text-gray-200">
                                            <span class="font-semibold">{{ $capaian['jumlah'] }}</span>/{{ $capaian['total'] }}</dd>
                                    </div>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700" aria-hidden="true">
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
                                        'bg-gray-50 text-gray-600 active:bg-gray-100 dark:bg-gray-700 dark:text-gray-300' => ! $canEdit,
                                    ])>
                                    <i class="fas {{ $canEdit ? 'fa-edit' : 'fa-eye' }} text-xs" aria-hidden="true"></i>
                                    {{ $canEdit ? 'Isi' : 'Lihat' }} Juz {{ $juzKartu }}
                                </a>
                            @endforeach
                            @if ($siswa->tahfidz)
                                <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester, 'juz' => 30]) }}"
                                    target="_blank"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-50 px-3 text-sm font-semibold text-blue-700 active:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-100">
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
                class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                {{ __('Tidak ada siswa yang cocok.') }}</p>
        @endif
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm max-md:hidden dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table
                class="p-6 min-w-full divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
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
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($siswas as $index => $siswa)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $siswa->nisn ?? '—' }}</td>
                            <td class="px-3 py-3 font-medium">{{ $siswa->nama }}</td>
                            <td class="px-3 py-3">{{ strtoupper(substr($siswa->jenis_kelamin ?? '-', 0, 1)) }}</td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $siswa->jumlah_surah_30 > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $siswa->jumlah_surah_30 }} / 38
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $siswa->jumlah_surah_29 > 0 ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $siswa->jumlah_surah_29 }} / 11
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">
                                    {{-- Juz 30 buttons --}}
                                    @if ($canEdit)
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 30]) }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-900/40 dark:text-amber-100">
                                            <i class="fas fa-edit text-[11px]"></i> Juz 30
                                        </a>
                                    @else
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 30]) }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                            <i class="fas fa-eye text-[11px]"></i> Juz 30
                                        </a>
                                    @endif

                                    {{-- Juz 29 buttons --}}
                                    @if ($canEdit)
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 29]) }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700 hover:bg-purple-100 dark:bg-purple-900/40 dark:text-purple-100">
                                            <i class="fas fa-edit text-[11px]"></i> Juz 29
                                        </a>
                                    @else
                                        <a href="{{ route('tahfidz.show', ['siswa' => $siswa->id, 'juz' => 29]) }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                            <i class="fas fa-eye text-[11px]"></i> Juz 29
                                        </a>
                                    @endif

                                    {{-- Print buttons --}}
                                    @if ($siswa->tahfidz)
                                        <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester, 'juz' => 30]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-100">
                                            <i class="fas fa-print text-[11px]"></i> Cetak 30
                                        </a>
                                        <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester, 'juz' => 29]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-100">
                                            <i class="fas fa-print text-[11px]"></i> Cetak 29
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
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
