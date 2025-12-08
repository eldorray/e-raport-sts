<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Master Data</p>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Cetak Rapor Siswa</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ optional($siswas->first()) ? route('rapor.print', ['siswa' => $siswas->first()->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) : '#' }}"
                @if (!$siswas->first()) aria-disabled="true" @endif
                class="inline-flex items-center gap-2 rounded-lg bg-sky-100 px-3 py-2 text-xs font-semibold text-sky-700 shadow-sm dark:bg-sky-900/30 dark:text-sky-100">
                <i class="fas fa-file-export text-xs"></i> Semua Rapor
            </a>
            @if (($role ?? null) === 'admin')
                <a href="#"
                    class="inline-flex items-center gap-2 rounded-lg bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 shadow-sm dark:bg-rose-900/30 dark:text-rose-100">
                    <i class="fas fa-list-ol text-xs"></i> Semua Nilai
                </a>
                <a href="#"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-100 px-3 py-2 text-xs font-semibold text-indigo-700 shadow-sm dark:bg-indigo-900/30 dark:text-indigo-100">
                    <i class="fas fa-file-alt text-xs"></i> Sampul
                </a>
                <a href="#"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-100 px-3 py-2 text-xs font-semibold text-green-700 shadow-sm dark:bg-green-900/30 dark:text-green-100">
                    <i class="fas fa-book-open text-xs"></i> Identitas
                </a>
            @endif
        </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Tingkat</label>
            <form id="filterForm" method="GET" class="contents">
                <select name="tingkat" onchange="document.getElementById('filterForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Semua</option>
                    @foreach ($tingkatOptions as $opt)
                        <option value="{{ $opt }}" @selected($tingkat == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</label>
            <form id="kelasForm" method="GET" class="contents">
                <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                <select name="kelas_id" onchange="document.getElementById('kelasForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Semua</option>
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected((string) $kelasId === (string) $kelas->id)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table
                class="min-w-full divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
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
                                    @if (($role ?? null) === 'admin')
                                        <a href="#"
                                            class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-100">
                                            <i class="fas fa-clipboard-list text-[11px]"></i> Nilai
                                        </a>
                                    @endif
                                    <a href="{{ route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">
                                        <i class="fas fa-file-lines text-[11px]"></i> Rapor
                                    </a>
                                    @if (($role ?? null) === 'admin')
                                        <a href="#"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-100">
                                            <i class="fas fa-table text-[11px]"></i> Rekap
                                        </a>
                                        <button type="button"
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-100">
                                            <i class="fas fa-ellipsis-h text-[11px]"></i>
                                        </button>
                                    @endif
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
